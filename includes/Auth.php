<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Enterprise Authentication & Credential Security Manager
 * Implements Bcrypt hashing (cost 12), session versioning & brute-force protection
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

class Auth {

    /**
     * Authenticate user by username or email
     */
    public static function login(string $identifier, string $password): array {
        $db = Database::getConnection();
        $identifier = trim($identifier);

        // 1. Fetch user by username or email
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'Invalid username/email or password.'];
        }

        // 2. Check if account is temporarily locked due to failed attempts
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'error' => "Account temporarily locked due to failed attempts. Try again in {$remaining} minutes."];
        }

        // 3. Verify Bcrypt password hash
        if (!password_verify($password, $user['password_hash'])) {
            // Increment failed attempts
            $failed = (int)$user['failed_login_attempts'] + 1;
            $lockout = null;
            if ($failed >= MAX_LOGIN_ATTEMPTS) {
                $lockout = date('Y-m-d H:i:s', strtotime('+' . LOGIN_LOCKOUT_MINUTES . ' minutes'));
            }
            $upd = $db->prepare("UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?");
            $upd->execute([$failed, $lockout, $user['id']]);

            return ['success' => false, 'error' => 'Invalid username/email or password.'];
        }

        // 4. Reset failed attempts on successful login
        $db->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?")->execute([$user['id']]);

        // 5. Initialize secure session
        self::createSession($user);

        return [
            'success' => true,
            'user'    => $user,
            'must_change_password' => (bool)$user['must_change_password']
        ];
    }

    /**
     * Register a new youth member
     */
    public static function register(array $data): array {
        $db = Database::getConnection();
        $settings = get_site_settings();

        $name        = trim($data['name'] ?? '');
        $username    = slugify($data['username'] ?? $name);
        $email       = strtolower(trim($data['email'] ?? ''));
        $password    = $data['password'] ?? '';
        $institution = trim($data['institution'] ?? '');
        $district    = trim($data['district'] ?? 'Dhaka');
        $phone       = trim($data['phone'] ?? '');
        $pillar      = strtoupper(trim($data['focus_pillar'] ?? 'CLIMATE'));
        $skills      = trim($data['skills'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Please fill in all required fields (Name, Email, Password).'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
        }

        // Check unique email and username
        $chk = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
        $chk->execute([$email, $username]);
        if ($chk->fetch()) {
            return ['success' => false, 'error' => 'An account with this email or username already exists.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $status = ($settings['global_user_control'] === 'AUTO') ? 'APPROVED' : 'PENDING';

        $stmt = $db->prepare("INSERT INTO users (
            username, name, email, password_hash, institution, district, phone, focus_pillar, skills, role, control_mode, member_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'USER', ?, ?)");

        $stmt->execute([
            $username, $name, $email, $hash, $institution, $district, $phone, $pillar, $skills,
            $settings['global_user_control'], $status
        ]);

        $userId = $db->lastInsertId();

        // Log audit event
        $db->prepare("INSERT INTO user_activity_logs (user_id, action, post_type, target_title, details) VALUES (?, 'JOIN_MEMBER', 'SYSTEM', ?, ?)")
           ->execute([$userId, 'Membership Registration', "Registered as youth advocate from {$district}."]);

        // Auto-login
        $stmtUser = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $newUser = $stmtUser->fetch();
        self::createSession($newUser);

        return ['success' => true, 'user' => $newUser];
    }

    /**
     * Admin password management: Reset password with Bcrypt and revoke active sessions
     */
    public static function adminResetPassword(int $targetUserId, string $newPassword, bool $mustChange = true): array {
        if (!is_admin()) {
            return ['success' => false, 'error' => 'Unauthorized operation. Admin access required.'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
        }

        $db = Database::getConnection();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        // Increment session_version to invalidate stale sessions across other devices
        $stmt = $db->prepare("UPDATE users SET 
            password_hash = ?, 
            must_change_password = ?, 
            session_version = session_version + 1,
            failed_login_attempts = 0,
            locked_until = NULL
            WHERE id = ?");
        $stmt->execute([$hash, $mustChange ? 1 : 0, $targetUserId]);

        // Log admin audit action
        $admin = current_user();
        $db->prepare("INSERT INTO user_activity_logs (user_id, action, post_type, target_title, details) VALUES (?, 'CREDENTIAL_RESET', 'SYSTEM', ?, ?)")
           ->execute([$admin['id'], "User #{$targetUserId}", "Admin reset credentials with session revocation."]);

        return ['success' => true];
    }

    /**
     * One-Click Demo Role Switcher for instant testing of Admin vs User
     */
    public static function demoSwitch(string $targetRole): bool {
        $db = Database::getConnection();
        $role = strtoupper($targetRole) === 'ADMIN' ? 'ADMIN' : 'USER';
        
        $stmt = $db->prepare("SELECT * FROM users WHERE role = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$role]);
        $user = $stmt->fetch();

        if ($user) {
            self::createSession($user);
            return true;
        }
        return false;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    private static function createSession(array $user): void {
        $_SESSION['user_id']         = (int)$user['id'];
        $_SESSION['user_name']       = $user['name'] ?? $user['full_name'] ?? $user['username'];
        $_SESSION['user_email']      = $user['email'];
        $_SESSION['user_role']       = $user['role'];
        $_SESSION['control_mode']    = $user['control_mode'] ?? 'AUTO';
        $_SESSION['session_version'] = (int)($user['session_version'] ?? 1);
    }

    // Compatibility & Static Accessor Methods
    public static function check(): bool {
        return is_logged_in();
    }

    public static function user(): ?array {
        $u = current_user();
        if (!$u) return null;
        $u['full_name'] = $u['full_name'] ?? $u['name'] ?? $u['username'];
        $u['city']      = $u['city'] ?? $u['district'] ?? 'Dhaka';
        $u['avatar']    = $u['avatar'] ?? (!empty($u['avatar_url']) ? basename($u['avatar_url']) : null);
        $u['role']      = strtolower($u['role'] ?? 'member');
        if ($u['role'] === 'user') $u['role'] = 'member';
        return $u;
    }

    public static function isAdmin(): bool {
        return is_admin();
    }

    public static function requireLogin(): void {
        require_login();
    }

    public static function requireAdmin(): void {
        require_admin();
    }

    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function attempt(string $identifier, string $password, bool $remember = false): array {
        $res = self::login($identifier, $password);
        if ($res['success']) {
            $u = $res['user'];
            $u['role'] = strtolower($u['role'] ?? 'member');
            if ($u['role'] === 'user') $u['role'] = 'member';
            return ['success' => true, 'user' => $u];
        }
        return ['success' => false, 'message' => $res['error'] ?? 'Authentication failed.'];
    }

    public static function demoLogin(string $targetRole): ?array {
        $db = Database::getConnection();
        $role = strtoupper($targetRole) === 'ADMIN' ? 'ADMIN' : 'USER';
        $stmt = $db->prepare("SELECT * FROM users WHERE UPPER(role) = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$role]);
        $user = $stmt->fetch();
        if (!$user) {
            $stmt2 = $db->query("SELECT * FROM users ORDER BY id ASC LIMIT 1");
            $user = $stmt2->fetch();
        }
        if ($user) {
            self::createSession($user);
            $user['role'] = strtolower($user['role'] ?? 'member');
            if ($user['role'] === 'user') $user['role'] = 'member';
            return $user;
        }
        return null;
    }
}


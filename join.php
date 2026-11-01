<?php
/**
 * A Greener Tomorrow - Youth Mobilization & Membership Application (join.php)
 * Free nationwide registration under the Sofiq Organization framework.
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed (CSRF token mismatch). Please try again.';
    } else {
        $fullName = sanitize_text($_POST['full_name'] ?? '');
        $username = sanitize_text($_POST['username'] ?? '');
        $email = sanitize_text($_POST['email'] ?? '');
        $phone = sanitize_text($_POST['phone'] ?? '');
        $city = sanitize_text($_POST['city'] ?? 'Dhaka');
        $pillar = sanitize_text($_POST['pillar'] ?? 'Youth Action');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
            $errorMsg = 'Please complete all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $errorMsg = 'Password must be at least 6 characters in length.';
        } elseif ($password !== $confirmPassword) {
            $errorMsg = 'Passwords do not match.';
        } else {
            $db = Database::getConnection();
            // Check if username or email exists
            $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $checkStmt->execute([$username, $email]);
            if ($checkStmt->fetch()) {
                $errorMsg = 'A member with this username or email address is already registered.';
            } else {
                $pwdHash = Auth::hashPassword($password);
                $bio = "Sofiq Organization Youth Mobilizer focusing on " . $pillar . " in " . $city . ".";
                try {
                    $insertStmt = $db->prepare("INSERT INTO users (username, email, password_hash, full_name, role, phone, city, bio, is_active, created_at) VALUES (?, ?, ?, ?, 'member', ?, ?, ?, 1, datetime('now'))");
                    $insertStmt->execute([$username, $email, $pwdHash, $fullName, $phone, $city, $bio]);

                    // Automatically attempt login or redirect
                    redirect(BASE_URL . '/login.php?registered=1');
                } catch (PDOException $e) {
                    $errorMsg = 'Database error during registration: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Become a Member | A Greener Tomorrow Youth Mobilization';
$pageDescription = 'Join over 18,500 active youth volunteers across 64 districts in Bangladesh planting trees, conducting climate research, and fighting pollution.';

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="page-header-banner">
    <div class="container">
        <span class="badge badge-accent"><i class="fa-solid fa-seedling"></i> Youth Action Network</span>
        <h1 class="page-banner-title">Become a Member Today</h1>
        <p class="page-banner-subtitle">Free grassroots membership for students, researchers, and climate advocates across all 64 districts of Bangladesh.</p>
    </div>
</div>

<div class="container py-5">
    <div class="golden-grid">
        <!-- Major Column: Application Form (61.8%) -->
        <div class="golden-col-major">
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger alert-dismissible mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="contact-form-card">
                <h3 class="mb-2">Membership Application</h3>
                <p class="text-muted mb-4">Complete your registration to access the contributor mini-dashboard and receive official event invites.</p>

                <form action="<?= BASE_URL ?>/join.php" method="POST" class="site-form">
                    <?= csrf_field() ?>

                    <div class="form-row-2">
                        <div class="form-group mb-3">
                            <label for="reg_fullname" class="form-label">Full Name *</label>
                            <input type="text" id="reg_fullname" name="full_name" class="form-control" required placeholder="e.g. Mahfuzur Rahman">
                        </div>
                        <div class="form-group mb-3">
                            <label for="reg_username" class="form-label">Desired Username *</label>
                            <input type="text" id="reg_username" name="username" class="form-control" required placeholder="mahfuz_green">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group mb-3">
                            <label for="reg_email" class="form-label">Email Address *</label>
                            <input type="email" id="reg_email" name="email" class="form-control" required placeholder="mahfuz@example.com">
                        </div>
                        <div class="form-group mb-3">
                            <label for="reg_phone" class="form-label">Phone / WhatsApp Number</label>
                            <input type="tel" id="reg_phone" name="phone" class="form-control" placeholder="+880 1712-345678">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group mb-3">
                            <label for="reg_city" class="form-label">District / Division *</label>
                            <select id="reg_city" name="city" class="form-control" required>
                                <option value="Dhaka">Dhaka (Central Secretariat)</option>
                                <option value="Chittagong">Chittagong</option>
                                <option value="Rajshahi">Rajshahi</option>
                                <option value="Khulna">Khulna (Sundarbans)</option>
                                <option value="Barisal">Barisal (Coastal Delta)</option>
                                <option value="Sylhet">Sylhet</option>
                                <option value="Rangpur">Rangpur</option>
                                <option value="Mymensingh">Mymensingh</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reg_pillar" class="form-label">Primary Interest Pillar *</label>
                            <select id="reg_pillar" name="pillar" class="form-control" required>
                                <option value="Urban Reforestation">Urban Reforestation & Trees</option>
                                <option value="Climate Research">Climate Research & Policy</option>
                                <option value="River Cleanups">River & Waste Cleanups</option>
                                <option value="Renewable Energy">Renewable Energy Solutions</option>
                                <option value="Youth Leadership">Youth Leadership & Mobilization</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group mb-3">
                            <label for="reg_password" class="form-label">Create Password *</label>
                            <input type="password" id="reg_password" name="password" class="form-control" required placeholder="Min 6 characters">
                        </div>
                        <div class="form-group mb-3">
                            <label for="reg_confirm" class="form-label">Confirm Password *</label>
                            <input type="password" id="reg_confirm" name="confirm_password" class="form-control" required placeholder="Re-type password">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="checkbox-label">
                            <input type="checkbox" required name="agree_terms" value="1">
                            I agree to adhere to the Sofiq Organization environmental code of conduct and participate non-violently.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-leaf"></i> Complete Free Membership Registration
                    </button>
                </form>
            </div>
        </div>

        <!-- Minor Column: Membership Benefits (38.2%) -->
        <div class="golden-col-minor">
            <div class="sidebar-sticky-wrap">
                <div class="meta-sidebar-card">
                    <span class="badge badge-accent mb-2">Member Privileges</span>
                    <h3>Why Join Us?</h3>
                    <ul class="framework-checklist mt-3">
                        <li><i class="fa-solid fa-circle-check"></i> Official Sofiq Organization volunteer certificate</li>
                        <li><i class="fa-solid fa-circle-check"></i> Contributor profile card on all your published articles</li>
                        <li><i class="fa-solid fa-circle-check"></i> Free access to climate workshops & data training</li>
                        <li><i class="fa-solid fa-circle-check"></i> Seed grants for community plantation drives</li>
                    </ul>
                </div>

                <div class="meta-sidebar-card mt-4">
                    <h4>Already a registered member?</h4>
                    <p class="text-muted">Sign in to publish posts or track your readership statistics.</p>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-block mt-2">Sign In to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>


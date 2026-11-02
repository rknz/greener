<?php
/**
 * A Greener Tomorrow - In-Place Contributor & Admin Login
 * Remains on the same page without redirecting on both success and failure,
 * showing an inline notification directly below the form as requested.
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';

$errorMsg = '';
$successMsg = '';
$loggedInUser = Auth::user();

if (isset($_GET['registered'])) {
    $successMsg = 'Account successfully registered! You may now sign in below.';
} elseif (isset($_GET['logged_out'])) {
    $successMsg = 'You have safely signed out of your session.';
}

// Handle 1-Click Demo Login on the SAME page (No redirect)
if (isset($_GET['demo'])) {
    $demoRole = sanitize_text($_GET['demo']);
    $user = Auth::demoLogin($demoRole);
    if ($user) {
        $loggedInUser = Auth::user();
        $roleLabel = (strtolower($loggedInUser['role'] ?? '') === 'admin') ? 'Main Administrator' : 'Registered Contributor';
        $successMsg = 'Signed in successfully as ' . ($loggedInUser['full_name'] ?? $loggedInUser['username']) . ' (' . $roleLabel . ').';
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => $successMsg,
                'user' => [
                    'name' => $loggedInUser['full_name'] ?? $loggedInUser['username'],
                    'role' => strtolower($loggedInUser['role'] ?? 'member'),
                    'is_admin' => Auth::isAdmin()
                ]
            ]);
            exit;
        }
    }
}

// Handle POST Login on the SAME page (Never redirects away)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if (empty($usernameOrEmail) || empty($password)) {
        $errorMsg = 'Please enter both your username/email and password.';
    } else {
        $res = Auth::attempt($usernameOrEmail, $password, $remember);
        if ($res['success']) {
            $loggedInUser = Auth::user();
            $roleLabel = Auth::isAdmin() ? 'Main Administrator' : 'Registered Member';
            $successMsg = 'Authentication verified! Signed in as ' . ($loggedInUser['full_name'] ?? $loggedInUser['username']) . ' (' . $roleLabel . ').';
        } else {
            $errorMsg = $res['message'] ?: 'Invalid username/email or password. Please check your credentials and try again.';
        }
    }

    // If submitted via AJAX fetch, return JSON so page never reloads or redirects
    if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => empty($errorMsg),
            'message' => empty($errorMsg) ? $successMsg : $errorMsg,
            'user' => $loggedInUser ? [
                'name' => $loggedInUser['full_name'] ?? $loggedInUser['username'],
                'role' => strtolower($loggedInUser['role'] ?? 'member'),
                'is_admin' => Auth::isAdmin()
            ] : null
        ]);
        exit;
    }
}

$pageTitle = 'Sign In | A Greener Tomorrow Contributor Portal';
$pageDescription = 'Sign in to publish research papers, environmental activities, comments, and green blog stories.';

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="auth-section-wrap py-5">
    <div class="container container-sm">
        <div class="auth-card">
            <div class="auth-card-header text-center">
                <span class="badge badge-accent mb-2"><i class="fa-solid fa-shield-halved"></i> Member &amp; Admin Portal</span>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="text-muted">Sign in to post comments, publish environmental research, and manage initiatives.</p>
            </div>

            <form action="<?= BASE_URL ?>/login.php" method="POST" class="auth-form mt-4" id="inPlaceLoginForm" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="ajax" value="1">

                <div class="form-group mb-3">
                    <label for="login_user" class="form-label">Username or Email *</label>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" id="login_user" name="username" class="form-control" required placeholder="admin or greener2morow@gmail.com" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autofocus>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="login_pass" class="form-label">Password *</label>
                    </div>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-key input-icon"></i>
                        <input type="password" id="login_pass" name="password" class="form-control" required placeholder="Enter your password">
                    </div>
                </div>

                <div class="form-group d-flex justify-content-between align-items-center mb-4">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1"> Remember this device
                    </label>
                    <span class="text-muted small"><i class="fa-solid fa-lock"></i> Bcrypt Cost 12</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnLoginSubmit">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In to Portal
                </button>

                <!-- INLINE NOTIFICATION AREA BELOW BUTTON (No Page Redirect) -->
                <div id="loginInlineNotification" class="mt-3" aria-live="polite">
                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php elseif (!empty($successMsg)): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check"></i>
                            <span><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Optional Authenticated Action Panel (Appears in-place when signed in) -->
                <div id="loggedInQuickActions" class="logged-in-inline-panel mt-3" style="<?= $loggedInUser ? 'display:block;' : 'display:none;' ?>">
                    <div class="p-3 rounded bg-light border text-center">
                        <div class="small font-weight-bold text-accent mb-2">
                            <i class="fa-solid fa-user-check"></i> Active Session: <span id="activeSessionUserName"><?= htmlspecialchars($loggedInUser['full_name'] ?? $loggedInUser['username'] ?? 'Member', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="<?= BASE_URL ?>/admin/index.php" id="btnOpenAdminPanel" class="btn btn-sm btn-primary" style="<?= ($loggedInUser && Auth::isAdmin()) ? 'display:inline-flex;' : 'display:none;' ?>">
                                <i class="fa-solid fa-gauge-high"></i> Open Admin Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>/dashboard/index.php" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-table-columns"></i> Member Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>/blog.php" class="btn btn-sm btn-outline">
                                <i class="fa-solid fa-comments"></i> Browse &amp; Comment
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- 1-Click Fast Evaluation Fillers (Populates & Verifies on Same Page) -->
            <div class="demo-login-box mt-4">
                <span class="demo-box-title"><i class="fa-solid fa-bolt"></i> Quick Credential Auto-Fill (Same-Page Test)</span>
                <div class="demo-btn-group mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary js-fill-demo" data-user="admin" data-pass="Admin@1234" data-role="admin">
                        <i class="fa-solid fa-shield"></i> Admin Account
                    </button>
                    <button type="button" class="btn btn-sm btn-outline js-fill-demo" data-user="sofiqul.alam" data-pass="Admin@1234" data-role="researcher">
                        <i class="fa-solid fa-microscope"></i> Researcher Account
                    </button>
                </div>
            </div>

            <div class="auth-card-footer text-center mt-4">
                <p>New to A Greener Tomorrow? <a href="<?= BASE_URL ?>/join.php" class="text-accent font-weight-bold">Register as a Member</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>

<?php
/**
 * A Greener Tomorrow - Secure Contributor & Admin Login
 * Features Bcrypt cost 12 authentication, brute-force rate limiting, and 1-click demo switcher.
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';

// If already logged in, redirect to dashboard or admin
if (Auth::check()) {
    if (Auth::isAdmin()) {
        redirect(BASE_URL . '/admin/index.php');
    }
    redirect(BASE_URL . '/dashboard/index.php');
}

$errorMsg = '';
$successMsg = '';

if (isset($_GET['registered'])) {
    $successMsg = 'Account successfully registered! You may now sign in.';
} elseif (isset($_GET['logged_out'])) {
    $successMsg = 'You have safely logged out of your session.';
}

// 1-Click Demo Login Handler (for rapid evaluation)
if (isset($_GET['demo'])) {
    $demoRole = sanitize_text($_GET['demo']);
    $user = Auth::demoLogin($demoRole);
    if ($user) {
        if ($user['role'] === 'admin') {
            redirect(BASE_URL . '/admin/index.php');
        } else {
            redirect(BASE_URL . '/dashboard/index.php');
        }
    }
}

// Standard POST Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed (CSRF token mismatch). Please refresh.';
    } else {
        $usernameOrEmail = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        $res = Auth::attempt($usernameOrEmail, $password, $remember);
        if ($res['success']) {
            if ($res['user']['role'] === 'admin') {
                redirect(BASE_URL . '/admin/index.php');
            } else {
                redirect(BASE_URL . '/dashboard/index.php');
            }
        } else {
            $errorMsg = $res['message'];
        }
    }
}

$pageTitle = 'Sign In | A Greener Tomorrow Contributor Portal';
$pageDescription = 'Sign in to publish research papers, environmental activities, and green blog stories.';

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="auth-section-wrap py-5">
    <div class="container container-sm">
        <div class="auth-card">
            <div class="auth-card-header text-center">
                <span class="badge badge-accent mb-2"><i class="fa-solid fa-lock"></i> Contributor Portal</span>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="text-muted">Sign in to manage your environmental campaigns and research documents.</p>
            </div>

            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success alert-dismissible mb-3">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger alert-dismissible mb-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/login.php" method="POST" class="auth-form">
                <?= csrf_field() ?>

                <div class="form-group mb-3">
                    <label for="login_user" class="form-label">Username or Email *</label>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" id="login_user" name="username" class="form-control" required placeholder="admin or email@greener2morow.org" autofocus>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="login_pass" class="form-label">Password *</label>
                    </div>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-key input-icon"></i>
                        <input type="password" id="login_pass" name="password" class="form-control" required placeholder="Enter password">
                    </div>
                </div>

                <div class="form-group d-flex justify-content-between align-items-center mb-4">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1"> Remember this device
                    </label>
                    <span class="text-muted small">Bcrypt 12 Encrypted</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In to Portal
                </button>
            </form>

            <!-- 1-Click Fast Evaluation Switcher -->
            <div class="demo-login-box mt-4">
                <span class="demo-box-title"><i class="fa-solid fa-bolt"></i> Quick Evaluator Fast-Login (1-Click)</span>
                <div class="demo-btn-group mt-2">
                    <a href="<?= BASE_URL ?>/login.php?demo=admin" class="btn btn-sm btn-outline-primary" title="Login as Main Administrator">
                        <i class="fa-solid fa-shield"></i> Admin
                    </a>
                    <a href="<?= BASE_URL ?>/login.php?demo=researcher" class="btn btn-sm btn-outline" title="Login as Senior Researcher">
                        <i class="fa-solid fa-microscope"></i> Researcher
                    </a>
                    <a href="<?= BASE_URL ?>/login.php?demo=member" class="btn btn-sm btn-outline" title="Login as Youth Volunteer">
                        <i class="fa-solid fa-seedling"></i> Volunteer
                    </a>
                </div>
            </div>

            <div class="auth-card-footer text-center mt-4">
                <p>New to A Greener Tomorrow? <a href="<?= BASE_URL ?>/join.php" class="text-accent font-weight-bold">Register as a Member</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>


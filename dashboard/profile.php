<?php
/**
 * A Greener Tomorrow - Contributor Profile & Credential Settings
 */

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';
require_once AGY_ROOT . '/includes/UploadManager.php';

Auth::requireLogin();
$currentUser = Auth::user();
$userId = (int)$currentUser['id'];

$db = Database::getConnection();
$errorMsg = '';
$successMsg = '';

// Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed (CSRF mismatch).';
    } else {
        $action = $_POST['action'] ?? 'update_profile';

        if ($action === 'update_profile') {
            $fullName = sanitize_text($_POST['full_name'] ?? '');
            $email = sanitize_text($_POST['email'] ?? '');
            $phone = sanitize_text($_POST['phone'] ?? '');
            $city = sanitize_text($_POST['city'] ?? '');
            $bio = sanitize_text($_POST['bio'] ?? '');

            if (empty($fullName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = 'Please provide a valid Full Name and Email address.';
            } else {
                $avatarFilename = $currentUser['avatar'] ?? null;
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $avRes = UploadManager::uploadImage($_FILES['avatar'], 'avatars');
                    if ($avRes['success']) {
                        $avatarFilename = $avRes['filename'];
                    } else {
                        $errorMsg = $avRes['error'];
                    }
                }

                if (empty($errorMsg)) {
                    try {
                        $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, city = ?, bio = ?, avatar = ?, updated_at = datetime('now') WHERE id = ?");
                        $stmt->execute([$fullName, $email, $phone, $city, $bio, $avatarFilename, $userId]);
                        
                        // Refresh session user data
                        $fetchStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                        $fetchStmt->execute([$userId]);
                        $_SESSION['agy_user'] = $fetchStmt->fetch();
                        $currentUser = $_SESSION['agy_user'];

                        $successMsg = 'Profile successfully updated!';
                    } catch (PDOException $e) {
                        $errorMsg = 'Database error updating profile: ' . $e->getMessage();
                    }
                }
            }
        } elseif ($action === 'change_password') {
            $currentPwd = $_POST['current_password'] ?? '';
            $newPwd = $_POST['new_password'] ?? '';
            $confirmPwd = $_POST['confirm_password'] ?? '';

            if (empty($currentPwd) || empty($newPwd)) {
                $errorMsg = 'Please complete all password fields.';
            } elseif (!password_verify($currentPwd, $currentUser['password_hash'])) {
                $errorMsg = 'Your current password was entered incorrectly.';
            } elseif (strlen($newPwd) < 6) {
                $errorMsg = 'New password must be at least 6 characters.';
            } elseif ($newPwd !== $confirmPwd) {
                $errorMsg = 'New passwords do not match.';
            } else {
                $newHash = Auth::hashPassword($newPwd);
                try {
                    $stmt = $db->prepare("UPDATE users SET password_hash = ?, session_version = session_version + 1, updated_at = datetime('now') WHERE id = ?");
                    $stmt->execute([$newHash, $userId]);
                    $successMsg = 'Password successfully changed and credentials secured!';
                } catch (PDOException $e) {
                    $errorMsg = 'Failed to update password.';
                }
            }
        }
    }
}

$pageTitle = 'Edit Profile | Contributor Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.min.css?v=2.2.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.min.css?v=2.2.0">
</head>
<body class="dashboard-body">
    <header class="dash-topbar">
        <div class="dash-topbar-left">
            <a href="<?= BASE_URL ?>/dashboard/index.php" class="dash-brand-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <div class="dash-topbar-right">
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out</a>
        </div>
    </header>

    <div class="container container-md py-4">
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

        <!-- Edit Profile Form -->
        <div class="card p-4 mb-4">
            <h3 class="mb-3"><i class="fa-solid fa-id-badge"></i> Contributor Information</h3>
            <p class="text-muted mb-4">This verified information appears on all activities, researches, and articles you publish.</p>

            <form action="" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="form-row-2">
                    <div class="form-group mb-3">
                        <label for="prof_name" class="form-label">Full Name *</label>
                        <input type="text" id="prof_name" name="full_name" class="form-control" value="<?= htmlspecialchars($currentUser['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="prof_email" class="form-label">Email Address *</label>
                        <input type="email" id="prof_email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group mb-3">
                        <label for="prof_phone" class="form-label">Phone Number</label>
                        <input type="tel" id="prof_phone" name="phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="prof_city" class="form-label">City / District</label>
                        <input type="text" id="prof_city" name="city" class="form-control" value="<?= htmlspecialchars($currentUser['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="prof_bio" class="form-label">Short Biography</label>
                    <textarea id="prof_bio" name="bio" class="form-control" rows="3"><?= htmlspecialchars($currentUser['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group mb-4">
                    <label for="avatar" class="form-label">Profile Avatar (Max 4 MB)</label>
                    <input type="file" id="avatar" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp" data-max-mb="4">
                </div>

                <button type="submit" class="btn btn-primary">Save Profile Details</button>
            </form>
        </div>

        <!-- Change Password Box -->
        <div class="card p-4">
            <h3 class="mb-3"><i class="fa-solid fa-key"></i> Security & Password</h3>
            <form action="" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="form-group mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-row-2">
                    <div class="form-group mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Min 6 characters">
                    </div>
                    <div class="form-group mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-outline-primary">Update Password</button>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/dashboard.min.js?v=2.0.0"></script>
</body>
</html>


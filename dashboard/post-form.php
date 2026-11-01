<?php
/**
 * A Greener Tomorrow - Post Editor & Creation Form
 * Handles Multi-format Posts (Activity, Research, Blog, Vlog),
 * Document upload (<=15MB), Video upload (<=25MB), and Image WebP compression.
 */

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';
require_once AGY_ROOT . '/includes/PostRepository.php';
require_once AGY_ROOT . '/includes/UploadManager.php';
require_once AGY_ROOT . '/includes/ImageOptimizer.php';

Auth::requireLogin();
$currentUser = Auth::user();
$userId = (int)$currentUser['id'];
$isAdmin = Auth::isAdmin();

$postId = (int)($_GET['id'] ?? 0);
$post = null;
$isEdit = false;

// Delete handler
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $delPost = PostRepository::getById($delId);
    if ($delPost && ($isAdmin || (int)$delPost['user_id'] === $userId)) {
        PostRepository::delete($delId);
        redirect(BASE_URL . '/dashboard/index.php?msg=deleted');
    } else {
        exit('Unauthorized action.');
    }
}

// If editing, load post
if ($postId > 0) {
    $post = PostRepository::getById($postId);
    if (!$post || (!$isAdmin && (int)$post['user_id'] !== $userId)) {
        redirect(BASE_URL . '/dashboard/index.php');
    }
    $isEdit = true;
}

$errorMsg = '';
$successMsg = '';

// Form submission handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed (CSRF mismatch).';
    } else {
        $title = sanitize_text($_POST['title'] ?? '');
        $postType = sanitize_text($_POST['post_type'] ?? 'blog');
        $category = sanitize_text($_POST['category'] ?? 'General');
        $excerpt = sanitize_text($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $videoUrl = sanitize_text($_POST['video_url'] ?? '');
        $status = sanitize_text($_POST['status'] ?? ($isAdmin ? 'published' : 'published'));

        if (empty($title) || empty($content)) {
            $errorMsg = 'Please provide both a Title and Content for your post.';
        } else {
            // 1. Handle Featured Image
            $featuredImagePath = $isEdit ? ($post['featured_image'] ?? '') : '';
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $imgRes = UploadManager::uploadImage($_FILES['featured_image'], 'posts');
                if ($imgRes['success']) {
                    $featuredImagePath = 'assets/uploads/posts/' . $imgRes['filename'];
                } else {
                    $errorMsg = $imgRes['error'];
                }
            }

            // 2. Handle Document Attachment (for research/activities)
            $documentPath = $isEdit ? ($post['document_path'] ?? '') : '';
            if (empty($errorMsg) && isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $docRes = UploadManager::uploadDocument($_FILES['document_file']);
                if ($docRes['success']) {
                    $documentPath = $docRes['filename'];
                } else {
                    $errorMsg = $docRes['error'];
                }
            }

            // 3. Handle Direct Video Upload (for vlogs)
            $videoPath = $isEdit ? ($post['video_path'] ?? '') : '';
            if (empty($errorMsg) && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $vidRes = UploadManager::uploadVideo($_FILES['video_file']);
                if ($vidRes['success']) {
                    $videoPath = $vidRes['filename'];
                } else {
                    $errorMsg = $vidRes['error'];
                }
            }

            // If no errors, persist
            if (empty($errorMsg)) {
                $data = [
                    'user_id' => $userId,
                    'title' => $title,
                    'post_type' => $postType,
                    'category' => $category,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'featured_image' => $featuredImagePath,
                    'document_path' => $documentPath,
                    'video_url' => $videoUrl,
                    'video_path' => $videoPath,
                    'status' => $status
                ];

                if ($isEdit) {
                    PostRepository::update($postId, $data);
                    $successMsg = 'Post successfully updated!';
                    $post = PostRepository::getById($postId);
                } else {
                    $newId = PostRepository::create($data);
                    redirect(BASE_URL . '/dashboard/index.php?msg=created');
                }
            }
        }
    }
}

$pageTitle = ($isEdit ? 'Edit Post' : 'Create New Post') . ' | Contributor Dashboard';
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
    <!-- Topbar -->
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
        <div class="card p-4">
            <div class="card-header pb-3 mb-4 border-bottom">
                <h2><?= $isEdit ? 'Edit Initiative Post' : 'Publish New Initiative / Research' ?></h2>
                <p class="text-muted">Multi-format publishing engine adhering to shared hosting upload limits (Video &le; 25MB, Docs &le; 15MB).</p>
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

            <form action="" method="POST" enctype="multipart/form-data" class="post-editor-form">
                <?= csrf_field() ?>

                <div class="form-row-2">
                    <div class="form-group mb-3">
                        <label for="post_type" class="form-label">Post Format *</label>
                        <select id="post_type" name="post_type" class="form-control" required>
                            <option value="activity" <?= ($post['post_type'] ?? '') === 'activity' ? 'selected' : '' ?>>Activity / Program Initiative</option>
                            <option value="research" <?= ($post['post_type'] ?? '') === 'research' ? 'selected' : '' ?>>Scientific Research Paper</option>
                            <option value="blog" <?= ($post['post_type'] ?? 'blog') === 'blog' ? 'selected' : '' ?>>In-Depth Green Blog</option>
                            <option value="vlog" <?= ($post['post_type'] ?? '') === 'vlog' ? 'selected' : '' ?>>Video Vlog</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category" class="form-label">Environmental Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="Reforestation" <?= ($post['category'] ?? '') === 'Reforestation' ? 'selected' : '' ?>>Urban Reforestation & Trees</option>
                            <option value="Climate Adaptation" <?= ($post['category'] ?? '') === 'Climate Adaptation' ? 'selected' : '' ?>>Climate Adaptation & Resilience</option>
                            <option value="Pollution Control" <?= ($post['category'] ?? '') === 'Pollution Control' ? 'selected' : '' ?>>River & Air Pollution Control</option>
                            <option value="Renewable Energy" <?= ($post['category'] ?? '') === 'Renewable Energy' ? 'selected' : '' ?>>Renewable Energy Transition</option>
                            <option value="Youth Action" <?= ($post['category'] ?? '') === 'Youth Action' ? 'selected' : '' ?>>Youth Mobilization</option>
                            <option value="Disaster Response" <?= ($post['category'] ?? '') === 'Disaster Response' ? 'selected' : '' ?>>Natural Disaster Response</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="title" class="form-label">Post Title *</label>
                    <input type="text" id="title" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="e.g. 5,000 Native Trees Planted Across Hatirpool Corridor">
                </div>

                <div class="form-group mb-3">
                    <label for="excerpt" class="form-label">Short Summary / Abstract</label>
                    <textarea id="excerpt" name="excerpt" class="form-control" rows="2" placeholder="Brief summary displayed on cards and search engines..."><?= htmlspecialchars($post['excerpt'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group mb-4">
                    <label for="content" class="form-label">Detailed Content & Findings *</label>
                    <textarea id="content" name="content" class="form-control" rows="10" required placeholder="Comprehensive report regarding the specific initiative, research methodologies, or volunteer actions..."><?= htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Featured Image Upload with Client Size Guard -->
                <div class="form-group mb-4 p-3 bg-light rounded">
                    <label for="featured_image" class="form-label"><strong>Featured Image</strong> (Max 8 MB &bull; Auto-optimized to WebP)</label>
                    <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" data-max-mb="8">
                    <?php if (!empty($post['featured_image'])): ?>
                        <div class="mt-2 text-muted small">Current: <code><?= htmlspecialchars($post['featured_image'], ENT_QUOTES, 'UTF-8') ?></code></div>
                    <?php endif; ?>
                </div>

                <!-- Research Document Attachment (PDF/DOCX <= 15MB) -->
                <div class="form-group mb-4 p-3 bg-light rounded" id="document_section">
                    <label for="document_file" class="form-label"><strong>Attach Research Document / Paper</strong> (PDF, DOCX &bull; Max 15 MB)</label>
                    <input type="file" id="document_file" name="document_file" class="form-control" accept=".pdf,.doc,.docx,.xlsx" data-max-mb="15">
                    <?php if (!empty($post['document_path'])): ?>
                        <div class="mt-2 text-muted small">Current document: <code><?= htmlspecialchars($post['document_path'], ENT_QUOTES, 'UTF-8') ?></code></div>
                    <?php endif; ?>
                </div>

                <!-- Video Format Selection: YouTube URL vs Direct Video Upload (<=25MB) -->
                <div class="card p-3 mb-4" id="video_section">
                    <label class="form-label"><strong>Video Option for Vlogs</strong></label>
                    <div class="d-flex gap-4 mb-3">
                        <label class="radio-label">
                            <input type="radio" name="video_source" id="video_type_youtube" value="youtube" <?= empty($post['video_path']) ? 'checked' : '' ?>>
                            YouTube Embed URL
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="video_source" id="video_type_upload" value="upload" <?= !empty($post['video_path']) ? 'checked' : '' ?>>
                            Direct Video Upload (&le; 25 MB Shared Hosting Limit)
                        </label>
                    </div>

                    <div id="youtube_field_group">
                        <label for="video_url" class="form-label small">YouTube Video URL</label>
                        <input type="url" id="video_url" name="video_url" class="form-control" value="<?= htmlspecialchars($post['video_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="https://www.youtube.com/watch?v=...">
                    </div>

                    <div id="direct_upload_field_group" style="display:none;">
                        <label for="video_file" class="form-label small">Upload MP4 / WebM Video File (Max 25 MB)</label>
                        <input type="file" id="video_file" name="video_file" class="form-control" accept="video/mp4,video/webm" data-max-mb="25">
                        <?php if (!empty($post['video_path'])): ?>
                            <div class="mt-2 text-muted small">Current video: <code><?= htmlspecialchars($post['video_path'], ENT_QUOTES, 'UTF-8') ?></code></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="<?= BASE_URL ?>/dashboard/index.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i> <?= $isEdit ? 'Save Changes' : 'Publish Post' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/dashboard.min.js?v=2.0.0"></script>
</body>
</html>


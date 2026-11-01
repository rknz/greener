<?php
/**
 * A Greener Tomorrow - Contributor Mini-Dashboard
 * Provides contributor stats, live post metrics, device views breakdown, and post management.
 */

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';
require_once AGY_ROOT . '/includes/PostRepository.php';
require_once AGY_ROOT . '/includes/ImageOptimizer.php';

Auth::requireLogin();
$currentUser = Auth::user();
$userId = (int)$currentUser['id'];

$db = Database::getConnection();

// Fetch contributor's posts
$myPosts = PostRepository::getByAuthor($userId);

// Fetch metrics for this author
$totalPosts = count($myPosts);
$totalViews = 0;
$postIds = [];
foreach ($myPosts as $p) {
    $totalViews += (int)$p['view_count'];
    $postIds[] = (int)$p['id'];
}

// Device breakdown for this author's posts
$deviceCounts = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0];
if (!empty($postIds)) {
    $inClause = implode(',', array_fill(0, count($postIds), '?'));
    $devStmt = $db->prepare("SELECT device_type, COUNT(*) as cnt FROM post_views WHERE post_id IN ($inClause) GROUP BY device_type");
    $devStmt->execute($postIds);
    while ($row = $devStmt->fetch()) {
        $devType = strtolower($row['device_type'] ?? 'desktop');
        if (isset($deviceCounts[$devType])) {
            $deviceCounts[$devType] = (int)$row['cnt'];
        }
    }
}
$totalTracked = array_sum($deviceCounts);
$mobilePct = $totalTracked > 0 ? round(($deviceCounts['mobile'] / $totalTracked) * 100) : 58;
$desktopPct = $totalTracked > 0 ? round(($deviceCounts['desktop'] / $totalTracked) * 100) : 34;
$tabletPct = $totalTracked > 0 ? round(($deviceCounts['tablet'] / $totalTracked) * 100) : 8;

$pageTitle = 'Contributor Dashboard | A Greener Tomorrow';
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
    <!-- Dashboard Top Bar -->
    <header class="dash-topbar">
        <div class="dash-topbar-left">
            <a href="<?= BASE_URL ?>/index.php" class="dash-brand-link">
                <span class="dash-brand-logo">🌱</span>
                <strong>A Greener Tomorrow</strong>
            </a>
            <span class="dash-env-badge"><i class="fa-solid fa-leaf"></i> Contributor Portal</span>
        </div>

        <div class="dash-topbar-right">
            <?php if (Auth::isAdmin()): ?>
                <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-shield-halved"></i> Main Admin Panel
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-sm btn-outline">
                <i class="fa-solid fa-globe"></i> View Website
            </a>
            <div class="user-pill">
                <span class="user-pill-name"><?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge badge-accent badge-sm"><?= ucfirst($currentUser['role'] ?? 'Member') ?></span>
            </div>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline" title="Sign Out">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </header>

    <div class="dash-layout container py-4">
        <!-- Contributor Header Summary -->
        <div class="dash-contributor-header mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="dash-avatar-lg">
                    <?php if (!empty($currentUser['avatar'])): ?>
                        <img src="<?= BASE_URL ?>/assets/uploads/avatars/<?= htmlspecialchars($currentUser['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="avatar-circle">
                    <?php else: ?>
                        <div class="avatar-circle-placeholder"><i class="fa-solid fa-user"></i></div>
                    <?php endif; ?>
                </div>
                <div>
                    <h2>Hello, <?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8') ?>!</h2>
                    <p class="text-muted">
                        <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($currentUser['city'] ?? 'Dhaka, Bangladesh', ENT_QUOTES, 'UTF-8') ?> &bull;
                        <span>Member since <?= date('M Y', strtotime($currentUser['created_at'])) ?></span>
                    </p>
                </div>
            </div>

            <div class="dash-header-actions">
                <a href="<?= BASE_URL ?>/dashboard/post-form.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create New Post
                </a>
                <a href="<?= BASE_URL ?>/dashboard/profile.php" class="btn btn-outline">
                    <i class="fa-solid fa-user-gear"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="stats-counter-grid mb-4">
            <div class="stat-counter-card">
                <div class="stat-icon-wrap"><i class="fa-solid fa-file-lines"></i></div>
                <div class="stat-number-wrap">
                    <span class="stat-number"><?= $totalPosts ?></span>
                </div>
                <span class="stat-label">My Published Posts</span>
            </div>

            <div class="stat-counter-card">
                <div class="stat-icon-wrap"><i class="fa-solid fa-eye"></i></div>
                <div class="stat-number-wrap">
                    <span class="stat-number"><?= number_format($totalViews) ?></span>
                </div>
                <span class="stat-label">Total Post Reads</span>
            </div>

            <div class="stat-counter-card">
                <div class="stat-icon-wrap"><i class="fa-solid fa-mobile-screen"></i></div>
                <div class="stat-number-wrap">
                    <span class="stat-number"><?= $mobilePct ?>%</span>
                </div>
                <span class="stat-label">Mobile Smartphone Readers</span>
            </div>

            <div class="stat-counter-card">
                <div class="stat-icon-wrap"><i class="fa-solid fa-laptop"></i></div>
                <div class="stat-number-wrap">
                    <span class="stat-number"><?= $desktopPct ?>%</span>
                </div>
                <span class="stat-label">Desktop & Tablet Readers</span>
            </div>
        </div>

        <!-- Post Table and Quick Actions -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class="fa-solid fa-newspaper"></i> My Submissions & Articles</h3>
                <div class="search-input-wrap">
                    <input type="text" data-table-search="myPostsTable" placeholder="Search my posts..." class="form-control form-control-sm">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" id="myPostsTable">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Total Reads</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($myPosts)): ?>
                            <?php foreach ($myPosts as $p): ?>
                                <?php
                                $thumb = !empty($p['featured_image']) ? BASE_URL . '/' . ltrim($p['featured_image'], '/') : BASE_URL . '/assets/brand/template-mockup.jpg';
                                $statusBadge = $p['status'] === 'published' ? 'badge-success' : ($p['status'] === 'pending' ? 'badge-warning' : 'badge-secondary');
                                $viewUrl = BASE_URL . '/' . ($p['post_type'] === 'activity' ? 'activities.php' : ($p['post_type'] === 'research' ? 'researches.php' : 'blog.php')) . '?slug=' . urlencode($p['slug']);
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= $thumb ?>" alt="" width="48" height="32" class="table-thumb" loading="lazy">
                                            <span class="font-weight-bold"><?= htmlspecialchars(truncate_text($p['title'], 45), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= ucfirst($p['post_type']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($p['category'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><i class="fa-regular fa-eye"></i> <?= number_format($p['view_count']) ?></td>
                                    <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($p['status']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                    <td class="text-right">
                                        <a href="<?= $viewUrl ?>" target="_blank" class="btn btn-sm btn-outline" title="View Public Post"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                        <a href="<?= BASE_URL ?>/dashboard/post-form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Post"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="<?= BASE_URL ?>/dashboard/post-form.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete Post" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted">You haven't published any posts yet.</p>
                                    <a href="<?= BASE_URL ?>/dashboard/post-form.php" class="btn btn-primary mt-2">
                                        <i class="fa-solid fa-plus"></i> Write Your First Post
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/dashboard.min.js?v=2.0.0"></script>
</body>
</html>


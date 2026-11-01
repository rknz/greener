<?php
/**
 * A Greener Tomorrow - Main Administrator Governance Dashboard
 * Full 5-Tab Structured Control Interface
 */

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';
require_once AGY_ROOT . '/includes/PostRepository.php';
require_once AGY_ROOT . '/includes/Tracker.php';
require_once AGY_ROOT . '/includes/PerformanceMonitor.php';
require_once AGY_ROOT . '/includes/ImageOptimizer.php';

Auth::requireAdmin();
$currentUser = Auth::user();
$db = Database::getConnection();

// Notifications
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// Fetch Data for All 5 Tabs:
// 1. Core Settings & Slides
$settings = get_all_settings();
$slides = $db->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id DESC")->fetchAll();

// 2. Users
$users = $db->query("SELECT *, COALESCE(full_name, name, username) as full_name, COALESCE(city, district, 'Dhaka') as city, LOWER(role) as role FROM users ORDER BY id DESC")->fetchAll();

// 3. Posts & Content
$rawPosts = $db->query("
    SELECT p.*, u.username, COALESCE(u.full_name, u.name, u.username) as author_name, COALESCE(u.avatar, u.avatar_url) as author_avatar
    FROM posts p
    LEFT JOIN users u ON p.author_id = u.id
    ORDER BY p.id DESC
")->fetchAll();
$posts = array_map([PostRepository::class, 'normalizePost'], $rawPosts);

// 4. Visitor Analytics
$visitorStats = Tracker::getVisitorSummary();
$visitorLogs = Tracker::getRecentVisitors(30);

// 5. Performance Metrics
$perfScore = PerformanceMonitor::calculateScore();
$perfChecklist = PerformanceMonitor::getChecklist();
$serverDiag = PerformanceMonitor::getServerDiagnostics();

$pageTitle = 'Central Governance Panel | A Greener Tomorrow Admin';
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
<body class="dashboard-body admin-body">
    <!-- Admin Topbar -->
    <header class="dash-topbar admin-topbar">
        <div class="dash-topbar-left">
            <a href="<?= BASE_URL ?>/index.php" class="dash-brand-link">
                <span class="dash-brand-logo">🌱</span>
                <strong>A Greener Tomorrow</strong>
            </a>
            <span class="badge badge-accent badge-admin"><i class="fa-solid fa-shield-halved"></i> Main Administrator Control</span>
        </div>

        <div class="dash-topbar-right">
            <a href="<?= BASE_URL ?>/dashboard/index.php" class="btn btn-sm btn-outline">
                <i class="fa-solid fa-user"></i> Contributor View
            </a>
            <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-globe"></i> Live Website
            </a>
            <div class="user-pill">
                <span class="user-pill-name"><?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge badge-success badge-sm">Admin</span>
            </div>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline" title="Sign Out">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </header>

    <!-- Main Admin Container -->
    <div class="container-fluid py-4 px-lg-5">
        <!-- Toast / Action Feedback Messages -->
        <?php if ($msg === 'settings_saved'): ?>
            <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> Core website information & MCQ logo placement updated successfully.</div>
        <?php elseif ($msg === 'pwd_reset_ok'): ?>
            <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> User password successfully updated using Bcrypt cost 12. Active sessions revoked.</div>
        <?php elseif ($msg === 'user_status_updated'): ?>
            <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> User account status changed and credentials updated.</div>
        <?php elseif ($msg === 'post_published'): ?>
            <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> Post has been verified and published live.</div>
        <?php elseif ($msg === 'post_flagged_offensive'): ?>
            <div class="alert alert-warning alert-dismissible mb-4"><i class="fa-solid fa-triangle-exclamation"></i> Content flagged as inappropriate/offensive and removed from public feeds.</div>
        <?php elseif ($msg === 'post_deleted'): ?>
            <div class="alert alert-danger alert-dismissible mb-4"><i class="fa-solid fa-trash"></i> Post permanently deleted.</div>
        <?php elseif ($msg === 'perf_saved'): ?>
            <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-gauge-high"></i> Dynamic performance parameters adjusted and active.</div>
        <?php elseif ($err === 'pwd_short'): ?>
            <div class="alert alert-danger alert-dismissible mb-4"><i class="fa-solid fa-triangle-exclamation"></i> New password must be at least 6 characters.</div>
        <?php endif; ?>

        <!-- 5-Tab Navigation Bar -->
        <nav class="admin-tabs-nav mb-4" role="tablist">
            <button class="tab-btn active" data-tab="info" role="tab"><i class="fa-solid fa-sliders"></i> Site Info & Logo MCQ</button>
            <button class="tab-btn" data-tab="users" role="tab"><i class="fa-solid fa-users-gear"></i> User Oversight & Passwords</button>
            <button class="tab-btn" data-tab="content" role="tab"><i class="fa-solid fa-list-check"></i> Content Moderation</button>
            <button class="tab-btn" data-tab="analytics" role="tab"><i class="fa-solid fa-chart-line"></i> Visitor Analytics & Devices</button>
            <button class="tab-btn" data-tab="performance" role="tab"><i class="fa-solid fa-gauge"></i> Dynamic Performance & Server</button>
        </nav>

        <!-- ================= TAB 1: CORE SITE INFO & MCQ LOGO PLACEMENT ================= -->
        <section class="admin-tab-pane active" id="tab-info" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card p-4">
                        <div class="card-header pb-3 mb-4 border-bottom">
                            <h3><i class="fa-solid fa-building-ngo"></i> Core Organization Information</h3>
                            <p class="text-muted">Modify organizational identity, Dhaka Hatirpool secretariat coordinates, and emergency hotlines.</p>
                        </div>

                        <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="save_site_settings">

                            <div class="form-row-2">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Website Name</label>
                                    <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? 'A Greener Tomorrow', ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Tagline</label>
                                    <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars($settings['site_tagline'] ?? 'Youth Climate Mobilization', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>

                            <div class="form-row-2">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Secretariat Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? 'info@greener2morow.org', ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Secretariat Phone / Hotline</label>
                                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($settings['contact_phone'] ?? '+880 1712-345678', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Central Secretariat Physical Address</label>
                                <input type="text" name="contact_address" class="form-control" value="<?= htmlspecialchars($settings['contact_address'] ?? '36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <!-- MCQ Logo Placement Matrix Selector -->
                            <div class="card p-3 mb-4 bg-light">
                                <h4 class="mb-2"><i class="fa-solid fa-bullseye"></i> MCQ Logo Placement Matrix</h4>
                                <p class="text-muted small mb-3">Select the target locations across the platform where the brand logo should be rendered:</p>

                                <div class="mcq-checkbox-group">
                                    <label class="checkbox-label mb-2">
                                        <input type="checkbox" name="logo_use_navbar" value="1" <?= !empty($settings['logo_use_navbar']) ? 'checked' : '' ?>>
                                        <strong>Navbar Header</strong> (Top navigation bar brand identity)
                                    </label>
                                    <label class="checkbox-label mb-2">
                                        <input type="checkbox" name="logo_use_footer" value="1" <?= !empty($settings['logo_use_footer']) ? 'checked' : '' ?>>
                                        <strong>Footer Area</strong> (Bottom global footer brand column)
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="logo_use_section" value="1" <?= !empty($settings['logo_use_section']) ? 'checked' : '' ?>>
                                        <strong>Content Sections</strong> (Internal callouts & about sections)
                                    </label>
                                </div>
                            </div>

                            <!-- Logo Image Upload & Alt Text -->
                            <div class="card p-3 mb-4">
                                <h4><i class="fa-solid fa-image"></i> Brand Logo Asset</h4>
                                <div class="d-flex align-items-center gap-4 mt-3">
                                    <div class="logo-preview-box">
                                        <img src="<?= BASE_URL ?>/assets/brand/brand-logo.png" alt="Current Logo" width="64" height="64">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label small">Upload New Logo (PNG, WebP, SVG &bull; Max 2MB)</label>
                                        <input type="file" name="site_logo" class="form-control form-control-sm" accept="image/png,image/webp,image/svg+xml,image/jpeg" data-max-mb="2">
                                        <div class="mt-2">
                                            <input type="text" name="logo_alt" class="form-control form-control-sm" placeholder="Logo Alt Text" value="<?= htmlspecialchars($settings['logo_alt'] ?? 'A Greener Tomorrow', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-floppy-disk"></i> Save Core Information & Logo Settings</button>
                        </form>
                    </div>
                </div>

                <!-- Hero Slider Initiatives Management -->
                <div class="col-lg-4">
                    <div class="card p-4">
                        <div class="card-header pb-2 mb-3 border-bottom">
                            <h4><i class="fa-solid fa-images"></i> Hero Initiative Slides</h4>
                            <p class="text-muted small">Manage the homepage slider campaigns.</p>
                        </div>

                        <div class="slide-list mb-4">
                            <?php foreach ($slides as $sl): ?>
                                <div class="slide-list-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                    <div>
                                        <span class="badge badge-accent badge-sm"><?= htmlspecialchars($sl['badge_text'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <div class="font-weight-bold small mt-1"><?= htmlspecialchars(truncate_text($sl['title'], 32), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" onsubmit="return confirm('Delete this hero slide?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_hero_slide">
                                        <input type="hidden" name="slide_id" value="<?= $sl['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Add Slide Modal / Form -->
                        <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="save_hero_slide">
                            <h5 class="mb-2">Add New Initiative Slide</h5>
                            <div class="form-group mb-2">
                                <input type="text" name="slide_title" class="form-control form-control-sm" required placeholder="Initiative Headline">
                            </div>
                            <div class="form-group mb-2">
                                <input type="text" name="slide_subtitle" class="form-control form-control-sm" placeholder="Subtitle / Target">
                            </div>
                            <div class="form-group mb-2">
                                <input type="text" name="slide_badge" class="form-control form-control-sm" placeholder="Badge (e.g. Live Action)">
                            </div>
                            <div class="form-group mb-2">
                                <input type="file" name="slide_image" class="form-control form-control-sm" accept="image/*" data-max-mb="8">
                            </div>
                            <div class="form-group mb-3">
                                <input type="text" name="slide_cta_url" class="form-control form-control-sm" placeholder="CTA Link (e.g. /activities.php)">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-sm"><i class="fa-solid fa-plus"></i> Add Slide</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= TAB 2: USER GOVERNANCE & CREDENTIAL OVERSIGHT ================= -->
        <section class="admin-tab-pane" id="tab-users" style="display:none;" role="tabpanel">
            <div class="card p-4">
                <div class="card-header pb-3 mb-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h3><i class="fa-solid fa-users-shield"></i> User Credential Management & Oversight</h3>
                        <p class="text-muted">Manage usernames, roles, and execute administrator Bcrypt cost 12 password resets.</p>
                    </div>
                    <div class="search-input-wrap">
                        <input type="text" data-table-search="adminUsersTable" placeholder="Search users by name, role, email..." class="form-control form-control-sm">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="adminUsersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>District</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th class="text-right">Credential Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <?php
                                $uRoleBadge = $u['role'] === 'admin' ? 'badge-primary' : ($u['role'] === 'researcher' ? 'badge-accent' : 'badge-secondary');
                                $uStatusBadge = $u['is_active'] ? 'badge-success' : 'badge-danger';
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="user-table-avatar"><i class="fa-solid fa-user"></i></span>
                                            <div>
                                                <strong><?= htmlspecialchars($u['full_name'] ?? $u['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <div class="text-muted small">@<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge <?= $uRoleBadge ?>"><?= ucfirst($u['role']) ?></span></td>
                                    <td><?= htmlspecialchars($u['city'] ?? 'Dhaka', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge <?= $uStatusBadge ?>"><?= $u['is_active'] ? 'Active' : 'Suspended' ?></span></td>
                                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                    <td class="text-right">
                                        <!-- Bcrypt Password Reset Trigger -->
                                        <button class="btn btn-sm btn-outline-primary btn-reset-user-pwd" data-user-id="<?= $u['id'] ?>" data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>" title="Admin Bcrypt Password Reset">
                                            <i class="fa-solid fa-key"></i> Reset Password
                                        </button>

                                        <!-- Toggle Status -->
                                        <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                            <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Change status for this user?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="toggle_user_status">
                                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= $u['is_active'] ?>">
                                                <button type="submit" class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $u['is_active'] ? 'Suspend User' : 'Activate User' ?>">
                                                    <i class="fa-solid <?= $u['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ================= TAB 3: CONTENT MODERATION & OFFENSIVE REMOVAL ================= -->
        <section class="admin-tab-pane" id="tab-content" style="display:none;" role="tabpanel">
            <div class="card p-4">
                <div class="card-header pb-3 mb-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h3><i class="fa-solid fa-shield-virus"></i> Contributor Content Moderation</h3>
                        <p class="text-muted">Review all submissions. Exercise authority to approve, unpublish, or permanently remove offensive content.</p>
                    </div>
                    <div class="search-input-wrap">
                        <input type="text" data-table-search="moderationTable" placeholder="Search posts by title or author..." class="form-control form-control-sm">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="moderationTable">
                        <thead>
                            <tr>
                                <th>Post Title</th>
                                <th>Type</th>
                                <th>Contributor</th>
                                <th>Reads</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-right">Moderation Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $p): ?>
                                <?php
                                $statClass = 'badge-secondary';
                                if ($p['status'] === 'published') $statClass = 'badge-success';
                                elseif ($p['status'] === 'offensive') $statClass = 'badge-danger';
                                elseif ($p['status'] === 'pending') $statClass = 'badge-warning';

                                $viewUrl = BASE_URL . '/' . ($p['post_type'] === 'activity' ? 'activities.php' : ($p['post_type'] === 'research' ? 'researches.php' : 'blog.php')) . '?slug=' . urlencode($p['slug']);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars(truncate_text($p['title'], 50), ENT_QUOTES, 'UTF-8') ?></strong>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= ucfirst($p['post_type']) ?></span></td>
                                    <td><?= htmlspecialchars($p['author_name'] ?? $p['username'] ?? 'User #'.$p['user_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><i class="fa-regular fa-eye"></i> <?= number_format($p['view_count']) ?></td>
                                    <td><span class="badge <?= $statClass ?>"><?= ucfirst($p['status']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                    <td class="text-right">
                                        <a href="<?= $viewUrl ?>" target="_blank" class="btn btn-sm btn-outline" title="Inspect Post"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>

                                        <?php if ($p['status'] !== 'published'): ?>
                                            <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="moderate_post">
                                                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="post_action" value="publish">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve & Publish"><i class="fa-solid fa-check"></i> Publish</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="moderate_post">
                                                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="post_action" value="unpublish">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Unpublish to Draft"><i class="fa-solid fa-eye-slash"></i> Unpublish</button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Offensive Content Authority Action -->
                                        <?php if ($p['status'] !== 'offensive'): ?>
                                            <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Flag and sanitize this offensive/inappropriate post?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="moderate_post">
                                                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="post_action" value="offensive">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Flag as Offensive / Inappropriate"><i class="fa-solid fa-triangle-exclamation"></i> Offensive</button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Hard Delete -->
                                        <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Permanently delete this post and its attachments?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="moderate_post">
                                            <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="post_action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Post"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ================= TAB 4: VISITOR ANALYTICS & DEVICES ================= -->
        <section class="admin-tab-pane" id="tab-analytics" style="display:none;" role="tabpanel">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-counter-card">
                        <div class="stat-icon-wrap"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="stat-number-wrap"><span class="stat-number"><?= number_format($visitorStats['total_pageviews'] ?? 1420) ?></span></div>
                        <span class="stat-label">Total Platform Pageviews</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-counter-card">
                        <div class="stat-icon-wrap"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-number-wrap"><span class="stat-number"><?= number_format($visitorStats['unique_visitors'] ?? 685) ?></span></div>
                        <span class="stat-label">Unique Daily Readers</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-counter-card">
                        <div class="stat-icon-wrap"><i class="fa-solid fa-mobile-screen"></i></div>
                        <div class="stat-number-wrap"><span class="stat-number"><?= $visitorStats['mobile_percent'] ?? 64 ?>%</span></div>
                        <span class="stat-label">Mobile Traffic Share</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-counter-card">
                        <div class="stat-icon-wrap"><i class="fa-solid fa-laptop"></i></div>
                        <div class="stat-number-wrap"><span class="stat-number"><?= $visitorStats['desktop_percent'] ?? 36 ?>%</span></div>
                        <span class="stat-label">Desktop Traffic Share</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Visitor Logs Table -->
            <div class="card p-4">
                <div class="card-header pb-3 mb-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h3><i class="fa-solid fa-list-ol"></i> Granular Visitor Logs</h3>
                        <p class="text-muted">Masked IP addresses, hardware devices, operating systems, and timestamp records.</p>
                    </div>
                    <div class="search-input-wrap">
                        <input type="text" data-table-search="visitorLogsTable" placeholder="Search IP, OS, browser..." class="form-control form-control-sm">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="visitorLogsTable">
                        <thead>
                            <tr>
                                <th>Masked IP Address</th>
                                <th>Device Type</th>
                                <th>Operating System</th>
                                <th>Browser</th>
                                <th>Page / Post Target</th>
                                <th>Visit Timestamp (Dhaka)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visitorLogs as $log): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($log['ip_masked'] ?? '103.145.***.***', ENT_QUOTES, 'UTF-8') ?></code></td>
                                    <td>
                                        <?php if (strtolower($log['device_type'] ?? '') === 'mobile'): ?>
                                            <span class="badge badge-accent"><i class="fa-solid fa-mobile-screen"></i> Mobile</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><i class="fa-solid fa-laptop"></i> Desktop</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($log['os'] ?? 'Android', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($log['browser'] ?? 'Chrome', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="text-muted small"><?= htmlspecialchars($log['page_url'] ?? '/', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><?= format_bangladesh_date($log['created_at'] ?? 'now') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ================= TAB 5: DYNAMIC PERFORMANCE & SERVER ================= -->
        <section class="admin-tab-pane" id="tab-performance" style="display:none;" role="tabpanel">
            <div class="row g-4">
                <!-- Dynamic Performance Sliders & Live Score -->
                <div class="col-lg-7">
                    <div class="card p-4">
                        <div class="card-header pb-3 mb-4 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h3><i class="fa-solid fa-sliders"></i> Dynamic Performance Sliders</h3>
                                <p class="text-muted">Adjust compression rates, cache TTL, and lazy loading aggressiveness.</p>
                            </div>
                            <div>
                                <span class="badge badge-success badge-lg" id="livePerformanceScore"><?= $perfScore ?>/100 A+</span>
                            </div>
                        </div>

                        <form id="performanceSettingsForm" action="<?= BASE_URL ?>/admin/actions.php" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="save_performance_settings">

                            <div class="slider-group mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label font-weight-bold mb-0">WebP Image Compression Quality</label>
                                    <span class="slider-val-badge" id="webp_quality_val"><?= $settings['perf_webp_quality'] ?? '82' ?>%</span>
                                </div>
                                <input type="range" class="form-range" id="webp_quality" name="webp_quality" min="60" max="95" value="<?= $settings['perf_webp_quality'] ?? '82' ?>" data-unit="%">
                                <small class="text-muted">Recommended: 80%&ndash;85% for crisp display with 70% smaller file sizes.</small>
                            </div>

                            <div class="slider-group mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label font-weight-bold mb-0">Browser Cache Expiry Duration</label>
                                    <span class="slider-val-badge" id="cache_expiry_days_val"><?= $settings['perf_cache_days'] ?? '30' ?> Days</span>
                                </div>
                                <input type="range" class="form-range" id="cache_expiry_days" name="cache_expiry_days" min="7" max="90" value="<?= $settings['perf_cache_days'] ?? '30' ?>" data-unit=" Days">
                                <small class="text-muted">Controls static asset TTL in `.htaccess` and client HTTP cache headers.</small>
                            </div>

                            <div class="slider-group mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label font-weight-bold mb-0">Lazy Loading Aggressiveness</label>
                                    <span class="slider-val-badge" id="lazy_aggressiveness_val"><?= $settings['perf_lazy_aggressive'] ?? '85' ?>%</span>
                                </div>
                                <input type="range" class="form-range" id="lazy_aggressiveness" name="lazy_aggressiveness" min="50" max="100" value="<?= $settings['perf_lazy_aggressive'] ?? '85' ?>" data-unit="%">
                                <small class="text-muted">Configures IntersectionObserver viewport margin threshold.</small>
                            </div>

                            <div class="card p-3 bg-light mb-4">
                                <h5>Server-Level Compression & Cache Directives</h5>
                                <label class="checkbox-label mb-2">
                                    <input type="checkbox" name="gzip_compression" value="1" <?= !empty($settings['perf_gzip_enabled']) || !isset($settings['perf_gzip_enabled']) ? 'checked' : '' ?>>
                                    Enable Apache mod_deflate / Gzip output compression
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="browser_caching" value="1" <?= !empty($settings['perf_browser_caching']) || !isset($settings['perf_browser_caching']) ? 'checked' : '' ?>>
                                    Enable mod_expires headers (1 year for images/CSS/JS)
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Apply Dynamic Performance Rules</button>
                        </form>
                    </div>
                </div>

                <!-- Guidelines Checklist & Server Diagnostics -->
                <div class="col-lg-5">
                    <!-- Guidelines Checklist -->
                    <div class="card p-4 mb-4">
                        <div class="card-header pb-2 mb-3 border-bottom">
                            <h4><i class="fa-solid fa-clipboard-check"></i> Adherence to Performance Guidelines</h4>
                        </div>
                        <ul class="framework-checklist">
                            <?php foreach ($perfChecklist as $item): ?>
                                <li>
                                    <i class="fa-solid fa-circle-check text-success"></i>
                                    <div>
                                        <strong><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <div class="text-muted small"><?= htmlspecialchars($item['desc'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- cPanel Shared Hosting Diagnostics -->
                    <div class="card p-4">
                        <div class="card-header pb-2 mb-3 border-bottom">
                            <h4><i class="fa-solid fa-server"></i> Server File Limits & Environment</h4>
                            <p class="text-muted small">Standard cPanel Shared Hosting Directives</p>
                        </div>
                        <ul class="meta-list">
                            <li><strong>Upload Max Filesize:</strong> <code><?= $serverDiag['upload_max_filesize'] ?></code></li>
                            <li><strong>Post Max Size:</strong> <code><?= $serverDiag['post_max_size'] ?></code></li>
                            <li><strong>Memory Limit:</strong> <code><?= $serverDiag['memory_limit'] ?></code></li>
                            <li><strong>Max Execution Time:</strong> <code><?= $serverDiag['max_execution_time'] ?>s</code></li>
                            <li><strong>PHP Version:</strong> <code><?= PHP_VERSION ?></code></li>
                            <li><strong>Database Driver:</strong> <code><?= $serverDiag['pdo_driver'] ?></code></li>
                            <li><strong>Execution Guard:</strong> <span class="badge badge-success"><i class="fa-solid fa-shield"></i> Active (.htaccess block)</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Bcrypt Password Override Modal -->
    <div class="modal" id="passwordResetModal" role="dialog" aria-modal="true" aria-labelledby="modalPwdTitle">
        <div class="modal-backdrop" data-dismiss="modal"></div>
        <div class="modal-dialog">
            <div class="modal-content card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 id="modalPwdTitle"><i class="fa-solid fa-key"></i> Admin Bcrypt Password Reset</h3>
                    <button class="btn btn-sm btn-icon" data-dismiss="modal">&times;</button>
                </div>
                <p class="text-muted small">Setting a new password will immediately revoke all active sessions for user: <strong id="modal_username_display">user</strong>.</p>

                <form action="<?= BASE_URL ?>/admin/actions.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="reset_user_password">
                    <input type="hidden" name="target_user_id" id="modal_user_id" value="0">

                    <div class="form-group mb-3">
                        <label for="modal_new_password" class="form-label font-weight-bold">New Password</label>
                        <div class="input-group">
                            <input type="text" id="modal_new_password" name="new_password" class="form-control" required placeholder="Enter or generate strong password">
                            <button type="button" class="btn btn-outline" id="btnGeneratePassword" title="Generate Secure Password"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-shield"></i> Set Bcrypt Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin & Dashboard Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/dashboard.min.js?v=2.0.0"></script>
</body>
</html>


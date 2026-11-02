<?php
/**
 * A Greener Tomorrow - Complete Enterprise Admin Governance Dashboard
 * Redesigned with Left Sidebar Navigation & 7 Structured Management Tabs:
 * 1. Overview & KPIs (#overview)
 * 2. Publish & Content Moderation (#content)
 * 3. Hero Slider & Photo Gallery (#media)
 * 4. Comments & Contact Inbox (#communications)
 * 5. Users, Roles & Bcrypt Passwords (#users)
 * 6. Site Identity, Stats & Logo MCQ (#settings)
 * 7. Visitor Analytics, Performance & Server (#performance)
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

// Feedback notifications
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// 1. Settings & Media
$settings = get_all_settings();
$slides = $db->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id DESC")->fetchAll();
$galleryItems = $db->query("SELECT * FROM gallery_items ORDER BY id DESC")->fetchAll();

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

// 4. Comments & Contact Messages
$comments = [];
try {
    $comments = $db->query("
        SELECT c.*, p.title as post_title, p.slug as post_slug, p.type as post_type
        FROM post_comments c
        LEFT JOIN posts p ON c.post_id = p.id
        ORDER BY c.id DESC LIMIT 50
    ")->fetchAll();
} catch (PDOException $e) {}

$contactMessages = [];
try {
    $contactMessages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 30")->fetchAll();
} catch (PDOException $e) {}

// 5. Visitor Analytics & Performance
$visitorStats = Tracker::getVisitorSummary();
$visitorLogs = Tracker::getRecentVisitors(30);
$perfScore = PerformanceMonitor::calculateScore();
$perfChecklist = PerformanceMonitor::getChecklist();
$serverDiag = PerformanceMonitor::getServerDiagnostics();

$totalReadsSum = array_sum(array_column($posts, 'view_count'));
$pageTitle = 'Central Governance Dashboard | A Greener Tomorrow';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.min.css?v=3.0.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.min.css?v=3.0.0">
</head>
<body class="dashboard-body admin-layout-body">

    <!-- Mobile Sidebar Backdrop -->
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

    <div class="admin-shell">
        <!-- ================= LEFT SIDEBAR NAVIGATION ================= -->
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin Sidebar Navigation">
            <div class="admin-sidebar-brand">
                <a href="<?= BASE_URL ?>/index.php" class="admin-brand-link">
                    <img src="<?= BASE_URL ?>/assets/brand/brand-logo.png" alt="Logo" class="admin-brand-img" width="40" height="40">
                    <div>
                        <span class="admin-brand-title">A Greener Tomorrow</span>
                        <span class="admin-brand-sub">Governance Console &bull; Version 2</span>
                    </div>
                </a>
                <button type="button" class="admin-sidebar-close d-lg-none" id="adminSidebarClose" aria-label="Close Sidebar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="admin-sidebar-user">
                <img src="<?= BASE_URL ?>/assets/uploads/avatars/avatar-admin.webp" alt="Admin" class="admin-user-avatar" width="42" height="42">
                <div class="admin-user-meta">
                    <strong><?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><i class="fa-solid fa-shield-halved"></i> Executive Admin</span>
                </div>
            </div>

            <div class="admin-sidebar-group-label">Core Workspace</div>
            <nav class="admin-tabs-nav" role="tablist">
                <button type="button" class="tab-btn active" data-tab="overview" role="tab">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Executive Overview</span>
                </button>
                <button type="button" class="tab-btn" data-tab="content" role="tab">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Posts &amp; Publishing</span>
                    <span class="sidebar-count-pill"><?= count($posts) ?></span>
                </button>
                <button type="button" class="tab-btn" data-tab="media" role="tab">
                    <i class="fa-solid fa-images"></i>
                    <span>Hero Slider &amp; Gallery</span>
                    <span class="sidebar-count-pill"><?= count($slides) + count($galleryItems) ?></span>
                </button>
                <button type="button" class="tab-btn" data-tab="communications" role="tab">
                    <i class="fa-solid fa-comments"></i>
                    <span>Comments &amp; Inbox</span>
                    <span class="sidebar-count-pill"><?= count($comments) + count($contactMessages) ?></span>
                </button>

                <div class="admin-sidebar-group-label mt-3">Administration &amp; System</div>
                <button type="button" class="tab-btn" data-tab="users" role="tab">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Users &amp; Passwords</span>
                    <span class="sidebar-count-pill"><?= count($users) ?></span>
                </button>
                <button type="button" class="tab-btn" data-tab="settings" role="tab">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Site Info &amp; Logo MCQ</span>
                </button>
                <button type="button" class="tab-btn" data-tab="performance" role="tab">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Analytics &amp; Server</span>
                    <span class="sidebar-count-pill pill-emerald"><?= $perfScore ?>%</span>
                </button>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="<?= BASE_URL ?>/dashboard/index.php" class="sidebar-footer-link">
                    <i class="fa-solid fa-table-columns"></i> Contributor Portal
                </a>
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="sidebar-footer-link">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View Live Website
                </a>
                <a href="<?= BASE_URL ?>/logout.php" class="sidebar-footer-link text-danger">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- ================= MAIN WORKSPACE AREA ================= -->
        <div class="admin-main-area">
            <!-- Top Workspace Header Bar -->
            <header class="admin-workspace-header">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="admin-mobile-toggle d-lg-none" id="adminSidebarToggle" aria-label="Open Sidebar Menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="admin-page-heading" id="activeTabTitle">Executive Overview</h1>
                        <p class="admin-page-subheading">Hatirpool Central Secretariat &bull; Real-time Database &amp; Content Control</p>
                    </div>
                </div>

                <div class="admin-header-actions">
                    <button type="button" class="btn btn-sm btn-primary js-jump-tab" data-target-tab="content">
                        <i class="fa-solid fa-plus"></i> New Publication
                    </button>
                    <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-sm btn-outline">
                        <i class="fa-solid fa-globe"></i> Live Site
                    </a>
                </div>
            </header>

            <div class="admin-workspace-content">
                <!-- Action Feedback Banner -->
                <?php if ($msg === 'settings_saved'): ?>
                    <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> Organization settings, impact counters, and MCQ logo placements updated.</div>
                <?php elseif ($msg === 'post_created'): ?>
                    <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> New publication created and published live!</div>
                <?php elseif ($msg === 'pwd_reset_ok'): ?>
                    <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> User password updated with Bcrypt cost 12 and active sessions revoked.</div>
                <?php elseif ($msg === 'user_status_updated' || $msg === 'role_updated' || $msg === 'user_created'): ?>
                    <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> User account directory and privileges updated.</div>
                <?php elseif ($msg === 'post_published' || $msg === 'post_drafted'): ?>
                    <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-circle-check"></i> Post publication status updated.</div>
                <?php elseif ($msg === 'post_flagged_offensive'): ?>
                    <div class="alert alert-warning alert-dismissible mb-4"><i class="fa-solid fa-triangle-exclamation"></i> Content flagged as offensive and removed from public feeds.</div>
                <?php elseif ($msg === 'post_deleted' || $msg === 'comment_deleted' || $msg === 'message_deleted'): ?>
                    <div class="alert alert-danger alert-dismissible mb-4"><i class="fa-solid fa-trash"></i> Selected record permanently removed.</div>
                <?php elseif ($msg === 'slide_saved' || $msg === 'slide_deleted' || $msg === 'gallery_added' || $msg === 'gallery_deleted'): ?>
                    <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-images"></i> Media asset library updated successfully.</div>
                <?php elseif ($msg === 'perf_saved'): ?>
                    <div class="alert alert-success alert-dismissible mb-4"><i class="fa-solid fa-gauge-high"></i> Dynamic performance parameters saved and active.</div>
                <?php elseif (!empty($err)): ?>
                    <div class="alert alert-danger alert-dismissible mb-4"><i class="fa-solid fa-triangle-exclamation"></i> Operation could not be completed (Code: <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>).</div>
                <?php endif; ?>

                <!-- ================= TAB 1: EXECUTIVE OVERVIEW ================= -->
                <section class="admin-tab-pane active" id="tab-overview" data-title="Executive Overview" role="tabpanel">
                    <div class="admin-kpi-grid mb-4">
                        <div class="admin-kpi-card">
                            <div class="kpi-icon kpi-emerald"><i class="fa-solid fa-layer-group"></i></div>
                            <div>
                                <div class="kpi-value"><?= count($posts) ?></div>
                                <div class="kpi-label">Total Publications</div>
                            </div>
                        </div>
                        <div class="admin-kpi-card">
                            <div class="kpi-icon kpi-blue"><i class="fa-solid fa-eye"></i></div>
                            <div>
                                <div class="kpi-value"><?= number_format($totalReadsSum) ?></div>
                                <div class="kpi-label">Cumulative Readership</div>
                            </div>
                        </div>
                        <div class="admin-kpi-card">
                            <div class="kpi-icon kpi-amber"><i class="fa-solid fa-comments"></i></div>
                            <div>
                                <div class="kpi-value"><?= count($comments) ?></div>
                                <div class="kpi-label">Member Comments</div>
                            </div>
                        </div>
                        <div class="admin-kpi-card">
                            <div class="kpi-icon kpi-purple"><i class="fa-solid fa-users"></i></div>
                            <div>
                                <div class="kpi-value"><?= count($users) ?></div>
                                <div class="kpi-label">Registered Accounts</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Action Cards -->
                    <div class="card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h3 class="mb-1"><i class="fa-solid fa-bolt text-accent"></i> Quick Management Launchpad</h3>
                                <p class="text-muted small mb-0">Jump directly to any administrative module in the sidebar.</p>
                            </div>
                        </div>
                        <div class="admin-quick-grid">
                            <button type="button" class="quick-launch-card js-jump-tab" data-target-tab="content">
                                <i class="fa-solid fa-file-circle-plus"></i>
                                <strong>Publish Post / Research</strong>
                                <span>Create Activities, Researches, Blogs &amp; Vlogs</span>
                            </button>
                            <button type="button" class="quick-launch-card js-jump-tab" data-target-tab="media">
                                <i class="fa-solid fa-panorama"></i>
                                <strong>Hero Banners &amp; Gallery</strong>
                                <span>Manage homepage slider &amp; field photography</span>
                            </button>
                            <button type="button" class="quick-launch-card js-jump-tab" data-target-tab="communications">
                                <i class="fa-solid fa-inbox"></i>
                                <strong>Comments &amp; Inquiries</strong>
                                <span>Moderate reader replies &amp; secretariat messages</span>
                            </button>
                            <button type="button" class="quick-launch-card js-jump-tab" data-target-tab="settings">
                                <i class="fa-solid fa-building-shield"></i>
                                <strong>Site Identity &amp; Logo MCQ</strong>
                                <span>Update address, hotlines, stats &amp; brand logo</span>
                            </button>
                        </div>
                    </div>

                    <!-- Recent Publications Snapshot -->
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0"><i class="fa-solid fa-clock-rotate-left"></i> Latest Platform Publications</h3>
                            <button type="button" class="btn btn-sm btn-outline js-jump-tab" data-target-tab="content">Manage All Posts</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Format</th>
                                        <th>Contributor</th>
                                        <th>Views</th>
                                        <th>Status</th>
                                        <th class="text-right">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($posts, 0, 5) as $p): ?>
                                        <?php
                                        $vUrl = BASE_URL . '/' . ($p['post_type'] === 'activity' ? 'activities.php' : ($p['post_type'] === 'research' ? 'researches.php' : 'blog.php')) . '?slug=' . urlencode($p['slug']);
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars(truncate_text($p['title'], 52), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><span class="badge badge-secondary"><?= strtoupper($p['post_type']) ?></span></td>
                                            <td><?= htmlspecialchars($p['author_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= number_format($p['view_count']) ?></td>
                                            <td><span class="badge badge-success"><?= ucfirst($p['status']) ?></span></td>
                                            <td class="text-right">
                                                <a href="<?= $vUrl ?>" target="_blank" class="btn btn-sm btn-outline"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ================= TAB 2: PUBLISH & CONTENT MODERATION ================= -->
                <section class="admin-tab-pane" id="tab-content" data-title="Posts & Content Publishing" style="display:none;" role="tabpanel">
                    <!-- Direct Post Creation Card -->
                    <div class="card p-4 mb-4">
                        <div class="card-header pb-3 mb-4 border-bottom">
                            <h3><i class="fa-solid fa-feather-pointed"></i> Publish New Activity, Research Paper, Blog or Vlog</h3>
                            <p class="text-muted mb-0">Publish directly to the live website with automatic WebP image optimization and PDF attachment support.</p>
                        </div>

                        <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="admin_create_post">

                            <div class="form-row-2 mb-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Publication Title *</label>
                                    <input type="text" name="title" class="form-control" required placeholder="Enter headline...">
                                </div>
                                <div class="form-row-2">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Content Format *</label>
                                        <select name="post_type" class="form-control">
                                            <option value="BLOG">Editorial Blog</option>
                                            <option value="ACTIVITY">Field Activity / Drive</option>
                                            <option value="RESEARCH">Scientific Research Paper</option>
                                            <option value="VLOG">Video Vlog</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="form-label">Action Pillar *</label>
                                        <select name="pillar" class="form-control">
                                            <option value="CLIMATE">Climate Adaptation</option>
                                            <option value="DEFORESTATION">Reforestation</option>
                                            <option value="POLLUTION">Pollution Control</option>
                                            <option value="DISASTER">Disaster Resilience</option>
                                            <option value="ENVIRONMENT">Biodiversity</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-2 mb-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Executive Summary / Abstract</label>
                                    <input type="text" name="excerpt" class="form-control" placeholder="Brief 1-2 sentence summary for post cards...">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label">Field Location / District</label>
                                    <input type="text" name="location" class="form-control" value="Hatirpool, Dhaka, Bangladesh">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Full Article / Research Body (HTML or Plain Text) *</label>
                                <textarea name="content" rows="5" class="form-control" required placeholder="Write the full article, field findings, or research methodology..."></textarea>
                            </div>

                            <div class="form-row-2 mb-4">
                                <div class="form-group mb-0">
                                    <label class="form-label">Cover Photograph (Auto-WebP &bull; Max 8MB)</label>
                                    <input type="file" name="featured_image" class="form-control form-control-sm" accept="image/*" data-max-mb="8">
                                </div>
                                <div class="form-row-2">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Research PDF / Doc (Optional)</label>
                                        <input type="file" name="research_doc" class="form-control form-control-sm" accept=".pdf,.doc,.docx" data-max-mb="15">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="form-label">YouTube URL (For Vlogs)</label>
                                        <input type="url" name="video_url" class="form-control form-control-sm" placeholder="https://youtube.com/watch?v=...">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publish Now</button>
                        </form>
                    </div>

                    <!-- Content Moderation Table -->
                    <div class="card p-4">
                        <div class="card-header pb-3 mb-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h3><i class="fa-solid fa-list-check"></i> All Publications &amp; Content Moderation</h3>
                                <p class="text-muted mb-0">Approve, unpublish, flag offensive violations, or delete submissions.</p>
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
                                            <td><strong><?= htmlspecialchars(truncate_text($p['title'], 48), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><span class="badge badge-secondary"><?= ucfirst($p['post_type']) ?></span></td>
                                            <td><?= htmlspecialchars($p['author_name'] ?? $p['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><i class="fa-regular fa-eye"></i> <?= number_format($p['view_count']) ?></td>
                                            <td><span class="badge <?= $statClass ?>"><?= ucfirst($p['status']) ?></span></td>
                                            <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                            <td class="text-right">
                                                <a href="<?= $viewUrl ?>" target="_blank" class="btn btn-sm btn-outline" title="View Details Page"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>

                                                <?php if ($p['status'] !== 'published'): ?>
                                                    <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="moderate_post">
                                                        <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                                        <input type="hidden" name="post_action" value="publish">
                                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i> Publish</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="moderate_post">
                                                        <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                                        <input type="hidden" name="post_action" value="unpublish">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye-slash"></i> Draft</button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($p['status'] !== 'offensive'): ?>
                                                    <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Flag and remove this post as offensive?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="moderate_post">
                                                        <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                                        <input type="hidden" name="post_action" value="offensive">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Flag Offensive"><i class="fa-solid fa-triangle-exclamation"></i></button>
                                                    </form>
                                                <?php endif; ?>

                                                <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Permanently delete this post?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="moderate_post">
                                                    <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                                    <input type="hidden" name="post_action" value="delete">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ================= TAB 3: HERO SLIDER & PHOTO GALLERY ================= -->
                <section class="admin-tab-pane" id="tab-media" data-title="Hero Slider & Photo Gallery" style="display:none;" role="tabpanel">
                    <div class="row g-4">
                        <!-- Hero Slider Management -->
                        <div class="col-lg-6">
                            <div class="card p-4">
                                <div class="card-header pb-3 mb-3 border-bottom">
                                    <h3><i class="fa-solid fa-panorama"></i> Homepage Hero Slider</h3>
                                    <p class="text-muted small mb-0">Manage full-width black-overlay hero banners on the homepage.</p>
                                </div>

                                <div class="slide-list mb-4">
                                    <?php foreach ($slides as $sl): ?>
                                        <div class="slide-list-item d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded border">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= BASE_URL . '/' . ltrim($sl['image_path'], '/') ?>" alt="Slide" width="68" height="44" class="table-thumb">
                                                <div>
                                                    <span class="badge badge-accent badge-sm"><?= htmlspecialchars($sl['badge_text'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <div class="font-weight-bold small mt-1"><?= htmlspecialchars(truncate_text($sl['title'], 42), ENT_QUOTES, 'UTF-8') ?></div>
                                                </div>
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

                                <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" enctype="multipart/form-data" class="p-3 bg-light rounded border">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="save_hero_slide">
                                    <h4 class="mb-3"><i class="fa-solid fa-plus-circle"></i> Add New Hero Slide</h4>
                                    <div class="form-group mb-2">
                                        <input type="text" name="slide_title" class="form-control form-control-sm" required placeholder="Slide Headline *">
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="text" name="slide_subtitle" class="form-control form-control-sm" placeholder="Subtitle / Campaign Description">
                                    </div>
                                    <div class="form-row-2 mb-2">
                                        <input type="text" name="slide_badge" class="form-control form-control-sm" placeholder="Badge (e.g. Live Action)">
                                        <input type="text" name="slide_cta_url" class="form-control form-control-sm" placeholder="CTA Link (e.g. /activities.php)">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small text-muted">Banner Image (WebP/JPG &bull; Max 8MB)</label>
                                        <input type="file" name="slide_image" class="form-control form-control-sm" accept="image/*" data-max-mb="8">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add Hero Slide</button>
                                </form>
                            </div>
                        </div>

                        <!-- Photo Gallery Management -->
                        <div class="col-lg-6">
                            <div class="card p-4">
                                <div class="card-header pb-3 mb-3 border-bottom">
                                    <h3><i class="fa-solid fa-camera-retro"></i> Field Photo Gallery (<?= count($galleryItems) ?>)</h3>
                                    <p class="text-muted small mb-0">Add or remove category-filtered photographs in the Gallery.</p>
                                </div>

                                <div class="slide-list mb-4" style="max-height: 290px; overflow-y: auto;">
                                    <?php foreach ($galleryItems as $gItem): ?>
                                        <div class="slide-list-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded border">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= BASE_URL . '/' . ltrim($gItem['thumbnail_path'] ?? $gItem['image_path'], '/') ?>" alt="Gallery" width="60" height="42" class="table-thumb">
                                                <div>
                                                    <span class="badge badge-secondary badge-sm"><?= htmlspecialchars($gItem['category'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <div class="font-weight-bold small mt-1"><?= htmlspecialchars(truncate_text($gItem['title'], 38), ENT_QUOTES, 'UTF-8') ?></div>
                                                </div>
                                            </div>
                                            <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" onsubmit="return confirm('Remove this photo from gallery?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete_gallery_item">
                                                <input type="hidden" name="gallery_id" value="<?= $gItem['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" enctype="multipart/form-data" class="p-3 bg-light rounded border">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="add_gallery_item">
                                    <h4 class="mb-3"><i class="fa-solid fa-cloud-arrow-up"></i> Upload New Gallery Photo</h4>
                                    <div class="form-row-2 mb-2">
                                        <input type="text" name="gallery_title" class="form-control form-control-sm" required placeholder="Photo Title *">
                                        <select name="gallery_category" class="form-control form-control-sm">
                                            <option value="Reforestation">Reforestation</option>
                                            <option value="Pollution Control">Pollution Control</option>
                                            <option value="Youth Mobilization">Youth Mobilization</option>
                                            <option value="Climate Adaptation">Climate Adaptation</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="text" name="gallery_caption" class="form-control form-control-sm" placeholder="Photo Caption & District Location">
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="file" name="gallery_image" class="form-control form-control-sm" accept="image/*" data-max-mb="8">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-upload"></i> Add to Gallery</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ================= TAB 4: COMMENTS & CONTACT INBOX ================= -->
                <section class="admin-tab-pane" id="tab-communications" data-title="Member Comments & Secretariat Inbox" style="display:none;" role="tabpanel">
                    <div class="card p-4 mb-4">
                        <div class="card-header pb-3 mb-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h3><i class="fa-solid fa-comments"></i> Community Comments &amp; Replies (<?= count($comments) ?>)</h3>
                                <p class="text-muted small mb-0">Review and moderate opinions posted by members on Blog, Research, and Activity details pages.</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Author</th>
                                        <th>Comment / Reply</th>
                                        <th>Target Publication</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comments as $cm): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($cm['author_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <div class="text-muted small"><?= htmlspecialchars($cm['author_role'] ?? 'Member', ENT_QUOTES, 'UTF-8') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars(truncate_text($cm['comment_text'], 75), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><strong><?= htmlspecialchars(truncate_text($cm['post_title'] ?? 'Post #'.$cm['post_id'], 36), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td>
                                                <?php if ((int)$cm['parent_id'] > 0): ?>
                                                    <span class="badge badge-secondary">Reply</span>
                                                <?php else: ?>
                                                    <span class="badge badge-primary">Comment</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= format_bangladesh_date($cm['created_at']) ?></td>
                                            <td class="text-right">
                                                <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this comment?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete_comment">
                                                    <input type="hidden" name="comment_id" value="<?= $cm['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Contact Messages Inbox -->
                    <div class="card p-4">
                        <div class="card-header pb-3 mb-3 border-bottom">
                            <h3><i class="fa-solid fa-envelope-open-text"></i> Central Secretariat Contact Inquiries (<?= count($contactMessages) ?>)</h3>
                            <p class="text-muted small mb-0">Direct dispatches submitted through the Contact Secretariat form.</p>
                        </div>
                        <?php if (!empty($contactMessages)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Sender</th>
                                            <th>Email / Phone</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Received</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contactMessages as $m): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                                <td>
                                                    <div><?= htmlspecialchars($m['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($m['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($m['subject'] ?? 'General Inquiry', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars(truncate_text($m['message'], 70), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= format_bangladesh_date($m['created_at']) ?></td>
                                                <td class="text-right">
                                                    <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" onsubmit="return confirm('Delete this inquiry?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="delete_contact_message">
                                                        <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No contact inquiries in the inbox yet.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- ================= TAB 5: USERS, ROLES & BCRYPT PASSWORDS ================= -->
                <section class="admin-tab-pane" id="tab-users" data-title="User Directory & Credential Oversight" style="display:none;" role="tabpanel">
                    <div class="card p-4 mb-4">
                        <div class="card-header pb-3 mb-3 border-bottom">
                            <h3><i class="fa-solid fa-user-plus"></i> Register New Team Member / Researcher / Admin</h3>
                        </div>
                        <form action="<?= BASE_URL ?>/admin/actions.php" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="admin_create_user">
                            <div class="form-row-2 mb-3">
                                <input type="text" name="name" class="form-control form-control-sm" required placeholder="Full Name *">
                                <input type="email" name="email" class="form-control form-control-sm" required placeholder="Email Address *">
                            </div>
                            <div class="form-row-2 mb-3">
                                <input type="text" name="username" class="form-control form-control-sm" required placeholder="Username *">
                                <input type="password" name="password" class="form-control form-control-sm" required placeholder="Initial Password (Min 6 chars) *">
                            </div>
                            <div class="form-row-2 mb-3">
                                <select name="role" class="form-control form-control-sm">
                                    <option value="USER">Registered Member</option>
                                    <option value="RESEARCHER">Environmental Researcher</option>
                                    <option value="ADMIN">Administrator</option>
                                </select>
                                <input type="text" name="district" class="form-control form-control-sm" value="Dhaka" placeholder="District / Chapter">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-check"></i> Create Account</button>
                        </form>
                    </div>

                    <div class="card p-4">
                        <div class="card-header pb-3 mb-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h3><i class="fa-solid fa-users-shield"></i> User Credential Management &amp; Bcrypt Resets</h3>
                                <p class="text-muted mb-0">Manage roles, suspend accounts, or execute Bcrypt cost 12 password resets.</p>
                            </div>
                            <div class="search-input-wrap">
                                <input type="text" data-table-search="adminUsersTable" placeholder="Search users..." class="form-control form-control-sm">
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
                                            <td class="text-right">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-reset-user-pwd" data-user-id="<?= $u['id'] ?>" data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fa-solid fa-key"></i> Reset Password
                                                </button>
                                                <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                                    <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Toggle account status?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="toggle_user_status">
                                                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                        <input type="hidden" name="current_status" value="<?= $u['is_active'] ?>">
                                                        <button type="submit" class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
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

                <!-- ================= TAB 6: SITE IDENTITY, STATS & LOGO MCQ ================= -->
                <section class="admin-tab-pane" id="tab-settings" data-title="Site Identity & Logo MCQ Placement" style="display:none;" role="tabpanel">
                    <div class="card p-4">
                        <div class="card-header pb-3 mb-4 border-bottom">
                            <h3><i class="fa-solid fa-building-ngo"></i> Organization Configuration, Impact Counters &amp; MCQ Logo Placement</h3>
                            <p class="text-muted mb-0">Control global branding, Dhaka Hatirpool HQ contact details, and where the brand logo appears.</p>
                        </div>

                        <form action="<?= BASE_URL ?>/admin/actions.php" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="save_site_settings">

                            <div class="form-row-2 mb-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Organization / Website Name</label>
                                    <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? 'A Greener Tomorrow', ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label">Official Tagline</label>
                                    <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars($settings['site_tagline'] ?? 'Youth Climate Mobilization', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>

                            <div class="form-row-2 mb-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Secretariat Public Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? 'greener2morow@gmail.com', ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label">Secretariat Hotline / Phone</label>
                                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($settings['contact_phone'] ?? '+880 1712-345678', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label">Central Secretariat Physical Address</label>
                                <input type="text" name="contact_address" class="form-control" value="<?= htmlspecialchars($settings['contact_address'] ?? '36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <!-- Impact Counters -->
                            <div class="card p-3 mb-4 bg-light">
                                <h4 class="mb-3"><i class="fa-solid fa-chart-simple"></i> Homepage Impact Metrics</h4>
                                <div class="form-row-2">
                                    <div class="form-group mb-2">
                                        <label class="form-label small">Trees Planted</label>
                                        <input type="number" name="stat_trees" class="form-control form-control-sm" value="<?= (int)($settings['stat_trees'] ?? 50000) ?>">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="form-label small">Youth Mobilized</label>
                                        <input type="number" name="stat_members" class="form-control form-control-sm" value="<?= (int)($settings['stat_members'] ?? 18500) ?>">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="form-label small">Active Districts</label>
                                        <input type="number" name="stat_districts" class="form-control form-control-sm" value="<?= (int)($settings['stat_districts'] ?? 64) ?>">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="form-label small">Published Researches</label>
                                        <input type="number" name="stat_researches" class="form-control form-control-sm" value="<?= (int)($settings['stat_researches'] ?? 32) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-2 mb-4">
                                <!-- MCQ Logo Placement Matrix -->
                                <div class="card p-3 bg-light">
                                    <h4 class="mb-2"><i class="fa-solid fa-bullseye"></i> MCQ Logo Placement Matrix</h4>
                                    <p class="text-muted small mb-3">Choose where the official brand logo is displayed:</p>
                                    <div class="mcq-checkbox-group">
                                        <label class="checkbox-label mb-2">
                                            <input type="checkbox" name="logo_use_navbar" value="1" <?= !empty($settings['logo_use_navbar']) ? 'checked' : '' ?>>
                                            <strong>Navbar Header</strong> (Top navigation bar)
                                        </label>
                                        <label class="checkbox-label mb-2">
                                            <input type="checkbox" name="logo_use_footer" value="1" <?= !empty($settings['logo_use_footer']) ? 'checked' : '' ?>>
                                            <strong>Global Footer</strong> (Bottom brand column)
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="logo_use_section" value="1" <?= !empty($settings['logo_use_section']) ? 'checked' : '' ?>>
                                            <strong>Content Sections</strong> (Internal callouts)
                                        </label>
                                    </div>
                                </div>

                                <!-- Brand Logo Upload -->
                                <div class="card p-3 bg-light">
                                    <h4 class="mb-2"><i class="fa-solid fa-image"></i> Brand Logo Asset</h4>
                                    <div class="d-flex align-items-center gap-3 mt-2">
                                        <div class="logo-preview-box">
                                            <img src="<?= BASE_URL ?>/assets/brand/brand-logo.png" alt="Logo" width="60" height="60">
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" name="site_logo" class="form-control form-control-sm mb-2" accept="image/*" data-max-mb="2">
                                            <input type="text" name="logo_alt" class="form-control form-control-sm" placeholder="Logo Alt Text" value="<?= htmlspecialchars($settings['logo_alt'] ?? 'A Greener Tomorrow', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-floppy-disk"></i> Save All Site Settings</button>
                        </form>
                    </div>
                </section>

                <!-- ================= TAB 7: ANALYTICS, PERFORMANCE & SERVER ================= -->
                <section class="admin-tab-pane" id="tab-performance" data-title="Visitor Analytics & Dynamic Performance" style="display:none;" role="tabpanel">
                    <div class="row g-4 mb-4">
                        <div class="col-lg-7">
                            <div class="card p-4">
                                <div class="card-header pb-3 mb-4 border-bottom d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3><i class="fa-solid fa-sliders"></i> Dynamic Performance Sliders</h3>
                                        <p class="text-muted small mb-0">Fine-tune WebP compression, browser cache TTL, and lazy loading.</p>
                                    </div>
                                    <span class="badge badge-success badge-lg" id="livePerformanceScore"><?= $perfScore ?>/100 A+</span>
                                </div>

                                <form id="performanceSettingsForm" action="<?= BASE_URL ?>/admin/actions.php" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="save_performance_settings">

                                    <div class="slider-group mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0">WebP Image Compression Quality</label>
                                            <span class="slider-val-badge" id="webp_quality_val"><?= $settings['perf_webp_quality'] ?? '82' ?>%</span>
                                        </div>
                                        <input type="range" class="form-range" id="webp_quality" name="webp_quality" min="60" max="95" value="<?= $settings['perf_webp_quality'] ?? '82' ?>" data-unit="%">
                                    </div>

                                    <div class="slider-group mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0">Browser Cache Expiry Duration</label>
                                            <span class="slider-val-badge" id="cache_expiry_days_val"><?= $settings['perf_cache_days'] ?? '30' ?> Days</span>
                                        </div>
                                        <input type="range" class="form-range" id="cache_expiry_days" name="cache_expiry_days" min="7" max="90" value="<?= $settings['perf_cache_days'] ?? '30' ?>" data-unit=" Days">
                                    </div>

                                    <div class="slider-group mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0">Lazy Loading Aggressiveness</label>
                                            <span class="slider-val-badge" id="lazy_aggressiveness_val"><?= $settings['perf_lazy_aggressive'] ?? '85' ?>%</span>
                                        </div>
                                        <input type="range" class="form-range" id="lazy_aggressiveness" name="lazy_aggressiveness" min="50" max="100" value="<?= $settings['perf_lazy_aggressive'] ?? '85' ?>" data-unit="%">
                                    </div>

                                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Performance Rules</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card p-4">
                                <div class="card-header pb-2 mb-3 border-bottom">
                                    <h4><i class="fa-solid fa-server"></i> Namecheap / cPanel Hosting Diagnostics</h4>
                                </div>
                                <ul class="meta-list">
                                    <li><strong>Upload Max Filesize:</strong> <code><?= $serverDiag['upload_max_filesize'] ?></code></li>
                                    <li><strong>Post Max Size:</strong> <code><?= $serverDiag['post_max_size'] ?></code></li>
                                    <li><strong>Memory Limit:</strong> <code><?= $serverDiag['memory_limit'] ?></code></li>
                                    <li><strong>PHP Version:</strong> <code><?= PHP_VERSION ?></code></li>
                                    <li><strong>Active Database Driver:</strong> <code><?= $serverDiag['pdo_driver'] ?></code></li>
                                    <li><strong>Execution Guard:</strong> <span class="badge badge-success">Active (.htaccess)</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Granular Visitor Logs -->
                    <div class="card p-4">
                        <div class="card-header pb-3 mb-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h3><i class="fa-solid fa-chart-line"></i> Recent Visitor Logs &amp; Device Telemetry</h3>
                            </div>
                            <div class="search-input-wrap">
                                <input type="text" data-table-search="visitorLogsTable" placeholder="Search IP, OS, browser..." class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="visitorLogsTable">
                                <thead>
                                    <tr>
                                        <th>Masked IP</th>
                                        <th>Device</th>
                                        <th>OS</th>
                                        <th>Browser</th>
                                        <th>Page Target</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($visitorLogs as $log): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($log['ip_masked'] ?? '103.145.***.***', ENT_QUOTES, 'UTF-8') ?></code></td>
                                            <td><span class="badge badge-secondary"><?= htmlspecialchars($log['device_type'] ?? 'Desktop', ENT_QUOTES, 'UTF-8') ?></span></td>
                                            <td><?= htmlspecialchars($log['os'] ?? 'Windows', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($log['browser'] ?? 'Chrome', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><code><?= htmlspecialchars($log['page_url'] ?? '/', ENT_QUOTES, 'UTF-8') ?></code></td>
                                            <td><?= format_bangladesh_date($log['created_at'] ?? 'now') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Bcrypt Password Override Modal -->
    <div class="modal" id="passwordResetModal" role="dialog" aria-modal="true" aria-labelledby="modalPwdTitle">
        <div class="modal-backdrop" data-dismiss="modal"></div>
        <div class="modal-dialog">
            <div class="modal-content card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 id="modalPwdTitle"><i class="fa-solid fa-key"></i> Admin Bcrypt Password Reset</h3>
                    <button type="button" class="btn btn-sm btn-icon" data-dismiss="modal">&times;</button>
                </div>
                <p class="text-muted small">Setting a new password will immediately revoke active sessions for: <strong id="modal_username_display">user</strong>.</p>

                <form action="<?= BASE_URL ?>/admin/actions.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="reset_user_password">
                    <input type="hidden" name="target_user_id" id="modal_user_id" value="0">

                    <div class="form-group mb-3">
                        <label for="modal_new_password" class="form-label font-weight-bold">New Password</label>
                        <div class="input-group">
                            <input type="text" id="modal_new_password" name="new_password" class="form-control" required placeholder="Enter or generate strong password">
                            <button type="button" class="btn btn-outline" id="btnGeneratePassword"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate</button>
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

    <script src="<?= BASE_URL ?>/assets/js/dashboard.min.js?v=3.0.0"></script>
</body>
</html>

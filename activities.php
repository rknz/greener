<?php
/**
 * A Greener Tomorrow - Activities & Mobilization Programs
 * List view and single initiative detail with participation RSVP.
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/PostRepository.php';
require_once AGY_ROOT . '/includes/Tracker.php';
require_once AGY_ROOT . '/includes/ImageOptimizer.php';

// Log visitor session
Tracker::trackVisit();

$slug = $_GET['slug'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$category = sanitize_text($_GET['cat'] ?? '');
$search = sanitize_text($_GET['q'] ?? '');

// Check if viewing a single activity
$singleActivity = null;
if (!empty($slug)) {
    $singleActivity = PostRepository::getBySlug($slug);
} elseif ($id > 0) {
    $singleActivity = PostRepository::getById($id);
}

// Single activity view
if ($singleActivity) {
    $pageTitle = $singleActivity['title'] . ' | Activity Initiative';
    $pageDescription = truncate_text($singleActivity['excerpt'] ?? strip_tags($singleActivity['content']), 150);
    $pageImage = !empty($singleActivity['featured_image']) ? BASE_URL . '/' . ltrim($singleActivity['featured_image'], '/') : null;
    $ogType = 'article';
    $authorName = $singleActivity['author_name'] ?? 'Sofiq Organization';
    $publishedTime = $singleActivity['created_at'];

    require_once AGY_ROOT . '/views/partials/header.php';
    ?>
    <div class="page-header-banner">
        <div class="container">
            <div class="breadcrumb-trail">
                <a href="<?= BASE_URL ?>/index.php">Home</a> &rsaquo;
                <a href="<?= BASE_URL ?>/activities.php">Activities</a> &rsaquo;
                <span><?= htmlspecialchars(truncate_text($singleActivity['title'], 35), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="badge badge-primary mt-2"><i class="fa-solid fa-calendar-check"></i> Environmental Initiative</div>
            <h1 class="page-banner-title"><?= htmlspecialchars($singleActivity['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="post-meta-strip">
                <span><i class="fa-regular fa-clock"></i> <?= format_bangladesh_date($singleActivity['created_at']) ?></span>
                <span><i class="fa-regular fa-eye"></i> <?= number_format($singleActivity['view_count']) ?> Views</span>
                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($singleActivity['category'] ?? 'Dhaka, Bangladesh', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <!-- Active Reader Container with View Tracker Marker -->
    <div class="container py-5" data-track-post="<?= $singleActivity['id'] ?>">
        <div class="golden-grid">
            <!-- Major Column: Activity Content (61.8%) -->
            <div class="golden-col-major">
                <?php if (!empty($singleActivity['featured_image'])): ?>
                    <div class="single-post-media mb-4">
                        <?= ImageOptimizer::renderLazy(
                            $singleActivity['featured_image'],
                            $singleActivity['title'],
                            'single-hero-img',
                            800,
                            450
                        ) ?>
                    </div>
                <?php endif; ?>

                <div class="single-post-content rich-text-body">
                    <?= nl2br(htmlspecialchars($singleActivity['content'], ENT_QUOTES, 'UTF-8')) ?>
                </div>

                <!-- Verified Contributor Box -->
                <div class="mt-5">
                    <h3 class="mb-3">Initiative Organizer</h3>
                    <?php
                    $contributor = [
                        'full_name' => $singleActivity['author_name'] ?? 'Field Mobilizer',
                        'role' => $singleActivity['author_role'] ?? 'Program Coordinator',
                        'avatar' => $singleActivity['author_avatar'] ?? null,
                        'bio' => $singleActivity['author_bio'] ?? null,
                        'email' => $singleActivity['author_email'] ?? 'volunteer@greener2morow.org',
                        'phone' => $singleActivity['author_phone'] ?? '+880 1712-345678',
                        'city' => $singleActivity['author_city'] ?? 'Dhaka, Bangladesh'
                    ];
                    require AGY_ROOT . '/views/partials/contributor-box.php';
                    ?>
                </div>
            </div>

            <!-- Minor Column: Action Sidebar & RSVP (38.2%) -->
            <div class="golden-col-minor">
                <div class="sidebar-sticky-wrap">
                    <!-- RSVP Registration Box -->
                    <div class="rsvp-card">
                        <div class="rsvp-card-header">
                            <span class="badge badge-accent">Free Participation</span>
                            <h3>Volunteer for this Drive</h3>
                            <p class="text-muted">Join the field team for native sapling planting and local site monitoring.</p>
                        </div>
                        <form id="activityRsvpForm" class="rsvp-form" action="<?= BASE_URL ?>/api/server-ops.php?action=rsvp" method="POST">
                            <input type="hidden" name="activity_id" value="<?= $singleActivity['id'] ?>">
                            <div class="form-group mb-3">
                                <label for="rsvp_name" class="form-label">Full Name *</label>
                                <input type="text" id="rsvp_name" name="name" class="form-control" required placeholder="Your Name">
                            </div>
                            <div class="form-group mb-3">
                                <label for="rsvp_email" class="form-label">Email Address *</label>
                                <input type="email" id="rsvp_email" name="email" class="form-control" required placeholder="you@example.com">
                            </div>
                            <div class="form-group mb-3">
                                <label for="rsvp_phone" class="form-label">Phone Number *</label>
                                <input type="tel" id="rsvp_phone" name="phone" class="form-control" required placeholder="017xxxxxxxx">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" id="btnRsvpSubmit">
                                <i class="fa-solid fa-hands-holding-circle"></i> Confirm RSVP
                            </button>
                            <div id="rsvpFeedback" class="mt-2 text-center" style="display:none;"></div>
                        </form>
                    </div>

                    <!-- Program Summary Box -->
                    <div class="meta-sidebar-card mt-4">
                        <h4>Initiative Snapshot</h4>
                        <ul class="meta-list">
                            <li><strong>Framework:</strong> Sofiq Organization</li>
                            <li><strong>Field Location:</strong> Dhaka Hatirpool & Districts</li>
                            <li><strong>Status:</strong> Active Mobilization</li>
                            <li><strong>Cost:</strong> 100% Free / Youth Driven</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var form = document.getElementById('activityRsvpForm');
        var feedback = document.getElementById('rsvpFeedback');
        var submitBtn = document.getElementById('btnRsvpSubmit');
        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
                
                var formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(function(res){ return res.json(); })
                .then(function(data){
                    feedback.style.display = 'block';
                    if (data.success) {
                        feedback.className = 'alert alert-success mt-2';
                        feedback.textContent = data.message;
                        form.reset();
                    } else {
                        feedback.className = 'alert alert-danger mt-2';
                        feedback.textContent = data.error || 'Registration failed.';
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-hands-holding-circle"></i> Confirm RSVP';
                })
                .catch(function(){
                    feedback.style.display = 'block';
                    feedback.className = 'alert alert-danger mt-2';
                    feedback.textContent = 'Server connection error. Please try again.';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-hands-holding-circle"></i> Confirm RSVP';
                });
            });
        }
    });
    </script>

    <?php
    require_once AGY_ROOT . '/views/partials/footer.php';
    exit;
}

// ---------------- Directory Listing View ----------------
$pageTitle = 'Environmental Activities & Youth Mobilization | A Greener Tomorrow';
$pageDescription = 'Discover upcoming tree planting drives, river cleanup campaigns, and climate resilience workshops across Bangladesh organized by Sofiq Organization.';

// Fetch activities matching filter
$filterParams = [
    'post_type' => 'activity',
    'status' => 'published',
    'search' => $search,
    'limit' => 12
];
if (!empty($category)) {
    $filterParams['category'] = $category;
}

$activities = PostRepository::getAll($filterParams);

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="page-header-banner">
    <div class="container">
        <span class="badge badge-primary"><i class="fa-solid fa-person-digging"></i> Action Mobilization</span>
        <h1 class="page-banner-title">Environmental Programs & Field Drives</h1>
        <p class="page-banner-subtitle">Join community volunteers across Bangladesh taking tangible action to regenerate our local ecosystems.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Filter and Search Row -->
    <div class="filter-controls-row mb-4">
        <div class="category-pills">
            <a href="<?= BASE_URL ?>/activities.php" class="pill-btn <?= empty($category) ? 'active' : '' ?>">All Programs</a>
            <a href="<?= BASE_URL ?>/activities.php?cat=Reforestation" class="pill-btn <?= $category === 'Reforestation' ? 'active' : '' ?>">Reforestation</a>
            <a href="<?= BASE_URL ?>/activities.php?cat=Pollution+Control" class="pill-btn <?= $category === 'Pollution Control' ? 'active' : '' ?>">Pollution Control</a>
            <a href="<?= BASE_URL ?>/activities.php?cat=Climate+Adaptation" class="pill-btn <?= $category === 'Climate Adaptation' ? 'active' : '' ?>">Climate Adaptation</a>
            <a href="<?= BASE_URL ?>/activities.php?cat=Youth+Action" class="pill-btn <?= $category === 'Youth Action' ? 'active' : '' ?>">Youth Action</a>
        </div>

        <form action="<?= BASE_URL ?>/activities.php" method="GET" class="filter-search-box">
            <?php if (!empty($category)): ?>
                <input type="hidden" name="cat" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <div class="search-input-wrap">
                <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search activities..." class="form-control">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>
    </div>

    <!-- Activities Grid -->
    <div class="post-cards-grid">
        <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $post): ?>
                <?php require AGY_ROOT . '/views/partials/post-card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-card col-span-3">
                <i class="fa-solid fa-calendar-xmark empty-icon"></i>
                <h3>No activities found</h3>
                <p>We couldn't find any activities matching your filter criteria. Try viewing all programs or check back soon!</p>
                <a href="<?= BASE_URL ?>/activities.php" class="btn btn-primary mt-3">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>


<?php
/**
 * A Greener Tomorrow - Blog & Video Vlog Hub
 * Features responsive YouTube embed or direct HTML5 video playback, rich text, and contributor cards.
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
$typeFilter = sanitize_text($_GET['type'] ?? '');
$category = sanitize_text($_GET['cat'] ?? '');
$search = sanitize_text($_GET['q'] ?? '');

// Single article or vlog view
$singlePost = null;
if (!empty($slug)) {
    $singlePost = PostRepository::getBySlug($slug);
} elseif ($id > 0) {
    $singlePost = PostRepository::getById($id);
}

// Single post view
if ($singlePost) {
    $isVlog = ($singlePost['post_type'] === 'vlog') || !empty($singlePost['video_url']) || !empty($singlePost['video_path']);
    $pageTitle = $singlePost['title'] . ($isVlog ? ' (Video Vlog)' : ' | Blog');
    $pageDescription = truncate_text($singlePost['excerpt'] ?? strip_tags($singlePost['content']), 150);
    $pageImage = !empty($singlePost['featured_image']) ? BASE_URL . '/' . ltrim($singlePost['featured_image'], '/') : null;
    $ogType = 'article';
    $authorName = $singlePost['author_name'] ?? 'Sofiq Contributor';
    $publishedTime = $singlePost['created_at'];

    // Parse YouTube embed if present
    $youtubeEmbedUrl = '';
    if (!empty($singlePost['video_url'])) {
        $rawUrl = $singlePost['video_url'];
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $rawUrl, $matches)) {
            $youtubeEmbedUrl = 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        }
    }

    // Direct video upload path
    $directVideoUrl = '';
    if (!empty($singlePost['video_path'])) {
        $directVideoUrl = BASE_URL . '/assets/uploads/videos/' . htmlspecialchars($singlePost['video_path'], ENT_QUOTES, 'UTF-8');
    }

    require_once AGY_ROOT . '/views/partials/header.php';
    ?>
    <div class="page-header-banner">
        <div class="container">
            <div class="breadcrumb-trail">
                <a href="<?= BASE_URL ?>/index.php">Home</a> &rsaquo;
                <a href="<?= BASE_URL ?>/blog.php">Blog & Vlog</a> &rsaquo;
                <span><?= htmlspecialchars(truncate_text($singlePost['title'], 35), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="badge <?= $isVlog ? 'badge-vlog' : 'badge-secondary' ?> mt-2">
                <i class="<?= $isVlog ? 'fa-solid fa-circle-play' : 'fa-solid fa-newspaper' ?>"></i>
                <?= $isVlog ? 'Video Vlog Feature' : 'Editorial Article' ?>
            </div>
            <h1 class="page-banner-title"><?= htmlspecialchars($singlePost['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="post-meta-strip">
                <span><i class="fa-regular fa-calendar"></i> <?= format_bangladesh_date($singlePost['created_at']) ?></span>
                <span><i class="fa-regular fa-eye"></i> <?= number_format($singlePost['view_count']) ?> Views</span>
                <span><i class="fa-solid fa-book-open-reader"></i> <?= number_format($singlePost['unique_read_count'] ?? round($singlePost['view_count'] * 0.76)) ?> Total Readers</span>
                <span><i class="fa-regular fa-clock"></i> <?= (int)($singlePost['reading_time'] ?? 4) ?> Min Read</span>
                <span><i class="fa-solid fa-folder"></i> <?= htmlspecialchars($singlePost['category'] ?? 'Community Story', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <!-- Active Reader with View Tracker Marker -->
    <div class="container py-5" data-track-post="<?= $singlePost['id'] ?>">
        <div class="golden-grid">
            <!-- Major Column: Article / Video Media (61.8%) -->
            <div class="golden-col-major">
                <!-- Video Media Embed: YouTube or Direct Video Player -->
                <?php if (!empty($youtubeEmbedUrl)): ?>
                    <div class="video-embed-wrapper mb-4">
                        <iframe src="<?= $youtubeEmbedUrl ?>" title="<?= htmlspecialchars($singlePost['title'], ENT_QUOTES, 'UTF-8') ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="video-iframe"></iframe>
                    </div>
                <?php elseif (!empty($directVideoUrl)): ?>
                    <div class="direct-video-wrapper mb-4">
                        <video controls preload="metadata" class="direct-video-player" poster="<?= !empty($singlePost['featured_image']) ? BASE_URL . '/' . ltrim($singlePost['featured_image'], '/') : '' ?>">
                            <source src="<?= $directVideoUrl ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                <?php elseif (!empty($singlePost['featured_image'])): ?>
                    <div class="single-post-media mb-4">
                        <?= ImageOptimizer::renderLazy(
                            $singlePost['featured_image'],
                            $singlePost['title'],
                            'single-hero-img',
                            800,
                            450
                        ) ?>
                    </div>
                <?php endif; ?>

                <div class="single-post-content rich-text-body">
                    <?= render_post_body_html($singlePost['content']) ?>
                </div>

                <!-- Verified Contributor Box -->
                <div class="mt-5">
                    <h3 class="mb-3">Story Contributor</h3>
                    <?php
                    $contributor = [
                        'full_name' => $singlePost['author_name'] ?? 'Environmental Contributor',
                        'role' => $singlePost['author_role'] ?? 'Youth Journalist',
                        'avatar' => $singlePost['author_avatar'] ?? null,
                        'bio' => $singlePost['author_bio'] ?? null,
                        'email' => $singlePost['author_email'] ?? 'writer@greener2morow.org',
                        'phone' => $singlePost['author_phone'] ?? '+880 1712-345678',
                        'city' => $singlePost['author_city'] ?? 'Dhaka, Bangladesh'
                    ];
                    require AGY_ROOT . '/views/partials/contributor-box.php';
                    ?>
                </div>

                <!-- Readership Metrics & Threaded Member Comments -->
                <?php
                $activePost = $singlePost;
                require AGY_ROOT . '/views/partials/comments-section.php';
                ?>
            </div>

            <!-- Minor Column: Sidebar (38.2%) -->
            <div class="golden-col-minor">
                <div class="sidebar-sticky-wrap">
                    <div class="meta-sidebar-card">
                        <h4>Community Publishing</h4>
                        <p class="text-muted">Empowering local voices, students, and community organizers to share climate stories from all 64 districts of Bangladesh.</p>
                        <hr>
                        <a href="<?= BASE_URL ?>/join.php" class="btn btn-primary btn-block">
                            <i class="fa-solid fa-pen"></i> Submit Your Story
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once AGY_ROOT . '/views/partials/footer.php';
    exit;
}

// ---------------- Directory Listing View ----------------
$pageTitle = 'Green Blogs & Video Vlogs | A Greener Tomorrow';
$pageDescription = 'Read inspiring environmental stories, youth mobilization dispatches, and watch video documentaries produced across Bangladesh by Sofiq Organization.';

$filterParams = [
    'status' => 'published',
    'search' => $search,
    'limit' => 12
];
if (!empty($typeFilter)) {
    $filterParams['post_type'] = $typeFilter;
} else {
    // If no type specified, show both blog and vlog
    // PostRepository::getAll will filter if we provide it or we query appropriately
}
if (!empty($category)) {
    $filterParams['category'] = $category;
}

$posts = PostRepository::getAll($filterParams);

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="page-header-banner">
    <div class="container">
        <span class="badge badge-secondary"><i class="fa-solid fa-photo-film"></i> Media & Dispatches</span>
        <h1 class="page-banner-title">Green Blogs & Video Vlogs</h1>
        <p class="page-banner-subtitle">Field updates, youth opinions, photo essays, and documentary vlogs bringing environmental action to life.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Filter Bar -->
    <div class="filter-controls-row mb-4">
        <div class="category-pills">
            <a href="<?= BASE_URL ?>/blog.php" class="pill-btn <?= (empty($typeFilter) && empty($category)) ? 'active' : '' ?>">All Media</a>
            <a href="<?= BASE_URL ?>/blog.php?type=blog" class="pill-btn <?= $typeFilter === 'blog' ? 'active' : '' ?>"><i class="fa-solid fa-newspaper"></i> Articles</a>
            <a href="<?= BASE_URL ?>/blog.php?type=vlog" class="pill-btn <?= $typeFilter === 'vlog' ? 'active' : '' ?>"><i class="fa-solid fa-circle-play"></i> Video Vlogs</a>
            <a href="<?= BASE_URL ?>/blog.php?cat=Reforestation" class="pill-btn <?= $category === 'Reforestation' ? 'active' : '' ?>">Reforestation</a>
            <a href="<?= BASE_URL ?>/blog.php?cat=Climate+Action" class="pill-btn <?= $category === 'Climate Action' ? 'active' : '' ?>">Climate Action</a>
        </div>

        <form action="<?= BASE_URL ?>/blog.php" method="GET" class="filter-search-box">
            <?php if (!empty($typeFilter)): ?>
                <input type="hidden" name="type" value="<?= htmlspecialchars($typeFilter, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <div class="search-input-wrap">
                <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search articles, vlogs..." class="form-control">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>
    </div>

    <!-- Posts Grid -->
    <div class="post-cards-grid">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <?php require AGY_ROOT . '/views/partials/post-card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-card col-span-3">
                <i class="fa-solid fa-newspaper empty-icon"></i>
                <h3>No articles found</h3>
                <p>No content matches your selection. Try browsing all media or sign in to publish a story.</p>
                <a href="<?= BASE_URL ?>/blog.php" class="btn btn-primary mt-3">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>


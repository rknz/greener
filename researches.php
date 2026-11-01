<?php
/**
 * A Greener Tomorrow - Climate Research Library & Policy Archive
 * Single academic reader with document download, citation generator, and contributor profile.
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

// Single research reader
$singleResearch = null;
if (!empty($slug)) {
    $singleResearch = PostRepository::getBySlug($slug);
} elseif ($id > 0) {
    $singleResearch = PostRepository::getById($id);
}

// Single research paper view
if ($singleResearch) {
    $pageTitle = $singleResearch['title'] . ' | Research Paper';
    $pageDescription = truncate_text($singleResearch['excerpt'] ?? strip_tags($singleResearch['content']), 150);
    $pageImage = !empty($singleResearch['featured_image']) ? BASE_URL . '/' . ltrim($singleResearch['featured_image'], '/') : null;
    $ogType = 'article';
    $authorName = $singleResearch['author_name'] ?? 'Sofiq Research Fellow';
    $publishedTime = $singleResearch['created_at'];

    // Citation string generator
    $year = date('Y', strtotime($singleResearch['created_at']));
    $apaCitation = htmlspecialchars($authorName . " (" . $year . "). " . $singleResearch['title'] . ". Sofiq Organization Climate Research Repository, Dhaka.", ENT_QUOTES, 'UTF-8');

    // Document attachment details
    $hasDocument = !empty($singleResearch['document_path']);
    $docFile = $hasDocument ? AGY_ROOT . '/assets/uploads/docs/' . $singleResearch['document_path'] : '';
    $docUrl = $hasDocument ? BASE_URL . '/assets/uploads/docs/' . htmlspecialchars($singleResearch['document_path'], ENT_QUOTES, 'UTF-8') : '';
    $docSizeMb = ($hasDocument && file_exists($docFile)) ? round(filesize($docFile) / (1024 * 1024), 2) : '1.45';

    require_once AGY_ROOT . '/views/partials/header.php';
    ?>
    <div class="page-header-banner">
        <div class="container">
            <div class="breadcrumb-trail">
                <a href="<?= BASE_URL ?>/index.php">Home</a> &rsaquo;
                <a href="<?= BASE_URL ?>/researches.php">Researches</a> &rsaquo;
                <span><?= htmlspecialchars(truncate_text($singleResearch['title'], 35), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="badge badge-accent mt-2"><i class="fa-solid fa-microscope"></i> Peer-Reviewed Publication</div>
            <h1 class="page-banner-title"><?= htmlspecialchars($singleResearch['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="post-meta-strip">
                <span><i class="fa-regular fa-calendar"></i> Published: <?= format_bangladesh_date($singleResearch['created_at']) ?></span>
                <span><i class="fa-regular fa-eye"></i> <?= number_format($singleResearch['view_count']) ?> Reads</span>
                <span><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($singleResearch['category'] ?? 'Climate Policy', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <!-- Active Reader with View Tracker Marker -->
    <div class="container py-5" data-track-post="<?= $singleResearch['id'] ?>">
        <div class="golden-grid">
            <!-- Major Column: Research Findings (61.8%) -->
            <div class="golden-col-major">
                <?php if (!empty($singleResearch['featured_image'])): ?>
                    <div class="single-post-media mb-4">
                        <?= ImageOptimizer::renderLazy(
                            $singleResearch['featured_image'],
                            $singleResearch['title'],
                            'single-hero-img',
                            800,
                            450
                        ) ?>
                    </div>
                <?php endif; ?>

                <!-- Abstract Callout -->
                <?php if (!empty($singleResearch['excerpt'])): ?>
                    <div class="research-abstract-box mb-4">
                        <h4 class="abstract-title"><i class="fa-solid fa-quote-left"></i> Executive Abstract</h4>
                        <p class="abstract-text"><?= nl2br(htmlspecialchars($singleResearch['excerpt'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                <?php endif; ?>

                <div class="single-post-content rich-text-body">
                    <?= nl2br(htmlspecialchars($singleResearch['content'], ENT_QUOTES, 'UTF-8')) ?>
                </div>

                <!-- Academic Citation Box -->
                <div class="citation-box mt-5">
                    <div class="citation-header">
                        <h4><i class="fa-solid fa-graduation-cap"></i> Cite This Publication</h4>
                        <button class="btn btn-sm btn-outline-light" id="btnCopyCitation">
                            <i class="fa-regular fa-copy"></i> Copy Citation
                        </button>
                    </div>
                    <div class="citation-text" id="citationContent"><?= $apaCitation ?></div>
                </div>

                <!-- Verified Contributor Box -->
                <div class="mt-5">
                    <h3 class="mb-3">Lead Author & Researcher</h3>
                    <?php
                    $contributor = [
                        'full_name' => $singleResearch['author_name'] ?? 'Senior Climate Fellow',
                        'role' => $singleResearch['author_role'] ?? 'Research Contributor',
                        'avatar' => $singleResearch['author_avatar'] ?? null,
                        'bio' => $singleResearch['author_bio'] ?? null,
                        'email' => $singleResearch['author_email'] ?? 'research@greener2morow.org',
                        'phone' => $singleResearch['author_phone'] ?? '+880 1712-345678',
                        'city' => $singleResearch['author_city'] ?? 'Dhaka, Bangladesh'
                    ];
                    require AGY_ROOT . '/views/partials/contributor-box.php';
                    ?>
                </div>
            </div>

            <!-- Minor Column: Document Download & Meta (38.2%) -->
            <div class="golden-col-minor">
                <div class="sidebar-sticky-wrap">
                    <!-- Research Document Download Box -->
                    <?php if ($hasDocument): ?>
                        <div class="document-download-card">
                            <div class="doc-icon-wrap">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>
                            <h3>Full Research Document</h3>
                            <p class="text-muted">Includes complete methodology, empirical data tables, and field survey results.</p>
                            <div class="doc-file-meta">
                                <span>Format: PDF Document</span> &bull;
                                <span>Size: <?= $docSizeMb ?> MB</span>
                            </div>
                            <a href="<?= $docUrl ?>" target="_blank" download class="btn btn-primary btn-block mt-3">
                                <i class="fa-solid fa-cloud-arrow-down"></i> Download Document
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="document-download-card">
                            <div class="doc-icon-wrap">
                                <i class="fa-solid fa-book-bookmark"></i>
                            </div>
                            <h3>Open Access Summary</h3>
                            <p class="text-muted">This empirical paper is published under Creative Commons Attribution for non-profit and educational use.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Research Metadata Card -->
                    <div class="meta-sidebar-card mt-4">
                        <h4>Publication Metadata</h4>
                        <ul class="meta-list">
                            <li><strong>Institution:</strong> Sofiq Organization</li>
                            <li><strong>Peer Review:</strong> Verified Academic</li>
                            <li><strong>Access:</strong> Open Access / Zero Paywall</li>
                            <li><strong>Secretariat:</strong> Dhaka Hatirpool HQ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('btnCopyCitation');
        var text = document.getElementById('citationContent');
        if (btn && text) {
            btn.addEventListener('click', function(){
                navigator.clipboard.writeText(text.innerText).then(function(){
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                    setTimeout(function(){
                        btn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy Citation';
                    }, 2500);
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
$pageTitle = 'Climate Research & Scientific Studies | A Greener Tomorrow';
$pageDescription = 'Access peer-reviewed climate change research, urban heat mapping, and policy documents generated by Sofiq Organization researchers in Bangladesh.';

$filterParams = [
    'post_type' => 'research',
    'status' => 'published',
    'search' => $search,
    'limit' => 12
];
if (!empty($category)) {
    $filterParams['category'] = $category;
}

$researches = PostRepository::getAll($filterParams);

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="page-header-banner">
    <div class="container">
        <span class="badge badge-accent"><i class="fa-solid fa-microscope"></i> Scientific Evidence</span>
        <h1 class="page-banner-title">Climate Researches & Policy Repository</h1>
        <p class="page-banner-subtitle">Rigorous, empirical scientific documentation examining ecological degradation, adaptation strategies, and clean transition in Bangladesh.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Filter Bar -->
    <div class="filter-controls-row mb-4">
        <div class="category-pills">
            <a href="<?= BASE_URL ?>/researches.php" class="pill-btn <?= empty($category) ? 'active' : '' ?>">All Researches</a>
            <a href="<?= BASE_URL ?>/researches.php?cat=Climate+Policy" class="pill-btn <?= $category === 'Climate Policy' ? 'active' : '' ?>">Climate Policy</a>
            <a href="<?= BASE_URL ?>/researches.php?cat=Urban+Heat" class="pill-btn <?= $category === 'Urban Heat' ? 'active' : '' ?>">Urban Heat</a>
            <a href="<?= BASE_URL ?>/researches.php?cat=Renewable+Energy" class="pill-btn <?= $category === 'Renewable Energy' ? 'active' : '' ?>">Renewable Energy</a>
            <a href="<?= BASE_URL ?>/researches.php?cat=Salinity+Adaptation" class="pill-btn <?= $category === 'Salinity Adaptation' ? 'active' : '' ?>">Salinity Adaptation</a>
        </div>

        <form action="<?= BASE_URL ?>/researches.php" method="GET" class="filter-search-box">
            <?php if (!empty($category)): ?>
                <input type="hidden" name="cat" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <div class="search-input-wrap">
                <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search papers, keywords..." class="form-control">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>
    </div>

    <!-- Researches Grid -->
    <div class="post-cards-grid">
        <?php if (!empty($researches)): ?>
            <?php foreach ($researches as $post): ?>
                <?php require AGY_ROOT . '/views/partials/post-card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-card col-span-3">
                <i class="fa-solid fa-magnifying-glass-chart empty-icon"></i>
                <h3>No research papers found</h3>
                <p>No papers match the specified query. Browse all researches or check back soon for our next release.</p>
                <a href="<?= BASE_URL ?>/researches.php" class="btn btn-primary mt-3">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>


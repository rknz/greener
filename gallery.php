<?php
/**
 * A Greener Tomorrow - Photo Gallery & Field Showcase
 * Features responsive masonry grid, lazy loading, category filtering, and lightbox modal.
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Tracker.php';
require_once AGY_ROOT . '/includes/ImageOptimizer.php';

// Log visitor session
Tracker::trackVisit();

$db = Database::getConnection();
$category = sanitize_text($_GET['cat'] ?? '');

$sql = "SELECT * FROM gallery_items WHERE 1=1";
$params = [];
if (!empty($category) && $category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY sort_order ASC, id DESC";

$items = [];
try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Gallery query error: " . $e->getMessage());
}

$pageTitle = 'Photo Gallery | A Greener Tomorrow';
$pageDescription = 'High-resolution photo archive documenting tree planting drives, river cleanups, and youth mobilization across Bangladesh by Sofiq Organization.';

require_once AGY_ROOT . '/views/partials/header.php';
?>

<div class="page-header-banner">
    <div class="container">
        <span class="badge badge-accent"><i class="fa-solid fa-camera"></i> Field Photography</span>
        <h1 class="page-banner-title">Action on the Ground Gallery</h1>
        <p class="page-banner-subtitle">Documenting environmental regeneration, youth volunteers, and scientific field expeditions across Bangladesh.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Category Filter Bar -->
    <div class="gallery-filter-bar text-center mb-5">
        <button class="gallery-filter-btn <?= empty($category) || $category === 'all' ? 'active' : '' ?>" data-filter="all">All Photos (<?= count($items) ?>)</button>
        <button class="gallery-filter-btn <?= $category === 'Reforestation' ? 'active' : '' ?>" data-filter="Reforestation">Reforestation</button>
        <button class="gallery-filter-btn <?= $category === 'Pollution Control' ? 'active' : '' ?>" data-filter="Pollution Control">Cleanups</button>
        <button class="gallery-filter-btn <?= $category === 'Youth Mobilization' ? 'active' : '' ?>" data-filter="Youth Mobilization">Youth Action</button>
        <button class="gallery-filter-btn <?= $category === 'Climate Adaptation' ? 'active' : '' ?>" data-filter="Climate Adaptation">Adaptation</button>
    </div>

    <!-- Gallery Responsive Grid -->
    <div class="gallery-grid">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <?php
                $cat = $item['category'] ?? 'Reforestation';
                $thumbSrc = !empty($item['thumbnail_path']) ? BASE_URL . '/' . ltrim($item['thumbnail_path'], '/') : BASE_URL . '/assets/uploads/gallery/gallery-tree-planting.webp';
                $fullSrc = !empty($item['image_path']) ? BASE_URL . '/' . ltrim($item['image_path'], '/') : $thumbSrc;
                $caption = $item['caption'] ?? 'Volunteer initiative in Bangladesh';
                ?>
                <div class="gallery-item" data-category="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
                    <a href="<?= htmlspecialchars($fullSrc, ENT_QUOTES, 'UTF-8') ?>" class="gallery-item-link lightbox-trigger" data-caption="<?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') ?>">
                        <?= ImageOptimizer::renderLazy(
                            $item['thumbnail_path'] ?? 'assets/uploads/gallery/gallery-tree-planting.webp',
                            $caption,
                            'gallery-thumb-img',
                            480,
                            320
                        ) ?>
                        <div class="gallery-overlay">
                            <span class="gallery-cat-tag"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></span>
                            <h4 class="gallery-caption-text"><?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') ?></h4>
                            <span class="gallery-zoom-icon"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-card col-span-3 text-center">
                <i class="fa-solid fa-images empty-icon"></i>
                <h3>No photos available</h3>
                <p>No photos in this category yet. Check back soon for fresh dispatches!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Fullscreen Lightbox Modal -->
<div class="lightbox-modal" id="galleryLightbox" role="dialog" aria-modal="true" aria-label="Photo View">
    <div class="lightbox-backdrop"></div>
    <div class="lightbox-dialog">
        <button class="lightbox-close" aria-label="Close Lightbox">&times;</button>
        <div class="lightbox-content">
            <img src="" alt="" class="lightbox-image">
            <div class="lightbox-caption"></div>
        </div>
    </div>
</div>

<?php require_once AGY_ROOT . '/views/partials/footer.php'; ?>


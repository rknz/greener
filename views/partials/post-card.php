<?php
/**
 * A Greener Tomorrow - Golden Ratio Post Card Partial
 * Aspect Ratio: 1.618 Golden Ratio Card with Lazy-Loaded WebP Image & Contributor Info.
 *
 * @param array $post Associative post array
 */

if (!defined('AGY_ROOT')) {
    exit('Direct access not permitted.');
}

if (!isset($post) || !is_array($post)) {
    return;
}

require_once AGY_ROOT . '/includes/ImageOptimizer.php';

// Route URL based on post type
$type = $post['post_type'] ?? 'blog';
$slug = !empty($post['slug']) ? $post['slug'] : 'post-' . $post['id'];

switch ($type) {
    case 'activity':
        $detailUrl = BASE_URL . '/activities.php?slug=' . urlencode($slug);
        $badgeClass = 'badge-primary';
        $badgeIcon = 'fa-solid fa-calendar-check';
        $badgeText = 'Initiative';
        break;
    case 'research':
        $detailUrl = BASE_URL . '/researches.php?slug=' . urlencode($slug);
        $badgeClass = 'badge-accent';
        $badgeIcon = 'fa-solid fa-microscope';
        $badgeText = 'Research';
        break;
    case 'vlog':
        $detailUrl = BASE_URL . '/blog.php?slug=' . urlencode($slug);
        $badgeClass = 'badge-vlog';
        $badgeIcon = 'fa-solid fa-circle-play';
        $badgeText = 'Video Vlog';
        break;
    case 'blog':
    default:
        $detailUrl = BASE_URL . '/blog.php?slug=' . urlencode($slug);
        $badgeClass = 'badge-secondary';
        $badgeIcon = 'fa-solid fa-newspaper';
        $badgeText = 'Blog';
        break;
}

// Contributor info
$authorName = !empty($post['author_name']) ? $post['author_name'] : (!empty($post['username']) ? $post['username'] : 'Sofiq Mobilizer');
$authorAvatar = !empty($post['author_avatar']) ? BASE_URL . '/assets/uploads/avatars/' . htmlspecialchars($post['author_avatar'], ENT_QUOTES, 'UTF-8') : BASE_URL . '/assets/brand/brand-logo.png';

$views = (int)($post['view_count'] ?? 0);
$pubDate = format_bangladesh_date($post['created_at'] ?? 'now');
?>

<article class="post-card post-card-golden">
    <div class="post-card-media">
        <a href="<?= $detailUrl ?>" aria-label="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>">
            <?= ImageOptimizer::renderLazy(
                $post['featured_image'] ?? 'assets/brand/template-mockup.jpg',
                $post['title'],
                'post-card-thumb',
                400,
                247
            ) ?>
        </a>
        <div class="post-card-badges">
            <span class="badge <?= $badgeClass ?>"><i class="<?= $badgeIcon ?>"></i> <?= $badgeText ?></span>
            <?php if (!empty($post['document_path'])): ?>
                <span class="badge badge-doc-pill" title="Has attached research document/PDF">
                    <i class="fa-solid fa-file-lines"></i> Document
                </span>
            <?php endif; ?>
            <?php if ($type === 'vlog'): ?>
                <span class="badge-play-overlay" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="post-card-body">
        <?php if (!empty($post['category'])): ?>
            <span class="post-category-tag"><?= htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>

        <h3 class="post-card-title">
            <a href="<?= $detailUrl ?>"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></a>
        </h3>

        <p class="post-card-excerpt">
            <?= htmlspecialchars(truncate_text($post['excerpt'] ?? strip_tags($post['content'] ?? ''), 115), ENT_QUOTES, 'UTF-8') ?>
        </p>

        <!-- Contributor & Metrics Footer -->
        <div class="post-card-footer">
            <div class="post-contributor-mini">
                <img src="<?= $authorAvatar ?>" alt="<?= htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') ?>" class="contributor-mini-avatar" width="28" height="28" loading="lazy">
                <span class="contributor-mini-name"><?= htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div class="post-meta-metrics">
                <span title="Total Reads"><i class="fa-regular fa-eye"></i> <?= number_format($views) ?></span>
                <span title="Published Date"><i class="fa-regular fa-clock"></i> <?= $pubDate ?></span>
            </div>
        </div>
    </div>
</article>


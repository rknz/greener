<?php
/**
 * A Greener Tomorrow - Dynamic XML Sitemap
 * Outputs clean XML sitemap according to sitemaps.org standard.
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/PostRepository.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim(BASE_URL, '/');

// Static core pages
$staticPages = [
    ['loc' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => $baseUrl . '/activities.php', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => $baseUrl . '/researches.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => $baseUrl . '/blog.php', 'priority' => '0.8', 'changefreq' => 'daily'],
    ['loc' => $baseUrl . '/gallery.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => $baseUrl . '/contact.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => $baseUrl . '/join.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
];

// Fetch all published posts
$posts = PostRepository::getAll(['status' => 'published', 'limit' => 500]);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($staticPages as $page): ?>
    <url>
        <loc><?= htmlspecialchars($page['loc'], ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq><?= $page['changefreq'] ?></changefreq>
        <priority><?= $page['priority'] ?></priority>
    </url>
<?php endforeach; ?>

<?php foreach ($posts as $post): ?>
    <?php
    $targetFile = 'blog.php';
    if ($post['post_type'] === 'activity') $targetFile = 'activities.php';
    elseif ($post['post_type'] === 'research') $targetFile = 'researches.php';
    $postLoc = $baseUrl . '/' . $targetFile . '?slug=' . urlencode($post['slug']);
    $lastmod = !empty($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : date('Y-m-d', strtotime($post['created_at']));
    ?>
    <url>
        <loc><?= htmlspecialchars($postLoc, ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>
</urlset>


<?php
/**
 * A Greener Tomorrow - Hero Initiative Slider Partial
 * Highlights urgent environmental initiatives, climate campaigns, and mobilization calls.
 */

if (!defined('AGY_ROOT')) {
    exit('Direct access not permitted.');
}

$db = Database::getConnection();
$slides = [];
try {
    $stmt = $db->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order ASC, id DESC LIMIT 5");
    $slides = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Hero slides fetch error: " . $e->getMessage());
}

// Fallback slides if database empty
if (empty($slides)) {
    $slides = [
        [
            'id' => 1,
            'title' => 'Dhaka Urban Greening & Clean Air Initiative',
            'subtitle' => 'Mobilizing 2,500+ youth across Hatirpool and Dhanmondi to plant oxygen belts and lower surface temperatures.',
            'badge_text' => 'Live Initiative',
            'image_path' => 'assets/uploads/slider/slide-climate-mangrove.webp',
            'cta_text' => 'Join This Action',
            'cta_url' => BASE_URL . '/activities.php',
            'secondary_cta_text' => 'Become a Member',
            'secondary_cta_url' => BASE_URL . '/join.php'
        ],
        [
            'id' => 2,
            'title' => 'Coastal Mangrove Restoration in Sundarbans Fringe',
            'subtitle' => 'Community-led wave barriers and saline-tolerant mangrove planting protecting 15,000 delta residents.',
            'badge_text' => 'Climate Adaptation',
            'image_path' => 'assets/uploads/slider/slide-river-cleanup.webp',
            'cta_text' => 'Read Field Research',
            'cta_url' => BASE_URL . '/researches.php',
            'secondary_cta_text' => 'Support Campaign',
            'secondary_cta_url' => BASE_URL . '/contact.php'
        ],
        [
            'id' => 3,
            'title' => 'Youth Climate Mobilization Across 64 Districts',
            'subtitle' => 'Sofiq Organization grassroots network training students to lead environmental clubs and river audits.',
            'badge_text' => 'Nationwide Movement',
            'image_path' => 'assets/uploads/slider/slide-youth-planting.webp',
            'cta_text' => 'Become a Member',
            'cta_url' => BASE_URL . '/join.php',
            'secondary_cta_text' => 'View Gallery',
            'secondary_cta_url' => BASE_URL . '/gallery.php'
        ]
    ];
}
?>

<section class="hero-slider-section" aria-label="Featured Initiatives">
    <div class="hero-slider" id="heroSlider">
        <?php foreach ($slides as $index => $slide): ?>
            <?php
            $bgImg = !empty($slide['image_path']) ? BASE_URL . '/' . ltrim($slide['image_path'], '/') : BASE_URL . '/assets/uploads/slider/slide-climate-mangrove.webp';
            $isActive = $index === 0 ? 'active' : '';
            $ctaUrl = $slide['cta_url'] ?? '';
            if (!empty($ctaUrl)) {
                $ctaUrl = preg_replace('#^https?://localhost(?::\d+)?/#', '', $ctaUrl);
                if (!str_starts_with($ctaUrl, 'http') && !str_starts_with($ctaUrl, '#')) {
                    $ctaUrl = BASE_URL . '/' . ltrim($ctaUrl, '/');
                }
            }
            $secUrl = $slide['secondary_cta_url'] ?? '';
            if (!empty($secUrl)) {
                $secUrl = preg_replace('#^https?://localhost(?::\d+)?/#', '', $secUrl);
                if (!str_starts_with($secUrl, 'http') && !str_starts_with($secUrl, '#')) {
                    $secUrl = BASE_URL . '/' . ltrim($secUrl, '/');
                }
            }
            ?>
            <div class="hero-slide <?= $isActive ?>" style="background-image: linear-gradient(135deg, rgba(20, 40, 13, 0.88) 0%, rgba(42, 124, 19, 0.72) 100%), url('<?= htmlspecialchars($bgImg, ENT_QUOTES, 'UTF-8') ?>');" data-index="<?= $index ?>">
                <div class="container hero-container">
                    <div class="hero-content">
                        <?php if (!empty($slide['badge_text'])): ?>
                            <span class="hero-badge"><i class="fa-solid fa-bullhorn"></i> <?= htmlspecialchars($slide['badge_text'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        
                        <h1 class="hero-title"><?= htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                        <p class="hero-subtitle"><?= htmlspecialchars($slide['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>

                        <div class="hero-cta-group">
                            <?php if (!empty($ctaUrl)): ?>
                                <a href="<?= htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-accent btn-lg">
                                    <i class="fa-solid fa-hands-holding-circle"></i> <?= htmlspecialchars($slide['cta_text'] ?? 'Get Involved', ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($secUrl)): ?>
                                <a href="<?= htmlspecialchars($secUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-light btn-lg">
                                    <?= htmlspecialchars($slide['secondary_cta_text'] ?? 'Learn More', ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Slide Controls -->
        <?php if (count($slides) > 1): ?>
            <button class="slider-arrow slider-prev" aria-label="Previous Slide">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="slider-arrow slider-next" aria-label="Next Slide">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            <div class="slider-dots" role="tablist" aria-label="Slider Pagination"></div>
        <?php endif; ?>
    </div>
</section>


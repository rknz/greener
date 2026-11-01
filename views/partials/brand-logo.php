<?php
/**
 * A Greener Tomorrow - Brand Logo Partial
 * Renders the brand logo based on Admin MCQ Placement selection:
 * - logo_use_navbar
 * - logo_use_footer
 * - logo_use_section
 *
 * @param string $placement 'navbar' | 'footer' | 'section'
 * @param string $extraClass Optional CSS classes
 */

if (!defined('AGY_ROOT')) {
    exit('Direct access not permitted.');
}

function render_brand_logo(string $placement = 'navbar', string $extraClass = ''): void {
    static $settings = null;
    if ($settings === null) {
        $settings = get_all_settings();
    }

    $siteName = $settings['site_name'] ?? 'A Greener Tomorrow';
    $rawTagline = $settings['site_tagline'] ?? 'Youth Climate Mobilization';
    $siteTagline = ($placement === 'navbar') ? 'Youth Climate Mobilization' : $rawTagline;
    $logoFile = $settings['logo_path'] ?? 'brand-logo.png';
    $logoAlt = $settings['logo_alt'] ?? $siteName;

    // Check MCQ placement switches
    $displayLogo = true;
    if ($placement === 'navbar' && empty($settings['logo_use_navbar'])) {
        $displayLogo = false;
    } elseif ($placement === 'footer' && empty($settings['logo_use_footer'])) {
        $displayLogo = false;
    } elseif ($placement === 'section' && empty($settings['logo_use_section'])) {
        $displayLogo = false;
    }

    // Resolve physical path
    $logoWebPath = '';
    if (!empty($logoFile)) {
        if (file_exists(AGY_ROOT . '/assets/uploads/logos/' . $logoFile)) {
            $logoWebPath = BASE_URL . '/assets/uploads/logos/' . htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8');
        } elseif (file_exists(AGY_ROOT . '/assets/brand/' . $logoFile)) {
            $logoWebPath = BASE_URL . '/assets/brand/' . htmlspecialchars($logoFile, ENT_QUOTES, 'UTF-8');
        } elseif (file_exists(AGY_ROOT . '/assets/brand/brand-logo.png')) {
            $logoWebPath = BASE_URL . '/assets/brand/brand-logo.png';
        }
    }

    $homeUrl = BASE_URL . '/index.php';
    ?>
    <a href="<?= $homeUrl ?>" class="brand-identity brand-placement-<?= htmlspecialchars($placement, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?> Home">
        <?php if ($displayLogo && !empty($logoWebPath)): ?>
            <img src="<?= $logoWebPath ?>"
                 alt="<?= htmlspecialchars($logoAlt, ENT_QUOTES, 'UTF-8') ?>"
                 class="brand-logo-img"
                 width="48"
                 height="48"
                 loading="eager"
                 decoding="async">
        <?php else: ?>
            <div class="brand-emblem-fallback" aria-hidden="true">🌱</div>
        <?php endif; ?>
        <div class="brand-text-block">
            <span class="brand-title"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></span>
            <?php if ($placement !== 'navbar-compact'): ?>
                <span class="brand-subtitle"><?= htmlspecialchars($siteTagline, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
    </a>
    <?php
}


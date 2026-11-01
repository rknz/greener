<?php
/**
 * A Greener Tomorrow - Global Header Partial
 * Responsive Golden-Ratio navigation, SEO metadata, and dynamic Auth states.
 */

if (!defined('AGY_ROOT')) {
    require_once __DIR__ . '/../../config/config.php';
}

require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';
require_once AGY_ROOT . '/includes/SEO.php';
require_once AGY_ROOT . '/views/partials/brand-logo.php';

$currentUser = Auth::user();
$isLoggedIn = Auth::check();
$isAdmin = Auth::isAdmin();

// Determine active navigation link
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#2A7C13">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Preconnect CDN Links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Primary Stylesheet (Zero-build Pre-minified Golden Ratio CSS) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.min.css?v=2.2.0">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/brand/brand-logo.png">

    <!-- SEO Meta Tags & JSON-LD Structured Data -->
    <?php
    SEO::render([
        'title' => $pageTitle ?? null,
        'description' => $pageDescription ?? null,
        'image' => $pageImage ?? null,
        'url' => $canonicalUrl ?? null,
        'type' => $ogType ?? 'website',
        'published_time' => $publishedTime ?? null,
        'author' => $authorName ?? null,
    ]);
    ?>
    <script>
        window.AGY_BASE_URL = '<?= rtrim(BASE_URL, '/') ?>';
    </script>
</head>
<body class="agy-body">
    <!-- Skip to main content for accessibility -->
    <a href="#mainContent" class="skip-link">Skip to main content</a>

    <!-- Top Notice Strip (Dhaka Hatirpool HQ & Emergency Hotline) -->
    <div class="top-bar">
        <div class="container top-bar-inner">
            <div class="top-bar-left">
                <span><i class="fa-solid fa-location-dot"></i> 36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205</span>
                <span class="d-none-sm"><i class="fa-solid fa-phone"></i> +880 1712-345678</span>
            </div>
            <div class="top-bar-right">
                <span class="badge-mini-accent"><i class="fa-solid fa-leaf"></i> Sofiq Org Action Initiative</span>
                <?php if ($isLoggedIn): ?>
                    <span class="user-greeting">Welcome, <strong><?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Sticky Navigation Header -->
    <header class="site-header" id="siteHeader">
        <div class="container header-container">
            <!-- Brand Logo (with MCQ placement logic) -->
            <?php render_brand_logo('navbar'); ?>

            <!-- Desktop & Mobile Navigation Links -->
            <nav class="site-nav" id="navMenu" aria-label="Main Navigation">
                <div class="mobile-nav-header d-lg-none">
                    <?php render_brand_logo('navbar-compact'); ?>
                </div>

                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= $currentScript === 'index.php' ? 'active' : '' ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/activities.php" class="nav-link <?= $currentScript === 'activities.php' ? 'active' : '' ?>">Activities</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/researches.php" class="nav-link <?= $currentScript === 'researches.php' ? 'active' : '' ?>">Researches</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/blog.php" class="nav-link <?= $currentScript === 'blog.php' ? 'active' : '' ?>">Blog & Vlog</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/gallery.php" class="nav-link <?= $currentScript === 'gallery.php' ? 'active' : '' ?>">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/contact.php" class="nav-link <?= $currentScript === 'contact.php' ? 'active' : '' ?>">Contact</a>
                    </li>
                </ul>

                <div class="mobile-nav-footer d-lg-none">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= BASE_URL ?>/dashboard/index.php" class="btn btn-secondary btn-block">My Dashboard</a>
                        <?php if ($isAdmin): ?>
                            <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-primary btn-block">Admin Panel</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline btn-block mt-2">Sign Out</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-block">Sign In</a>
                        <a href="<?= BASE_URL ?>/join.php" class="btn btn-accent btn-block mt-2">Become a Member</a>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Header Action Controls -->
            <div class="header-actions">
                <?php if ($isLoggedIn): ?>
                    <div class="user-action-dropdown">
                        <?php if ($isAdmin): ?>
                            <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-sm btn-outline" title="Admin Governance Control">
                                <i class="fa-solid fa-gauge-high"></i> Admin
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/dashboard/index.php" class="btn btn-sm btn-primary" title="Contributor Mini-Dashboard">
                            <i class="fa-solid fa-user"></i> Dashboard
                        </a>
                        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-icon" title="Sign Out">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-outline d-none-sm">Sign In</a>
                    <a href="<?= BASE_URL ?>/join.php" class="btn btn-sm btn-accent">
                        <i class="fa-solid fa-seedling"></i> Become a Member
                    </a>
                <?php endif; ?>

                <!-- Smart 3-bar Morphing Hamburger Toggle -->
                <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Off-canvas backdrop overlay -->
    <div class="nav-overlay" id="navOverlay" aria-hidden="true"></div>

    <!-- Main Content Anchor -->
    <main id="mainContent" class="site-main">


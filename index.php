<?php
/**
 * A Greener Tomorrow - Homepage (index.php)
 * 9-Section Dynamic Action Framework under Sofiq Organization
 */

require_once __DIR__ . '/config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/PostRepository.php';
require_once AGY_ROOT . '/includes/Tracker.php';
require_once AGY_ROOT . '/includes/ImageOptimizer.php';

// Log visitor session
Tracker::trackVisit();

// Fetch dynamic homepage data
$db = Database::getConnection();

// 1. Featured Activities (limit 3)
$featuredActivities = PostRepository::getAll([
    'post_type' => 'activity',
    'status' => 'published',
    'limit' => 3
]);

// 2. Featured Researches (limit 3)
$featuredResearches = PostRepository::getAll([
    'post_type' => 'research',
    'status' => 'published',
    'limit' => 3
]);

// 3. Featured Blogs & Vlogs (limit 3)
$recentBlogs = PostRepository::getAll([
    'status' => 'published',
    'limit' => 3
]);

// 4. Gallery Items (limit 6)
$galleryItems = [];
try {
    $galleryStmt = $db->query("SELECT * FROM gallery_items ORDER BY sort_order ASC, id DESC LIMIT 6");
    $galleryItems = $galleryStmt->fetchAll();
} catch (PDOException $e) {
    // Gallery table empty or error handled gracefully
}

// 5. Impact Metrics
$impactStats = [
    ['num' => 45000, 'suffix' => '+', 'label' => 'Trees Planted', 'icon' => 'fa-solid fa-tree'],
    ['num' => 18500, 'suffix' => '+', 'label' => 'Youth Mobilized', 'icon' => 'fa-solid fa-people-group'],
    ['num' => 64, 'suffix' => '', 'label' => 'Districts Active', 'icon' => 'fa-solid fa-map-location-dot'],
    ['num' => 32, 'suffix' => '+', 'label' => 'Researches Published', 'icon' => 'fa-solid fa-book-open-reader']
];

// Page SEO Meta
$pageTitle = 'A Greener Tomorrow - Youth Climate Mobilization & Action';
$pageDescription = 'Mobilizing grassroots communities and youth across Bangladesh under the Sofiq Organization action framework to plant trees, combat pollution, and publish scientific research.';

require_once AGY_ROOT . '/views/partials/header.php';
?>

<!-- SECTION 1: HERO INITIATIVE SLIDER -->
<?php require AGY_ROOT . '/views/partials/hero-slider.php'; ?>

<!-- SECTION 2: ABOUT & SOFIQ ORGANIZATION FRAMEWORK (Golden Ratio 61.8% / 38.2%) -->
<section class="section about-framework-section" id="about">
    <div class="container">
        <div class="golden-grid">
            <!-- Major Column: 61.8% Narrative -->
            <div class="golden-col-major">
                <span class="section-tag"><i class="fa-solid fa-seedling"></i> Sofiq Organization Action Framework</span>
                <h2 class="section-title">A Resilient, Green Bangladesh Built by Youth Mobilization</h2>
                <p class="lead-text">
                    <strong>A Greener Tomorrow</strong> operates as an agile, field-driven initiative dedicated to addressing the triple planetary crisis: climate breakdown, environmental pollution, and devastating natural disasters across the Bengal Delta.
                </p>
                <p class="body-text">
                    Headquartered at <strong>36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka</strong>, we bridge rigorous scientific climate research with hands-on mobilization. We train local volunteers, empower students in 64 districts, restore urban tree canopies, and safeguard coastal communities on the front lines of sea-level rise.
                </p>
                <div class="about-highlights-grid mt-4">
                    <div class="highlight-item">
                        <i class="fa-solid fa-shield-halved highlight-icon"></i>
                        <div>
                            <h4>Grassroots Mobilization</h4>
                            <p>Organizing rapid volunteer responses during river cleans, flash floods, and heatwaves.</p>
                        </div>
                    </div>
                    <div class="highlight-item">
                        <i class="fa-solid fa-microscope highlight-icon"></i>
                        <div>
                            <h4>Evidence-Based Action</h4>
                            <p>Publishing peer-reviewed policy papers with downloadable datasets and field findings.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Minor Column: 38.2% Impact Card -->
            <div class="golden-col-minor">
                <div class="framework-card">
                    <div class="framework-card-header">
                        <span class="badge badge-accent">Dhaka Central HQ</span>
                        <h3>Our Action Mandate</h3>
                    </div>
                    <ul class="framework-checklist">
                        <li><i class="fa-solid fa-circle-check"></i> Zero plastic zones in urban commercial hubs</li>
                        <li><i class="fa-solid fa-circle-check"></i> 100,000 native saplings in urban schoolyards</li>
                        <li><i class="fa-solid fa-circle-check"></i> Solar-powered cyclone shelter electrification</li>
                        <li><i class="fa-solid fa-circle-check"></i> Independent Buriganga water purity tracking</li>
                    </ul>
                    <div class="framework-card-footer mt-4">
                        <a href="<?= BASE_URL ?>/join.php" class="btn btn-primary btn-block">
                            <i class="fa-solid fa-user-plus"></i> Join the Movement
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: 5 ENVIRONMENTAL ACTION PILLARS -->
<section class="section section-pillars" id="pillars">
    <div class="container text-center">
        <span class="section-tag"><i class="fa-solid fa-leaf"></i> Strategic Focus</span>
        <h2 class="section-title">Five Core Environmental Pillars</h2>
        <p class="section-subtitle max-w-700">Targeted interventions designed to build ecological resilience from Hatirpool urban corridors to remote coastal deltas.</p>

        <div class="pillars-grid mt-5">
            <div class="pillar-card">
                <div class="pillar-icon-box"><i class="fa-solid fa-tree"></i></div>
                <h3 class="pillar-title">Urban Reforestation</h3>
                <p class="pillar-text">Restoring micro-forests, rooftop gardens, and oxygen belts across Dhaka to counteract intense urban heat island effects.</p>
                <a href="<?= BASE_URL ?>/activities.php?cat=reforestation" class="pillar-link">View Projects <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon-box"><i class="fa-solid fa-cloud-sun-rain"></i></div>
                <h3 class="pillar-title">Climate Adaptation</h3>
                <p class="pillar-text">Deploying saline-tolerant crops, rainwater harvesting systems, and community early-warning storm systems in vulnerable deltas.</p>
                <a href="<?= BASE_URL ?>/researches.php?cat=climate" class="pillar-link">View Researches <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon-box"><i class="fa-solid fa-water"></i></div>
                <h3 class="pillar-title">Pollution Control</h3>
                <p class="pillar-text">Mobilizing volunteer fleets to clean Buriganga and Turag riverbanks while campaigning for industrial effluent treatment.</p>
                <a href="<?= BASE_URL ?>/activities.php?cat=pollution" class="pillar-link">Join Cleanups <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon-box"><i class="fa-solid fa-solar-panel"></i></div>
                <h3 class="pillar-title">Renewable Energy</h3>
                <p class="pillar-text">Assisting rural off-grid communities and cyclone shelters with community micro-solar grids and clean cooking alternatives.</p>
                <a href="<?= BASE_URL ?>/researches.php?cat=energy" class="pillar-link">Read Studies <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon-box"><i class="fa-solid fa-bullhorn"></i></div>
                <h3 class="pillar-title">Youth Mobilization</h3>
                <p class="pillar-text">Empowering students and young professionals across all 64 districts to become certified grassroots climate leaders.</p>
                <a href="<?= BASE_URL ?>/join.php" class="pillar-link">Register Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 4: IMPACT STATS COUNTER -->
<section class="section section-stats" id="impact">
    <div class="container">
        <div class="stats-counter-grid">
            <?php foreach ($impactStats as $stat): ?>
                <div class="stat-counter-card">
                    <div class="stat-icon-wrap"><i class="<?= $stat['icon'] ?>"></i></div>
                    <div class="stat-number-wrap">
                        <span class="stat-number" data-target="<?= $stat['num'] ?>" data-suffix="<?= $stat['suffix'] ?>">0</span>
                    </div>
                    <span class="stat-label"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION 5: FEATURED ACTIVITIES & INITIATIVES -->
<section class="section section-activities" id="activities">
    <div class="container">
        <div class="section-header-row">
            <div>
                <span class="section-tag"><i class="fa-solid fa-person-digging"></i> Direct Mobilization</span>
                <h2 class="section-title">Upcoming & Live Activities</h2>
            </div>
            <a href="<?= BASE_URL ?>/activities.php" class="btn btn-outline">Explore All Activities <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="post-cards-grid mt-4">
            <?php if (!empty($featuredActivities)): ?>
                <?php foreach ($featuredActivities as $post): ?>
                    <?php require AGY_ROOT . '/views/partials/post-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-card col-span-3">
                    <i class="fa-solid fa-calendar-day empty-icon"></i>
                    <p>No published activities at this moment. Stay tuned for our upcoming tree plantation drives!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SECTION 6: SCIENTIFIC RESEARCH & EVIDENCE -->
<section class="section section-research" id="researches">
    <div class="container">
        <div class="section-header-row">
            <div>
                <span class="section-tag"><i class="fa-solid fa-file-waveform"></i> Empirical Science</span>
                <h2 class="section-title">Climate Research & Policy Papers</h2>
            </div>
            <a href="<?= BASE_URL ?>/researches.php" class="btn btn-outline">Browse Research Archive <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="post-cards-grid mt-4">
            <?php if (!empty($featuredResearches)): ?>
                <?php foreach ($featuredResearches as $post): ?>
                    <?php require AGY_ROOT . '/views/partials/post-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-card col-span-3">
                    <i class="fa-solid fa-microscope empty-icon"></i>
                    <p>New climate and environmental research studies are currently in peer-review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SECTION 7: GREEN BLOGS & VIDEO VLOGS -->
<section class="section section-blog-vlog" id="blog">
    <div class="container">
        <div class="section-header-row">
            <div>
                <span class="section-tag"><i class="fa-solid fa-newspaper"></i> Knowledge Hub</span>
                <h2 class="section-title">Latest Articles & Video Vlogs</h2>
            </div>
            <a href="<?= BASE_URL ?>/blog.php" class="btn btn-outline">View All Stories <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="post-cards-grid mt-4">
            <?php if (!empty($recentBlogs)): ?>
                <?php foreach ($recentBlogs as $post): ?>
                    <?php require AGY_ROOT . '/views/partials/post-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-card col-span-3">
                    <i class="fa-solid fa-pen-nib empty-icon"></i>
                    <p>No blogs published yet. Sign in as a contributor to publish the first story!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SECTION 8: OPTIMIZED PHOTO GALLERY -->
<section class="section section-gallery" id="gallery">
    <div class="container text-center">
        <span class="section-tag"><i class="fa-solid fa-images"></i> Visual Impact</span>
        <h2 class="section-title">Action on the Ground</h2>
        <p class="section-subtitle max-w-700">Capturing our volunteers, youth leaders, and research teams in action across Bangladesh.</p>

        <!-- Category Filters -->
        <div class="gallery-filter-bar mt-4">
            <button class="gallery-filter-btn active" data-filter="all">All Photos</button>
            <button class="gallery-filter-btn" data-filter="Reforestation">Reforestation</button>
            <button class="gallery-filter-btn" data-filter="Pollution Control">Cleanups</button>
            <button class="gallery-filter-btn" data-filter="Youth Mobilization">Youth Action</button>
            <button class="gallery-filter-btn" data-filter="Climate Adaptation">Adaptation</button>
        </div>

        <!-- Gallery Grid -->
        <div class="gallery-grid mt-4">
            <?php if (!empty($galleryItems)): ?>
                <?php foreach ($galleryItems as $item): ?>
                    <?php
                    $cat = $item['category'] ?? 'Reforestation';
                    $thumbSrc = !empty($item['thumbnail_path']) ? BASE_URL . '/' . ltrim($item['thumbnail_path'], '/') : BASE_URL . '/assets/uploads/gallery/gallery-tree-planting.webp';
                    $fullSrc = !empty($item['image_path']) ? BASE_URL . '/' . ltrim($item['image_path'], '/') : $thumbSrc;
                    $caption = $item['caption'] ?? 'A Greener Tomorrow Volunteer Event';
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
                <div class="gallery-item" data-category="Reforestation">
                    <a href="<?= BASE_URL ?>/assets/uploads/gallery/gallery-tree-planting.webp" class="gallery-item-link lightbox-trigger" data-caption="Urban Tree Plantation Drive at Hatirpool">
                        <img src="<?= BASE_URL ?>/assets/uploads/gallery/gallery-tree-planting.webp" alt="Tree Plantation" class="gallery-thumb-img" loading="lazy">
                        <div class="gallery-overlay">
                            <span class="gallery-cat-tag">Reforestation</span>
                            <h4 class="gallery-caption-text">Urban Tree Plantation Drive at Hatirpool</h4>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/gallery.php" class="btn btn-outline">View Full Photo Archive <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- SECTION 9: "BECOME A MEMBER" YOUTH MOBILIZATION BANNER -->
<section class="section section-cta-join">
    <div class="container">
        <div class="cta-join-banner">
            <div class="cta-join-content">
                <span class="badge badge-accent mb-2"><i class="fa-solid fa-hand-holding-heart"></i> Sofiq Org Free Youth Membership</span>
                <h2 class="cta-join-title">Are You Ready to Lead Climate Action in Your District?</h2>
                <p class="cta-join-subtitle">
                    Join over 18,500 students, researchers, and volunteers mobilizing across Bangladesh. Gain leadership training, contribute field research, and receive official certificates.
                </p>
                <div class="cta-btn-wrap mt-4">
                    <a href="<?= BASE_URL ?>/join.php" class="btn btn-accent btn-lg">
                        <i class="fa-solid fa-seedling"></i> Become a Member Today
                    </a>
                    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-light btn-lg">
                        <i class="fa-solid fa-envelope"></i> Contact Central Secretariat
                    </a>
                </div>
            </div>
            <div class="cta-join-decoration">
                <i class="fa-solid fa-earth-asia cta-bg-icon"></i>
            </div>
        </div>
    </div>
</section>

<!-- Fullscreen Lightbox Modal (For Gallery Images) -->
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


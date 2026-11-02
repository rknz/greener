<?php
/**
 * A Greener Tomorrow - Modern European Global Footer Partial
 * Features refined dark slate-obsidian styling, Dhaka Hatirpool HQ coordinates,
 * 2026 Foundation copyright, and clickable designer/developer credit for rknz.
 */

if (!defined('AGY_ROOT')) {
    exit('Direct access not permitted.');
}

require_once AGY_ROOT . '/views/partials/brand-logo.php';
$settings = get_all_settings();
$siteName = $settings['site_name'] ?? 'A Greener Tomorrow';
?>
    </main><!-- /.site-main -->

    <!-- Global Footer -->
    <footer class="site-footer" id="siteFooter">
        <!-- Top Action Ribbon inside Footer -->
        <div class="footer-top-ribbon">
            <div class="container footer-ribbon-inner">
                <div class="footer-ribbon-text">
                    <span class="footer-ribbon-badge"><i class="fa-solid fa-earth-asia"></i> 64 Districts Network</span>
                    <h3>Partner with A Greener Tomorrow Foundation</h3>
                    <p>Join our scientific research fellowships, urban reforestation cells, and river protection brigades.</p>
                </div>
                <div class="footer-ribbon-actions">
                    <a href="<?= BASE_URL ?>/join.php" class="btn btn-accent">
                        <i class="fa-solid fa-user-plus"></i> Join the Foundation
                    </a>
                    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-light">
                        <i class="fa-regular fa-envelope"></i> Contact Secretariat
                    </a>
                </div>
            </div>
        </div>

        <div class="container footer-container">
            <div class="footer-grid">
                <!-- Col 1: Brand & Mission -->
                <div class="footer-col footer-col-brand">
                    <?php render_brand_logo('footer'); ?>
                    <p class="footer-mission-text">
                        Mobilizing youth and grassroots communities across Bangladesh to mitigate climate change, reverse environmental pollution, and publish open-access ecological research under the <strong>Sofiq Organization</strong> action model.
                    </p>
                    <div class="footer-social-links">
                        <a href="https://facebook.com/greener2morow" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="https://x.com" target="_blank" rel="noopener" aria-label="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
                    </div>
                </div>

                <!-- Col 2: 5 Action Pillars -->
                <div class="footer-col">
                    <h4 class="footer-heading">5 Action Pillars</h4>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/activities.php?cat=Reforestation"><i class="fa-solid fa-chevron-right"></i> Urban Reforestation</a></li>
                        <li><a href="<?= BASE_URL ?>/researches.php?cat=Climate+Policy"><i class="fa-solid fa-chevron-right"></i> Climate Adaptation</a></li>
                        <li><a href="<?= BASE_URL ?>/activities.php?cat=Pollution+Control"><i class="fa-solid fa-chevron-right"></i> River &amp; Air Cleanups</a></li>
                        <li><a href="<?= BASE_URL ?>/researches.php?cat=Renewable+Energy"><i class="fa-solid fa-chevron-right"></i> Renewable Energy</a></li>
                        <li><a href="<?= BASE_URL ?>/join.php"><i class="fa-solid fa-chevron-right"></i> Youth Mobilization</a></li>
                    </ul>
                </div>

                <!-- Col 3: Quick Navigation -->
                <div class="footer-col">
                    <h4 class="footer-heading">Platform Directory</h4>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/activities.php"><i class="fa-solid fa-chevron-right"></i> Live Field Initiatives</a></li>
                        <li><a href="<?= BASE_URL ?>/researches.php"><i class="fa-solid fa-chevron-right"></i> Research Repository</a></li>
                        <li><a href="<?= BASE_URL ?>/blog.php"><i class="fa-solid fa-chevron-right"></i> Green Blogs &amp; Vlogs</a></li>
                        <li><a href="<?= BASE_URL ?>/gallery.php"><i class="fa-solid fa-chevron-right"></i> Field Photo Archive</a></li>
                        <li><a href="<?= BASE_URL ?>/login.php"><i class="fa-solid fa-chevron-right"></i> Member Sign In</a></li>
                        <li><a href="<?= BASE_URL ?>/admin/index.php"><i class="fa-solid fa-chevron-right"></i> Governance Portal</a></li>
                    </ul>
                </div>

                <!-- Col 4: Dhaka HQ & Contact -->
                <div class="footer-col footer-col-contact">
                    <h4 class="footer-heading">Central Secretariat</h4>
                    <ul class="footer-contact-list">
                        <li>
                            <span class="footer-contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <span><?= htmlspecialchars($settings['contact_address'] ?? '36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh', ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                        <li>
                            <span class="footer-contact-icon"><i class="fa-solid fa-phone"></i></span>
                            <a href="tel:+8801712345678"><?= htmlspecialchars($settings['contact_phone'] ?? '+880 1712-345678', ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                        <li>
                            <span class="footer-contact-icon"><i class="fa-solid fa-envelope"></i></span>
                            <a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? 'greener2morow@gmail.com', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($settings['contact_email'] ?? 'greener2morow@gmail.com', ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                        <li>
                            <span class="footer-contact-icon"><i class="fa-solid fa-clock"></i></span>
                            <span>Sun &ndash; Thu: 9:00 AM &ndash; 6:00 PM (BST)</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Copyright & Developer Credit Strip -->
            <div class="footer-bottom">
                <div class="footer-bottom-left">
                    <p>&copy; 2026 <strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?> Foundation</strong>. All Rights Reserved. <span class="badge badge-secondary" style="margin-left:0.4rem;">Version 2</span></p>
                </div>
                <div class="footer-bottom-right">
                    <span class="footer-credit-pill">
                        Designed &amp; Developed by <a href="mailto:rukonuzzaman.dev@gmail.com" class="footer-dev-link" title="Send email to rukonuzzaman.dev@gmail.com"><i class="fa-regular fa-envelope"></i> rknz</a>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Floating Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <!-- Global Application Scripts (Zero-build Vanilla JS) -->
    <script src="<?= BASE_URL ?>/assets/js/app.min.js?v=3.0.0"></script>
</body>
</html>

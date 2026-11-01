<?php
/**
 * A Greener Tomorrow - Global Footer Partial
 * Features Dhaka Hatirpool HQ contact info, MCQ Logo Placement, 5 Pillars, and Back-to-Top.
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
        <div class="container footer-container">
            <div class="footer-grid">
                <!-- Col 1: Brand & Mission -->
                <div class="footer-col footer-col-brand">
                    <?php render_brand_logo('footer'); ?>
                    <p class="footer-mission-text">
                        Mobilizing communities across Bangladesh to mitigate climate change, reverse environmental pollution, and foster grassroots resilience under the <strong>Sofiq Organization</strong> action blueprint.
                    </p>
                    <div class="footer-social-links">
                        <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="https://x.com" target="_blank" rel="noopener" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                    </div>
                </div>

                <!-- Col 2: Action Pillars -->
                <div class="footer-col">
                    <h4 class="footer-heading">5 Action Pillars</h4>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/activities.php?cat=reforestation"><i class="fa-solid fa-angle-right"></i> Urban Reforestation</a></li>
                        <li><a href="<?= BASE_URL ?>/researches.php?cat=climate"><i class="fa-solid fa-angle-right"></i> Climate Adaptation</a></li>
                        <li><a href="<?= BASE_URL ?>/activities.php?cat=pollution"><i class="fa-solid fa-angle-right"></i> River & Air Cleanups</a></li>
                        <li><a href="<?= BASE_URL ?>/researches.php?cat=energy"><i class="fa-solid fa-angle-right"></i> Renewable Energy</a></li>
                        <li><a href="<?= BASE_URL ?>/join.php"><i class="fa-solid fa-angle-right"></i> Youth Mobilization</a></li>
                    </ul>
                </div>

                <!-- Col 3: Quick Navigation -->
                <div class="footer-col">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/activities.php"><i class="fa-solid fa-angle-right"></i> Live Initiatives</a></li>
                        <li><a href="<?= BASE_URL ?>/researches.php"><i class="fa-solid fa-angle-right"></i> Climate Researches</a></li>
                        <li><a href="<?= BASE_URL ?>/blog.php"><i class="fa-solid fa-angle-right"></i> Green Blogs & Vlogs</a></li>
                        <li><a href="<?= BASE_URL ?>/gallery.php"><i class="fa-solid fa-angle-right"></i> Photo Gallery</a></li>
                        <li><a href="<?= BASE_URL ?>/join.php"><i class="fa-solid fa-angle-right"></i> Become a Member</a></li>
                        <li><a href="<?= BASE_URL ?>/login.php"><i class="fa-solid fa-angle-right"></i> Contributor Portal</a></li>
                    </ul>
                </div>

                <!-- Col 4: Dhaka HQ & Contact -->
                <div class="footer-col footer-col-contact">
                    <h4 class="footer-heading">Central Secretariat</h4>
                    <ul class="footer-contact-list">
                        <li>
                            <i class="fa-solid fa-location-dot"></i>
                            <span>36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <a href="tel:+8801712345678">+880 1712-345678</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <a href="mailto:info@greener2morow.org">info@greener2morow.org</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-clock"></i>
                            <span>Sun &ndash; Thu: 9:00 AM &ndash; 6:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom Strip -->
            <div class="footer-bottom">
                <div class="footer-bottom-left">
                    <p>&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></strong> &bull; An Action Framework by <strong>Sofiq Organization</strong>. All Rights Reserved.</p>
                </div>
                <div class="footer-bottom-right">
                    <span>Designed with Golden Ratio &phi; (1.618) &bull; Carbon Neutral Hosting</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Floating Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <!-- Global Application Scripts (Zero-build Vanilla JS) -->
    <script src="<?= BASE_URL ?>/assets/js/app.min.js?v=2.0.0"></script>
</body>
</html>


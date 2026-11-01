<?php
/**
 * A Greener Tomorrow - Admin Action Processor
 * Handles Settings, MCQ Logo Placement, Bcrypt Password Overrides,
 * Content Moderation, Hero Sliders, and Dynamic Performance Tweaks.
 */

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';
require_once AGY_ROOT . '/includes/PostRepository.php';
require_once AGY_ROOT . '/includes/UploadManager.php';

Auth::requireAdmin();
$currentUser = Auth::user();
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/index.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    exit('Security error: CSRF validation failed.');
}

$action = $_POST['action'] ?? '';

switch ($action) {
    // ---------------- 1. CORE SITE INFO & MCQ LOGO PLACEMENT ----------------
    case 'save_site_settings':
        $siteName = sanitize_text($_POST['site_name'] ?? 'A Greener Tomorrow');
        $siteTagline = sanitize_text($_POST['site_tagline'] ?? '');
        $contactEmail = sanitize_text($_POST['contact_email'] ?? '');
        $contactPhone = sanitize_text($_POST['contact_phone'] ?? '');
        $contactAddress = sanitize_text($_POST['contact_address'] ?? '');
        $logoAlt = sanitize_text($_POST['logo_alt'] ?? $siteName);

        // MCQ Logo Placement Switches
        $useNavbar = !empty($_POST['logo_use_navbar']) ? '1' : '0';
        $useFooter = !empty($_POST['logo_use_footer']) ? '1' : '0';
        $useSection = !empty($_POST['logo_use_section']) ? '1' : '0';

        set_setting('site_name', $siteName);
        set_setting('site_tagline', $siteTagline);
        set_setting('contact_email', $contactEmail);
        set_setting('contact_phone', $contactPhone);
        set_setting('contact_address', $contactAddress);
        set_setting('logo_alt', $logoAlt);
        set_setting('logo_use_navbar', $useNavbar);
        set_setting('logo_use_footer', $useFooter);
        set_setting('logo_use_section', $useSection);

        // Handle Logo Upload if provided
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $upRes = UploadManager::uploadLogo($_FILES['site_logo']);
            if ($upRes['success']) {
                set_setting('logo_path', $upRes['filename']);
            }
        }

        redirect(BASE_URL . '/admin/index.php?msg=settings_saved#info');
        break;

    // ---------------- 2. USER CREDENTIAL OVERSIGHT (ADMIN RESET) ----------------
    case 'reset_user_password':
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $newPlainPassword = $_POST['new_password'] ?? '';

        if ($targetUserId <= 0 || strlen($newPlainPassword) < 6) {
            redirect(BASE_URL . '/admin/index.php?err=pwd_short#users');
        }

        $res = Auth::adminResetPassword($targetUserId, $newPlainPassword);
        if ($res['success']) {
            redirect(BASE_URL . '/admin/index.php?msg=pwd_reset_ok#users');
        } else {
            redirect(BASE_URL . '/admin/index.php?err=pwd_failed#users');
        }
        break;

    case 'toggle_user_status':
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $currentStatus = (int)($_POST['current_status'] ?? 1);
        $newStatus = $currentStatus === 1 ? 0 : 1;

        if ($targetUserId === (int)$currentUser['id']) {
            redirect(BASE_URL . '/admin/index.php?err=cannot_deactivate_self#users');
        }

        $stmt = $db->prepare("UPDATE users SET is_active = ?, session_version = session_version + 1 WHERE id = ?");
        $stmt->execute([$newStatus, $targetUserId]);
        redirect(BASE_URL . '/admin/index.php?msg=user_status_updated#users');
        break;

    case 'change_user_role':
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $newRole = sanitize_text($_POST['role'] ?? 'member');
        if (in_array($newRole, ['admin', 'researcher', 'member'], true)) {
            $stmt = $db->prepare("UPDATE users SET role = ?, session_version = session_version + 1 WHERE id = ?");
            $stmt->execute([$newRole, $targetUserId]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=role_updated#users');
        break;

    // ---------------- 3. CONTENT MODERATION & OFFENSIVE REMOVAL ----------------
    case 'moderate_post':
        $postId = (int)($_POST['post_id'] ?? 0);
        $postAction = $_POST['post_action'] ?? '';

        if ($postId <= 0) {
            redirect(BASE_URL . '/admin/index.php#content');
        }

        if ($postAction === 'publish') {
            PostRepository::updateStatus($postId, 'published');
            redirect(BASE_URL . '/admin/index.php?msg=post_published#content');
        } elseif ($postAction === 'unpublish') {
            PostRepository::updateStatus($postId, 'draft');
            redirect(BASE_URL . '/admin/index.php?msg=post_drafted#content');
        } elseif ($postAction === 'offensive') {
            // Unpublish and tag title/content with offensive marker
            $post = PostRepository::getById($postId);
            if ($post) {
                $db->prepare("UPDATE posts SET status = 'offensive', excerpt = '[Content Removed by Moderation: Inappropriate / Offensive violation]' WHERE id = ?")->execute([$postId]);
            }
            redirect(BASE_URL . '/admin/index.php?msg=post_flagged_offensive#content');
        } elseif ($postAction === 'delete') {
            PostRepository::delete($postId);
            redirect(BASE_URL . '/admin/index.php?msg=post_deleted#content');
        }
        break;

    // ---------------- 4. DYNAMIC PERFORMANCE SLIDERS ----------------
    case 'save_performance_settings':
        $webpQuality = (int)($_POST['webp_quality'] ?? 82);
        $cacheDays = (int)($_POST['cache_expiry_days'] ?? 30);
        $lazyAggressive = (int)($_POST['lazy_aggressiveness'] ?? 85);
        $gzipEnabled = !empty($_POST['gzip_compression']) ? '1' : '0';
        $browserCaching = !empty($_POST['browser_caching']) ? '1' : '0';

        set_setting('perf_webp_quality', (string)$webpQuality);
        set_setting('perf_cache_days', (string)$cacheDays);
        set_setting('perf_lazy_aggressive', (string)$lazyAggressive);
        set_setting('perf_gzip_enabled', $gzipEnabled);
        set_setting('perf_browser_caching', $browserCaching);

        redirect(BASE_URL . '/admin/index.php?msg=perf_saved#performance');
        break;

    // ---------------- 5. HERO SLIDE MANAGEMENT ----------------
    case 'save_hero_slide':
        $slideId = (int)($_POST['slide_id'] ?? 0);
        $title = sanitize_text($_POST['slide_title'] ?? '');
        $subtitle = sanitize_text($_POST['slide_subtitle'] ?? '');
        $badge = sanitize_text($_POST['slide_badge'] ?? '');
        $ctaText = sanitize_text($_POST['slide_cta_text'] ?? '');
        $ctaUrl = sanitize_text($_POST['slide_cta_url'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        $imagePath = 'assets/brand/template-mockup.jpg';
        if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK) {
            $upRes = UploadManager::uploadImage($_FILES['slide_image'], 'slider');
            if ($upRes['success']) {
                $imagePath = 'assets/uploads/slider/' . $upRes['filename'];
            }
        }

        if ($slideId > 0) {
            $stmt = $db->prepare("UPDATE hero_slides SET title = ?, subtitle = ?, badge_text = ?, cta_text = ?, cta_url = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$title, $subtitle, $badge, $ctaText, $ctaUrl, $sortOrder, $slideId]);
        } else {
            $stmt = $db->prepare("INSERT INTO hero_slides (title, subtitle, badge_text, image_path, cta_text, cta_url, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, datetime('now'))");
            $stmt->execute([$title, $subtitle, $badge, $imagePath, $ctaText, $ctaUrl, $sortOrder]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=slide_saved#info');
        break;

    case 'delete_hero_slide':
        $slideId = (int)($_POST['slide_id'] ?? 0);
        if ($slideId > 0) {
            $db->prepare("DELETE FROM hero_slides WHERE id = ?")->execute([$slideId]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=slide_deleted#info');
        break;

    default:
        redirect(BASE_URL . '/admin/index.php');
        break;
}


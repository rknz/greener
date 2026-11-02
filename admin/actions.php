<?php
/**
 * A Greener Tomorrow - Complete Admin Governance Action Processor
 * Handles Site Identity, MCQ Logo Matrix, Impact Stats, Content Publishing & Moderation,
 * Hero Sliders, Photo Gallery, Comments & Contact Inbox, User Roles & Bcrypt Passwords,
 * and Dynamic Performance Sliders.
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
    // ---------------- 1. CORE SITE INFO, STATS & MCQ LOGO PLACEMENT ----------------
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

        // Update impact counters if provided
        if (isset($_POST['stat_trees'])) {
            $stmtStats = $db->prepare("UPDATE site_settings SET stat_trees = ?, stat_members = ?, stat_districts = ?, stat_researches = ? WHERE id = 1");
            $stmtStats->execute([
                (int)$_POST['stat_trees'],
                (int)($_POST['stat_members'] ?? 18500),
                (int)($_POST['stat_districts'] ?? 64),
                (int)($_POST['stat_researches'] ?? 32)
            ]);
        }

        // Handle Logo Upload if provided
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $upRes = UploadManager::uploadLogo($_FILES['site_logo']);
            if ($upRes['success']) {
                set_setting('logo_path', $upRes['filename']);
            }
        }

        redirect(BASE_URL . '/admin/index.php?msg=settings_saved#settings');
        break;

    // ---------------- 2. ADMIN DIRECT POST CREATION ----------------
    case 'admin_create_post':
        $title = sanitize_text($_POST['title'] ?? '');
        $postType = strtoupper(sanitize_text($_POST['post_type'] ?? 'BLOG'));
        $pillar = strtoupper(sanitize_text($_POST['pillar'] ?? 'CLIMATE'));
        $excerptText = sanitize_text($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $videoUrl = sanitize_text($_POST['video_url'] ?? '');
        $location = sanitize_text($_POST['location'] ?? 'Hatirpool, Dhaka');

        $coverPath = '/assets/uploads/posts/post-climate-march.webp';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upImg = UploadManager::uploadImage($_FILES['featured_image'], 'posts');
            if ($upImg['success']) {
                $coverPath = '/assets/uploads/posts/' . $upImg['filename'];
            }
        }

        $attachments = [];
        if (isset($_FILES['research_doc']) && $_FILES['research_doc']['error'] === UPLOAD_ERR_OK) {
            $upDoc = UploadManager::uploadDocument($_FILES['research_doc']);
            if ($upDoc['success']) {
                $attachments[] = [
                    'file_name' => $upDoc['filename'],
                    'file_path' => '/assets/uploads/docs/' . $upDoc['filename'],
                    'file_type' => 'PDF',
                    'size_kb' => (int)round(($_FILES['research_doc']['size'] ?? 102400) / 1024)
                ];
            }
        }

        $res = PostRepository::create([
            'title' => $title,
            'type' => $postType,
            'pillar' => $pillar,
            'excerpt' => $excerptText,
            'content' => $content,
            'cover_image' => $coverPath,
            'thumb_image' => $coverPath,
            'video_type' => !empty($videoUrl) ? 'YOUTUBE' : 'NONE',
            'video_embed_url' => $videoUrl,
            'location' => $location
        ], $attachments);

        if ($res['success']) {
            redirect(BASE_URL . '/admin/index.php?msg=post_created#content');
        } else {
            redirect(BASE_URL . '/admin/index.php?err=post_create_failed#content');
        }
        break;

    // ---------------- 3. USER CREDENTIAL OVERSIGHT & CREATION ----------------
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
        $newRole = strtoupper(sanitize_text($_POST['role'] ?? 'USER'));
        if (in_array($newRole, ['ADMIN', 'RESEARCHER', 'USER', 'MEMBER'], true)) {
            $stmt = $db->prepare("UPDATE users SET role = ?, session_version = session_version + 1 WHERE id = ?");
            $stmt->execute([$newRole, $targetUserId]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=role_updated#users');
        break;

    case 'admin_create_user':
        $name = sanitize_text($_POST['name'] ?? '');
        $username = slugify($_POST['username'] ?? $name);
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role = strtoupper(sanitize_text($_POST['role'] ?? 'USER'));
        $district = sanitize_text($_POST['district'] ?? 'Dhaka');
        $designation = sanitize_text($_POST['designation'] ?? 'Youth Advocate');

        if (!empty($name) && !empty($email) && strlen($password) >= 6) {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            try {
                $stmt = $db->prepare("INSERT INTO users (username, name, full_name, email, password_hash, designation, district, city, avatar_url, avatar, role, control_mode, member_status, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'avatar-researcher.webp', 'avatar-researcher.webp', ?, 'AUTO', 'APPROVED', 1)");
                $stmt->execute([$username, $name, $name, $email, $hash, $designation, $district, $district, $role]);
                redirect(BASE_URL . '/admin/index.php?msg=user_created#users');
            } catch (PDOException $e) {
                redirect(BASE_URL . '/admin/index.php?err=user_exists#users');
            }
        }
        redirect(BASE_URL . '/admin/index.php?err=pwd_short#users');
        break;

    // ---------------- 4. CONTENT MODERATION & OFFENSIVE REMOVAL ----------------
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
            $post = PostRepository::getById($postId);
            if ($post) {
                $db->prepare("UPDATE posts SET status = 'OFFENSIVE', excerpt = '[Content Removed by Moderation: Inappropriate / Offensive violation]' WHERE id = ?")->execute([$postId]);
            }
            redirect(BASE_URL . '/admin/index.php?msg=post_flagged_offensive#content');
        } elseif ($postAction === 'delete') {
            PostRepository::delete($postId);
            redirect(BASE_URL . '/admin/index.php?msg=post_deleted#content');
        }
        break;

    // ---------------- 5. HERO SLIDE & GALLERY MANAGEMENT ----------------
    case 'save_hero_slide':
        $slideId = (int)($_POST['slide_id'] ?? 0);
        $title = sanitize_text($_POST['slide_title'] ?? '');
        $subtitle = sanitize_text($_POST['slide_subtitle'] ?? '');
        $badge = sanitize_text($_POST['slide_badge'] ?? 'Live Initiative');
        $ctaText = sanitize_text($_POST['slide_cta_text'] ?? 'Join This Action');
        $ctaUrl = sanitize_text($_POST['slide_cta_url'] ?? '/activities.php');
        $sortOrder = (int)($_POST['sort_order'] ?? 1);

        $imagePath = 'assets/uploads/slider/slide-climate-mangrove.webp';
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
            $stmt = $db->prepare("INSERT INTO hero_slides (title, subtitle, badge_text, image_path, cta_text, cta_url, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)");
            $stmt->execute([$title, $subtitle, $badge, $imagePath, $ctaText, $ctaUrl, $sortOrder]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=slide_saved#media');
        break;

    case 'delete_hero_slide':
        $slideId = (int)($_POST['slide_id'] ?? 0);
        if ($slideId > 0) {
            $db->prepare("DELETE FROM hero_slides WHERE id = ?")->execute([$slideId]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=slide_deleted#media');
        break;

    case 'add_gallery_item':
        $title = sanitize_text($_POST['gallery_title'] ?? 'Field Action Photo');
        $category = sanitize_text($_POST['gallery_category'] ?? 'Reforestation');
        $caption = sanitize_text($_POST['gallery_caption'] ?? $title);

        $imgPath = 'assets/uploads/gallery/gallery-tree-planting.webp';
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === UPLOAD_ERR_OK) {
            $upRes = UploadManager::uploadImage($_FILES['gallery_image'], 'gallery');
            if ($upRes['success']) {
                $imgPath = 'assets/uploads/gallery/' . $upRes['filename'];
            }
        }

        $stmt = $db->prepare("INSERT INTO gallery_items (title, image_url, thumb_url, image_path, thumbnail_path, pillar, category, caption) VALUES (?, ?, ?, ?, ?, 'ENVIRONMENT', ?, ?)");
        $stmt->execute([$title, '/' . $imgPath, '/' . $imgPath, $imgPath, $imgPath, $category, $caption]);
        redirect(BASE_URL . '/admin/index.php?msg=gallery_added#media');
        break;

    case 'delete_gallery_item':
        $itemId = (int)($_POST['gallery_id'] ?? 0);
        if ($itemId > 0) {
            $db->prepare("DELETE FROM gallery_items WHERE id = ?")->execute([$itemId]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=gallery_deleted#media');
        break;

    // ---------------- 6. COMMENTS & CONTACT INBOX MODERATION ----------------
    case 'delete_comment':
        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId > 0) {
            $db->prepare("DELETE FROM post_comments WHERE id = ? OR parent_id = ?")->execute([$commentId, $commentId]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=comment_deleted#communications');
        break;

    case 'delete_contact_message':
        $msgId = (int)($_POST['message_id'] ?? 0);
        if ($msgId > 0) {
            $db->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$msgId]);
        }
        redirect(BASE_URL . '/admin/index.php?msg=message_deleted#communications');
        break;

    // ---------------- 7. DYNAMIC PERFORMANCE SLIDERS ----------------
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

    default:
        redirect(BASE_URL . '/admin/index.php');
        break;
}

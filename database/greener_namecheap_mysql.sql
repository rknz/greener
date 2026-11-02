-- ============================================================================
-- A Greener Tomorrow (greener2morow.org)
-- Complete Namecheap cPanel MySQL / MariaDB Production Database Dump (2026)
-- Import directly via Namecheap cPanel -> phpMyAdmin -> Import
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int NOT NULL PRIMARY KEY,
  `org_name` varchar(255) NOT NULL,
  `tagline` text,
  `about_text` text,
  `address` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `website` varchar(255) NOT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT '/assets/brand/brand-logo.png',
  `logo_use_navbar` tinyint(1) DEFAULT 1,
  `logo_use_footer` tinyint(1) DEFAULT 1,
  `logo_use_section` tinyint(1) DEFAULT 1,
  `global_user_control` varchar(50) DEFAULT 'AUTO',
  `stat_programs` int DEFAULT 42,
  `stat_trees` int DEFAULT 50000,
  `stat_members` int DEFAULT 18500,
  `stat_researches` int DEFAULT 32,
  `stat_districts` int DEFAULT 64,
  `perf_webp_quality` int DEFAULT 82,
  `perf_max_width` int DEFAULT 1200,
  `perf_thumb_width` int DEFAULT 480,
  `perf_max_video_mb` int DEFAULT 25,
  `perf_max_doc_mb` int DEFAULT 15,
  `perf_lazy_loading` tinyint(1) DEFAULT 1,
  `perf_cdn_enabled` tinyint(1) DEFAULT 1,
  `perf_gzip_enabled` tinyint(1) DEFAULT 1,
  `emergency_alert_active` tinyint(1) DEFAULT 1,
  `emergency_alert_title` text,
  `emergency_alert_text` text,
  `emergency_alert_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(100) NOT NULL UNIQUE,
  `name` varchar(255) DEFAULT 'Sofiq Mobilizer',
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(191) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `must_change_password` tinyint(1) DEFAULT 0,
  `failed_login_attempts` int DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `session_version` int DEFAULT 1,
  `avatar_url` varchar(255) DEFAULT 'avatar-researcher.webp',
  `avatar` varchar(255) DEFAULT 'avatar-researcher.webp',
  `designation` varchar(255) DEFAULT NULL,
  `public_email` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `district` varchar(100) DEFAULT 'Dhaka',
  `city` varchar(100) DEFAULT 'Dhaka',
  `focus_pillar` varchar(50) DEFAULT 'CLIMATE',
  `skills` text,
  `bio` text,
  `role` varchar(50) DEFAULT 'USER',
  `control_mode` varchar(50) DEFAULT 'AUTO',
  `member_status` varchar(50) DEFAULT 'APPROVED',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL UNIQUE,
  `type` varchar(50) NOT NULL,
  `pillar` varchar(50) NOT NULL,
  `is_slider_featured` tinyint(1) DEFAULT 0,
  `excerpt` text,
  `content` longtext NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `thumb_image` varchar(255) DEFAULT NULL,
  `original_kb` int DEFAULT 0,
  `compressed_kb` int DEFAULT 0,
  `video_type` varchar(50) DEFAULT 'NONE',
  `video_embed_url` varchar(255) DEFAULT NULL,
  `video_file_path` varchar(255) DEFAULT NULL,
  `video_size_mb` decimal(8,2) DEFAULT 0.00,
  `location` varchar(255) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `activity_status` varchar(50) DEFAULT 'COMPLETED',
  `status` varchar(50) DEFAULT 'PUBLISHED',
  `moderation_note` text,
  `view_count` int DEFAULT 0,
  `unique_read_count` int DEFAULT 0,
  `reading_time` int DEFAULT 4,
  `author_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_posts_type_status` (`type`, `status`, `created_at`),
  KEY `idx_posts_pillar` (`pillar`, `status`),
  KEY `idx_posts_author` (`author_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `post_attachments` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `post_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size_kb` int DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_attachments_post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `post_comments` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `post_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `parent_id` int DEFAULT 0,
  `author_name` varchar(255) NOT NULL,
  `author_role` varchar(255) DEFAULT 'Registered Member',
  `author_avatar` varchar(255) DEFAULT 'avatar-researcher.webp',
  `comment_text` text NOT NULL,
  `status` varchar(50) DEFAULT 'APPROVED',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_comments_post` (`post_id`, `status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `post_view_logs` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `post_id` int NOT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `visitor_hash` varchar(100) NOT NULL,
  `user_id` int DEFAULT NULL,
  `device_type` varchar(50) DEFAULT 'DESKTOP',
  `browser_info` varchar(100) DEFAULT NULL,
  `os_info` varchar(100) DEFAULT NULL,
  `ip_masked` varchar(100) DEFAULT NULL,
  `referrer_source` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `post_type` varchar(50) DEFAULT NULL,
  `target_title` varchar(255) DEFAULT NULL,
  `details` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery_items` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `thumb_url` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `pillar` varchar(50) DEFAULT 'ENVIRONMENT',
  `category` varchar(100) DEFAULT 'Reforestation',
  `caption` text,
  `sort_order` int DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT 'unread',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `subtitle` text,
  `badge_text` varchar(100) DEFAULT 'Live Action',
  `image_path` varchar(255) DEFAULT 'assets/uploads/slider/slide-climate-mangrove.webp',
  `cta_text` varchar(100) DEFAULT 'Join This Action',
  `cta_url` varchar(255) DEFAULT 'activities.php',
  `secondary_cta_text` varchar(100) DEFAULT 'Become a Member',
  `secondary_cta_url` varchar(255) DEFAULT 'join.php',
  `sort_order` int DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


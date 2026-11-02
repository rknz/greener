<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Unified Post & Content Repository ("Better DB")
 * Handles CRUD, Multi-format Attachments, Slider Features & Sub-5ms Indexed Queries
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/UploadManager.php';

class PostRepository {

    /**
     * Get Featured Initiative Slider Posts for Homepage
     */
    public static function getSliderInitiatives(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT p.*, u.name as author_name, u.avatar_url as author_avatar, u.designation as author_role
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.status = 'PUBLISHED' AND p.is_slider_featured = 1
            ORDER BY p.created_at DESC LIMIT 5");
        return $stmt->fetchAll();
    }

    /**
     * Get Published Posts with Filters and Pagination
     */
    public static function getPosts(array $filters = [], int $limit = 12, int $offset = 0): array {
        $db = Database::getConnection();
        $conditions = ["p.status = 'PUBLISHED'"];
        $params = [];

        if (!empty($filters['type'])) {
            if (is_array($filters['type'])) {
                $placeholders = implode(',', array_fill(0, count($filters['type']), '?'));
                $conditions[] = "p.type IN ($placeholders)";
                $params = array_merge($params, $filters['type']);
            } else {
                $conditions[] = "p.type = ?";
                $params[] = strtoupper($filters['type']);
            }
        }

        if (!empty($filters['pillar'])) {
            $conditions[] = "p.pillar = ?";
            $params[] = strtoupper($filters['pillar']);
        }

        if (!empty($filters['author_id'])) {
            $conditions[] = "p.author_id = ?";
            $params[] = (int)$filters['author_id'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(p.title LIKE ? OR p.excerpt LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT p.*, u.name as author_name, u.avatar_url as author_avatar, u.designation as author_role,
                       u.public_email as author_email, u.phone as author_phone, u.institution as author_institution, u.district as author_district
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE $whereClause
                ORDER BY p.created_at DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get Single Post by SEO Slug with Attachments & Author Info
     */
    public static function getBySlug(string $slug): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT p.*, u.name as author_name, u.avatar_url as author_avatar, u.designation as author_role,
                                      u.public_email as author_email, u.phone as author_phone, u.institution as author_institution, u.district as author_district
                               FROM posts p
                               LEFT JOIN users u ON p.author_id = u.id
                               WHERE p.slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if (!$post) return null;

        // Fetch child attachments
        $stmtAtt = $db->prepare("SELECT * FROM post_attachments WHERE post_id = ? ORDER BY id ASC");
        $stmtAtt->execute([$post['id']]);
        $post['attachments'] = $stmtAtt->fetchAll();

        return self::normalizePost($post);
    }

    /**
     * Normalize post array so both schema conventions work seamlessly
     */
    public static function normalizePost(array $p): array {
        $typeLower = strtolower($p['type'] ?? $p['post_type'] ?? 'blog');
        if ($typeLower === 'research_blog') $typeLower = 'blog';
        $p['post_type']      = $p['post_type'] ?? $typeLower;
        $p['featured_image'] = $p['featured_image'] ?? (!empty($p['cover_image']) ? ltrim($p['cover_image'], '/') : 'assets/brand/template-mockup.jpg');
        $p['category']       = $p['category'] ?? ucfirst(strtolower($p['pillar'] ?? 'Climate Action'));
        $p['status']         = strtolower($p['status'] ?? 'published');
        $p['user_id']        = $p['user_id'] ?? $p['author_id'] ?? 1;
        $p['video_url']      = $p['video_url'] ?? $p['video_embed_url'] ?? '';
        $p['video_path']     = $p['video_path'] ?? (!empty($p['video_file_path']) ? basename($p['video_file_path']) : '');
        $p['author_city']    = $p['author_city'] ?? $p['author_district'] ?? 'Dhaka, Bangladesh';
        if (!isset($p['document_path']) && !empty($p['attachments'][0]['file_path'])) {
            $p['document_path'] = basename($p['attachments'][0]['file_path']);
        }
        return $p;
    }

    public static function getAll(array $filters = []): array {
        $mapped = [];
        if (!empty($filters['post_type'])) {
            if (is_array($filters['post_type'])) {
                $mapped['type'] = array_map('strtoupper', $filters['post_type']);
            } else {
                $t = strtoupper($filters['post_type']);
                if ($t === 'BLOG') {
                    $mapped['type'] = ['BLOG', 'RESEARCH_BLOG'];
                } elseif ($t === 'BLOG_AND_VLOG') {
                    $mapped['type'] = ['BLOG', 'RESEARCH_BLOG', 'VLOG'];
                } else {
                    $mapped['type'] = $t;
                }
            }
        }
        if (!empty($filters['search'])) {
            $mapped['search'] = $filters['search'];
        }
        $limit = (int)($filters['limit'] ?? 12);
        $offset = (int)($filters['offset'] ?? 0);
        $rows = self::getPosts($mapped, $limit, $offset);
        return array_map([self::class, 'normalizePost'], $rows);
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT p.*, u.name as author_name, u.avatar_url as author_avatar, u.designation as author_role,
                                      u.public_email as author_email, u.phone as author_phone, u.institution as author_institution, u.district as author_district
                               FROM posts p
                               LEFT JOIN users u ON p.author_id = u.id
                               WHERE p.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if (!$post) return null;
        $stmtAtt = $db->prepare("SELECT * FROM post_attachments WHERE post_id = ? ORDER BY id ASC");
        $stmtAtt->execute([$post['id']]);
        $post['attachments'] = $stmtAtt->fetchAll();
        return self::normalizePost($post);
    }

    public static function getByAuthor(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT p.*, u.name as author_name, u.username FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.author_id = ? ORDER BY p.id DESC");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'normalizePost'], $rows);
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $title = trim($data['title'] ?? '');
        $type = strtoupper(trim($data['post_type'] ?? $data['type'] ?? 'BLOG'));
        $pillar = strtoupper(trim($data['category'] ?? $data['pillar'] ?? 'CLIMATE'));
        $excerpt = trim($data['excerpt'] ?? '');
        $content = trim($data['content'] ?? '');
        $cover = !empty($data['featured_image']) ? '/' . ltrim($data['featured_image'], '/') : '/assets/brand/template-mockup.jpg';
        $videoUrl = trim($data['video_url'] ?? '');
        $videoPath = !empty($data['video_path']) ? '/assets/uploads/videos/' . basename($data['video_path']) : null;
        $videoType = !empty($videoUrl) ? 'YOUTUBE' : (!empty($videoPath) ? 'UPLOAD' : 'NONE');
        $status = strtoupper(trim($data['status'] ?? 'PUBLISHED'));

        $stmt = $db->prepare("UPDATE posts SET title = ?, type = ?, pillar = ?, excerpt = ?, content = ?, cover_image = ?, thumb_image = ?, video_type = ?, video_embed_url = ?, video_file_path = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$title, $type, $pillar, $excerpt, $content, $cover, $cover, $videoType, $videoUrl, $videoPath, $status, $id]);
    }

    public static function updateStatus(int $id, string $status): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE posts SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([strtoupper($status), $id]);
    }

    /**
     * Create Post (with auto-moderation status logic based on user control mode)
     */
    public static function create(array $data, array $attachments = []): array {
        $db = Database::getConnection();
        $user = current_user();
        if (!$user) return ['success' => false, 'error' => 'Authentication required.'];

        $title = trim($data['title'] ?? '');
        $type = strtoupper(trim($data['type'] ?? 'BLOG'));
        $pillar = strtoupper(trim($data['pillar'] ?? 'CLIMATE'));
        $content = trim($data['content'] ?? '');
        $excerpt = trim($data['excerpt'] ?? '') ?: excerpt($content, 140);
        $slug = slugify($title) . '-' . substr(bin2hex(random_bytes(3)), 0, 5);

        if (empty($title) || empty($content)) {
            return ['success' => false, 'error' => 'Title and Content are required.'];
        }

        // Approval Logic: Admins & Auto users publish instantly; Manual users go to PENDING
        $status = ($user['role'] === 'ADMIN' || $user['control_mode'] === 'AUTO' || $user['control_mode'] === 'FULL_ACCESS') 
            ? 'PUBLISHED' 
            : 'PENDING';

        $isSlider = (!empty($data['is_slider_featured']) && is_admin()) ? 1 : 0;
        $videoType = strtoupper(trim($data['video_type'] ?? 'NONE'));
        $videoEmbed = trim($data['video_embed_url'] ?? '') ?: null;
        $videoPath = trim($data['video_file_path'] ?? '') ?: null;
        $videoSizeMb = (float)($data['video_size_mb'] ?? 0);
        $cover = trim($data['cover_image'] ?? '') ?: '/assets/brand/template-mockup.jpg';
        $thumb = trim($data['thumb_image'] ?? '') ?: $cover;
        $origKb = (int)($data['original_kb'] ?? 0);
        $compKb = (int)($data['compressed_kb'] ?? 0);
        $location = trim($data['location'] ?? 'Hatirpool, Dhaka');
        $eventDate = !empty($data['event_date']) ? $data['event_date'] : date('Y-m-d H:i:s');
        $activityStatus = strtoupper(trim($data['activity_status'] ?? 'COMPLETED'));
        $readingTime = max(1, (int)ceil(str_word_count(strip_tags($content)) / 200));

        $stmt = $db->prepare("INSERT INTO posts (
            title, slug, type, pillar, is_slider_featured, excerpt, content, cover_image, thumb_image,
            original_kb, compressed_kb, video_type, video_embed_url, video_file_path, video_size_mb,
            location, event_date, activity_status, status, reading_time, author_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $title, $slug, $type, $pillar, $isSlider, $excerpt, $content, $cover, $thumb,
            $origKb, $compKb, $videoType, $videoEmbed, $videoPath, $videoSizeMb,
            $location, $eventDate, $activityStatus, $status, $readingTime, $user['id']
        ]);

        $postId = (int)$db->lastInsertId();

        // Attach documents if provided
        if (!empty($attachments)) {
            $stmtAtt = $db->prepare("INSERT INTO post_attachments (post_id, file_name, file_path, file_type, file_size_kb) VALUES (?, ?, ?, ?, ?)");
            foreach ($attachments as $att) {
                if (!empty($att['file_name']) && !empty($att['file_path'])) {
                    $stmtAtt->execute([
                        $postId,
                        $att['file_name'],
                        $att['file_path'],
                        $att['file_type'] ?? 'PDF',
                        (int)($att['size_kb'] ?? 0)
                    ]);
                }
            }
        }

        // Log audit event
        $db->prepare("INSERT INTO user_activity_logs (user_id, action, post_type, target_title, details) VALUES (?, 'CREATE_POST', ?, ?, ?)")
           ->execute([$user['id'], $type, $title, "Created post with status {$status}."]);

        return ['success' => true, 'post_id' => $postId, 'slug' => $slug, 'status' => $status];
    }

    /**
     * ACID-Compliant Delete Post & Child Records & Disk Files
     */
    public static function delete(int $postId): bool {
        $db = Database::getConnection();
        $user = current_user();
        if (!$user) return false;

        // Verify ownership or admin privileges
        $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();
        if (!$post) return false;

        if (!is_admin() && (int)$post['author_id'] !== (int)$user['id']) {
            return false;
        }

        // Fetch attachments to unlink disk files
        $stmtAtt = $db->prepare("SELECT file_path FROM post_attachments WHERE post_id = ?");
        $stmtAtt->execute([$postId]);
        $attachments = $stmtAtt->fetchAll();

        // Start ACID Transaction
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM post_attachments WHERE post_id = ?")->execute([$postId]);
            $db->prepare("DELETE FROM post_view_logs WHERE post_id = ?")->execute([$postId]);
            $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$postId]);

            // Log activity
            $action = is_admin() && (int)$post['author_id'] !== (int)$user['id'] ? 'ADMIN_REMOVE_OFFENSIVE' : 'DELETE_POST';
            $db->prepare("INSERT INTO user_activity_logs (user_id, action, post_type, target_title, details) VALUES (?, ?, ?, ?, ?)")
               ->execute([$user['id'], $action, $post['type'], $post['title'], "Deleted post #{$postId} and child assets."]);

            $db->commit();

            // Unlink files from disk safely after commit succeeds
            if (!empty($post['video_file_path'])) UploadManager::deleteFile($post['video_file_path']);
            foreach ($attachments as $att) {
                UploadManager::deleteFile($att['file_path']);
            }

            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Normal User Mini-Dashboard Statistics
     */
    public static function getUserStats(int $userId): array {
        $db = Database::getConnection();

        // 1. Post counts by type
        $stmt = $db->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN type = 'BLOG' THEN 1 ELSE 0 END) as count_blog,
            SUM(CASE WHEN type = 'VLOG' THEN 1 ELSE 0 END) as count_vlog,
            SUM(CASE WHEN type = 'RESEARCH' THEN 1 ELSE 0 END) as count_research,
            SUM(CASE WHEN type = 'ACTIVITY' THEN 1 ELSE 0 END) as count_activity,
            SUM(CASE WHEN status = 'PUBLISHED' THEN 1 ELSE 0 END) as count_published,
            SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as count_pending,
            SUM(view_count) as total_views,
            SUM(unique_read_count) as total_unique_reads,
            SUM(original_kb - compressed_kb) as total_kb_saved
        FROM posts WHERE author_id = ?");
        $stmt->execute([$userId]);
        $counts = $stmt->fetch() ?: [];

        // 2. Visitor device breakdown on user's posts
        $stmtDev = $db->prepare("SELECT v.device_type, COUNT(*) as count 
            FROM post_view_logs v
            JOIN posts p ON v.post_id = p.id
            WHERE p.author_id = ?
            GROUP BY v.device_type");
        $stmtDev->execute([$userId]);
        $devices = $stmtDev->fetchAll(PDO::FETCH_KEY_PAIR);

        // 3. User's recent posts list
        $stmtPosts = $db->prepare("SELECT * FROM posts WHERE author_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmtPosts->execute([$userId]);
        $posts = $stmtPosts->fetchAll();

        // 4. User's activity log timeline
        $stmtLogs = $db->prepare("SELECT * FROM user_activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 15");
        $stmtLogs->execute([$userId]);
        $logs = $stmtLogs->fetchAll();

        return [
            'counts'  => $counts,
            'devices' => $devices,
            'posts'   => $posts,
            'logs'    => $logs
        ];
    }
}


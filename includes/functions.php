<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Master Helper Functions & View Components
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. URL & Asset Generators
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

function asset(string $path): string {
    $path = ltrim($path, '/');
    return BASE_URL . '/assets/' . $path;
}

// 2. Security & Sanitization
function sanitize(?string $data): string {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): bool {
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($submitted) && hash_equals($_SESSION['csrf_token'] ?? '', $submitted);
}

// 3. Authentication & Role Helpers
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    static $user = null;
    if ($user === null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function is_admin(): bool {
    $user = current_user();
    return $user && (strtoupper($user['role'] ?? '') === 'ADMIN' || ($user['control_mode'] ?? '') === 'FULL_ACCESS');
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . url('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        header('Location: ' . url('dashboard/index.php'));
        exit;
    }
}

// 4. Site Settings Singleton
function get_site_settings(): array {
    static $settings = null;
    if ($settings === null) {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
        $settings = $stmt->fetch() ?: [
            'org_name' => ORG_NAME,
            'tagline' => ORG_TAGLINE,
            'address' => ORG_ADDRESS,
            'email' => ORG_EMAIL,
            'phone' => ORG_PHONE,
            'website' => ORG_WEBSITE,
            'logo_url' => '/assets/brand/brand-logo.png',
            'logo_use_navbar' => 1,
            'logo_use_footer' => 1,
            'logo_use_section' => 1,
            'global_user_control' => 'AUTO',
            'stat_programs' => 42,
            'stat_trees' => 50000,
            'stat_members' => 1850,
            'stat_researches' => 24,
            'stat_districts' => 48,
            'emergency_alert_active' => 1,
            'emergency_alert_title' => 'Urgent Action: Saving the Sundarbans',
            'emergency_alert_text' => 'Mobilizing youth volunteers nationwide for coastal mangrove defense.',
            'emergency_alert_link' => '#initiatives'
        ];
    }
    return $settings;
}

// 5. Formatters & UI Utilities
function format_bytes(int $bytes, int $precision = 1): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}

function render_pillar_badge(string $pillar): string {
    $pillar = strtoupper($pillar);
    $badges = [
        'CLIMATE' => ['label' => 'Climate Action', 'icon' => 'ri-sun-line', 'class' => 'badge-climate'],
        'ENVIRONMENT' => ['label' => 'Environment', 'icon' => 'ri-leaf-line', 'class' => 'badge-environment'],
        'DEFORESTATION' => ['label' => 'Deforestation', 'icon' => 'ri-tree-line', 'class' => 'badge-deforestation'],
        'POLLUTION' => ['label' => 'Pollution Control', 'icon' => 'ri-drop-line', 'class' => 'badge-pollution'],
        'DISASTER' => ['label' => 'Disaster Resilience', 'icon' => 'ri-shield-cross-line', 'class' => 'badge-disaster'],
    ];
    $data = $badges[$pillar] ?? ['label' => $pillar, 'icon' => 'ri-earth-line', 'class' => 'badge-environment'];
    return sprintf(
        '<span class="badge %s"><i class="%s"></i> %s</span>',
        $data['class'],
        $data['icon'],
        htmlspecialchars($data['label'])
    );
}

function render_post_type_label(string $type): string {
    $labels = [
        'ACTIVITY' => '<span class="type-tag type-activity"><i class="ri-flag-line"></i> Activity</span>',
        'RESEARCH' => '<span class="type-tag type-research"><i class="ri-file-chart-line"></i> Research</span>',
        'RESEARCH_BLOG' => '<span class="type-tag type-rb"><i class="ri-article-line"></i> Research Blog</span>',
        'BLOG' => '<span class="type-tag type-blog"><i class="ri-quill-pen-line"></i> Blog</span>',
        'VLOG' => '<span class="type-tag type-vlog"><i class="ri-video-line"></i> Vlog</span>',
    ];
    return $labels[strtoupper($type)] ?? '<span class="type-tag">' . htmlspecialchars($type) . '</span>';
}

function excerpt(string $text, int $limit = 120): string {
    $clean = strip_tags($text);
    if (mb_strlen($clean) <= $limit) return $clean;
    return mb_substr($clean, 0, $limit) . '...';
}

// 6. Universal Bridge & Compatibility Helpers
function get_all_settings(): array {
    $s = get_site_settings();
    $s['site_name']       = $s['org_name'] ?? ORG_NAME;
    $s['site_tagline']    = $s['tagline'] ?? ORG_TAGLINE;
    $s['contact_email']   = $s['email'] ?? ORG_EMAIL;
    $s['contact_phone']   = $s['phone'] ?? ORG_PHONE;
    $s['contact_address'] = $s['address'] ?? ORG_ADDRESS;
    $s['logo_path']       = !empty($s['logo_url']) ? basename($s['logo_url']) : 'brand-logo.png';
    $s['logo_alt']        = $s['org_name'] ?? ORG_NAME;
    $s['perf_cache_days'] = $s['perf_cache_days'] ?? 30;
    $s['perf_lazy_aggressive'] = $s['perf_lazy_aggressive'] ?? 85;
    return $s;
}

function set_setting(string $key, string $value): void {
    $db = Database::getConnection();
    $map = [
        'site_name'       => 'org_name',
        'site_tagline'    => 'tagline',
        'contact_email'   => 'email',
        'contact_phone'   => 'phone',
        'contact_address' => 'address',
        'logo_path'       => 'logo_url'
    ];
    $col = $map[$key] ?? $key;
    $allowedCols = [
        'org_name', 'tagline', 'about_text', 'address', 'email', 'phone', 'website',
        'logo_url', 'logo_use_navbar', 'logo_use_footer', 'logo_use_section',
        'perf_webp_quality', 'perf_lazy_loading', 'perf_cdn_enabled', 'perf_gzip_enabled'
    ];
    if (in_array($col, $allowedCols, true)) {
        if ($col === 'logo_url' && !str_starts_with($value, '/')) {
            $value = '/assets/uploads/logos/' . $value;
        }
        $stmt = $db->prepare("UPDATE site_settings SET {$col} = ? WHERE id = 1");
        $stmt->execute([$value]);
    }
}

function sanitize_text(?string $data): string {
    return sanitize($data);
}

function verify_csrf_token(?string $token = null): bool {
    if ($token !== null) {
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
    return csrf_verify();
}

function truncate_text(?string $text, int $limit = 120): string {
    return excerpt((string)$text, $limit);
}

function format_bangladesh_date(?string $datetime): string {
    if (empty($datetime)) return date('d M Y');
    $ts = strtotime($datetime);
    return $ts ? date('d M Y', $ts) : date('d M Y');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function render_post_body_html(?string $content): string {
    if (empty($content)) return '';
    // If content already contains HTML structural tags, allow safe editorial tags
    if (preg_match('/<(h[1-6]|p|ul|ol|li|strong|em|blockquote|br)\b/i', $content)) {
        return strip_tags($content, '<h2><h3><h4><h5><p><ul><ol><li><strong><b><em><i><blockquote><br><a><span>');
    }
    return '<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) . '</p>';
}




<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Master Application Configuration & Auto-Detection
 */

// 1. Timezone & Error Handling
date_default_timezone_set('Asia/Dhaka');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// 2. Start Secure Session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// 3. Dynamic BASE_URL Auto-Detection
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Determine subdirectory if deployed in subfolder
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $docRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $serverDocRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    
    $subDir = '';
    if (!empty($serverDocRoot) && strpos($docRoot, $serverDocRoot) === 0) {
        $subDir = substr($docRoot, strlen($serverDocRoot));
    }
    $subDir = trim($subDir, '/');
    $baseUrl = $protocol . $host . ($subDir ? '/' . $subDir : '');
    define('BASE_URL', rtrim($baseUrl, '/'));
}

// 4. File Path Constants
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/..'));
    define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
    define('DATA_PATH', ROOT_PATH . '/data');
}
if (!defined('AGY_ROOT')) {
    define('AGY_ROOT', ROOT_PATH);
}

// 5. Organization Identity Constants
define('ORG_NAME', 'A Greener Tomorrow');
define('ORG_LEGAL_MODEL', 'Sofiq Organization Action & Mobilization Model');
define('ORG_TAGLINE', 'Youth-Based Environmental Advocacy & Independent Research Organization');
define('ORG_ADDRESS', '36, Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205, Bangladesh');
define('ORG_EMAIL', 'greener2morow@gmail.com');
define('ORG_WEBSITE', 'greener2morow.org');
define('ORG_PHONE', '+880 1712-345678');

// 6. Security & Upload Thresholds
define('MAX_VIDEO_MB', 25);
define('MAX_DOC_MB', 15);
define('MAX_IMAGE_MB', 10);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// 7. Cloud CDN URLs (Allowed & Preconnected)
define('CDN_GOOGLE_FONTS', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;600;700&display=swap');
define('CDN_REMIX_ICONS', 'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css');
define('CDN_CHART_JS', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js');

// 8. CSRF Protection Helpers
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

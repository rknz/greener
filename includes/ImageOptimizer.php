<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Dynamic PHP GD/WebP Image Compressor & Universal Lazy Loading Engine
 */

require_once __DIR__ . '/../config/config.php';

class ImageOptimizer {

    /**
     * Uploads and compresses an image into WebP format (dual size: full & thumbnail)
     * Returns: ['success' => bool, 'full_url' => string, 'thumb_url' => string, 'orig_kb' => int, 'comp_kb' => int, 'error' => string]
     */
    public static function uploadAndCompress(array $file, string $subFolder = 'posts', int $quality = 80): array {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'No file uploaded or upload error code: ' . ($file['error'] ?? 'unknown')];
        }

        // Validate MIME type via finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowed, true)) {
            return ['success' => false, 'error' => 'Invalid file format. Allowed: JPG, PNG, WebP.'];
        }

        $origBytes = filesize($file['tmp_name']);
        $targetDir = UPLOAD_PATH . '/' . trim($subFolder, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $hash = bin2hex(random_bytes(12));
        $fullFileName  = 'full_' . $hash . '.webp';
        $thumbFileName = 'thumb_' . $hash . '.webp';
        $fullPath      = $targetDir . '/' . $fullFileName;
        $thumbPath     = $targetDir . '/' . $thumbFileName;

        // Check if GD extension is available
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            // Fallback: move original file directly
            $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
            $fallbackName = 'file_' . $hash . '.' . $ext;
            $fallbackPath = $targetDir . '/' . $fallbackName;
            move_uploaded_file($file['tmp_name'], $fallbackPath);
            $relUrl = '/assets/uploads/' . trim($subFolder, '/') . '/' . $fallbackName;
            return [
                'success'   => true,
                'full_url'  => $relUrl,
                'thumb_url' => $relUrl,
                'orig_kb'   => (int)ceil($origBytes / 1024),
                'comp_kb'   => (int)ceil($origBytes / 1024)
            ];
        }

        // Create image resource based on MIME
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default      => null
        };

        if (!$img) {
            return ['success' => false, 'error' => 'Unable to decode image resource.'];
        }

        // Strip EXIF / Preserve alpha for PNG/WebP
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $width  = imagesx($img);
        $height = imagesy($img);

        // 1. Generate Full Version (Max Width: 1200px)
        $fullWidth = min($width, 1200);
        $fullHeight = (int)round(($height / $width) * $fullWidth);
        $fullImg = imagescale($img, $fullWidth, $fullHeight);
        if ($fullImg) {
            imagealphablending($fullImg, true);
            imagesavealpha($fullImg, true);
            imagewebp($fullImg, $fullPath, $quality);
            imagedestroy($fullImg);
        } else {
            imagewebp($img, $fullPath, $quality);
        }

        // 2. Generate Card Thumbnail (Max Width: 480px, Golden Ratio proportion)
        $thumbWidth = min($width, 480);
        $thumbHeight = (int)round(($height / $width) * $thumbWidth);
        $thumbImg = imagescale($img, $thumbWidth, $thumbHeight);
        if ($thumbImg) {
            imagealphablending($thumbImg, true);
            imagesavealpha($thumbImg, true);
            imagewebp($thumbImg, $thumbPath, max(75, $quality - 5));
            imagedestroy($thumbImg);
        } else {
            imagewebp($img, $thumbPath, max(75, $quality - 5));
        }

        imagedestroy($img);

        $compBytes = file_exists($fullPath) ? filesize($fullPath) : $origBytes;
        $relPath = '/assets/uploads/' . trim($subFolder, '/');

        return [
            'success'   => true,
            'full_url'  => $relPath . '/' . $fullFileName,
            'thumb_url' => $relPath . '/' . $thumbFileName,
            'orig_kb'   => (int)ceil($origBytes / 1024),
            'comp_kb'   => (int)ceil($compBytes / 1024)
        ];
    }

    /**
     * Universal Lazy-Loading Image Component with Golden Ratio & Responsive srcset
     */
    public static function renderLazy(
        string $src,
        string $alt = '',
        string $class = '',
        mixed $thumbSrc = '',
        mixed $isHero = false,
        string $aspectRatio = '1.618 / 1'
    ): string {
        $srcEsc = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
        $altEsc = htmlspecialchars($alt ?: ORG_NAME, ENT_QUOTES, 'UTF-8');
        $classEsc = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');

        // Handle case where width/height ints were passed as 4th and 5th args
        if (is_int($thumbSrc) || is_numeric($thumbSrc)) {
            $thumbSrc = '';
            $isHero = false;
        }
        $isHeroBool = (bool)$isHero;

        // Resolve absolute URL
        $fullUrl = (str_starts_with($src, 'http')) ? $src : BASE_URL . '/' . ltrim($src, '/');
        $thumbUrl = (!empty($thumbSrc) && is_string($thumbSrc)) ? ((str_starts_with($thumbSrc, 'http')) ? $thumbSrc : BASE_URL . '/' . ltrim($thumbSrc, '/')) : $fullUrl;

        if ($isHeroBool) {
            return sprintf(
                '<img src="%s" alt="%s" class="%s" fetchpriority="high" decoding="sync" style="aspect-ratio: %s; object-fit: cover;">',
                $fullUrl,
                $altEsc,
                $classEsc,
                $aspectRatio
            );
        }

        return sprintf(
            '<img src="%s" srcset="%s 480w, %s 1200w" sizes="(max-width: 640px) 480px, 1200px" alt="%s" class="%s lazy-img" loading="lazy" decoding="async" style="aspect-ratio: %s; object-fit: cover;">',
            $thumbUrl,
            $thumbUrl,
            $fullUrl,
            $altEsc,
            $classEsc,
            $aspectRatio
        );
    }
}


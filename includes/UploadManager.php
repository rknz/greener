<?php
/**
 * A Greener Tomorrow (greener2morow.org)
 * Secure Multi-Format Upload Manager (Docs, PDFs & Video)
 * Implements MIME inspection, filename hashing & shared hosting safeguards
 */

require_once __DIR__ . '/../config/config.php';

class UploadManager {

    /**
     * Uploads research & blog document attachments (PDF, DOCX, XLSX, CSV, Images)
     */
    public static function uploadDocument(array $file): array {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE)];
        }

        // 1. File size check
        $maxBytes = MAX_DOC_MB * 1024 * 1024;
        $fileSize = filesize($file['tmp_name']);
        if ($fileSize > $maxBytes) {
            return ['success' => false, 'error' => 'Document exceeds maximum size limit of ' . MAX_DOC_MB . ' MB.'];
        }

        // 2. MIME type verification
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'application/pdf' => 'PDF',
            'application/msword' => 'DOCX',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
            'application/vnd.ms-excel' => 'DATASET',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'DATASET',
            'text/csv' => 'DATASET',
            'text/plain' => 'DOCX',
            'image/jpeg' => 'IMAGE',
            'image/png' => 'IMAGE',
            'image/webp' => 'IMAGE'
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            return ['success' => false, 'error' => 'Unsupported document format. Allowed: PDF, Word DOCX, Excel/CSV, JPG/PNG/WebP.'];
        }

        // 3. Extension extraction and sanity check
        $origName = basename($file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $banned = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'pl', 'py', 'cgi', 'sh', 'bash', 'exe'];
        if (in_array($ext, $banned, true)) {
            return ['success' => false, 'error' => 'Disallowed file extension.'];
        }

        // 4. Crypto-random filename
        $targetDir = UPLOAD_PATH . '/docs';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $safeName = 'doc_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $targetDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Failed to save document to server. Check folder permissions.'];
        }

        return [
            'success'   => true,
            'filename'  => $safeName,
            'file_name' => $origName,
            'file_path' => '/assets/uploads/docs/' . $safeName,
            'file_type' => $allowedMimes[$mime],
            'size_kb'   => (int)ceil($fileSize / 1024)
        ];
    }

    /**
     * Handles direct video file upload (MP4/WebM) with strict size limits
     */
    public static function uploadVideo(array $file, int $maxMb = MAX_VIDEO_MB): array {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE)];
        }

        // 1. File size check
        $maxBytes = $maxMb * 1024 * 1024;
        $fileSize = filesize($file['tmp_name']);
        if ($fileSize > $maxBytes) {
            return ['success' => false, 'error' => "Video file exceeds the server limit of {$maxMb} MB. Please compress the video or use a YouTube link."];
        }

        // 2. MIME type verification
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedVideos = [
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/quicktime' => 'mov'
        ];

        if (!array_key_exists($mime, $allowedVideos)) {
            return ['success' => false, 'error' => 'Invalid video format. Allowed: MP4, WebM, MOV.'];
        }

        // 3. Crypto-random filename
        $targetDir = UPLOAD_PATH . '/videos';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $ext = $allowedVideos[$mime];
        $safeName = 'vid_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $targetDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Failed to move video file.'];
        }

        return [
            'success'   => true,
            'filename'  => $safeName,
            'file_path' => '/assets/uploads/videos/' . $safeName,
            'size_mb'   => round($fileSize / (1024 * 1024), 2)
        ];
    }

    public static function uploadImage(array $file, string $subFolder = 'posts'): array {
        require_once __DIR__ . '/ImageOptimizer.php';
        $res = ImageOptimizer::uploadAndCompress($file, $subFolder, 82);
        if ($res['success']) {
            $res['filename'] = basename($res['full_url']);
        }
        return $res;
    }

    public static function uploadLogo(array $file): array {
        return self::uploadImage($file, 'logos');
    }

    /**
     * Safely deletes an uploaded asset from disk
     */
    public static function deleteFile(string $relPath): bool {
        $clean = ltrim($relPath, '/');
        // Prevent path traversal
        if (str_contains($clean, '..')) return false;
        $fullPath = ROOT_PATH . '/' . $clean;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return @unlink($fullPath);
        }
        return false;
    }

    private static function getUploadErrorMessage(int $code): string {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds HTML form MAX_FILE_SIZE limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to server disk.',
            default               => 'Unknown upload error occurred.'
        };
    }
}


<?php
/**
 * A Greener Tomorrow (Version 2) — Namecheap cPanel 1-Click Auto-Installer & Extractor
 * Upload this single file (4 KB) to /home/greeykdv/public_html/cpanel-auto-install.php
 * and open https://yourdomain.com/cpanel-auto-install.php in your browser.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(300);

$targetDir = __DIR__;
$githubRawZip = 'https://raw.githubusercontent.com/rknz/greener/main/greener-version-2-namecheap.zip';
$localZip = $targetDir . '/greener-version-2-namecheap.zip';
$legacyZip = $targetDir . '/greener-namecheap-cpanel-ready.zip';

$status = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'github';

    if ($mode === 'github') {
        $ch = curl_init($githubRawZip);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Greener-cPanel-Installer/2.0'
        ]);
        $data = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || !$data || strlen($data) < 100000) {
            $error = "Failed to download archive from GitHub (HTTP $httpCode). $curlErr";
        } else {
            file_put_contents($localZip, $data);
        }
    }

    $zipToExtract = file_exists($localZip) ? $localZip : (file_exists($legacyZip) ? $legacyZip : '');

    if (!$error && (!$zipToExtract || !file_exists($zipToExtract))) {
        $error = 'No ZIP file found to extract. Please choose "Download Directly from GitHub & Extract".';
    }

    if (!$error && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        $res = $zip->open($zipToExtract);
        if ($res === true) {
            $zip->extractTo($targetDir);
            $count = $zip->numFiles;
            $zip->close();
            @chmod($targetDir . '/database', 0755);
            @chmod($targetDir . '/database/greener.sqlite', 0664);
            @chmod($targetDir . '/assets/uploads', 0755);
            $status = "Successfully extracted $count files into $targetDir! Your Version 2 website is now live.";
        } else {
            $error = "ZipArchive failed to open file (Error Code: $res). The uploaded file may be incomplete or corrupted. Click 'Fetch Directly from GitHub & Extract' below to fix it automatically.";
        }
    } elseif (!$error) {
        $error = 'PHP ZipArchive extension is not enabled. Please extract greener-cpanel-linux.tar.gz in cPanel File Manager.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A Greener Tomorrow V2 — cPanel Auto-Installer</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0B1912; color: #F8FAFC; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { max-width: 580px; width: 100%; background: #12261C; border: 1px solid rgba(16,185,129,0.25); border-radius: 16px; padding: 32px; box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
        h1 { margin: 0 0 8px; font-size: 1.5rem; color: #10B981; }
        p { color: #CBD5E1; line-height: 1.6; font-size: 0.95rem; }
        .alert { padding: 14px 16px; border-radius: 10px; margin: 16px 0; font-weight: 600; font-size: 0.92rem; }
        .alert-success { background: rgba(16,185,129,0.18); border: 1px solid #10B981; color: #6EE7B7; }
        .alert-error { background: rgba(239,68,68,0.18); border: 1px solid #EF4444; color: #FCA5A5; }
        .btn { display: block; width: 100%; padding: 14px 20px; border-radius: 10px; border: none; font-weight: 700; font-size: 1rem; cursor: pointer; margin-top: 12px; text-align: center; text-decoration: none; }
        .btn-primary { background: #10B981; color: #052E16; }
        .btn-primary:hover { background: #34D399; }
        .btn-secondary { background: rgba(255,255,255,0.08); color: #E2E8F0; border: 1px solid rgba(255,255,255,0.15); }
    </style>
</head>
<body>
<div class="card">
    <h1>A Greener Tomorrow — Version 2</h1>
    <p>Namecheap cPanel 1-Click Deployment Utility (Target: <code><?= htmlspecialchars($targetDir) ?></code>)</p>

    <?php if ($status): ?>
        <div class="alert alert-success"><?= htmlspecialchars($status) ?></div>
        <a href="index.php" class="btn btn-primary">Open Live Website &rarr;</a>
        <a href="admin/index.php" class="btn btn-secondary">Open Admin Dashboard &rarr;</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="mode" value="github">
            <button type="submit" class="btn btn-primary">1-Click Fetch from GitHub &amp; Extract to public_html</button>
        </form>

        <form method="post">
            <input type="hidden" name="mode" value="local">
            <button type="submit" class="btn btn-secondary">Extract Uploaded Local ZIP in public_html</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>

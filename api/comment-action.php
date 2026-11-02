<?php
/**
 * A Greener Tomorrow - Threaded Comment & Reply API Endpoint
 * Allows registered members (and verified contributors) to post comments and nested replies.
 */

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$db = Database::getConnection();
$currentUser = Auth::user();

$action = $_POST['action'] ?? 'add_comment';

if ($action === 'delete_comment') {
    if (!Auth::isAdmin()) {
        echo json_encode(['success' => false, 'error' => 'Administrator privileges required.']);
        exit;
    }
    $commentId = (int)($_POST['comment_id'] ?? 0);
    if ($commentId > 0) {
        $db->prepare("DELETE FROM post_comments WHERE id = ? OR parent_id = ?")->execute([$commentId, $commentId]);
    }
    echo json_encode(['success' => true, 'message' => 'Comment removed.']);
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$parentId = (int)($_POST['parent_id'] ?? 0);
$commentText = trim(strip_tags($_POST['comment_text'] ?? ''));

if ($postId <= 0 || empty($commentText)) {
    echo json_encode(['success' => false, 'error' => 'Please write a comment before submitting.']);
    exit;
}

if (mb_strlen($commentText) < 3) {
    echo json_encode(['success' => false, 'error' => 'Comment is too short.']);
    exit;
}

// Determine author credentials (Registered Member or Quick Guest/Member)
if ($currentUser) {
    $userId = (int)$currentUser['id'];
    $authorName = $currentUser['full_name'] ?? $currentUser['name'] ?? $currentUser['username'];
    $authorRole = !empty($currentUser['designation'])
        ? $currentUser['designation']
        : ($currentUser['role'] === 'admin' ? 'Central Executive Director' : 'Registered Member (' . ($currentUser['city'] ?? 'Dhaka') . ')');
    $authorAvatar = !empty($currentUser['avatar']) ? basename($currentUser['avatar']) : 'avatar-researcher.webp';
} else {
    $guestName = trim(strip_tags($_POST['author_name'] ?? ''));
    if (empty($guestName)) {
        echo json_encode([
            'success' => false,
            'require_login' => true,
            'error' => 'Please sign in as a registered member (or enter your member name) to post a comment.'
        ]);
        exit;
    }
    $userId = null;
    $authorName = $guestName;
    $authorRole = 'Registered Youth Member';
    $authorAvatar = 'avatar-researcher.webp';
}

try {
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, parent_id, author_name, author_role, author_avatar, comment_text, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'APPROVED', ?)");
    $stmt->execute([$postId, $userId, $parentId, $authorName, $authorRole, $authorAvatar, $commentText, $now]);
    $newId = (int)$db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => $parentId > 0 ? 'Your reply has been published!' : 'Your comment has been published!',
        'comment' => [
            'id' => $newId,
            'post_id' => $postId,
            'parent_id' => $parentId,
            'author_name' => htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8'),
            'author_role' => htmlspecialchars($authorRole, ENT_QUOTES, 'UTF-8'),
            'author_avatar' => BASE_URL . '/assets/uploads/avatars/' . htmlspecialchars($authorAvatar, ENT_QUOTES, 'UTF-8'),
            'comment_text' => nl2br(htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8')),
            'created_at_formatted' => 'Just now'
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Could not save comment. Please try again.']);
}

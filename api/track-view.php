<?php
/**
 * A Greener Tomorrow - Async Post View Tracker Endpoint
 * Logs genuine reader engagement, device type, and masked IP.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/Tracker.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Read raw body (compatible with navigator.sendBeacon and fetch)
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!isset($data['post_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing post_id']);
    exit;
}

$postId = (int)$data['post_id'];
if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post_id']);
    exit;
}

try {
    $tracked = Tracker::trackPostView($postId);
    echo json_encode([
        'success' => true,
        'tracked' => $tracked,
        'post_id' => $postId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Tracking failure']);
}


<?php
/**
 * A Greener Tomorrow - Server Operations & AJAX Endpoints
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once AGY_ROOT . '/includes/functions.php';
require_once AGY_ROOT . '/includes/Auth.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'rsvp':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $activityId = (int)($_POST['activity_id'] ?? 0);
        $name = sanitize_text($_POST['name'] ?? '');
        $email = sanitize_text($_POST['email'] ?? '');
        $phone = sanitize_text($_POST['phone'] ?? '');

        if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please provide a valid name and email.']);
            exit;
        }

        $db = Database::getConnection();
        try {
            // Save RSVP inquiry to contact_messages as event RSVP
            $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'unread', datetime('now'))");
            $subject = "Event RSVP for Activity #" . $activityId;
            $msg = "Participant {$name} ({$phone}) registered for activity ID {$activityId}.";
            $stmt->execute([$name, $email, $phone, $subject, $msg]);

            echo json_encode([
                'success' => true,
                'message' => 'Thank you for registering! Our field coordinator will reach out to you.'
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error while saving RSVP.']);
        }
        break;

    case 'health':
        echo json_encode([
            'status' => 'operational',
            'timestamp' => date('c'),
            'php_version' => PHP_VERSION,
            'driver' => Database::getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME)
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unknown action requested']);
        break;
}


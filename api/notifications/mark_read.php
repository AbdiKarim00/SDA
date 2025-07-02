<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get request body
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['notification_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Notification ID is required']);
    exit;
}

$notificationId = $data['notification_id'];
$userId = $auth->getCurrentUser()['id'];

try {
    $db = Database::getInstance();
    
    // Mark notification as read
    $db->query("
        UPDATE notifications 
        SET is_read = true 
        WHERE id = :id AND user_id = :user_id
    ", [
        'id' => $notificationId,
        'user_id' => $userId
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to mark notification as read']);
} 
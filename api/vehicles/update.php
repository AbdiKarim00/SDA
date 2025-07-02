<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/notifications.php';

header('Content-Type: application/json');

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn() || !in_array($auth->getCurrentUser()['role'], ['admin', 'logistics'])) {
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

// Validate required fields
if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Vehicle ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $notifications = new Notifications();
    
    // Start transaction
    $db->beginTransaction();
    
    // Get current vehicle data
    $currentVehicle = $db->query("
        SELECT registration_number, status
        FROM vehicles
        WHERE id = :id
    ", ['id' => $data['id']])->fetch();
    
    if (!$currentVehicle) {
        http_response_code(404);
        echo json_encode(['error' => 'Vehicle not found']);
        exit;
    }
    
    // Build update query
    $updates = [];
    $params = ['id' => $data['id']];
    
    $fields = [
        'registration_number', 'make', 'model', 'year',
        'capacity', 'status', 'current_location'
    ];
    
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            if ($field === 'current_location') {
                $updates[] = "current_location = ST_SetSRID(ST_GeomFromText(:$field), 4326)";
            } else {
                $updates[] = "$field = :$field";
            }
            $params[$field] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        exit;
    }
    
    // Update vehicle
    $db->query("
        UPDATE vehicles
        SET " . implode(', ', $updates) . "
        WHERE id = :id
    ", $params);
    
    // Send notification if status changed
    if (isset($data['status']) && $data['status'] !== $currentVehicle['status']) {
        $notifications->createForRole(
            'admin',
            'Vehicle Status Updated',
            "Vehicle {$currentVehicle['registration_number']} status changed from {$currentVehicle['status']} to {$data['status']}.",
            'info'
        );
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update vehicle']);
} 
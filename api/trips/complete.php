<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/notifications.php';

header('Content-Type: application/json');

// Check if user is logged in and is a driver
$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'driver') {
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
if (!isset($data['trip_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Trip ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $notifications = new Notifications();
    
    // Start transaction
    $db->beginTransaction();
    
    // Get trip details
    $trip = $db->query("
        SELECT 
            t.*,
            v.registration_number,
            v.id as vehicle_id,
            u.email as driver_email
        FROM trips t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN users u ON t.driver_id = u.id
        WHERE t.id = :trip_id AND t.driver_id = :driver_id
    ", [
        'trip_id' => $data['trip_id'],
        'driver_id' => $auth->getCurrentUser()['id']
    ])->fetch();
    
    if (!$trip) {
        http_response_code(404);
        echo json_encode(['error' => 'Trip not found']);
        exit;
    }
    
    if ($trip['status'] !== 'in_progress') {
        http_response_code(400);
        echo json_encode(['error' => 'Trip is not in progress']);
        exit;
    }
    
    // Update trip status
    $db->query("
        UPDATE trips
        SET status = 'completed', end_time = NOW()
        WHERE id = :trip_id
    ", ['trip_id' => $data['trip_id']]);
    
    // Update vehicle status
    $db->query("
        UPDATE vehicles
        SET status = 'active'
        WHERE id = :vehicle_id
    ", ['vehicle_id' => $trip['vehicle_id']]);
    
    // Send notifications
    $notifications->createForRole(
        'logistics',
        'Trip Completed',
        "Trip for vehicle {$trip['registration_number']} has been completed by driver {$trip['driver_email']}.",
        'success'
    );
    
    $notifications->createForRole(
        'admin',
        'Trip Completed',
        "Trip for vehicle {$trip['registration_number']} has been completed.",
        'success'
    );
    
    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(['error' => 'Failed to complete trip']);
} 
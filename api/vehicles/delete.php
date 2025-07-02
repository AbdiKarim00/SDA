<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/notifications.php';

header('Content-Type: application/json');

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'admin') {
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
    
    // Get vehicle details for notification
    $vehicle = $db->query("
        SELECT registration_number
        FROM vehicles
        WHERE id = :id
    ", ['id' => $data['id']])->fetch();
    
    if (!$vehicle) {
        http_response_code(404);
        echo json_encode(['error' => 'Vehicle not found']);
        exit;
    }
    
    // Check if vehicle has any active trips
    $activeTrips = $db->query("
        SELECT COUNT(*) as count
        FROM trips
        WHERE vehicle_id = :id AND status = 'in_progress'
    ", ['id' => $data['id']])->fetch()['count'];
    
    if ($activeTrips > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete vehicle with active trips']);
        exit;
    }
    
    // Delete vehicle
    $db->query("
        DELETE FROM vehicles
        WHERE id = :id
    ", ['id' => $data['id']]);
    
    // Send notification
    $notifications->createForRole(
        'admin',
        'Vehicle Deleted',
        "Vehicle {$vehicle['registration_number']} has been deleted from the fleet.",
        'warning'
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
    echo json_encode(['error' => 'Failed to delete vehicle']);
} 
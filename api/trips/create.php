<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/notifications.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
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
$required_fields = ['vehicle_id', 'driver_id', 'start_location', 'end_location', 'start_time'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

try {
    $db = Database::getInstance();
    $notifications = new Notifications();
    
    // Start transaction
    $db->beginTransaction();
    
    // Check if vehicle is available
    $vehicle = $db->query("
        SELECT v.*, COUNT(t.id) as active_trips
        FROM vehicles v
        LEFT JOIN trips t ON v.id = t.vehicle_id AND t.status = 'in_progress'
        WHERE v.id = :vehicle_id
        GROUP BY v.id
    ", ['vehicle_id' => $data['vehicle_id']])->fetch();
    
    if (!$vehicle) {
        throw new Exception('Vehicle not found');
    }
    
    if ($vehicle['status'] !== 'active') {
        throw new Exception('Vehicle is not available for trips');
    }
    
    if ($vehicle['active_trips'] > 0) {
        throw new Exception('Vehicle is already on an active trip');
    }
    
    // Check if driver is available
    $driver = $db->query("
        SELECT u.*, COUNT(t.id) as active_trips
        FROM users u
        LEFT JOIN trips t ON u.id = t.driver_id AND t.status = 'in_progress'
        WHERE u.id = :driver_id AND u.role = 'driver'
        GROUP BY u.id
    ", ['driver_id' => $data['driver_id']])->fetch();
    
    if (!$driver) {
        throw new Exception('Driver not found');
    }
    
    if ($driver['active_trips'] > 0) {
        throw new Exception('Driver is already on an active trip');
    }
    
    // Create trip
    $db->query("
        INSERT INTO trips (
            vehicle_id,
            driver_id,
            start_location,
            end_location,
            start_time,
            notes,
            status,
            created_by
        ) VALUES (
            :vehicle_id,
            :driver_id,
            ST_GeomFromText(:start_location),
            ST_GeomFromText(:end_location),
            :start_time,
            :notes,
            'pending',
            :created_by
        )
    ", [
        'vehicle_id' => $data['vehicle_id'],
        'driver_id' => $data['driver_id'],
        'start_location' => $data['start_location'],
        'end_location' => $data['end_location'],
        'start_time' => $data['start_time'],
        'notes' => $data['notes'] ?? null,
        'created_by' => $auth->getCurrentUser()['id']
    ]);
    
    // Send notifications
    $notifications->createForUser(
        $data['driver_id'],
        'New Trip Assigned',
        "You have been assigned a new trip starting at {$data['start_time']}.",
        'info'
    );
    
    $notifications->createForRole(
        'admin',
        'New Trip Created',
        "A new trip has been created for vehicle {$vehicle['registration_number']}.",
        'info'
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
    echo json_encode(['error' => $e->getMessage()]);
} 
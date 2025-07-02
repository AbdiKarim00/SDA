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
    
    // Get current trip details
    $currentTrip = $db->query("
        SELECT 
            t.*,
            v.registration_number,
            v.id as vehicle_id,
            u.email as driver_email
        FROM trips t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN users u ON t.driver_id = u.id
        WHERE t.id = :trip_id
    ", ['trip_id' => $data['trip_id']])->fetch();
    
    if (!$currentTrip) {
        throw new Exception('Trip not found');
    }
    
    if ($currentTrip['status'] !== 'pending') {
        throw new Exception('Only pending trips can be edited');
    }
    
    // Build update query
    $updates = [];
    $params = ['trip_id' => $data['trip_id']];
    
    if (isset($data['vehicle_id']) && $data['vehicle_id'] !== $currentTrip['vehicle_id']) {
        // Check if new vehicle is available
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
        
        $updates[] = "vehicle_id = :vehicle_id";
        $params['vehicle_id'] = $data['vehicle_id'];
    }
    
    if (isset($data['driver_id']) && $data['driver_id'] !== $currentTrip['driver_id']) {
        // Check if new driver is available
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
        
        $updates[] = "driver_id = :driver_id";
        $params['driver_id'] = $data['driver_id'];
    }
    
    if (isset($data['start_location'])) {
        $updates[] = "start_location = ST_GeomFromText(:start_location)";
        $params['start_location'] = $data['start_location'];
    }
    
    if (isset($data['end_location'])) {
        $updates[] = "end_location = ST_GeomFromText(:end_location)";
        $params['end_location'] = $data['end_location'];
    }
    
    if (isset($data['start_time'])) {
        $updates[] = "start_time = :start_time";
        $params['start_time'] = $data['start_time'];
    }
    
    if (isset($data['notes'])) {
        $updates[] = "notes = :notes";
        $params['notes'] = $data['notes'];
    }
    
    if (isset($data['status'])) {
        $updates[] = "status = :status";
        $params['status'] = $data['status'];
        
        // Add to trip history
        $db->query("
            INSERT INTO trip_history (
                trip_id,
                status,
                notes,
                updated_by
            ) VALUES (
                :trip_id,
                :status,
                :notes,
                :updated_by
            )
        ", [
            'trip_id' => $data['trip_id'],
            'status' => $data['status'],
            'notes' => $data['status_notes'] ?? null,
            'updated_by' => $auth->getCurrentUser()['id']
        ]);
        
        // Send notifications based on status change
        switch ($data['status']) {
            case 'in_progress':
                $notifications->createForUser(
                    $currentTrip['driver_id'],
                    'Trip Started',
                    "Your trip with vehicle {$currentTrip['registration_number']} has started.",
                    'info'
                );
                break;
            
            case 'delayed':
                $notifications->createForRole(
                    'admin',
                    'Trip Delayed',
                    "Trip for vehicle {$currentTrip['registration_number']} has been delayed.",
                    'warning'
                );
                break;
            
            case 'cancelled':
                $notifications->createForUser(
                    $currentTrip['driver_id'],
                    'Trip Cancelled',
                    "Your trip with vehicle {$currentTrip['registration_number']} has been cancelled.",
                    'warning'
                );
                break;
        }
    }
    
    if (!empty($updates)) {
        $query = "UPDATE trips SET " . implode(', ', $updates) . " WHERE id = :trip_id";
        $db->query($query, $params);
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
    echo json_encode(['error' => $e->getMessage()]);
} 
<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

// Check if user is authorized
$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'logistics') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get and validate input
$vehicleId = $_POST['vehicle'] ?? null;
$driverId = $_POST['driver'] ?? null;
$destination = $_POST['destination'] ?? null;

if (!$vehicleId || !$driverId || !$destination) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$db = Database::getInstance();

try {
    // Start transaction
    $db->getConnection()->beginTransaction();

    // Check if vehicle is available
    $vehicleCheck = $db->query("
        SELECT v.*, vl.status as current_status
        FROM vehicles v
        LEFT JOIN vehicle_logs vl ON v.id = vl.vehicle_id 
            AND vl.status = 'in_progress'
        WHERE v.id = :vehicle_id
    ", ['vehicle_id' => $vehicleId])->fetch();

    if (!$vehicleCheck || $vehicleCheck['current_status'] !== 'available') {
        throw new Exception('Vehicle is not available for assignment');
    }

    // Check if driver is available
    $driverCheck = $db->query("
        SELECT id
        FROM users
        WHERE id = :driver_id
        AND role = 'driver'
        AND id NOT IN (
            SELECT driver_id 
            FROM vehicle_logs 
            WHERE status = 'in_progress'
        )
    ", ['driver_id' => $driverId])->fetch();

    if (!$driverCheck) {
        throw new Exception('Driver is not available for assignment');
    }

    // Create vehicle log entry
    $db->query("
        INSERT INTO vehicle_logs (
            vehicle_id, 
            driver_id, 
            trip_start,
            start_location,
            status
        ) VALUES (
            :vehicle_id,
            :driver_id,
            CURRENT_TIMESTAMP,
            :destination,
            'in_progress'
        )
    ", [
        'vehicle_id' => $vehicleId,
        'driver_id' => $driverId,
        'destination' => $destination
    ]);

    // Create notification for driver
    $db->query("
        INSERT INTO notifications (
            user_id,
            title,
            message,
            type
        ) VALUES (
            :driver_id,
            'New Vehicle Assignment',
            :message,
            'assignment'
        )
    ", [
        'driver_id' => $driverId,
        'message' => "You have been assigned to vehicle #{$vehicleCheck['registration_number']}. Destination: {$destination}"
    ]);

    // Commit transaction
    $db->getConnection()->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Vehicle assigned successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $db->getConnection()->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} 
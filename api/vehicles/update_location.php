<?php
require_once '../../config/database.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

header('Content-Type: application/json');

// Check if user is a logistics officer
$auth = new Auth();
if (!$auth->isLoggedIn() || !in_array($auth->getCurrentUser()['role'], ['logistics'])) {
    sendError('Unauthorized access');
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['vehicle_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
    sendError('Missing required fields');
        exit;
    }

$vehicle_id = $data['vehicle_id'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];
$notes = $data['notes'] ?? '';
$updated_by = $auth->getCurrentUser()['id'];

try {
    $pdo = getConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update vehicle location
    $stmt = $pdo->prepare("
        UPDATE vehicles
        SET latitude = :latitude,
            longitude = :longitude,
            location_updated_at = CURRENT_TIMESTAMP,
            last_updated_by = :updated_by
        WHERE id = :vehicle_id
    ");
    
    $stmt->execute([
        ':vehicle_id' => $vehicle_id,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':updated_by' => $updated_by
    ]);
    
    // Log location update
    $stmt = $pdo->prepare("
        INSERT INTO vehicle_location_history 
        (vehicle_id, latitude, longitude, notes, updated_by, updated_at)
        VALUES (:vehicle_id, :latitude, :longitude, :notes, :updated_by, CURRENT_TIMESTAMP)
    ");
    
    $stmt->execute([
        ':vehicle_id' => $vehicle_id,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':notes' => $notes,
        ':updated_by' => $updated_by
        ]);
    
    // Commit transaction
    $pdo->commit();
    
    sendSuccess('Location updated successfully');
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    sendError('Failed to update location: ' . $e->getMessage());
} 
<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../bootstrap.php';

// Check authentication
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Fetch vehicle locations with driver information
    $query = "
        SELECT 
            v.id,
            v.registration_number,
            v.latitude,
            v.longitude,
            v.status,
            v.last_updated,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name
        FROM vehicles v
        LEFT JOIN drivers d ON v.current_driver_id = d.id
        WHERE v.latitude IS NOT NULL 
        AND v.longitude IS NOT NULL
        AND v.status != 'inactive'
        ORDER BY v.last_updated DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($locations);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch vehicle locations']);
} 
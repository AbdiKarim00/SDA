<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn() || !in_array($auth->getCurrentUser()['role'], ['admin', 'logistics'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get vehicle ID from query string
$vehicleId = $_GET['id'] ?? null;
if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(['error' => 'Vehicle ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get vehicle details
    $vehicle = $db->query("
        SELECT 
            v.*,
            ST_AsText(v.current_location) as current_location_text
        FROM vehicles v
        WHERE v.id = :id
    ", ['id' => $vehicleId])->fetch();
    
    if (!$vehicle) {
        http_response_code(404);
        echo json_encode(['error' => 'Vehicle not found']);
        exit;
    }
    
    // Format location if available
    if ($vehicle['current_location_text']) {
        $vehicle['current_location'] = $vehicle['current_location_text'];
    }
    unset($vehicle['current_location_text']);
    
    echo json_encode([
        'success' => true,
        'vehicle' => $vehicle
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get vehicle details']);
} 
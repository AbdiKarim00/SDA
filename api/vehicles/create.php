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

// Validate required fields
$requiredFields = ['registration_number', 'make', 'model', 'year', 'capacity', 'status'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
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
    
    // Check if registration number already exists
    $exists = $db->query("
        SELECT id FROM vehicles WHERE registration_number = :reg
    ", ['reg' => $_POST['registration_number']])->fetch();
    
    if ($exists) {
        http_response_code(400);
        echo json_encode(['error' => 'Registration number already exists']);
        exit;
    }
    
    // Create vehicle
    $vehicleId = $db->query("
        INSERT INTO vehicles (
            registration_number, make, model, year,
            capacity, status, created_by
        ) VALUES (
            :registration_number, :make, :model, :year,
            :capacity, :status, :created_by
        ) RETURNING id
    ", [
        'registration_number' => $_POST['registration_number'],
        'make' => $_POST['make'],
        'model' => $_POST['model'],
        'year' => $_POST['year'],
        'capacity' => $_POST['capacity'],
        'status' => $_POST['status'],
        'created_by' => $auth->getCurrentUser()['id']
    ])->fetch()['id'];
    
    // Send notification to admin
    $notifications->createForRole(
        'admin',
        'New Vehicle Added',
        "A new vehicle ({$_POST['registration_number']}) has been added to the fleet.",
        'info'
    );
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'vehicle_id' => $vehicleId
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create vehicle']);
} 
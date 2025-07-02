<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/notifications.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn() || !in_array($auth->getCurrentUser()['role'], ['admin', 'logistics'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['vehicle_id', 'type', 'scheduled_date', 'priority', 'description'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();
    
    // Check if vehicle exists and is active
    $vehicle = $db->query("
        SELECT id, registration_number, status
        FROM vehicles
        WHERE id = :id
    ", ['id' => $data['vehicle_id']])->fetch();
    
    if (!$vehicle) {
        throw new Exception('Vehicle not found');
    }
    
    if ($vehicle['status'] !== 'active') {
        throw new Exception('Vehicle is not active');
    }
    
    // Create maintenance task
    $task_id = $db->query("
        INSERT INTO maintenance_tasks (
            vehicle_id,
            type,
            description,
            scheduled_date,
            priority,
            estimated_cost,
            status,
            created_by,
            created_at
        ) VALUES (
            :vehicle_id,
            :type,
            :description,
            :scheduled_date,
            :priority,
            :estimated_cost,
            'pending',
            :created_by,
            CURRENT_TIMESTAMP
        ) RETURNING id
    ", [
        'vehicle_id' => $data['vehicle_id'],
        'type' => $data['type'],
        'description' => $data['description'],
        'scheduled_date' => $data['scheduled_date'],
        'priority' => $data['priority'],
        'estimated_cost' => $data['estimated_cost'] ?? null,
        'created_by' => $auth->getCurrentUser()['id']
    ])->fetch()['id'];
    
    // Add to maintenance history
    $db->query("
        INSERT INTO maintenance_history (
            maintenance_task_id,
            vehicle_id,
            action,
            description,
            created_by,
            created_at
        ) VALUES (
            :task_id,
            :vehicle_id,
            'created',
            :description,
            :created_by,
            CURRENT_TIMESTAMP
        )
    ", [
        'task_id' => $task_id,
        'vehicle_id' => $data['vehicle_id'],
        'description' => "Maintenance task created: {$data['type']} - {$data['description']}",
        'created_by' => $auth->getCurrentUser()['id']
    ]);
    
    // Send notifications
    $notifications = new Notifications();
    
    // Notify admin
    $notifications->sendNotification(
        'admin',
        'New Maintenance Task',
        "New maintenance task scheduled for vehicle {$vehicle['registration_number']}",
        "/logistics/maintenance_details.php?id=$task_id"
    );
    
    // Notify logistics
    $notifications->sendNotification(
        'logistics',
        'New Maintenance Task',
        "New maintenance task scheduled for vehicle {$vehicle['registration_number']}",
        "/logistics/maintenance_details.php?id=$task_id"
    );
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Maintenance task created successfully',
        'task_id' => $task_id
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 
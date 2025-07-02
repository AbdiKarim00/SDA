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

if (empty($data['task_id']) || empty($data['technician_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();
    
    // Get task details
    $task = $db->query("
        SELECT mt.*, v.registration_number
        FROM maintenance_tasks mt
        JOIN vehicles v ON mt.vehicle_id = v.id
        WHERE mt.id = :id
    ", ['id' => $data['task_id']])->fetch();
    
    if (!$task) {
        throw new Exception('Maintenance task not found');
    }
    
    if ($task['status'] !== 'pending') {
        throw new Exception('Can only assign technicians to pending tasks');
    }
    
    // Check if technician exists and has the correct role
    $technician = $db->query("
        SELECT id, email
        FROM users
        WHERE id = :id AND role = 'technician'
    ", ['id' => $data['technician_id']])->fetch();
    
    if (!$technician) {
        throw new Exception('Invalid technician');
    }
    
    // Update task
    $db->query("
        UPDATE maintenance_tasks
        SET 
            assigned_to = :technician_id,
            status = 'in_progress',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :task_id
    ", [
        'technician_id' => $data['technician_id'],
        'task_id' => $data['task_id']
    ]);
    
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
            'updated',
            :description,
            :created_by,
            CURRENT_TIMESTAMP
        )
    ", [
        'task_id' => $task['id'],
        'vehicle_id' => $task['vehicle_id'],
        'description' => "Task assigned to technician {$technician['email']}",
        'created_by' => $auth->getCurrentUser()['id']
    ]);
    
    // Send notifications
    $notifications = new Notifications();
    
    // Notify technician
    $notifications->sendNotification(
        'technician',
        'New Maintenance Assignment',
        "You have been assigned to a maintenance task for vehicle {$task['registration_number']}",
        "/technician/maintenance_details.php?id={$task['id']}"
    );
    
    // Notify admin
    $notifications->sendNotification(
        'admin',
        'Maintenance Task Assigned',
        "Maintenance task for vehicle {$task['registration_number']} has been assigned to {$technician['email']}",
        "/logistics/maintenance_details.php?id={$task['id']}"
    );
    
    // Notify logistics
    $notifications->sendNotification(
        'logistics',
        'Maintenance Task Assigned',
        "Maintenance task for vehicle {$task['registration_number']} has been assigned to {$technician['email']}",
        "/logistics/maintenance_details.php?id={$task['id']}"
    );
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Technician assigned successfully'
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
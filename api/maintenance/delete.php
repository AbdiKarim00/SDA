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

if (empty($data['task_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing task ID']);
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
        throw new Exception('Only pending tasks can be deleted');
    }
    
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
            'deleted',
            :description,
            :created_by,
            CURRENT_TIMESTAMP
        )
    ", [
        'task_id' => $task['id'],
        'vehicle_id' => $task['vehicle_id'],
        'description' => "Maintenance task deleted: {$task['type']} - {$task['description']}",
        'created_by' => $auth->getCurrentUser()['id']
    ]);
    
    // Delete the task
    $db->query("
        DELETE FROM maintenance_tasks
        WHERE id = :id
    ", ['id' => $task['id']]);
    
    // Send notifications
    $notifications = new Notifications();
    
    // Notify admin
    $notifications->sendNotification(
        'admin',
        'Maintenance Task Deleted',
        "Maintenance task for vehicle {$task['registration_number']} has been deleted",
        "/logistics/maintenance.php"
    );
    
    // Notify logistics
    $notifications->sendNotification(
        'logistics',
        'Maintenance Task Deleted',
        "Maintenance task for vehicle {$task['registration_number']} has been deleted",
        "/logistics/maintenance.php"
    );
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Maintenance task deleted successfully'
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
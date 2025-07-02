<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$vehicleId = $_POST['vehicle_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$startDate || !$endDate) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing date range']);
    exit();
}

$db = Database::getInstance();

try {
    $params = [
        'start_date' => $startDate,
        'end_date' => $endDate
    ];
    
    $whereClauses = ["maintenance_date >= :start_date", "maintenance_date <= :end_date"];
    
    if ($vehicleId) {
        $whereClauses[] = "mr.vehicle_id = :vehicle_id";
        $params['vehicle_id'] = $vehicleId;
    }
    
    if ($status) {
        $whereClauses[] = "mr.status = :status";
        $params['status'] = $status;
    }
    
    $whereClause = implode(" AND ", $whereClauses);
    
    $results = $db->query("
        SELECT 
            v.registration_number,
            v.make,
            v.model,
            mr.maintenance_type,
            mr.maintenance_date,
            mr.vendor,
            mr.cost,
            mr.description,
            mr.next_maintenance_date,
            mr.status
        FROM maintenance_records mr
        JOIN vehicles v ON v.id = mr.vehicle_id
        WHERE {$whereClause}
        ORDER BY mr.maintenance_date DESC
    ", $params)->fetchAll();
    
    // Format dates and costs
    $formattedResults = array_map(function($row) {
        return [
            'registration_number' => $row['registration_number'],
            'vehicle' => $row['make'] . ' ' . $row['model'],
            'maintenance_type' => $row['maintenance_type'],
            'maintenance_date' => date('Y-m-d', strtotime($row['maintenance_date'])),
            'vendor' => $row['vendor'] ?? 'N/A',
            'cost' => $row['cost'] ? number_format($row['cost'], 2) : 'N/A',
            'description' => $row['description'] ?? 'N/A',
            'next_maintenance_date' => $row['next_maintenance_date'] ? date('Y-m-d', strtotime($row['next_maintenance_date'])) : 'N/A',
            'status' => $row['status'] ?? 'completed'
        ];
    }, $results);
    
    echo json_encode([
        'success' => true,
        'results' => $formattedResults
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate maintenance report'
    ]);
} 
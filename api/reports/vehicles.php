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
    
    $whereClauses = ["v.created_at >= :start_date", "v.created_at <= :end_date"];
    
    if ($vehicleId) {
        $whereClauses[] = "v.id = :vehicle_id";
        $params['vehicle_id'] = $vehicleId;
    }
    
    $whereClause = implode(" AND ", $whereClauses);
    
    // Get vehicle details with trip and maintenance statistics
    $results = $db->query("
        WITH vehicle_stats AS (
            SELECT 
                v.id,
                COUNT(DISTINCT vl.id) as total_trips,
                COUNT(DISTINCT CASE WHEN vl.status = 'completed' THEN vl.id END) as completed_trips,
                COUNT(DISTINCT mr.id) as maintenance_count,
                SUM(mr.cost) as total_maintenance_cost
            FROM vehicles v
            LEFT JOIN vehicle_logs vl ON v.id = vl.vehicle_id
            LEFT JOIN maintenance_records mr ON v.id = mr.vehicle_id
            WHERE {$whereClause}
            GROUP BY v.id
        )
        SELECT 
            v.registration_number,
            v.make,
            v.model,
            v.status,
            v.current_location,
            vs.total_trips,
            vs.completed_trips,
            vs.maintenance_count,
            vs.total_maintenance_cost,
            (
                SELECT COUNT(*) 
                FROM maintenance_records mr 
                WHERE mr.vehicle_id = v.id 
                AND mr.next_maintenance_date <= CURRENT_DATE
            ) as overdue_maintenance
        FROM vehicles v
        JOIN vehicle_stats vs ON v.id = vs.id
        ORDER BY v.registration_number
    ", $params)->fetchAll();
    
    // Format the results
    $formattedResults = array_map(function($row) {
        return [
            'registration_number' => $row['registration_number'],
            'vehicle' => $row['make'] . ' ' . $row['model'],
            'status' => $row['status'],
            'current_location' => $row['current_location'] ?? 'N/A',
            'total_trips' => $row['total_trips'],
            'completed_trips' => $row['completed_trips'],
            'maintenance_count' => $row['maintenance_count'],
            'total_maintenance_cost' => $row['total_maintenance_cost'] ? number_format($row['total_maintenance_cost'], 2) : '0.00',
            'overdue_maintenance' => $row['overdue_maintenance']
        ];
    }, $results);
    
    echo json_encode([
        'success' => true,
        'results' => $formattedResults
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate vehicle report'
    ]);
} 
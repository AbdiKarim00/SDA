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
$driverId = $_POST['driver_id'] ?? null;
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
    
    $whereClauses = ["trip_start >= :start_date", "trip_start <= :end_date"];
    
    if ($vehicleId) {
        $whereClauses[] = "vl.vehicle_id = :vehicle_id";
        $params['vehicle_id'] = $vehicleId;
    }
    
    if ($driverId) {
        $whereClauses[] = "vl.driver_id = :driver_id";
        $params['driver_id'] = $driverId;
    }
    
    if ($status) {
        $whereClauses[] = "vl.status = :status";
        $params['status'] = $status;
    }
    
    $whereClause = implode(" AND ", $whereClauses);
    
    $results = $db->query("
        SELECT 
            v.registration_number,
            v.make,
            v.model,
            u.email as driver,
            vl.start_location,
            vl.end_location,
            vl.status,
            vl.trip_start,
            vl.trip_end,
            EXTRACT(EPOCH FROM (vl.trip_end - vl.trip_start))/3600 as duration_hours
        FROM vehicle_logs vl
        JOIN vehicles v ON v.id = vl.vehicle_id
        JOIN users u ON u.id = vl.driver_id
        WHERE {$whereClause}
        ORDER BY vl.trip_start DESC
    ", $params)->fetchAll();
    
    // Format dates and durations
    $formattedResults = array_map(function($row) {
        return [
            'registration_number' => $row['registration_number'],
            'vehicle' => $row['make'] . ' ' . $row['model'],
            'driver' => $row['driver'],
            'start_location' => $row['start_location'],
            'end_location' => $row['end_location'],
            'status' => $row['status'],
            'trip_start' => date('Y-m-d H:i', strtotime($row['trip_start'])),
            'trip_end' => $row['trip_end'] ? date('Y-m-d H:i', strtotime($row['trip_end'])) : 'N/A',
            'duration_hours' => round($row['duration_hours'], 2)
        ];
    }, $results);
    
    echo json_encode([
        'success' => true,
        'results' => $formattedResults
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate trip report'
    ]);
} 
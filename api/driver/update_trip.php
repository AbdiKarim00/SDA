<?php
require_once '../../logistics/mock_data.php';

header('Content-Type: application/json');

// Get mock data
$trips = get_mock_data('trips');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$tripId = $_POST['trip_id'] ?? null;
$currentLocation = trim($_POST['current_location'] ?? '');
$status = $_POST['status'] ?? 'In Progress';

if (!$tripId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing trip ID']);
    exit();
}

try {
    // Find the trip
    $trip = array_filter($trips, fn($t) => $t['id'] == $tripId);
    $trip = reset($trip);

    if (!$trip) {
        throw new Exception('Trip not found.');
    }

    if ($trip['status'] !== 'In Progress') {
        throw new Exception('Trip is not in progress.');
    }

    // Update trip status and location
    if ($currentLocation !== '') {
        $trip['end_location'] = $currentLocation;
    }
    if ($status === 'Completed') {
        $trip['status'] = 'Completed';
        $trip['end_date'] = date('Y-m-d H:i:s');
    } elseif ($status === 'Issue') {
        $trip['status'] = 'Issue';
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} 
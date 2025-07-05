<?php
// require_once '../../logistics/mock_data.php'; // Mock data system removed

header('Content-Type: application/json');

// This API endpoint is deprecated.
// Modern clients should use the Laravel API endpoints for trip updates,
// e.g., POST /api/v1/trips/{id}/complete or POST /api/v1/trips/{id}/update-location

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. This endpoint is deprecated.']);
    exit();
}

// $tripId = $_POST['trip_id'] ?? null;
// $currentLocation = trim($_POST['current_location'] ?? '');
// $status = $_POST['status'] ?? 'In Progress';

// if (!$tripId) {
//     http_response_code(400);
//     echo json_encode(['error' => 'Missing trip ID. This endpoint is deprecated.']);
//     exit();
// }

// Since the mock data system is removed and this endpoint is deprecated,
// returning an error message indicating deprecation.
http_response_code(410); // Gone
echo json_encode([
    'success' => false,
    'error' => 'This API endpoint (api/driver/update_trip.php) is deprecated and no longer functional. Please use the new Laravel API endpoints.'
]);
exit();

/*
// Original logic (now non-functional without mock data):
try {
    // Find the trip
    // $trip = array_filter($trips, fn($t) => $t['id'] == $tripId);
    // $trip = reset($trip);

    // if (!$trip) {
    //     throw new Exception('Trip not found.');
    // }

    // if ($trip['status'] !== 'In Progress') {
    //     throw new Exception('Trip is not in progress.');
    // }

    // Update trip status and location
    // if ($currentLocation !== '') {
    //     $trip['end_location'] = $currentLocation;
    // }
    // if ($status === 'Completed') {
    //     $trip['status'] = 'Completed';
    //     $trip['end_date'] = date('Y-m-d H:i:s');
    // } elseif ($status === 'Issue') {
    //     $trip['status'] = 'Issue';
    // }

    // echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
*/
?>
<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../logistics/mock_data.php';

// Get mock data
$drivers = get_mock_data('drivers');
$trips = get_mock_data('trips');
$vehicles = get_mock_data('vehicles');

// Mock current driver (in real app, this would come from session)
$current_driver = $drivers[0];

// Get current trip
$current_trip = array_filter($trips, fn($t) => 
    $t['driver_id'] === $current_driver['id'] && 
    $t['status'] === 'In Progress'
);
$current_trip = reset($current_trip);

// Get recent trips
$recent_trips = array_filter($trips, fn($t) => 
    $t['driver_id'] === $current_driver['id'] && 
    $t['status'] !== 'In Progress'
);
usort($recent_trips, function($a, $b) {
    return strtotime($b['start_date']) - strtotime($a['start_date']);
});
$recent_trips = array_slice($recent_trips, 0, 5);

// Get assigned vehicle
$vehicle = null;
if ($current_trip) {
    $vehicle = array_filter($vehicles, fn($v) => $v['id'] === $current_trip['vehicle_id']);
    $vehicle = reset($vehicle);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - SDATIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .mobile-nav { 
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            z-index: 50;
        }
    </style>
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../components/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Driver Dashboard</h1>
                </div>
                
                <?php if ($current_trip): ?>
                <!-- Current Trip -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Current Trip</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <th>Vehicle</th>
                                        <td>
                                            <?php 
                                            if ($vehicle) {
                                                echo htmlspecialchars($vehicle['registration_number'] . ' (' . $vehicle['make'] . ' ' . $vehicle['model'] . ')');
                                            } else {
                                                echo 'No vehicle assigned';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Start Location</th>
                                        <td><?php echo htmlspecialchars($current_trip['start_location'] ?? 'Not set'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>End Location</th>
                                        <td><?php echo htmlspecialchars($current_trip['end_location'] ?? 'Not set'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Start Time</th>
                                        <td><?php echo date('M d, Y H:i', strtotime($current_trip['start_date'] ?? 'now')); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div id="tripMap" style="height: 300px;"></div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-success" onclick="completeTrip(<?php echo $current_trip['id']; ?>)">
                                Complete Trip
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Vehicle Information -->
                <?php if ($vehicle): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Vehicle Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <th>Registration Number</th>
                                        <td><?php echo htmlspecialchars($vehicle['registration_number']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Make & Model</th>
                                        <td><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($vehicle['status']) {
                                                    'active' => 'success',
                                                    'maintenance' => 'warning',
                                                    'inactive' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($vehicle['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Last Maintenance</th>
                                        <td>
                                            <?php 
                                            echo isset($vehicle['last_maintenance_date']) && $vehicle['last_maintenance_date']
                                                ? date('M d, Y', strtotime($vehicle['last_maintenance_date']))
                                                : 'No maintenance records';
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <?php if (isset($vehicle['current_location'])): ?>
                            <div class="col-md-6">
                                <h6>Current Location</h6>
                                <div id="vehicleMap" style="height: 200px;"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Trips -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Trips</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_trips)): ?>
                            <p class="text-muted">No recent trips found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Start Location</th>
                                            <th>End Location</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_trips as $trip): ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                    $trip_vehicle = array_filter($vehicles, fn($v) => $v['id'] === $trip['vehicle_id']);
                                                    $trip_vehicle = reset($trip_vehicle);
                                                    echo $trip_vehicle ? htmlspecialchars($trip_vehicle['registration_number']) : 'N/A';
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($trip['start_location'] ?? 'Not set'); ?></td>
                                                <td><?php echo htmlspecialchars($trip['end_location'] ?? 'Not set'); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($trip['start_date'] ?? 'now')); ?></td>
                                                <td>
                                                    <?php 
                                                    echo isset($trip['end_date']) && $trip['end_date']
                                                        ? date('M d, Y H:i', strtotime($trip['end_date']))
                                                        : '-';
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($trip['status']) {
                                                            'Completed' => 'success',
                                                            'Cancelled' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($trip['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
    <?php if ($current_trip && isset($current_trip['start_location']) && isset($current_trip['end_location'])): ?>
    // Initialize trip map
    const tripMap = L.map('tripMap').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(tripMap);
    
    // Add start and end markers
    const startCoords = <?php echo json_encode($current_trip['start_location']); ?>;
    const endCoords = <?php echo json_encode($current_trip['end_location']); ?>;
    
    L.marker([startCoords[1], startCoords[0]])
        .addTo(tripMap)
        .bindPopup('Start Location')
        .openPopup();
    
    L.marker([endCoords[1], endCoords[0]])
        .addTo(tripMap)
        .bindPopup('End Location');
    
    // Fit map to show both markers
    const bounds = L.latLngBounds([
        [startCoords[1], startCoords[0]],
        [endCoords[1], endCoords[0]]
    ]);
    tripMap.fitBounds(bounds);
    <?php endif; ?>
    
    <?php if ($vehicle && isset($vehicle['current_location'])): ?>
    // Initialize vehicle map
    const vehicleMap = L.map('vehicleMap').setView([0, 0], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(vehicleMap);
    
    // Add vehicle marker
    const vehicleCoords = <?php echo json_encode($vehicle['current_location']); ?>;
    L.marker([vehicleCoords[1], vehicleCoords[0]])
        .addTo(vehicleMap)
        .bindPopup('<?php echo htmlspecialchars($vehicle['registration_number']); ?>')
        .openPopup();
    <?php endif; ?>
    
    async function completeTrip(tripId) {
        if (confirm('Are you sure you want to complete this trip?')) {
            try {
                const response = await fetch('/api/driver/update_trip.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        trip_id: tripId,
                        status: 'Completed'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    throw new Error(result.error || 'Failed to complete trip');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to complete trip');
            }
        }
    }
    </script>
</body>
</html> 
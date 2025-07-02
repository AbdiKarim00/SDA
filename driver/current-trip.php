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

// Get current trip for this driver
$trip = array_filter($trips, fn($t) => 
    $t['driver_id'] === $current_driver['id'] && 
    $t['status'] === 'In Progress'
);
$trip = reset($trip);

// Get vehicle details if trip exists
$vehicle = null;
if ($trip) {
    $vehicle = array_filter($vehicles, fn($v) => $v['id'] === $trip['vehicle_id']);
    $vehicle = reset($vehicle);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Trip - Driver Portal</title>
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
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="#">SDATIMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="current-trip.php">Current Trip</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tools.php">Tools</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Driver Dashboard</h1>

        <?php if ($trip): ?>
            <div class="dashboard-card mb-4">
                <h3>Current Trip</h3>
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($vehicle['registration_number'] . ' (' . $vehicle['make'] . ' ' . $vehicle['model'] . ')'); ?></p>
                <p><strong>Start Location:</strong> <?php echo htmlspecialchars($trip['start_location']); ?></p>
                <p><strong>Destination:</strong> <?php echo htmlspecialchars($trip['end_location'] ?? 'Not set'); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($trip['status']); ?></span></p>
                <form id="updateTripForm" class="row g-3 mt-3">
                    <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                    <div class="col-md-6">
                        <label for="current_location" class="form-label">Update Current Location</label>
                        <input type="text" class="form-control" id="current_location" name="current_location" placeholder="e.g. Rahim Auto Garage">
                        <button type="button" class="btn btn-link p-0 mt-1" onclick="getLocation()">Use My Location</button>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Trip Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="in_progress" <?php if($trip['status']==='in_progress') echo 'selected'; ?>>In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="issue">Report Issue</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update Trip</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-info">You have no active trips assigned.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Geolocation helper
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('current_location').value = `Lat: ${position.coords.latitude}, Lng: ${position.coords.longitude}`;
                }, function() {
                    alert('Unable to retrieve your location.');
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }

        // Handle trip update form
        const form = document.getElementById('updateTripForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                try {
                    const response = await fetch('/api/driver/update_trip.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert('Trip updated successfully!');
                        location.reload();
                    } else {
                        alert(result.error || 'Failed to update trip.');
                    }
                } catch (err) {
                    alert('An error occurred.');
                }
            });
        }
    </script>
</body>
</html> 
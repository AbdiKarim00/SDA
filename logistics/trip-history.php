<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// require_once 'mock_data.php'; // Mock data system removed

// Get mock data
// $trips = get_mock_data('trips'); // Mock data system removed
$trips = []; // Placeholder
// $vehicles = get_mock_data('vehicles'); // Mock data system removed
$vehicles = []; // Placeholder
// $drivers = get_mock_data('drivers'); // Mock data system removed
$drivers = []; // Placeholder

// Handle trip creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_trip') {
    $newTrip = [
        'id' => count($trips) + 1,
        'vehicle_id' => $_POST['vehicle_id'],
        'vehicle_registration' => $vehicles[array_search($_POST['vehicle_id'], array_column($vehicles, 'id'))]['registration_number'],
        'driver_id' => $_POST['driver_id'],
        'driver_name' => $drivers[array_search($_POST['driver_id'], array_column($drivers, 'id'))]['name'],
        'start_date' => $_POST['start_time'],
        'end_date' => null,
        'origin' => $_POST['start_location'],
        'destination' => $_POST['end_location'],
        'purpose' => $_POST['notes'] ?? 'Regular Trip',
        'status' => 'Scheduled',
        'mileage_start' => null,
        'mileage_end' => null,
        'fuel_used' => null,
        'notes' => $_POST['notes'] ?? ''
    ];
    
    // Add to mock data
    $trips[] = $newTrip;
    
    // Redirect to prevent form resubmission
    header('Location: trip-history.php?success=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip History - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('trip-history'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Trip History',
                'View and manage trip history'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Trip List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Trip Records</h3>
                            <button type="button" 
                                    class="btn btn-primary btn-sm"
                                    onclick="document.getElementById('createTripModal').classList.remove('hidden')">
                                <i class="bi bi-plus-lg"></i> New Trip
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origin</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mileage</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($trips as $trip): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $trip['vehicle_registration']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $trip['driver_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($trip['start_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $trip['end_date'] ? date('M d, Y', strtotime($trip['end_date'])) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $trip['origin']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $trip['destination']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php echo match($trip['status']) {
                                                    'Completed' => 'bg-green-100 text-green-800',
                                                    'In Progress' => 'bg-blue-100 text-blue-800',
                                                    'Scheduled' => 'bg-yellow-100 text-yellow-800',
                                                    'Cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                }; ?>">
                                                <?php echo $trip['status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                                if ($trip['mileage_start'] && $trip['mileage_end']) {
                                                    echo number_format($trip['mileage_end'] - $trip['mileage_start']);
                                                } else {
                                                    echo '-';
                                                }
                                            ?> km
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Trip Modal -->
    <div id="createTripModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" x-data="{ show: false }" x-show="show" @click.self="show = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Create New Trip</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500" @click="show = false">
                            <i class="bi bi-x text-xl"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="create_trip">
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                            <select name="vehicle_id" class="form-select w-full" required>
                                <option value="">Select Vehicle</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?php echo $vehicle['id']; ?>">
                                        <?php echo htmlspecialchars($vehicle['registration_number'] . ' (' . $vehicle['make'] . ' ' . $vehicle['model'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Driver</label>
                            <select name="driver_id" class="form-select w-full" required>
                                <option value="">Select Driver</option>
                                <?php foreach ($drivers as $driver): ?>
                                    <option value="<?php echo $driver['id']; ?>">
                                        <?php echo htmlspecialchars($driver['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Location</label>
                            <input type="text" name="start_location" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Location</label>
                            <input type="text" name="end_location" class="form-input w-full" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-input w-full" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" class="form-input w-full" rows="3"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn btn-secondary" @click="show = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Trip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show modal when clicking New Trip button
        document.querySelector('[onclick*="createTripModal"]').addEventListener('click', function() {
            document.querySelector('#createTripModal').__x.$data.show = true;
        });
    </script>
</body>
</html> 
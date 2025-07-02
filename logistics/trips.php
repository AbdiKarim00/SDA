<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once 'mock_data.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !in_array($auth->getCurrentUser()['role'], ['admin', 'logistics'])) {
    header('Location: /auth/login.php');
    exit;
}

$db = Database::getInstance();

// Get filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$vehicle_id = $_GET['vehicle_id'] ?? '';
$driver_id = $_GET['driver_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$query = "
    SELECT 
        t.*,
        v.registration_number,
        v.make,
        v.model,
        u.email as driver_email,
        ST_AsText(t.start_location) as start_location_text,
        ST_AsText(t.end_location) as end_location_text
    FROM trips t
    JOIN vehicles v ON t.vehicle_id = v.id
    JOIN users u ON t.driver_id = u.id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (
        v.registration_number ILIKE :search OR
        u.email ILIKE :search OR
        ST_AsText(t.start_location) ILIKE :search OR
        ST_AsText(t.end_location) ILIKE :search
    )";
    $params['search'] = "%$search%";
}

if ($status) {
    $query .= " AND t.status = :status";
    $params['status'] = $status;
}

if ($vehicle_id) {
    $query .= " AND t.vehicle_id = :vehicle_id";
    $params['vehicle_id'] = $vehicle_id;
}

if ($driver_id) {
    $query .= " AND t.driver_id = :driver_id";
    $params['driver_id'] = $driver_id;
}

if ($date_from) {
    $query .= " AND t.start_time >= :date_from";
    $params['date_from'] = $date_from;
}

if ($date_to) {
    $query .= " AND t.start_time <= :date_to";
    $params['date_to'] = $date_to;
}

$query .= " ORDER BY t.start_time DESC";

$trips = $db->query($query, $params)->fetchAll();

// Get vehicles and drivers for filters
$vehicles = $db->query("
    SELECT id, registration_number, make, model
    FROM vehicles
    WHERE status = 'active'
    ORDER BY registration_number
")->fetchAll();

$drivers = $db->query("
    SELECT id, email
    FROM users
    WHERE role = 'driver'
    ORDER BY email
")->fetchAll();

// Get mock data
$mock_trips = get_mock_data('trips');
$mock_vehicles = get_mock_data('vehicles');
$mock_drivers = get_mock_data('drivers');

// Handle trip creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_trip') {
    $newTrip = [
        'id' => count($mock_trips) + 1,
        'vehicle_id' => $_POST['vehicle_id'],
        'vehicle_registration' => $mock_vehicles[array_search($_POST['vehicle_id'], array_column($mock_vehicles, 'id'))]['registration_number'],
        'driver_id' => $_POST['driver_id'],
        'driver_name' => $mock_drivers[array_search($_POST['driver_id'], array_column($mock_drivers, 'id'))]['name'],
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
    $mock_trips[] = $newTrip;
    
    // Redirect to prevent form resubmission
    header('Location: trips.php?success=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Management - Transport IMS</title>
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
        <?php DashboardSidebar::render('trips'); ?>
    
        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Trip Management',
                'Create and manage vehicle trips'
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
                                <?php foreach ($mock_trips as $trip): ?>
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
                                <?php foreach ($mock_vehicles as $vehicle): ?>
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
                                <?php foreach ($mock_drivers as $driver): ?>
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
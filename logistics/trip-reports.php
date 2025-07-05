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

// Get date range
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Calculate trip statistics
$stats = [
    'total_trips' => count($trips),
    'completed_trips' => count(array_filter($trips, fn($trip) => $trip['status'] === 'Completed')),
    'cancelled_trips' => count(array_filter($trips, fn($trip) => $trip['status'] === 'Cancelled')),
    'delayed_trips' => count(array_filter($trips, fn($trip) => $trip['status'] === 'Delayed')),
    'avg_trip_duration' => 0
];

// Calculate average trip duration
$completed_trips = array_filter($trips, fn($trip) => 
    $trip['status'] === 'Completed' && 
    $trip['start_date'] && 
    $trip['end_date']
);

if (!empty($completed_trips)) {
    $total_duration = 0;
    foreach ($completed_trips as $trip) {
        $start = strtotime($trip['start_date']);
        $end = strtotime($trip['end_date']);
        $total_duration += ($end - $start) / 3600; // Convert to hours
    }
    $stats['avg_trip_duration'] = $total_duration / count($completed_trips);
}

// Get trips by status
$status_stats = [];
foreach ($trips as $trip) {
    $status = $trip['status'];
    if (!isset($status_stats[$status])) {
        $status_stats[$status] = 0;
    }
    $status_stats[$status]++;
}

// Get top drivers
$driver_stats = [];
foreach ($trips as $trip) {
    $driver = $trip['driver_name'];
    if (!isset($driver_stats[$driver])) {
        $driver_stats[$driver] = [
            'total_trips' => 0,
            'completed_trips' => 0,
            'total_duration' => 0
        ];
    }
    $driver_stats[$driver]['total_trips']++;
    if ($trip['status'] === 'Completed') {
        $driver_stats[$driver]['completed_trips']++;
        if ($trip['start_date'] && $trip['end_date']) {
            $start = strtotime($trip['start_date']);
            $end = strtotime($trip['end_date']);
            $driver_stats[$driver]['total_duration'] += ($end - $start) / 3600;
        }
    }
}

// Sort drivers by completed trips
uasort($driver_stats, fn($a, $b) => $b['completed_trips'] - $a['completed_trips']);
$top_drivers = array_slice($driver_stats, 0, 5, true);

// Get top vehicles
$vehicle_stats = [];
foreach ($trips as $trip) {
    $vehicle = $trip['vehicle_registration'];
    if (!isset($vehicle_stats[$vehicle])) {
        $vehicle_stats[$vehicle] = [
            'total_trips' => 0,
            'completed_trips' => 0,
            'total_duration' => 0
        ];
    }
    $vehicle_stats[$vehicle]['total_trips']++;
    if ($trip['status'] === 'Completed') {
        $vehicle_stats[$vehicle]['completed_trips']++;
        if ($trip['start_date'] && $trip['end_date']) {
            $start = strtotime($trip['start_date']);
            $end = strtotime($trip['end_date']);
            $vehicle_stats[$vehicle]['total_duration'] += ($end - $start) / 3600;
        }
    }
}

// Sort vehicles by completed trips
uasort($vehicle_stats, fn($a, $b) => $b['completed_trips'] - $a['completed_trips']);
$top_vehicles = array_slice($vehicle_stats, 0, 5, true);

// Get daily trip counts
$daily_trips = [];
foreach ($trips as $trip) {
    $date = date('Y-m-d', strtotime($trip['start_date']));
    if (!isset($daily_trips[$date])) {
        $daily_trips[$date] = [
            'total_trips' => 0,
            'completed_trips' => 0
        ];
    }
    $daily_trips[$date]['total_trips']++;
    if ($trip['status'] === 'Completed') {
        $daily_trips[$date]['completed_trips']++;
    }
}
ksort($daily_trips);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Reports - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('reports'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Trip Reports',
                'View trip statistics and analysis'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Date Range Filter -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <form method="GET" class="flex gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                            <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                            <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="form-input">
                        </div>
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </form>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="bi bi-truck text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Trips</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $stats['total_trips']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Completed Trips</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $stats['completed_trips']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600">
                                <i class="bi bi-x-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Cancelled Trips</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $stats['cancelled_trips']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="bi bi-clock text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Avg. Duration</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo round($stats['avg_trip_duration'], 1); ?> hrs</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Status Distribution -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Trip Status Distribution</h3>
                        <canvas id="statusChart" height="300"></canvas>
                    </div>

                    <!-- Daily Trip Counts -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Trip Counts</h3>
                        <canvas id="dailyTripsChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Top Drivers and Vehicles -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Top Drivers -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Top Drivers</h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Trips</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($top_drivers as $driver => $stats): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo $driver; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $stats['total_trips']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $stats['completed_trips']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $stats['completed_trips'] > 0 ? round($stats['total_duration'] / $stats['completed_trips'], 1) : 0; ?> hrs
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Top Vehicles -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Top Vehicles</h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Trips</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($top_vehicles as $vehicle => $stats): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo $vehicle; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $stats['total_trips']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $stats['completed_trips']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $stats['completed_trips'] > 0 ? round($stats['total_duration'] / $stats['completed_trips'], 1) : 0; ?> hrs
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trip History Link -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Trip History</h3>
                    <a href="trip-history.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-clock-history"></i> View Trip History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($status_stats)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($status_stats)); ?>,
                    backgroundColor: [
                        '#10b981', // Completed
                        '#3b82f6', // In Progress
                        '#f59e0b', // Scheduled
                        '#ef4444', // Cancelled
                        '#6b7280'  // Delayed
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Daily Trips Chart
        const dailyTripsCtx = document.getElementById('dailyTripsChart').getContext('2d');
        new Chart(dailyTripsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($date) {
                    return date('M d', strtotime($date));
                }, array_keys($daily_trips))); ?>,
                datasets: [{
                    label: 'Total Trips',
                    data: <?php echo json_encode(array_column($daily_trips, 'total_trips')); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Completed Trips',
                    data: <?php echo json_encode(array_column($daily_trips, 'completed_trips')); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 
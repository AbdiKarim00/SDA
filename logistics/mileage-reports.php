<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once 'mock_data.php';

// Get mock data
$vehicles = get_mock_data('vehicles');
$trips = get_mock_data('trips');

// Calculate total mileage
$total_mileage = array_sum(array_column($vehicles, 'current_mileage'));

// Calculate average mileage per vehicle
$average_mileage = $total_mileage / count($vehicles);

// Calculate mileage by vehicle type
$mileage_by_type = [];
foreach ($vehicles as $vehicle) {
    $type = $vehicle['vehicle_type'];
    if (!isset($mileage_by_type[$type])) {
        $mileage_by_type[$type] = [
            'total' => 0,
            'count' => 0
        ];
    }
    $mileage_by_type[$type]['total'] += $vehicle['current_mileage'];
    $mileage_by_type[$type]['count']++;
}

// Calculate average mileage by type
foreach ($mileage_by_type as $type => $data) {
    $mileage_by_type[$type]['average'] = $data['total'] / $data['count'];
}

// Calculate monthly mileage from trips
$monthly_mileage = [];
foreach ($trips as $trip) {
    if ($trip['mileage_end'] !== null && $trip['mileage_start'] !== null) {
        $month = date('Y-m', strtotime($trip['start_date']));
        $trip_mileage = $trip['mileage_end'] - $trip['mileage_start'];
        
        if (!isset($monthly_mileage[$month])) {
            $monthly_mileage[$month] = 0;
        }
        $monthly_mileage[$month] += $trip_mileage;
    }
}
ksort($monthly_mileage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mileage Reports - Transport IMS</title>
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
                'Mileage Reports',
                'View vehicle mileage statistics and analysis'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Status Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="bi bi-speedometer2 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Mileage</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($total_mileage); ?> km</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-graph-up text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Average Mileage</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($average_mileage); ?> km</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="bi bi-truck text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Vehicles</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo count($vehicles); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="bi bi-calendar-check text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Trips</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo count(array_filter($trips, fn($t) => $t['status'] === 'In Progress')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Mileage by Vehicle Type -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Average Mileage by Vehicle Type</h3>
                        <canvas id="mileageByTypeChart" height="300"></canvas>
                    </div>

                    <!-- Monthly Mileage Trend -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Mileage Trend</h3>
                        <canvas id="monthlyTrendChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Vehicle Mileage List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Vehicle Mileage Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Mileage</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Service</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Service</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $vehicle['registration_number']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $vehicle['vehicle_type']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo number_format($vehicle['current_mileage']); ?> km
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($vehicle['last_updated'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($vehicle['next_service_due'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    <?php echo match($vehicle['status']) {
                                                        'Available' => 'bg-green-100 text-green-800',
                                                        'In Service' => 'bg-blue-100 text-blue-800',
                                                        'Maintenance' => 'bg-yellow-100 text-yellow-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    }; ?>">
                                                    <?php echo $vehicle['status']; ?>
                                                </span>
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
    </div>

    <script>
        // Mileage by Type Chart
        const mileageByTypeCtx = document.getElementById('mileageByTypeChart').getContext('2d');
        new Chart(mileageByTypeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($mileage_by_type)); ?>,
                datasets: [{
                    label: 'Average Mileage',
                    data: <?php echo json_encode(array_column($mileage_by_type, 'average')); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Mileage (km)'
                        }
                    }
                }
            }
        });

        // Monthly Trend Chart
        const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($month) {
                    return date('M Y', strtotime($month . '-01'));
                }, array_keys($monthly_mileage))); ?>,
                datasets: [{
                    label: 'Monthly Mileage',
                    data: <?php echo json_encode(array_values($monthly_mileage)); ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Mileage (km)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 
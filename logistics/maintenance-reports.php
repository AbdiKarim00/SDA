<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// require_once 'mock_data.php'; // Mock data system removed

// Get mock data
// $maintenance = get_mock_data('maintenance'); // Mock data system removed
$maintenance = []; // Placeholder
// $vehicles = get_mock_data('vehicles'); // Mock data system removed
$vehicles = []; // Placeholder

// Calculate statistics
$total_maintenance = count($maintenance);
$scheduled_maintenance = count(array_filter($maintenance, fn($m) => $m['status'] === 'Scheduled'));
$completed_maintenance = count(array_filter($maintenance, fn($m) => $m['status'] === 'Completed'));
$in_progress_maintenance = count(array_filter($maintenance, fn($m) => $m['status'] === 'In Progress'));

// Calculate total cost
$total_cost = array_sum(array_column($maintenance, 'cost'));

// Group maintenance by service type
$maintenance_by_type = [];
foreach ($maintenance as $record) {
    $type = $record['service_type'];
    if (!isset($maintenance_by_type[$type])) {
        $maintenance_by_type[$type] = [
            'count' => 0,
            'total_cost' => 0
        ];
    }
    $maintenance_by_type[$type]['count']++;
    $maintenance_by_type[$type]['total_cost'] += $record['cost'];
}

// Group maintenance by month
$maintenance_by_month = [];
foreach ($maintenance as $record) {
    $month = date('Y-m', strtotime($record['service_date']));
    if (!isset($maintenance_by_month[$month])) {
        $maintenance_by_month[$month] = [
            'count' => 0,
            'total_cost' => 0
        ];
    }
    $maintenance_by_month[$month]['count']++;
    $maintenance_by_month[$month]['total_cost'] += $record['cost'];
}
ksort($maintenance_by_month);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Reports - Transport IMS</title>
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
                'Maintenance Reports',
                'View maintenance history and cost analysis'
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
                                <i class="bi bi-tools text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Maintenance</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $total_maintenance; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="bi bi-calendar-check text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Scheduled</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $scheduled_maintenance; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Completed</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $completed_maintenance; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="bi bi-currency-dollar text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Cost</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_cost, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Maintenance by Type -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance by Type</h3>
                        <canvas id="maintenanceTypeChart" height="300"></canvas>
                    </div>

                    <!-- Monthly Maintenance Trend -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Maintenance Trend</h3>
                        <canvas id="monthlyTrendChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Maintenance List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Maintenance History</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($maintenance as $record): ?>
                                        <?php
                                        $vehicle = array_filter($vehicles, fn($v) => $v['id'] == $record['vehicle_id'])[0] ?? null;
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $vehicle ? $vehicle['registration_number'] : 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $record['service_type']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($record['service_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    <?php echo match($record['status']) {
                                                        'Completed' => 'bg-green-100 text-green-800',
                                                        'In Progress' => 'bg-blue-100 text-blue-800',
                                                        'Scheduled' => 'bg-yellow-100 text-yellow-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    }; ?>">
                                                    <?php echo $record['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($record['cost'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $record['service_provider']; ?>
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
        // Maintenance by Type Chart
        const maintenanceTypeCtx = document.getElementById('maintenanceTypeChart').getContext('2d');
        new Chart(maintenanceTypeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($maintenance_by_type)); ?>,
                datasets: [{
                    label: 'Number of Services',
                    data: <?php echo json_encode(array_column($maintenance_by_type, 'count')); ?>,
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
                            text: 'Number of Services'
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
                }, array_keys($maintenance_by_month))); ?>,
                datasets: [{
                    label: 'Maintenance Cost',
                    data: <?php echo json_encode(array_column($maintenance_by_month, 'total_cost')); ?>,
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
                            text: 'Cost ($)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 
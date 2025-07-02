<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once 'mock_data.php';

// Get mock data
$incidents = get_mock_data('incidents');
$vehicles = get_mock_data('vehicles');

// Calculate statistics
$total_incidents = count($incidents);
$open_incidents = count(array_filter($incidents, fn($i) => $i['status'] === 'Open'));
$resolved_incidents = count(array_filter($incidents, fn($i) => $i['status'] === 'Resolved'));
$total_damage_cost = array_sum(array_column($incidents, 'cost_estimate'));

// Group incidents by type
$incidents_by_type = [];
foreach ($incidents as $incident) {
    $type = $incident['incident_type'];
    if (!isset($incidents_by_type[$type])) {
        $incidents_by_type[$type] = [
            'count' => 0,
            'total_cost' => 0
        ];
    }
    $incidents_by_type[$type]['count']++;
    $incidents_by_type[$type]['total_cost'] += $incident['cost_estimate'];
}

// Group incidents by month
$incidents_by_month = [];
foreach ($incidents as $incident) {
    $month = date('Y-m', strtotime($incident['incident_date']));
    if (!isset($incidents_by_month[$month])) {
        $incidents_by_month[$month] = [
            'count' => 0,
            'total_cost' => 0
        ];
    }
    $incidents_by_month[$month]['count']++;
    $incidents_by_month[$month]['total_cost'] += $incident['cost_estimate'];
}
ksort($incidents_by_month);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Reports - Transport IMS</title>
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
                'Incident Reports',
                'View incident statistics and analysis'
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
                                <i class="bi bi-exclamation-triangle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Incidents</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $total_incidents; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600">
                                <i class="bi bi-exclamation-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Open Incidents</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $open_incidents; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Resolved</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $resolved_incidents; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="bi bi-currency-dollar text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Damage Cost</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_damage_cost, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Incidents by Type -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Incidents by Type</h3>
                        <canvas id="incidentTypeChart" height="300"></canvas>
                    </div>

                    <!-- Monthly Incident Trend -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Incident Trend</h3>
                        <canvas id="monthlyTrendChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Incident List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Incident History</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Estimate</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($incidents as $incident): ?>
                                        <?php
                                        $vehicle = array_filter($vehicles, fn($v) => $v['id'] == $incident['vehicle_id'])[0] ?? null;
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $vehicle ? $vehicle['registration_number'] : 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $incident['driver_name']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($incident['incident_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $incident['incident_type']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    <?php echo match($incident['status']) {
                                                        'Resolved' => 'bg-green-100 text-green-800',
                                                        'Open' => 'bg-red-100 text-red-800',
                                                        'Under Investigation' => 'bg-yellow-100 text-yellow-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    }; ?>">
                                                    <?php echo $incident['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($incident['cost_estimate'], 2); ?>
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
        // Incidents by Type Chart
        const incidentTypeCtx = document.getElementById('incidentTypeChart').getContext('2d');
        new Chart(incidentTypeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($incidents_by_type)); ?>,
                datasets: [{
                    label: 'Number of Incidents',
                    data: <?php echo json_encode(array_column($incidents_by_type, 'count')); ?>,
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgb(239, 68, 68)',
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
                            text: 'Number of Incidents'
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
                }, array_keys($incidents_by_month))); ?>,
                datasets: [{
                    label: 'Incident Cost',
                    data: <?php echo json_encode(array_column($incidents_by_month, 'total_cost')); ?>,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
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
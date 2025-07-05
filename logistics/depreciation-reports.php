<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// require_once 'mock_data.php'; // Mock data system removed

// Get mock data
// $vehicles = get_mock_data('vehicles'); // Mock data system removed
$vehicles = []; // Placeholder

// Calculate depreciation statistics
$total_vehicles = count($vehicles);
$total_purchase_value = array_sum(array_column($vehicles, 'purchase_price'));
$total_current_value = 0;
$total_depreciation = 0;

// Calculate depreciation for each vehicle
foreach ($vehicles as &$vehicle) {
    $purchase_date = new DateTime($vehicle['date_of_purchase']);
    $current_date = new DateTime();
    $age_in_years = $current_date->diff($purchase_date)->y;
    
    // Calculate depreciation (using straight-line method)
    $annual_depreciation_rate = 0.15; // 15% per year
    $depreciation_factor = 1 - ($annual_depreciation_rate * $age_in_years);
    $depreciation_factor = max($depreciation_factor, 0.2); // Minimum 20% of original value
    
    $vehicle['current_value'] = $vehicle['purchase_price'] * $depreciation_factor;
    $vehicle['depreciation_amount'] = $vehicle['purchase_price'] - $vehicle['current_value'];
    $vehicle['depreciation_percentage'] = (($vehicle['purchase_price'] - $vehicle['current_value']) / $vehicle['purchase_price']) * 100;
    
    $total_current_value += $vehicle['current_value'];
    $total_depreciation += $vehicle['depreciation_amount'];
}

// Group vehicles by age
$vehicles_by_age = [];
foreach ($vehicles as $vehicle) {
    $purchase_date = new DateTime($vehicle['date_of_purchase']);
    $current_date = new DateTime();
    $age_in_years = $current_date->diff($purchase_date)->y;
    
    if (!isset($vehicles_by_age[$age_in_years])) {
        $vehicles_by_age[$age_in_years] = [
            'count' => 0,
            'total_value' => 0,
            'total_depreciation' => 0
        ];
    }
    
    $vehicles_by_age[$age_in_years]['count']++;
    $vehicles_by_age[$age_in_years]['total_value'] += $vehicle['current_value'];
    $vehicles_by_age[$age_in_years]['total_depreciation'] += $vehicle['depreciation_amount'];
}
ksort($vehicles_by_age);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depreciation Reports - Transport IMS</title>
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
                'Depreciation Reports',
                'View vehicle depreciation calculations and trends'
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
                                <i class="bi bi-truck text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Vehicles</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $total_vehicles; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-currency-dollar text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Purchase Value</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_purchase_value, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="bi bi-graph-down text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Depreciation</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_depreciation, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="bi bi-calculator text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Current Value</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_current_value, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Depreciation by Age -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Depreciation by Vehicle Age</h3>
                        <canvas id="depreciationByAgeChart" height="300"></canvas>
                    </div>

                    <!-- Value Distribution -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Value Distribution</h3>
                        <canvas id="valueDistributionChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Vehicle List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Vehicle Depreciation Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Date</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Price</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Value</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Depreciation</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Depreciation %</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $vehicle['registration_number']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($vehicle['date_of_purchase'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($vehicle['purchase_price'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($vehicle['current_value'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($vehicle['depreciation_amount'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    <?php echo $vehicle['depreciation_percentage'] > 50 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                    <?php echo number_format($vehicle['depreciation_percentage'], 1); ?>%
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
        // Depreciation by Age Chart
        const depreciationByAgeCtx = document.getElementById('depreciationByAgeChart').getContext('2d');
        new Chart(depreciationByAgeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($age) {
                    return $age . ' Year' . ($age != 1 ? 's' : '');
                }, array_keys($vehicles_by_age))); ?>,
                datasets: [{
                    label: 'Total Depreciation',
                    data: <?php echo json_encode(array_column($vehicles_by_age, 'total_depreciation')); ?>,
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
                            text: 'Depreciation Amount ($)'
                        }
                    }
                }
            }
        });

        // Value Distribution Chart
        const valueDistributionCtx = document.getElementById('valueDistributionChart').getContext('2d');
        new Chart(valueDistributionCtx, {
            type: 'pie',
            data: {
                labels: ['Purchase Value', 'Current Value', 'Depreciation'],
                datasets: [{
                    data: [
                        <?php echo $total_purchase_value; ?>,
                        <?php echo $total_current_value; ?>,
                        <?php echo $total_depreciation; ?>
                    ],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(16, 185, 129, 0.5)',
                        'rgba(239, 68, 68, 0.5)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 1
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
    </script>
</body>
</html> 
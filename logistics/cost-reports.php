<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once 'mock_data.php';

// Get mock data
$vehicles = get_mock_data('vehicles');
$maintenance = get_mock_data('maintenance');
$incidents = get_mock_data('incidents');
$trips = get_mock_data('trips');
$fuel_cards = get_mock_data('fuel_cards');

// Calculate total costs
$total_maintenance_cost = array_sum(array_column($maintenance, 'cost'));
$total_incident_cost = array_sum(array_column($incidents, 'cost_estimate'));
$total_fuel_cost = array_sum(array_column($fuel_cards, 'last_transaction_amount'));

// Calculate costs by vehicle
$costs_by_vehicle = [];
foreach ($vehicles as $vehicle) {
    $vehicle_id = $vehicle['id'];
    
    // Maintenance costs
    $maintenance_costs = array_sum(array_map(
        fn($m) => $m['cost'],
        array_filter($maintenance, fn($m) => $m['vehicle_id'] == $vehicle_id)
    ));
    
    // Incident costs
    $incident_costs = array_sum(array_map(
        fn($i) => $i['cost_estimate'],
        array_filter($incidents, fn($i) => $i['vehicle_id'] == $vehicle_id)
    ));
    
    // Fuel costs
    $fuel_costs = array_sum(array_map(
        fn($f) => $f['last_transaction_amount'],
        array_filter($fuel_cards, fn($f) => $f['vehicle_id'] == $vehicle_id)
    ));
    
    $costs_by_vehicle[$vehicle['registration_number']] = [
        'maintenance' => $maintenance_costs,
        'incidents' => $incident_costs,
        'fuel' => $fuel_costs,
        'total' => $maintenance_costs + $incident_costs + $fuel_costs
    ];
}

// Calculate monthly costs
$monthly_costs = [];
foreach ($maintenance as $record) {
    $month = date('Y-m', strtotime($record['service_date']));
    if (!isset($monthly_costs[$month])) {
        $monthly_costs[$month] = [
            'maintenance' => 0,
            'incidents' => 0,
            'fuel' => 0
        ];
    }
    $monthly_costs[$month]['maintenance'] += $record['cost'];
}

foreach ($incidents as $incident) {
    $month = date('Y-m', strtotime($incident['incident_date']));
    if (!isset($monthly_costs[$month])) {
        $monthly_costs[$month] = [
            'maintenance' => 0,
            'incidents' => 0,
            'fuel' => 0
        ];
    }
    $monthly_costs[$month]['incidents'] += $incident['cost_estimate'];
}

foreach ($fuel_cards as $card) {
    $month = date('Y-m', strtotime($card['last_transaction']));
    if (!isset($monthly_costs[$month])) {
        $monthly_costs[$month] = [
            'maintenance' => 0,
            'incidents' => 0,
            'fuel' => 0
        ];
    }
    $monthly_costs[$month]['fuel'] += $card['last_transaction_amount'];
}
ksort($monthly_costs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cost Reports - Transport IMS</title>
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
                'Cost Reports',
                'View cost analysis and breakdowns'
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
                                <p class="text-sm font-medium text-gray-500">Maintenance Cost</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_maintenance_cost, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600">
                                <i class="bi bi-exclamation-triangle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Incident Cost</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_incident_cost, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-fuel-pump text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Fuel Cost</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_fuel_cost, 2); ?></p>
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
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_maintenance_cost + $total_incident_cost + $total_fuel_cost, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Cost Distribution -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Cost Distribution</h3>
                        <canvas id="costDistributionChart" height="300"></canvas>
                    </div>

                    <!-- Monthly Cost Trend -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Cost Trend</h3>
                        <canvas id="monthlyTrendChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Vehicle Cost List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Vehicle Cost Breakdown</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maintenance</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incidents</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fuel</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($costs_by_vehicle as $registration => $costs): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $registration; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($costs['maintenance'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($costs['incidents'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($costs['fuel'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                $<?php echo number_format($costs['total'], 2); ?>
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
        // Cost Distribution Chart
        const costDistributionCtx = document.getElementById('costDistributionChart').getContext('2d');
        new Chart(costDistributionCtx, {
            type: 'pie',
            data: {
                labels: ['Maintenance', 'Incidents', 'Fuel'],
                datasets: [{
                    data: [
                        <?php echo $total_maintenance_cost; ?>,
                        <?php echo $total_incident_cost; ?>,
                        <?php echo $total_fuel_cost; ?>
                    ],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(239, 68, 68, 0.5)',
                        'rgba(16, 185, 129, 0.5)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(239, 68, 68)',
                        'rgb(16, 185, 129)'
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

        // Monthly Trend Chart
        const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($month) {
                    return date('M Y', strtotime($month . '-01'));
                }, array_keys($monthly_costs))); ?>,
                datasets: [
                    {
                        label: 'Maintenance',
                        data: <?php echo json_encode(array_column($monthly_costs, 'maintenance')); ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Incidents',
                        data: <?php echo json_encode(array_column($monthly_costs, 'incidents')); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Fuel',
                        data: <?php echo json_encode(array_column($monthly_costs, 'fuel')); ?>,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
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
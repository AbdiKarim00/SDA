<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// require_once 'mock_data.php'; // Mock data system removed

// Get mock data
// $vehicles = get_mock_data('vehicles'); // Mock data system removed
$vehicles = []; // Placeholder
// $trips = get_mock_data('trips'); // Mock data system removed
$trips = []; // Placeholder
// $fuel_cards = get_mock_data('fuel_cards'); // Mock data system removed
$fuel_cards = []; // Placeholder

// Calculate total fuel cost
$total_fuel_cost = array_sum(array_column($fuel_cards, 'last_transaction_amount'));

// Calculate total fuel used from trips
$total_fuel_used = array_sum(array_filter(array_column($trips, 'fuel_used'), fn($value) => $value !== null));

// Calculate average fuel cost per vehicle
$fuel_cost_by_vehicle = [];
foreach ($vehicles as $vehicle) {
    $vehicle_id = $vehicle['id'];
    $vehicle_fuel_cards = array_filter($fuel_cards, fn($card) => $card['vehicle_id'] == $vehicle_id);
    $fuel_cost_by_vehicle[$vehicle['registration_number']] = array_sum(array_column($vehicle_fuel_cards, 'last_transaction_amount'));
}

// Calculate monthly fuel costs
$monthly_fuel_costs = [];
foreach ($fuel_cards as $card) {
    $month = date('Y-m', strtotime($card['last_transaction']));
    if (!isset($monthly_fuel_costs[$month])) {
        $monthly_fuel_cards[$month] = [];
    }
    $monthly_fuel_cards[$month][] = $card;
}

$monthly_fuel_costs = [];
foreach ($monthly_fuel_cards as $month => $cards) {
    $monthly_fuel_costs[$month] = array_sum(array_column($cards, 'last_transaction_amount'));
}
ksort($monthly_fuel_costs);

// Calculate fuel efficiency by vehicle type
$fuel_efficiency = [];
foreach ($vehicles as $vehicle) {
    $type = $vehicle['vehicle_type'];
    if (!isset($fuel_efficiency[$type])) {
        $fuel_efficiency[$type] = [
            'total_fuel' => 0,
            'total_distance' => 0,
            'count' => 0
        ];
    }
    
    $vehicle_trips = array_filter($trips, fn($trip) => $trip['vehicle_id'] == $vehicle['id']);
    foreach ($vehicle_trips as $trip) {
        if ($trip['fuel_used'] !== null && $trip['mileage_end'] !== null && $trip['mileage_start'] !== null) {
            $fuel_efficiency[$type]['total_fuel'] += $trip['fuel_used'];
            $fuel_efficiency[$type]['total_distance'] += ($trip['mileage_end'] - $trip['mileage_start']);
            $fuel_efficiency[$type]['count']++;
        }
    }
}

// Calculate average fuel efficiency
foreach ($fuel_efficiency as $type => $data) {
    if ($data['total_fuel'] > 0) {
        $fuel_efficiency[$type]['average'] = $data['total_distance'] / $data['total_fuel'];
    } else {
        $fuel_efficiency[$type]['average'] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Reports - Transport IMS</title>
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
                'Fuel Reports',
                'View fuel usage statistics and analysis'
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
                                <i class="bi bi-fuel-pump text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Fuel Cost</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($total_fuel_cost, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-droplet text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Fuel Used</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($total_fuel_used, 2); ?> L</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="bi bi-truck text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Fuel Cards</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo count(array_filter($fuel_cards, fn($card) => $card['status'] === 'Active')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="bi bi-currency-dollar text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Average Cost per Vehicle</p>
                                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format(array_sum($fuel_cost_by_vehicle) / count($fuel_cost_by_vehicle), 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Fuel Efficiency by Vehicle Type -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Fuel Efficiency by Vehicle Type</h3>
                        <canvas id="fuelEfficiencyChart" height="300"></canvas>
                    </div>

                    <!-- Monthly Fuel Cost Trend -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Fuel Cost Trend</h3>
                        <canvas id="monthlyTrendChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Fuel Card List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Fuel Card Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card Number</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Limit</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Balance</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Transaction</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($fuel_cards as $card): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $card['card_number']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $card['vehicle_registration']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $card['driver_name']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($card['monthly_limit'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                $<?php echo number_format($card['current_balance'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($card['last_transaction'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    <?php echo match($card['status']) {
                                                        'Active' => 'bg-green-100 text-green-800',
                                                        'Suspended' => 'bg-red-100 text-red-800',
                                                        'Expired' => 'bg-yellow-100 text-yellow-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    }; ?>">
                                                    <?php echo $card['status']; ?>
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
        // Fuel Efficiency Chart
        const fuelEfficiencyCtx = document.getElementById('fuelEfficiencyChart').getContext('2d');
        new Chart(fuelEfficiencyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($fuel_efficiency)); ?>,
                datasets: [{
                    label: 'Average Fuel Efficiency (km/L)',
                    data: <?php echo json_encode(array_column($fuel_efficiency, 'average')); ?>,
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
                            text: 'Fuel Efficiency (km/L)'
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
                }, array_keys($monthly_fuel_costs))); ?>,
                datasets: [{
                    label: 'Monthly Fuel Cost',
                    data: <?php echo json_encode(array_values($monthly_fuel_costs)); ?>,
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
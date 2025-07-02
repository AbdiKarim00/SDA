<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../components/common/KpiCard.php';
require_once '../components/common/ChartCard.php';
require_once '../components/common/MapCard.php';

// Mock data for frontend development
$vehicleStats = [
    'total_vehicles' => 25,
    'active_vehicles' => 18,
    'maintenance_vehicles' => 5,
    'inactive_vehicles' => 2
];

$vehicleLocations = [
    [
        'id' => 1,
        'registration_number' => 'KAA 123A',
        'status' => 'active',
        'latitude' => -1.2921,
        'longitude' => 36.8219,
        'driver_name' => 'John Doe',
        'last_updated' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'status' => 'active',
        'latitude' => -1.2833,
        'longitude' => 36.8172,
        'driver_name' => 'Jane Smith',
        'last_updated' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 3,
        'registration_number' => 'KAA 789C',
        'status' => 'maintenance',
        'latitude' => -1.3000,
        'longitude' => 36.8000,
        'driver_name' => 'Mike Johnson',
        'last_updated' => date('Y-m-d H:i:s')
    ]
];

$recentLogs = [
    [
        'id' => 1,
        'registration_number' => 'KAA 123A',
        'driver_name' => 'John Doe',
        'status' => 'In Transit',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'driver_name' => 'Jane Smith',
        'status' => 'Completed',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
    ],
    [
        'id' => 3,
        'registration_number' => 'KAA 789C',
        'driver_name' => 'Mike Johnson',
        'status' => 'Maintenance',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ]
];

$maintenanceAlerts = [
    [
        'id' => 1,
        'registration_number' => 'KAA 123A',
        'maintenance_type' => 'Routine Service',
        'description' => 'Regular maintenance check',
        'next_maintenance_date' => date('Y-m-d', strtotime('+3 days')),
        'vehicle_status' => 'active'
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'maintenance_type' => 'Tire Replacement',
        'description' => 'Replace worn tires',
        'next_maintenance_date' => date('Y-m-d', strtotime('+5 days')),
        'vehicle_status' => 'active'
    ]
];

// Mock fuel consumption data
$fuelConsumption = [
    [
        'month' => date('Y-m-01', strtotime('-5 months')),
        'total_liters' => 1200,
        'total_cost' => 150000
    ],
    [
        'month' => date('Y-m-01', strtotime('-4 months')),
        'total_liters' => 1350,
        'total_cost' => 168750
    ],
    [
        'month' => date('Y-m-01', strtotime('-3 months')),
        'total_liters' => 1100,
        'total_cost' => 137500
    ],
    [
        'month' => date('Y-m-01', strtotime('-2 months')),
        'total_liters' => 1400,
        'total_cost' => 175000
    ],
    [
        'month' => date('Y-m-01', strtotime('-1 month')),
        'total_liters' => 1300,
        'total_cost' => 162500
    ],
    [
        'month' => date('Y-m-01'),
        'total_liters' => 1250,
        'total_cost' => 156250
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SDATIMS</title>
    <link href="../assets/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        timeRange: '30days',
        activeTab: 'overview'
        }" ,
        x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('dashboard'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Vehicle Reports',
                'Comprehensive overview of vehicle fleet status and analytics'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Tab Navigation -->
                <div class="mb-6 border-b border-gray-200">
                    <nav class="flex space-x-8">
                        <button @click="activeTab = 'overview'" 
                                :class="{'border-primary text-primary': activeTab === 'overview'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Overview
                        </button>
                        <button @click="activeTab = 'map'" 
                                :class="{'border-primary text-primary': activeTab === 'map'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Vehicle Locations
                        </button>
                        <button @click="activeTab = 'maintenance'" 
                                :class="{'border-primary text-primary': activeTab === 'maintenance'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Maintenance
                        </button>
                    </nav>
                </div>

                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'" x-cloak>
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <?php
                        KpiCard::render('Total Vehicles', $vehicleStats['total_vehicles'], 'truck');
                        KpiCard::render('Active Vehicles', $vehicleStats['active_vehicles'], 'check-circle');
                        KpiCard::render('In Maintenance', $vehicleStats['maintenance_vehicles'], 'tools');
                        KpiCard::render('Inactive', $vehicleStats['inactive_vehicles'], 'x-circle');
                        ?>
                    </div>

                    <!-- Charts and Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2">
                            <?php
                            $activeVehicles = $vehicleStats['active_vehicles'];
                            $maintenanceVehicles = $vehicleStats['maintenance_vehicles'];
                            $inactiveVehicles = $vehicleStats['inactive_vehicles'];
                            $totalVehicles = $vehicleStats['total_vehicles'];

                            ChartCard::render(
                                'Vehicle Status Distribution',
                                'vehicleStatusChart',
                                'donut',
                                [
                                    'series' => [$activeVehicles, $maintenanceVehicles, $inactiveVehicles]
                                ],
                                [
                                    'labels' => ['Active', 'In Maintenance', 'Inactive'],
                                    'colors' => ['#10b981', '#ef4444', '#6b7280'],
                                    'legend' => ['position' => 'bottom'],
                                    'dataLabels' => [
                                        'enabled' => true,
                                        'formatter' => 'function(val) { return val.toFixed(0) + "%"; }'
                                    ],
                                    'plotOptions' => [
                                        'pie' => [
                                            'donut' => [
                                                'size' => '50%',
                                                'labels' => [
                                                    'show' => true,
                                                    'total' => [
                                                        'show' => true,
                                                        'label' => 'Total Vehicles',
                                                        'formatter' => 'function() { return "' . $totalVehicles . '"; }'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            );
                            ?>
                        </div>
                        <div>
                            <div class="dashboard-card">
                                <h3 class="flex items-center gap-2 mb-4">
                                    <i class="bi bi-activity"></i>
                                    Recent Activity
                                </h3>
                                <?php if (empty($recentLogs)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="bi bi-info-circle text-2xl mb-2"></i>
                                        <p>No recent activity to display</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recentLogs as $log): ?>
                                        <div class="activity-item">
                                            <h6><?php echo htmlspecialchars($log['registration_number']); ?></h6>
                                            <p>
                                                <i class="bi bi-person me-1"></i>
                                                <?php echo htmlspecialchars($log['driver_name']); ?>
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($log['status']); ?>
                                            </p>
                                            <small class="text-gray-400">
                                                <?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Fuel Consumption Chart -->
                    <div class="mt-6">
                        <?php
                        $months = array_map(function($record) {
                            return date('M Y', strtotime($record['month']));
                        }, array_reverse($fuelConsumption));

                        $liters = array_map(function($record) {
                            return $record['total_liters'];
                        }, array_reverse($fuelConsumption));

                        ChartCard::render(
                            'Monthly Fuel Consumption',
                            'fuelConsumptionChart',
                            'bar',
                            [
                                'series' => [
                                    [
                                        'name' => 'Fuel Consumption (L)',
                                        'data' => $liters
                                    ]
                                ]
                            ],
                            [
                                'chart' => [
                                    'height' => 350,
                                    'type' => 'bar'
                                ],
                                'xaxis' => [
                                    'categories' => $months
                                ],
                                'yaxis' => [
                                    'title' => [
                                        'text' => 'Liters'
                                    ]
                                ]
                            ]
                        );
                        ?>
                    </div>
                </div>

                <!-- Map Tab -->
                <div x-show="activeTab === 'map'" x-cloak class="w-full">
                    <?php MapCard::render('Vehicle Locations', 'vehicleMap', $vehicleLocations); ?>
                </div>

                <!-- Maintenance Tab -->
                <div x-show="activeTab === 'maintenance'" x-cloak>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($maintenanceAlerts as $alert): ?>
                            <div class="dashboard-card">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <?php echo htmlspecialchars($alert['registration_number']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($alert['maintenance_type']); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            Next Maintenance
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($alert['next_maintenance_date'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($alert['description']); ?>
                                    </p>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <a href="maintenance.php?vehicle=<?php echo htmlspecialchars($alert['registration_number']); ?>" 
                                       class="btn btn-secondary btn-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
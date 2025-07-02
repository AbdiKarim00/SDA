<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../components/common/KpiCard.php';
require_once '../components/common/ChartCard.php';

// Mock data for frontend development
$fleetMetrics = [
    'total_vehicles' => 25,
    'active_vehicles' => 18,
    'maintenance_vehicles' => 5,
    'inactive_vehicles' => 2,
    'average_age' => 3.5,
    'utilization_rate' => 85,
    'fuel_efficiency' => 8.5,
    'maintenance_cost' => 150000
];

$vehicleTypes = [
    'Sedan' => 8,
    'SUV' => 6,
    'Truck' => 5,
    'Van' => 4,
    'Bus' => 2
];

$utilizationData = [
    [
        'month' => date('Y-m-01', strtotime('-5 months')),
        'utilization' => 82,
        'maintenance_hours' => 120
    ],
    [
        'month' => date('Y-m-01', strtotime('-4 months')),
        'utilization' => 85,
        'maintenance_hours' => 95
    ],
    [
        'month' => date('Y-m-01', strtotime('-3 months')),
        'utilization' => 88,
        'maintenance_hours' => 110
    ],
    [
        'month' => date('Y-m-01', strtotime('-2 months')),
        'utilization' => 84,
        'maintenance_hours' => 130
    ],
    [
        'month' => date('Y-m-01', strtotime('-1 month')),
        'utilization' => 87,
        'maintenance_hours' => 105
    ],
    [
        'month' => date('Y-m-01'),
        'utilization' => 85,
        'maintenance_hours' => 115
    ]
];

$maintenanceMetrics = [
    'scheduled' => 12,
    'unscheduled' => 3,
    'preventive' => 8,
    'corrective' => 7
];

$costMetrics = [
    'fuel' => 450000,
    'maintenance' => 150000,
    'insurance' => 75000,
    'depreciation' => 300000
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Analytics - SDATIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        timeRange: '30days',
        activeTab: 'overview'
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('vehicles'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Fleet Analytics',
                'Comprehensive analysis of vehicle fleet performance and metrics'
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
                        <button @click="activeTab = 'utilization'" 
                                :class="{'border-primary text-primary': activeTab === 'utilization'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Utilization
                        </button>
                        <button @click="activeTab = 'maintenance'" 
                                :class="{'border-primary text-primary': activeTab === 'maintenance'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Maintenance
                        </button>
                        <button @click="activeTab = 'costs'" 
                                :class="{'border-primary text-primary': activeTab === 'costs'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Cost Analysis
                        </button>
                    </nav>
                </div>

                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'">
                    <!-- Fleet Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <?php
                        KpiCard::render('Fleet Size', $fleetMetrics['total_vehicles'], 'truck');
                        KpiCard::render('Utilization Rate', $fleetMetrics['utilization_rate'] . '%', 'speedometer2');
                        KpiCard::render('Avg. Vehicle Age', $fleetMetrics['average_age'] . ' years', 'calendar');
                        KpiCard::render('Fuel Efficiency', $fleetMetrics['fuel_efficiency'] . ' km/L', 'fuel-pump');
                        ?>
                    </div>

                    <!-- Fleet Composition -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div>
                            <?php
                            ChartCard::render(
                                'Fleet Composition',
                                'fleetCompositionChart',
                                'donut',
                                [
                                    'series' => array_values($vehicleTypes)
                                ],
                                [
                                    'labels' => array_keys($vehicleTypes),
                                    'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                                    'legend' => ['position' => 'bottom'],
                                    'dataLabels' => [
                                        'enabled' => true,
                                        'formatter' => 'function(val) { return val.toFixed(0) + "%"; }'
                                    ]
                                ]
                            );
                            ?>
                        </div>
                        <div>
                            <?php
                            ChartCard::render(
                                'Vehicle Status Distribution',
                                'vehicleStatusChart',
                                'donut',
                                [
                                    'series' => [
                                        $fleetMetrics['active_vehicles'],
                                        $fleetMetrics['maintenance_vehicles'],
                                        $fleetMetrics['inactive_vehicles']
                                    ]
                                ],
                                [
                                    'labels' => ['Active', 'In Maintenance', 'Inactive'],
                                    'colors' => ['#10b981', '#ef4444', '#6b7280'],
                                    'legend' => ['position' => 'bottom'],
                                    'dataLabels' => [
                                        'enabled' => true,
                                        'formatter' => 'function(val) { return val.toFixed(0) + "%"; }'
                                    ]
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Utilization Tab -->
                <div x-show="activeTab === 'utilization'">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <?php
                            $months = array_map(function($record) {
                                return date('M Y', strtotime($record['month']));
                            }, array_reverse($utilizationData));

                            $utilization = array_map(function($record) {
                                return $record['utilization'];
                            }, array_reverse($utilizationData));

                            ChartCard::render(
                                'Fleet Utilization Trend',
                                'utilizationTrendChart',
                                'line',
                                [
                                    'series' => [
                                        [
                                            'name' => 'Utilization Rate',
                                            'data' => $utilization
                                        ]
                                    ]
                                ],
                                [
                                    'chart' => [
                                        'height' => 350,
                                        'type' => 'line'
                                    ],
                                    'xaxis' => [
                                        'categories' => $months
                                    ],
                                    'yaxis' => [
                                        'title' => [
                                            'text' => 'Utilization Rate (%)'
                                        ],
                                        'min' => 0,
                                        'max' => 100
                                    ]
                                ]
                            );
                            ?>
                        </div>
                        <div>
                            <?php
                            $maintenanceHours = array_map(function($record) {
                                return $record['maintenance_hours'];
                            }, array_reverse($utilizationData));

                            ChartCard::render(
                                'Maintenance Hours',
                                'maintenanceHoursChart',
                                'bar',
                                [
                                    'series' => [
                                        [
                                            'name' => 'Maintenance Hours',
                                            'data' => $maintenanceHours
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
                                            'text' => 'Hours'
                                        ]
                                    ]
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Tab -->
                <div x-show="activeTab === 'maintenance'">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <?php
                            ChartCard::render(
                                'Maintenance Distribution',
                                'maintenanceDistributionChart',
                                'donut',
                                [
                                    'series' => [
                                        $maintenanceMetrics['scheduled'],
                                        $maintenanceMetrics['unscheduled'],
                                        $maintenanceMetrics['preventive'],
                                        $maintenanceMetrics['corrective']
                                    ]
                                ],
                                [
                                    'labels' => ['Scheduled', 'Unscheduled', 'Preventive', 'Corrective'],
                                    'colors' => ['#10b981', '#ef4444', '#3b82f6', '#f59e0b'],
                                    'legend' => ['position' => 'bottom'],
                                    'dataLabels' => [
                                        'enabled' => true,
                                        'formatter' => 'function(val) { return val.toFixed(0) + "%"; }'
                                    ]
                                ]
                            );
                            ?>
                        </div>
                        <div>
                            <div class="dashboard-card">
                                <h3 class="flex items-center gap-2 mb-4">
                                    <i class="bi bi-tools"></i>
                                    Maintenance KPIs
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Mean Time Between Failures</h4>
                                        <p class="text-2xl font-semibold">45 days</p>
                                    </div>
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Mean Time to Repair</h4>
                                        <p class="text-2xl font-semibold">2.5 days</p>
                                    </div>
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Preventive Maintenance Compliance</h4>
                                        <p class="text-2xl font-semibold">92%</p>
                                    </div>
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Maintenance Cost per Vehicle</h4>
                                        <p class="text-2xl font-semibold">KSH 6,000</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost Analysis Tab -->
                <div x-show="activeTab === 'costs'">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <?php
                            ChartCard::render(
                                'Cost Distribution',
                                'costDistributionChart',
                                'donut',
                                [
                                    'series' => array_values($costMetrics)
                                ],
                                [
                                    'labels' => array_keys($costMetrics),
                                    'colors' => ['#10b981', '#ef4444', '#3b82f6', '#f59e0b'],
                                    'legend' => ['position' => 'bottom'],
                                    'dataLabels' => [
                                        'enabled' => true,
                                        'formatter' => 'function(val) { return val.toFixed(0) + "%"; }'
                                    ]
                                ]
                            );
                            ?>
                        </div>
                        <div>
                            <div class="dashboard-card">
                                <h3 class="flex items-center gap-2 mb-4">
                                    <i class="bi bi-currency-dollar"></i>
                                    Cost Metrics
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Total Fleet Cost</h4>
                                        <p class="text-2xl font-semibold">KSH 975,000</p>
                                    </div>
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Cost per Vehicle</h4>
                                        <p class="text-2xl font-semibold">KSH 39,000</p>
                                    </div>
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Fuel Cost per km</h4>
                                        <p class="text-2xl font-semibold">KSH 12.50</p>
                                    </div>
                                    <div class="p-4 bg-white rounded-lg shadow">
                                        <h4 class="text-sm font-medium text-gray-500">Maintenance Cost per km</h4>
                                        <p class="text-2xl font-semibold">KSH 4.20</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
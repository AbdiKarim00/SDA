<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../components/common/KpiCard.php';
require_once '../components/common/ChartCard.php';

// TODO: Replace mock data with database queries and dynamic data fetching.
// Example:
// $pdo = new PDO('pgsql:host=localhost;dbname=your_dbname', 'your_user', 'your_password');
// $stmt = $pdo->query("SELECT COUNT(*) FROM maintenance WHERE status = 'pending'");
// $pending_services = $stmt->fetchColumn();
// ...

$upcomingServices = [
    [
        'vehicle' => 'KAA 123A',
        'service_type' => 'Oil Change',
        'scheduled_date' => date('Y-m-d', strtotime('+5 days')),
        'estimated_cost' => 15000,
        'status' => 'Scheduled',
        'service_provider' => 'Toyota Service Center'
    ],
    [
        'vehicle' => 'KAA 456B',
        'service_type' => 'Brake Inspection',
        'scheduled_date' => date('Y-m-d', strtotime('+2 days')),
        'estimated_cost' => 20000,
        'status' => 'Scheduled',
        'service_provider' => 'Isuzu Service Center'
    ]
];

$maintenanceCosts = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'data' => [150000, 175000, 200000, 225000, 250000, 275000]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Management - SDATIMS</title>
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
        activeTab: 'overview'
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('maintenance'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Maintenance Management',
                'Vehicle maintenance tracking, scheduling, and analytics'
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
                        <button @click="activeTab = 'schedule'" 
                                :class="{'border-primary text-primary': activeTab === 'schedule'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Schedule
                        </button>
                        <button @click="activeTab = 'history'" 
                                :class="{'border-primary text-primary': activeTab === 'history'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            History
                        </button>
                    </nav>
                </div>

                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'">
                    <!-- Maintenance Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <?php
                        KpiCard::render('Pending Services', $maintenanceMetrics['pending_services'], 'tools');
                        KpiCard::render('In Progress', $maintenanceMetrics['in_progress'], 'gear');
                        KpiCard::render('Completed This Month', $maintenanceMetrics['completed_this_month'], 'check-circle');
                        KpiCard::render('Upcoming Scheduled', $maintenanceMetrics['upcoming_scheduled'], 'calendar');
                        ?>
                    </div>

                    <!-- Maintenance Alerts -->
                    <div class="dashboard-card mb-6">
                        <h3 class="flex items-center gap-2 mb-4">
                            <i class="bi bi-exclamation-triangle"></i>
                            Maintenance Alerts
                        </h3>
                        <div class="space-y-4">
                            <?php foreach ($maintenanceAlerts as $alert): ?>
                                <div class="p-4 <?php echo $alert['status'] === 'danger' ? 'bg-red-50' : 'bg-yellow-50'; ?> rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-medium"><?php echo htmlspecialchars($alert['vehicle']); ?> - <?php echo htmlspecialchars($alert['make_model']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($alert['service_type']); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium <?php echo $alert['status'] === 'danger' ? 'text-red-600' : 'text-yellow-600'; ?>">
                                                <?php echo $alert['days_remaining'] < 0 ? 'Overdue' : $alert['days_remaining'] . ' days remaining'; ?>
                                            </p>
                                            <p class="text-sm text-gray-500">Due: <?php echo date('Y-m-d', strtotime($alert['due_date'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Maintenance Cost Trend -->
                    <?php
                    ChartCard::render(
                        'Maintenance Cost Trend',
                        'maintenance-cost-trend',
                        'line',
                        [
                            'series' => [
                                [
                                    'name' => 'Maintenance Cost',
                                    'data' => $maintenanceCosts['data']
                                ]
                            ]
                        ],
                        [
                            'xaxis' => [
                                'categories' => $maintenanceCosts['labels']
                            ],
                            'yaxis' => [
                                'title' => [
                                    'text' => 'Cost (KSH)'
                                ]
                            ]
                        ]
                    );
                    ?>
                </div>

                <!-- Schedule Tab -->
                <div x-show="activeTab === 'schedule'">
                    <div class="dashboard-card">
                        <h3 class="flex items-center gap-2 mb-4">
                            <i class="bi bi-calendar-check"></i>
                            Upcoming Services
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Service Type</th>
                                        <th>Scheduled Date</th>
                                        <th>Estimated Cost</th>
                                        <th>Service Provider</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingServices as $service): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($service['vehicle']); ?></td>
                                            <td><?php echo htmlspecialchars($service['service_type']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($service['scheduled_date'])); ?></td>
                                            <td>KSH <?php echo number_format($service['estimated_cost']); ?></td>
                                            <td><?php echo htmlspecialchars($service['service_provider']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($service['status']) {
                                                        'Scheduled' => 'info',
                                                        'In Progress' => 'warning',
                                                        'Completed' => 'success',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($service['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- History Tab -->
                <div x-show="activeTab === 'history'">
                    <div class="dashboard-card">
                        <h3 class="flex items-center gap-2 mb-4">
                            <i class="bi bi-clock-history"></i>
                            Maintenance History
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Service Type</th>
                                        <th>Service Date</th>
                                        <th>Cost</th>
                                        <th>Service Provider</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenanceHistory as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['vehicle']); ?></td>
                                            <td><?php echo htmlspecialchars($record['service_type']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($record['service_date'])); ?></td>
                                            <td>KSH <?php echo number_format($record['cost']); ?></td>
                                            <td><?php echo htmlspecialchars($record['service_provider']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($record['status']) {
                                                        'Completed' => 'success',
                                                        'In Progress' => 'warning',
                                                        'Cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($record['status']); ?>
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
</body>
</html> 
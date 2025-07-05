<?php
require_once '../includes/auth.php';
session_start();
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../components/common/KpiCard.php';
require_once '../components/common/ChartCard.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Management - SDATIMS</title>
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
        <?php DashboardSidebar::render('drivers'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Driver Management',
                'Driver status,incidents and operational metrics'
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
                        <button @click="activeTab = 'incidents'" 
                                :class="{'border-primary text-primary': activeTab === 'incidents'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Incidents
                        </button>
                        <button @click="activeTab = 'assignments'" 
                                :class="{'border-primary text-primary': activeTab === 'assignments'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Vehicle Assignments
                        </button>
                    </nav>
                </div>

                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'">
                    <!-- Driver Status Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <?php
                        KpiCard::render('Active Drivers', $driverMetrics['total_active'], 'person');
                        KpiCard::render('Available', $driverMetrics['available'], 'person-check');
                        KpiCard::render('On Leave', $driverMetrics['on_leave'], 'calendar');
                        KpiCard::render('Suspended', $driverMetrics['suspended'], 'person-x');
                        ?>
                    </div>

                    <!-- License & Card Alerts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="dashboard-card">
                            <h3 class="flex items-center gap-2 mb-4">
                                <i class="bi bi-card-text"></i>
                                License Alerts
                            </h3>
                            <div class="space-y-4">
                                <?php foreach ($licenseAlerts as $alert): ?>
                                    <div class="p-4 <?php echo $alert['status'] === 'danger' ? 'bg-red-50' : 'bg-yellow-50'; ?> rounded-lg">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium"><?php echo htmlspecialchars($alert['driver_name']); ?></h4>
                                                <p class="text-sm text-gray-600">License: <?php echo htmlspecialchars($alert['license_number']); ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium <?php echo $alert['status'] === 'danger' ? 'text-red-600' : 'text-yellow-600'; ?>">
                                                    <?php echo $alert['days_remaining'] < 0 ? 'Expired' : $alert['days_remaining'] . ' days remaining'; ?>
                                                </p>
                                                <p class="text-sm text-gray-500">Expires: <?php echo date('Y-m-d', strtotime($alert['expiry_date'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="dashboard-card">
                            <h3 class="flex items-center gap-2 mb-4">
                                <i class="bi bi-credit-card"></i>
                                Card Alerts
                            </h3>
                            <div class="space-y-4">
                                <?php foreach ($cardAlerts as $alert): ?>
                                    <div class="p-4 bg-yellow-50 rounded-lg">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium"><?php echo htmlspecialchars($alert['driver_name']); ?></h4>
                                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($alert['card_type']); ?>: <?php echo htmlspecialchars($alert['card_number']); ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-yellow-600">
                                                    <?php echo date_diff(date_create($alert['expiry_date']), date_create('today'))->days; ?> days remaining
                                                </p>
                                                <p class="text-sm text-gray-500">Expires: <?php echo date('Y-m-d', strtotime($alert['expiry_date'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incidents Tab -->
                <div x-show="activeTab === 'incidents'">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="dashboard-card">
                            <h3 class="flex items-center gap-2 mb-4">
                                <i class="bi bi-exclamation-triangle"></i>
                                Recent Incidents
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Vehicle</th>
                                            <th>Severity</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($incidentReports as $incident): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($incident['driver_name']); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($incident['incident_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($incident['incident_type']); ?></td>
                                                <td><?php echo htmlspecialchars($incident['vehicle']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($incident['severity']) {
                                                            'Low' => 'success',
                                                            'Medium' => 'warning',
                                                            'High' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo htmlspecialchars($incident['severity']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($incident['status']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="dashboard-card">
                            <h3 class="flex items-center gap-2 mb-4">
                                <i class="bi bi-tools"></i>
                                Maintenance Reports
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th>Vehicle</th>
                                            <th>Issue Type</th>
                                            <th>Report Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenanceReports as $report): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($report['driver_name']); ?></td>
                                                <td><?php echo htmlspecialchars($report['vehicle']); ?></td>
                                                <td><?php echo htmlspecialchars($report['issue_type']); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($report['report_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($report['status']) {
                                                            'Pending' => 'warning',
                                                            'In Progress' => 'info',
                                                            'Completed' => 'success',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo htmlspecialchars($report['status']); ?>
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

                <!-- Vehicle Assignments Tab -->
                <div x-show="activeTab === 'assignments'">
                    <div class="dashboard-card">
                        <h3 class="flex items-center gap-2 mb-4">
                            <i class="bi bi-truck"></i>
                            Current Vehicle Assignments
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Vehicle</th>
                                        <th>Make/Model</th>
                                        <th>Assignment Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicleAssignments as $assignment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assignment['driver_name']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['vehicle']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['make_model']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($assignment['assignment_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($assignment['status']) {
                                                        'Active' => 'success',
                                                        'Pending' => 'warning',
                                                        'Inactive' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($assignment['status']); ?>
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
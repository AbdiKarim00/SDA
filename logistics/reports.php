<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once 'mock_data.php';

// Get mock data for reports
$vehicles = get_mock_data('vehicles');
$maintenance = get_mock_data('maintenance');
$incidents = get_mock_data('incidents');
$trips = get_mock_data('trips');
$fuel_cards = get_mock_data('fuel_cards');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
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
                'Reports Dashboard',
                'View and generate various reports'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Report Categories -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Vehicle Reports -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Reports</h3>
                        <div class="space-y-4">
                            <a href="vehicle-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-truck text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Vehicle Status Report</p>
                                    <p class="text-sm text-gray-500">View vehicle status and utilization</p>
                                </div>
                            </a>
                            <a href="maintenance-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-tools text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Maintenance Reports</p>
                                    <p class="text-sm text-gray-500">Track maintenance history and costs</p>
                                </div>
                            </a>
                            <a href="depreciation-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-graph-down text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Depreciation Reports</p>
                                    <p class="text-sm text-gray-500">View vehicle depreciation analysis</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Trip Reports -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Trip Reports</h3>
                        <div class="space-y-4">
                            <a href="trip-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-map text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Trip History</p>
                                    <p class="text-sm text-gray-500">View trip details and routes</p>
                                </div>
                            </a>
                            <a href="fuel-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-fuel-pump text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Fuel Usage Reports</p>
                                    <p class="text-sm text-gray-500">Track fuel consumption and costs</p>
                                </div>
                            </a>
                            <a href="mileage-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-speedometer2 text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Mileage Reports</p>
                                    <p class="text-sm text-gray-500">View vehicle mileage statistics</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Incident Reports -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Incident Reports</h3>
                        <div class="space-y-4">
                            <a href="incident-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-exclamation-triangle text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Incident Reports</p>
                                    <p class="text-sm text-gray-500">View incident history and analysis</p>
                                </div>
                            </a>
                            <a href="cost-reports.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-cash-stack text-xl text-primary mr-3"></i>
                                <div>
                                    <p class="font-medium">Cost Analysis</p>
                                    <p class="text-sm text-gray-500">View incident-related costs</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
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
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="bi bi-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Trips</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    <?php echo count(array_filter($trips, fn($t) => $t['status'] === 'In Progress')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="bi bi-tools text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Maintenance</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    <?php echo count(array_filter($maintenance, fn($m) => $m['status'] === 'Scheduled')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600">
                                <i class="bi bi-exclamation-triangle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Open Incidents</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    <?php echo count(array_filter($incidents, fn($i) => $i['status'] === 'Reported')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
<?php
session_start();
require_once '../includes/auth.php';
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../components/common/KpiCard.php';
require_once '../components/common/ChartCard.php';

// Mock data for vehicles
$vehicles = [
    [
        'id' => 1,
        'registration_number' => 'KAA 123A',
        'make' => 'Toyota',
        'model' => 'Hilux',
        'status' => 'Available',
        'next_service_due' => date('Y-m-d', strtotime('+5 days'))
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'make' => 'Isuzu',
        'model' => 'NPR',
        'status' => 'In Maintenance',
        'next_service_due' => date('Y-m-d', strtotime('+10 days'))
    ],
    [
        'id' => 3,
        'registration_number' => 'KAA 789C',
        'make' => 'Mitsubishi',
        'model' => 'Fuso',
        'status' => 'On Trip',
        'next_service_due' => date('Y-m-d', strtotime('+15 days'))
    ],
    [
        'id' => 4,
        'registration_number' => 'KAA 101D',
        'make' => 'Hino',
        'model' => '300',
        'status' => 'Available',
        'next_service_due' => date('Y-m-d', strtotime('+3 days'))
    ],
    [
        'id' => 5,
        'registration_number' => 'KAA 202E',
        'make' => 'Scania',
        'model' => 'G410',
        'status' => 'Available',
        'next_service_due' => date('Y-m-d', strtotime('+7 days'))
    ]
];

// Mock data for drivers
$drivers = [
    [
        'id' => 1,
        'name' => 'John Doe',
        'status' => 'Available'
    ],
    [
        'id' => 2,
        'name' => 'Jane Smith',
        'status' => 'On Trip'
    ]
];

// Calculate statistics
$total_vehicles = count($vehicles);
$available_vehicles = count(array_filter($vehicles, fn($v) => $v['status'] === 'Available'));
$maintenance_vehicles = count(array_filter($vehicles, fn($v) => $v['status'] === 'In Maintenance'));
$on_trip_vehicles = count(array_filter($vehicles, fn($v) => $v['status'] === 'On Trip'));

// Get recent vehicles (last 5)
$recent_vehicles = array_slice($vehicles, 0, 5);

// Get vehicles due for service (next 7 days)
$today = new DateTime();
$next_week = (clone $today)->modify('+7 days');
$service_due_vehicles = array_filter($vehicles, function($vehicle) use ($today, $next_week) {
    $service_date = new DateTime($vehicle['next_service_due']);
    return $service_date >= $today && $service_date <= $next_week;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Dashboard - Transport IMS</title>
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
        <?php DashboardSidebar::render('dashboard'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Vehicle Management',
                'Overview of vehicle fleet status and maintenance schedule'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <?php
                    KpiCard::render('Total Vehicles', $total_vehicles, 'truck');
                    KpiCard::render('Available', $available_vehicles, 'check-circle');
                    KpiCard::render('In Maintenance', $maintenance_vehicles, 'tools');
                    KpiCard::render('On Trip', $on_trip_vehicles, 'arrow-right-circle');
                    ?>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recent Vehicles -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b">
                            <h2 class="text-xl font-semibold text-gray-800">Recent Vehicles</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php foreach ($recent_vehicles as $vehicle): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['registration_number']); ?></h3>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></p>
                                        </div>
                                        <span class="px-3 py-1 text-sm rounded-full 
                                            <?php
                                            switch ($vehicle['status']) {
                                                case 'Available':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'In Maintenance':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'On Trip':
                                                    echo 'bg-purple-100 text-purple-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($vehicle['status']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Access Cards -->
                    <div class="space-y-6">
                        <!-- Service Due Soon -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b">
                                <h2 class="text-xl font-semibold text-gray-800">Service Due Soon</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <?php if (empty($service_due_vehicles)): ?>
                                        <p class="text-gray-500 text-center py-4">No vehicles due for service in the next 7 days</p>
                                    <?php else: ?>
                                        <?php foreach ($service_due_vehicles as $vehicle): ?>
                                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                                <div>
                                                    <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['registration_number']); ?></h3>
                                                    <p class="text-sm text-gray-500">Due: <?php echo date('M d, Y', strtotime($vehicle['next_service_due'])); ?></p>
                                                </div>
                                                <a href="edit-vehicle.php?id=<?php echo $vehicle['id']; ?>" 
                                                   class="text-primary hover:text-primary-dark">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Disposal Management -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b">
                                <h2 class="text-xl font-semibold text-gray-800">Disposal Management</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <h3 class="font-medium text-gray-900">Pending Disposals</h3>
                                            <p class="text-sm text-gray-500">Vehicles awaiting disposal approval</p>
                                        </div>
                                        <a href="disposal.php" class="text-primary hover:text-primary-dark">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <h3 class="font-medium text-gray-900">New Disposal Request</h3>
                                            <p class="text-sm text-gray-500">Initiate a new vehicle disposal process</p>
                                        </div>
                                        <a href="disposal-new.php" class="text-primary hover:text-primary-dark">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
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
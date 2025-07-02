<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for drivers
$drivers = [
    [
        'id' => 1,
        'personal_number' => 'EMP001',
        'name' => 'John Doe',
        'contact' => '+254 712 345 678',
        'license_number' => 'DL123456',
        'license_expiry' => '2024-12-31',
        'joining_date' => '2020-01-15',
        'department' => 'Transport Unit',
        'status' => 'Active',
        'current_vehicle' => 'KAA 123A',
        'fuel_card' => 'FC001'
    ],
    [
        'id' => 2,
        'personal_number' => 'EMP002',
        'name' => 'Jane Smith',
        'contact' => '+254 723 456 789',
        'license_number' => 'DL234567',
        'license_expiry' => '2024-06-30',
        'joining_date' => '2021-03-20',
        'department' => 'Logistics',
        'status' => 'On Leave',
        'current_vehicle' => null,
        'fuel_card' => 'FC002'
    ],
    [
        'id' => 3,
        'personal_number' => 'EMP003',
        'name' => 'Robert Johnson',
        'contact' => '+254 734 567 890',
        'license_number' => 'DL345678',
        'license_expiry' => '2025-03-15',
        'joining_date' => '2019-11-10',
        'department' => 'Transport Unit',
        'status' => 'Active',
        'current_vehicle' => 'KAA 456B',
        'fuel_card' => 'FC003'
    ]
];

// Get driver ID from URL
$driver_id = $_GET['id'] ?? null;

// Find the driver
$driver = null;
foreach ($drivers as $d) {
    if ($d['id'] == $driver_id) {
        $driver = $d;
        break;
    }
}

// If driver not found, redirect to drivers list
if (!$driver) {
    $_SESSION['error'] = 'Driver not found.';
    header('Location: drivers.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Details - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('drivers'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Driver Details',
                'View detailed information about the driver'
            ); ?>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <?php echo htmlspecialchars($driver['name']); ?>
                    </h2>
                    <div class="flex gap-4">
                        <a href="edit-driver.php?id=<?php echo $driver['id']; ?>" class="btn btn-secondary">
                            <i class="bi bi-pencil"></i>
                            Edit Driver
                        </a>
                        <a href="drivers.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Back to List
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Personal Number</label>
                            <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($driver['personal_number']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Contact Number</label>
                            <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($driver['contact']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Department</label>
                            <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($driver['department']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    switch ($driver['status']) {
                                        case 'Active':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'On Leave':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Suspended':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($driver['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">License Number</label>
                            <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($driver['license_number']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">License Expiry Date</label>
                            <p class="mt-1 text-gray-900"><?php echo date('M d, Y', strtotime($driver['license_expiry'])); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date of Joining</label>
                            <p class="mt-1 text-gray-900"><?php echo date('M d, Y', strtotime($driver['joining_date'])); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Current Vehicle</label>
                            <p class="mt-1">
                                <?php if ($driver['current_vehicle']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($driver['current_vehicle']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500">Not Assigned</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Fuel Card</label>
                            <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($driver['fuel_card']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Assignment History Section -->
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Assignment History</h3>
                        <a href="assignment-history.php?driver=<?php echo urlencode($driver['name']); ?>" 
                           class="text-sm text-blue-600 hover:text-blue-900">
                            View All History
                        </a>
                    </div>
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            <?php
                            // Mock assignment history data for this driver
                            $driver_assignments = [
                                [
                                    'id' => 1,
                                    'vehicle_reg' => 'KAA 123A',
                                    'assignment_type' => 'Primary Deployment',
                                    'start_location' => 'CMTE Grounds',
                                    'end_location' => 'Cabinet Secretary Office',
                                    'start_date' => '2024-03-01',
                                    'end_date' => '2024-03-15',
                                    'status' => 'Completed'
                                ],
                                [
                                    'id' => 2,
                                    'vehicle_reg' => 'KAA 456B',
                                    'assignment_type' => 'Temporary Assignment',
                                    'start_location' => 'Makindu Police Station',
                                    'end_location' => 'Athi River Police Station',
                                    'start_date' => '2024-03-10',
                                    'end_date' => null,
                                    'status' => 'Active'
                                ]
                            ];

                            foreach ($driver_assignments as $assignment):
                            ?>
                            <li>
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <p class="text-sm font-medium text-blue-600 truncate">
                                                <?php echo htmlspecialchars($assignment['vehicle_reg']); ?>
                                            </p>
                                            <div class="ml-2 flex-shrink-0 flex">
                                                <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $assignment['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                    <?php echo $assignment['status']; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="ml-2 flex-shrink-0 flex">
                                            <a href="assignment-details.php?id=<?php echo $assignment['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-2 sm:flex sm:justify-between">
                                        <div class="sm:flex">
                                            <p class="flex items-center text-sm text-gray-500">
                                                <i class="bi bi-geo-alt mr-1.5"></i>
                                                <?php echo htmlspecialchars($assignment['start_location']); ?> →
                                                <?php echo htmlspecialchars($assignment['end_location']); ?>
                                            </p>
                                        </div>
                                        <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                            <i class="bi bi-calendar mr-1.5"></i>
                                            <?php echo date('M d, Y', strtotime($assignment['start_date'])); ?>
                                            <?php if ($assignment['end_date']): ?>
                                                - <?php echo date('M d, Y', strtotime($assignment['end_date'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
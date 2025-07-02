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

// Mock data for available vehicles
$available_vehicles = [
    ['id' => 1, 'registration' => 'KAA 789C', 'type' => 'Sedan'],
    ['id' => 2, 'registration' => 'KAA 012D', 'type' => 'SUV'],
    ['id' => 3, 'registration' => 'KAA 345E', 'type' => 'Van']
];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_driver':
            // Validate required fields
            $required_fields = ['personal_number', 'name', 'contact', 'license_number', 'license_expiry', 'joining_date', 'department', 'status'];
            $missing_fields = array_filter($required_fields, function($field) {
                return empty($_POST[$field]);
            });
            
            if (!empty($missing_fields)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Please fill in all required fields: ' . implode(', ', $missing_fields)
                ]);
                exit;
            }
            
            // Simulate adding new driver
            $new_driver = [
                'id' => count($drivers) + 1,
                'personal_number' => $_POST['personal_number'],
                'name' => $_POST['name'],
                'contact' => $_POST['contact'],
                'license_number' => $_POST['license_number'],
                'license_expiry' => $_POST['license_expiry'],
                'joining_date' => $_POST['joining_date'],
                'department' => $_POST['department'],
                'status' => $_POST['status'],
                'current_vehicle' => null,
                'fuel_card' => 'FC' . str_pad(count($drivers) + 1, 3, '0', STR_PAD_LEFT)
            ];
            
            $drivers[] = $new_driver;
            
            echo json_encode([
                'success' => true,
                'message' => 'Driver added successfully'
            ]);
            exit;
            
        case 'assign_vehicle':
            if (empty($_POST['driver_id']) || empty($_POST['vehicle_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Driver ID and Vehicle ID are required'
                ]);
                exit;
            }
            
            // Find the vehicle
            $vehicle = array_filter($available_vehicles, function($v) {
                return $v['id'] == $_POST['vehicle_id'];
            });
            
            if (empty($vehicle)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Selected vehicle not found'
                ]);
                exit;
            }
            
            $vehicle = reset($vehicle);
            
            // Simulate assigning vehicle
            foreach ($drivers as &$driver) {
                if ($driver['id'] == $_POST['driver_id']) {
                    $driver['current_vehicle'] = $vehicle['registration'];
                    break;
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Vehicle assigned successfully'
            ]);
            exit;
            
        case 'change_status':
            if (empty($_POST['driver_id']) || empty($_POST['status'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Driver ID and Status are required'
                ]);
                exit;
            }
            
            // Simulate changing status
            foreach ($drivers as &$driver) {
                if ($driver['id'] == $_POST['driver_id']) {
                    $driver['status'] = $_POST['status'];
                    break;
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Driver status updated successfully'
            ]);
            exit;
    }
}

// Handle driver status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $driver_id = $_POST['driver_id'];
    $new_status = $_POST['new_status'];
    // In a real application, this would update the database
    // For mock data, we'll just simulate the action
    $success = true;
    if ($success) {
        $_SESSION['success'] = 'Driver status has been updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update driver status.';
    }
    header('Location: drivers.php');
    exit;
}

// Filter drivers
$status_filter = $_GET['status'] ?? '';
$department_filter = $_GET['department'] ?? '';
$expiry_filter = $_GET['expiry'] ?? '';
$search = $_GET['search'] ?? '';

$filtered_drivers = array_filter($drivers, function($driver) use ($status_filter, $department_filter, $expiry_filter, $search) {
    $matches_status = empty($status_filter) || $driver['status'] === $status_filter;
    $matches_department = empty($department_filter) || $driver['department'] === $department_filter;
    $matches_search = empty($search) || 
        stripos($driver['personal_number'], $search) !== false ||
        stripos($driver['name'], $search) !== false ||
        stripos($driver['contact'], $search) !== false;
    
    return $matches_status && $matches_department && $matches_search;
});

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="drivers.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, [
        'Personal Number',
        'Name',
        'Contact',
        'License Number',
        'License Expiry',
        'Department',
        'Status',
        'Current Vehicle',
        'Fuel Card'
    ]);
    
    // Add data
    foreach ($filtered_drivers as $driver) {
        fputcsv($output, [
            $driver['personal_number'],
            $driver['name'],
            $driver['contact'],
            $driver['license_number'],
            $driver['license_expiry'],
            $driver['department'],
            $driver['status'],
            $driver['current_vehicle'] ?? 'Not Assigned',
            $driver['fuel_card']
        ]);
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Management - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        showFilters: false,
        showAddDriverModal: false,
        showAssignVehicleModal: false,
        showChangeStatusModal: false,
        selectedDriverId: null
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('drivers'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Driver Management',
                'Manage and track all drivers in the system'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Filters and Actions -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <button @click="showFilters = !showFilters" 
                                        class="btn btn-secondary">
                                    <i class="bi bi-funnel"></i>
                                    Filters
                                </button>
                                <div class="relative">
                                    <input type="text" 
                                           name="search" 
                                           placeholder="Search drivers..." 
                                           class="form-input pl-10"
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <a href="?export=csv" class="btn btn-secondary">
                                    <i class="bi bi-download"></i>
                                    Export CSV
                                </a>
                                <button @click="showAddDriverModal = true" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i>
                                    Add New Driver
                                </button>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form x-show="showFilters" 
                              x-transition
                              class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4"
                              method="GET">
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="On Leave" <?php echo $status_filter === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                                    <option value="Suspended" <?php echo $status_filter === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Department</label>
                                <select name="department" class="form-select">
                                    <option value="">All Departments</option>
                                    <option value="Transport Unit" <?php echo $department_filter === 'Transport Unit' ? 'selected' : ''; ?>>Transport Unit</option>
                                    <option value="Logistics" <?php echo $department_filter === 'Logistics' ? 'selected' : ''; ?>>Logistics</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">License Expiry</label>
                                <select name="expiry" class="form-select">
                                    <option value="">All</option>
                                    <option value="30" <?php echo $expiry_filter === '30' ? 'selected' : ''; ?>>Expiring in 30 days</option>
                                    <option value="60" <?php echo $expiry_filter === '60' ? 'selected' : ''; ?>>Expiring in 60 days</option>
                                    <option value="90" <?php echo $expiry_filter === '90' ? 'selected' : ''; ?>>Expiring in 90 days</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="btn btn-primary w-full">
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Drivers Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Personal No.
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        License No.
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        License Expiry
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Current Vehicle
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($filtered_drivers)): ?>
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                            No drivers found matching your criteria.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($filtered_drivers as $driver): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($driver['personal_number']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($driver['name']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($driver['contact']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($driver['license_number']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($driver['license_expiry'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($driver['department']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($driver['current_vehicle']): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        <?php echo htmlspecialchars($driver['current_vehicle']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-500">Not Assigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <a href="driver-details.php?id=<?php echo $driver['id']; ?>" 
                                                       class="text-gray-600 hover:text-gray-900">
                                                        <i class="bi bi-eye text-lg"></i>
                                                    </a>
                                                    <a href="edit-driver.php?id=<?php echo $driver['id']; ?>"
                                                       class="text-gray-600 hover:text-gray-900">
                                                        <i class="bi bi-pencil text-lg"></i>
                                                    </a>
                                                    <a href="assign-vehicle.php?id=<?php echo $driver['id']; ?>"
                                                       class="text-gray-600 hover:text-gray-900">
                                                        <i class="bi bi-truck text-lg"></i>
                                                    </a>
                                                    <a href="change-status.php?id=<?php echo $driver['id']; ?>"
                                                       class="text-gray-600 hover:text-gray-900">
                                                        <i class="bi bi-gear text-lg"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div x-show="showAddDriverModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Driver</h3>
                            <div class="mt-4">
                                <form id="addDriverForm" class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="form-label">Personal Number</label>
                                            <input type="text" class="form-input" name="personal_number" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-input" name="name" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Contact Number</label>
                                            <input type="tel" class="form-input" name="contact" required>
                                        </div>
                                        <div>
                                            <label class="form-label">License Number</label>
                                            <input type="text" class="form-input" name="license_number" required>
                                        </div>
                                        <div>
                                            <label class="form-label">License Expiry Date</label>
                                            <input type="date" class="form-input" name="license_expiry" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Date of Joining</label>
                                            <input type="date" class="form-input" name="joining_date" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Department</label>
                                            <select class="form-select" name="department" required>
                                                <option value="">Select Department</option>
                                                <option value="Transport Unit">Transport Unit</option>
                                                <option value="Logistics">Logistics</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label">Initial Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="Active">Active</option>
                                                <option value="On Leave">On Leave</option>
                                                <option value="Suspended">Suspended</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            class="btn btn-primary"
                            @click="submitDriver()">
                        Add Driver
                    </button>
                    <button type="button" 
                            class="btn btn-secondary"
                            @click="showAddDriverModal = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Vehicle Modal -->
    <div x-show="showAssignVehicleModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Assign Vehicle</h3>
                            <div class="mt-4">
                                <form id="assignVehicleForm" class="space-y-4">
                                    <input type="hidden" name="driver_id" :value="selectedDriverId">
                                    <div>
                                        <label class="form-label">Select Vehicle</label>
                                        <select class="form-select" name="vehicle_id" required>
                                            <option value="">Select Vehicle</option>
                                            <?php foreach ($available_vehicles as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>">
                                                <?php echo htmlspecialchars($vehicle['registration'] . ' (' . $vehicle['type'] . ')'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            class="btn btn-primary"
                            @click="submitAssignment()">
                        Assign Vehicle
                    </button>
                    <button type="button" 
                            class="btn btn-secondary"
                            @click="showAssignVehicleModal = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div x-show="showChangeStatusModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Change Driver Status</h3>
                            <div class="mt-4">
                                <form id="changeStatusForm" class="space-y-4">
                                    <input type="hidden" name="driver_id" :value="selectedDriverId">
                                    <div>
                                        <label class="form-label">New Status</label>
                                        <select class="form-select" name="status" required>
                                            <option value="Active">Active</option>
                                            <option value="On Leave">On Leave</option>
                                            <option value="Suspended">Suspended</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Reason (Optional)</label>
                                        <textarea class="form-input" name="reason" rows="3"></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            class="btn btn-primary"
                            @click="submitStatusChange()">
                        Update Status
                    </button>
                    <button type="button" 
                            class="btn btn-secondary"
                            @click="showChangeStatusModal = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View driver details
        function viewDriver(id) {
            window.location.href = `driver-details.php?id=${id}`;
        }

        // Edit driver details
        function editDriver(id) {
            window.location.href = `edit-driver.php?id=${id}`;
        }

        // Submit new driver
        function submitDriver() {
            const form = document.getElementById('addDriverForm');
            const formData = new FormData(form);
            formData.append('action', 'add_driver');
            
            fetch('drivers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error adding driver');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding driver');
            });
        }

        // Submit vehicle assignment
        function submitAssignment() {
            const form = document.getElementById('assignVehicleForm');
            const formData = new FormData(form);
            formData.append('action', 'assign_vehicle');
            
            fetch('drivers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error assigning vehicle');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error assigning vehicle');
            });
        }

        // Submit status change
        function submitStatusChange() {
            const form = document.getElementById('changeStatusForm');
            const formData = new FormData(form);
            formData.append('action', 'change_status');
            
            fetch('drivers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error updating status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html> 
<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for vehicles
$vehicles = [
    [
        'id' => 1,
        'registration_number' => 'KAA 123A',
        'make' => 'Toyota',
        'model' => 'Hilux',
        'chassis_no' => 'JTF1J10P1N1234567',
        'engine_no' => '2GD-1234567',
        'vehicle_type' => 'Truck',
        'capacity' => '1.5 tons',
        'fuel_type' => 'Diesel',
        'purchase_date' => '2022-01-15',
        'funded_by' => 'Government',
        'initial_mileage' => 0,
        'current_mileage' => 25000,
        'status' => 'Available',
        'current_driver' => 'John Doe',
        'next_service_due' => date('Y-m-d', strtotime('+5 days')),
        'insurance_expiry' => date('Y-m-d', strtotime('+3 months')),
        'road_permit_expiry' => date('Y-m-d', strtotime('+6 months'))
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'make' => 'Isuzu',
        'model' => 'NPR',
        'chassis_no' => 'JTF1J10P1N7654321',
        'engine_no' => '4HK-7654321',
        'vehicle_type' => 'Truck',
        'capacity' => '3 tons',
        'fuel_type' => 'Diesel',
        'purchase_date' => '2021-06-20',
        'funded_by' => 'Private',
        'initial_mileage' => 0,
        'current_mileage' => 45000,
        'status' => 'In Maintenance',
        'current_driver' => 'Jane Smith',
        'next_service_due' => date('Y-m-d', strtotime('+10 days')),
        'insurance_expiry' => date('Y-m-d', strtotime('+2 months')),
        'road_permit_expiry' => date('Y-m-d', strtotime('+5 months'))
    ],
    [
        'id' => 3,
        'registration_number' => 'KAA 789C',
        'make' => 'Mitsubishi',
        'model' => 'Fuso',
        'chassis_no' => 'JTF1J10P1N9876543',
        'engine_no' => '6M70-9876543',
        'vehicle_type' => 'Truck',
        'capacity' => '5 tons',
        'fuel_type' => 'Diesel',
        'purchase_date' => '2023-03-10',
        'funded_by' => 'Government',
        'initial_mileage' => 0,
        'current_mileage' => 15000,
        'status' => 'On Trip',
        'current_driver' => 'Mike Johnson',
        'next_service_due' => date('Y-m-d', strtotime('+15 days')),
        'insurance_expiry' => date('Y-m-d', strtotime('+9 months')),
        'road_permit_expiry' => date('Y-m-d', strtotime('+12 months'))
    ]
];

// Handle vehicle decommissioning
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decommission'])) {
    $vehicle_id = $_POST['vehicle_id'];
    // In a real application, this would update the database
    // For mock data, we'll just simulate the action
    $success = true;
    if ($success) {
        $_SESSION['success'] = 'Vehicle has been decommissioned successfully.';
    } else {
        $_SESSION['error'] = 'Failed to decommission vehicle.';
    }
    header('Location: vehicles.php');
    exit;
}

// Filter vehicles
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$funded_by_filter = $_GET['funded_by'] ?? '';
$search = $_GET['search'] ?? '';

$filtered_vehicles = array_filter($vehicles, function($vehicle) use ($status_filter, $type_filter, $funded_by_filter, $search) {
    $matches_status = empty($status_filter) || $vehicle['status'] === $status_filter;
    $matches_type = empty($type_filter) || $vehicle['vehicle_type'] === $type_filter;
    $matches_funded_by = empty($funded_by_filter) || $vehicle['funded_by'] === $funded_by_filter;
    $matches_search = empty($search) || 
        stripos($vehicle['registration_number'], $search) !== false ||
        stripos($vehicle['make'], $search) !== false ||
        stripos($vehicle['model'], $search) !== false;
    
    return $matches_status && $matches_type && $matches_funded_by && $matches_search;
});

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="vehicles.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, [
        'Registration Number',
        'Make',
        'Model',
        'Type',
        'Status',
        'Current Driver',
        'Next Service Due',
        'Insurance Expiry',
        'Road Permit Expiry',
        'Funded By'
    ]);
    
    // Add data
    foreach ($filtered_vehicles as $vehicle) {
        fputcsv($output, [
            $vehicle['registration_number'],
            $vehicle['make'],
            $vehicle['model'],
            $vehicle['vehicle_type'],
            $vehicle['status'],
            $vehicle['current_driver'],
            $vehicle['next_service_due'],
            $vehicle['insurance_expiry'],
            $vehicle['road_permit_expiry'],
            $vehicle['funded_by']
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
    <title>Vehicle Management - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        showFilters: false
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('vehicles'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Vehicle Management',
                'Manage and track all vehicles in the fleet'
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
                                           placeholder="Search vehicles..." 
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
                            <a href="add-vehicle.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                Add Vehicle
                            </a>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form x-show="showFilters" 
                              x-transition
                              class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="Available" <?php echo $status_filter === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="In Maintenance" <?php echo $status_filter === 'In Maintenance' ? 'selected' : ''; ?>>In Maintenance</option>
                                    <option value="On Trip" <?php echo $status_filter === 'On Trip' ? 'selected' : ''; ?>>On Trip</option>
                                    <option value="Decommissioned" <?php echo $status_filter === 'Decommissioned' ? 'selected' : ''; ?>>Decommissioned</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Vehicle Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="Car" <?php echo $type_filter === 'Car' ? 'selected' : ''; ?>>Car</option>
                                    <option value="Van" <?php echo $type_filter === 'Van' ? 'selected' : ''; ?>>Van</option>
                                    <option value="Truck" <?php echo $type_filter === 'Truck' ? 'selected' : ''; ?>>Truck</option>
                                    <option value="Bus" <?php echo $type_filter === 'Bus' ? 'selected' : ''; ?>>Bus</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Funded By</label>
                                <select name="funded_by" class="form-select">
                                    <option value="">All Sources</option>
                                    <option value="Government" <?php echo $funded_by_filter === 'Government' ? 'selected' : ''; ?>>Government</option>
                                    <option value="Private" <?php echo $funded_by_filter === 'Private' ? 'selected' : ''; ?>>Private</option>
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

                <!-- Vehicles Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registration
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Make & Model
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Current Driver
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Next Service
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Insurance Expiry
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Funded By
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($filtered_vehicles)): ?>
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                            No vehicles found matching your criteria.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($filtered_vehicles as $vehicle): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($vehicle['registration_number']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($vehicle['vehicle_type']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
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
                                                        case 'Decommissioned':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>">
                                                    <?php echo htmlspecialchars($vehicle['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($vehicle['current_driver']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($vehicle['next_service_due'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($vehicle['insurance_expiry'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($vehicle['funded_by']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <a href="vehicle-details.php?id=<?php echo $vehicle['id']; ?>" 
                                                       class="text-primary hover:text-primary-dark"
                                                       title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit-vehicle.php?id=<?php echo $vehicle['id']; ?>" 
                                                       class="text-primary hover:text-primary-dark"
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="text-primary hover:text-primary-dark"
                                                            title="Change Status"
                                                            onclick="changeStatus(<?php echo $vehicle['id']; ?>)">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="text-primary hover:text-primary-dark"
                                                            title="Assign Driver"
                                                            onclick="assignDriver(<?php echo $vehicle['id']; ?>)">
                                                        <i class="bi bi-person-plus"></i>
                                                    </button>
                                                    <?php if ($vehicle['status'] !== 'Decommissioned'): ?>
                                                        <form method="POST" class="inline" 
                                                              onsubmit="return confirm('Are you sure you want to decommission this vehicle?');">
                                                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                            <button type="submit" name="decommission" 
                                                                    class="text-red-600 hover:text-red-900"
                                                                    title="Decommission">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
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

    <script>
    function changeStatus(vehicleId) {
        // Implement status change modal/form
        alert('Status change functionality to be implemented');
    }

    function assignDriver(vehicleId) {
        // Implement driver assignment modal/form
        alert('Driver assignment functionality to be implemented');
    }
    </script>
</body>
</html> 
<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// require_once 'mock_data.php'; // Mock data system removed

// Get task ID from query string
$task_id = $_GET['id'] ?? null;
if (!$task_id) {
    header('Location: maintenance.php');
    exit;
}

// Get mock data
// $maintenance = get_mock_data('maintenance'); // Mock data system removed
$maintenance = []; // Placeholder
// $vehicles = get_mock_data('vehicles'); // Mock data system removed
$vehicles = []; // Placeholder

// Find the maintenance task
$task = null;
foreach ($maintenance as $m) {
    if ($m['id'] == $task_id) {
        $task = $m;
        break;
    }
}

if (!$task) {
    header('Location: maintenance.php');
    exit;
}

// Find the vehicle
$vehicle = null;
foreach ($vehicles as $v) {
    if ($v['id'] == $task['vehicle_id']) {
        $vehicle = $v;
        break;
    }
}

// Mock technicians
$technicians = [
    ['id' => 1, 'name' => 'John Smith', 'email' => 'john.smith@example.com'],
    ['id' => 2, 'name' => 'Sarah Johnson', 'email' => 'sarah.j@example.com'],
    ['id' => 3, 'name' => 'Mike Brown', 'email' => 'mike.b@example.com']
];

// Mock maintenance history
$history = [
    [
        'id' => 1,
        'maintenance_task_id' => $task_id,
        'action' => 'Task created',
        'notes' => 'Maintenance task created for scheduled service',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'created_by' => 'System'
    ],
    [
        'id' => 2,
        'maintenance_task_id' => $task_id,
        'action' => 'Technician assigned',
        'notes' => 'Assigned to John Smith',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'created_by' => 'Admin'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Details - Transport IMS</title>
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
        <?php DashboardSidebar::render('maintenance'); ?>
    
        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Maintenance Details',
                'View and manage maintenance task details'
            ); ?>
            
            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <div class="flex justify-between items-center mb-6">
                    <a href="maintenance.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    <?php if ($task['status'] === 'Scheduled'): ?>
                        <div class="space-x-2">
                            <button type="button" class="btn btn-warning" onclick="editTask()">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteTask()">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                        <?php endif; ?>
                </div>
                
                <!-- Task Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Task Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Task Information</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                        <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo match($task['status']) {
                                            'Completed' => 'bg-green-100 text-green-800',
                                            'In Progress' => 'bg-blue-100 text-blue-800',
                                            'Scheduled' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        }; ?>">
                                        <?php echo $task['status']; ?>
                                            </span>
                                        </div>
                                        <div>
                                    <p class="text-sm font-medium text-gray-500">Service Type</p>
                                    <p class="text-sm text-gray-900"><?php echo $task['service_type']; ?></p>
                                </div>
                                    </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Description</p>
                                <p class="text-sm text-gray-900"><?php echo $task['description']; ?></p>
                                    </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Service Date</p>
                                    <p class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($task['service_date'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Next Service Due</p>
                                    <p class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($task['next_service_date'])); ?></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                        <div>
                                    <p class="text-sm font-medium text-gray-500">Cost</p>
                                    <p class="text-sm text-gray-900">$<?php echo number_format($task['cost'], 2); ?></p>
                                    </div>
                                        <div>
                                    <p class="text-sm font-medium text-gray-500">Service Provider</p>
                                    <p class="text-sm text-gray-900"><?php echo $task['service_provider']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vehicle Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Registration Number</p>
                                    <p class="text-sm text-gray-900"><?php echo $vehicle['registration_number']; ?></p>
                                </div>
                                        <div>
                                    <p class="text-sm font-medium text-gray-500">Make/Model</p>
                                    <p class="text-sm text-gray-900"><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Vehicle Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo match($vehicle['status']) {
                                            'Active' => 'bg-green-100 text-green-800',
                                            'Maintenance' => 'bg-yellow-100 text-yellow-800',
                                            'Inactive' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        }; ?>">
                                        <?php echo $vehicle['status']; ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Current Mileage</p>
                                    <p class="text-sm text-gray-900"><?php echo number_format($vehicle['current_mileage']); ?> km</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance History -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Maintenance History</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <?php foreach ($history as $index => $item): ?>
                                    <li>
                                        <div class="relative pb-8">
                                            <?php if ($index !== count($history) - 1): ?>
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="bi bi-clock-history text-white"></i>
                                                </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            <?php echo $item['action']; ?>
                                                            <span class="font-medium text-gray-900"><?php echo $item['notes']; ?></span>
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <time datetime="<?php echo $item['created_at']; ?>">
                                                            <?php echo date('M d, Y H:i', strtotime($item['created_at'])); ?>
                                                        </time>
                                                    </div>
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
    </div>
    
    <script>
        function editTask() {
            // TODO: Implement edit functionality
            alert('Edit functionality will be implemented soon');
    }
    
        function deleteTask() {
        if (confirm('Are you sure you want to delete this maintenance task?')) {
                // TODO: Implement delete functionality
                alert('Delete functionality will be implemented soon');
            }
        }
    </script>
</body>
</html> 
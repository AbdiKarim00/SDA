<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Get card ID from URL
$card_id = $_GET['id'] ?? null;
if (!$card_id) {
    header('Location: cards.php');
    exit;
}

// Mock data for a single card
$card = [
    'id' => 1,
    'card_number' => 'FC001',
    'type' => 'Fuel',
    'issued_to' => 'John Doe',
    'allocation_date' => '2024-01-01',
    'expiry_date' => '2024-12-31',
    'status' => 'Active',
    'current_liters' => 450,
    'max_liters' => 500
];

// Mock data for assignment history
$assignment_history = [
    [
        'driver_name' => 'John Doe',
        'start_date' => '2024-01-01',
        'end_date' => null,
        'status' => 'Current'
    ],
    [
        'driver_name' => 'Jane Smith',
        'start_date' => '2023-06-01',
        'end_date' => '2023-12-31',
        'status' => 'Completed'
    ]
];

// Mock data for status changes
$status_changes = [
    [
        'date' => '2024-01-01',
        'old_status' => 'Inactive',
        'new_status' => 'Active',
        'changed_by' => 'Admin User'
    ],
    [
        'date' => '2023-12-15',
        'old_status' => 'Active',
        'new_status' => 'Inactive',
        'changed_by' => 'Admin User'
    ]
];

// Mock data for usage history
$usage_history = [
    [
        'date' => '2024-02-20',
        'liters' => 50,
        'location' => 'Shell Station, Nairobi',
        'driver' => 'John Doe'
    ],
    [
        'date' => '2024-02-15',
        'liters' => 45,
        'location' => 'Total Station, Mombasa',
        'driver' => 'John Doe'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Details - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        activeTab: 'info'
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('cards'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Card Details',
                'View and manage card information'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Card Header -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    Card <?php echo htmlspecialchars($card['card_number']); ?>
                                </h2>
                                <p class="text-gray-600">
                                    Type: <?php echo htmlspecialchars($card['type']); ?>
                                </p>
                            </div>
                            <div class="flex gap-4">
                                <button type="button"
                                        class="btn btn-secondary"
                                        onclick="changeStatus(<?php echo $card['id']; ?>)">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Change Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'info'"
                                    :class="{'border-primary text-primary': activeTab === 'info'}"
                                    class="px-6 py-4 border-b-2 font-medium text-sm">
                                Card Information
                            </button>
                            <button @click="activeTab = 'assignments'"
                                    :class="{'border-primary text-primary': activeTab === 'assignments'}"
                                    class="px-6 py-4 border-b-2 font-medium text-sm">
                                Assignment History
                            </button>
                            <button @click="activeTab = 'status'"
                                    :class="{'border-primary text-primary': activeTab === 'status'}"
                                    class="px-6 py-4 border-b-2 font-medium text-sm">
                                Status Changes
                            </button>
                            <button @click="activeTab = 'usage'"
                                    :class="{'border-primary text-primary': activeTab === 'usage'}"
                                    class="px-6 py-4 border-b-2 font-medium text-sm">
                                Usage History
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Card Information -->
                        <div x-show="activeTab === 'info'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Card Details</h3>
                                    <dl class="grid grid-cols-1 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Card Number</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($card['card_number']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($card['type']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Current Status</dt>
                                            <dd class="mt-1">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php
                                                    switch ($card['status']) {
                                                        case 'Active':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'Inactive':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'Expired':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>">
                                                    <?php echo htmlspecialchars($card['status']); ?>
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Current Usage</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <?php echo $card['current_liters']; ?> / <?php echo $card['max_liters']; ?> L
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment Details</h3>
                                    <dl class="grid grid-cols-1 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Issued To</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($card['issued_to']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Allocation Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo date('M d, Y', strtotime($card['allocation_date'])); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo date('M d, Y', strtotime($card['expiry_date'])); ?></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment History -->
                        <div x-show="activeTab === 'assignments'">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($assignment_history as $assignment): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($assignment['driver_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($assignment['start_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo $assignment['end_date'] ? date('M d, Y', strtotime($assignment['end_date'])) : 'Present'; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php echo $assignment['status'] === 'Current' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                        <?php echo htmlspecialchars($assignment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Status Changes -->
                        <div x-show="activeTab === 'status'">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Old Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Changed By</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($status_changes as $change): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($change['date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        <?php echo htmlspecialchars($change['old_status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <?php echo htmlspecialchars($change['new_status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($change['changed_by']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Usage History -->
                        <div x-show="activeTab === 'usage'">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Liters</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($usage_history as $usage): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($usage['date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo $usage['liters']; ?> L
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($usage['location']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($usage['driver']); ?>
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
    </div>

    <script>
    function changeStatus(cardId) {
        // Implement status change modal/form
        alert('Status change functionality to be implemented');
    }
    </script>
</body>
</html> 
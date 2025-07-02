<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for fuel cards
$cards = [
    [
        'id' => 1,
        'card_number' => 'FC001',
        'type' => 'Fuel',
        'fuel_type' => 'Diesel',
        'service_provider' => 'Shell',
        'issued_to' => 'John Doe',
        'allocation_date' => '2024-01-01',
        'expiry_date' => '2024-12-31',
        'status' => 'Active',
        'current_liters' => 450,
        'max_liters' => 500
    ],
    [
        'id' => 2,
        'card_number' => 'GC001',
        'type' => 'GENERAL CARDS',
        'service_provider' => 'Rubis',
        'issued_to' => 'Service Department',
        'allocation_date' => '2024-01-15',
        'expiry_date' => '2024-12-31',
        'status' => 'Active',
        'current_liters' => 0,
        'max_liters' => 1000
    ],
    [
        'id' => 3,
        'card_number' => 'PE001',
        'type' => 'PLANTS & EQUIPMENT',
        'service_provider' => 'Total',
        'issued_to' => 'Equipment Department',
        'allocation_date' => '2024-02-01',
        'expiry_date' => '2024-12-31',
        'status' => 'Active',
        'current_liters' => 200,
        'max_liters' => 500
    ]
];

// Handle card status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $card_id = $_POST['card_id'];
    $new_status = $_POST['new_status'];
    // In a real application, this would update the database
    // For mock data, we'll just simulate the action
    $success = true;
    if ($success) {
        $_SESSION['success'] = 'Card status has been updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update card status.';
    }
    header('Location: cards.php');
    exit;
}

// Filter cards
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$provider_filter = $_GET['provider'] ?? '';
$search = $_GET['search'] ?? '';

$filtered_cards = array_filter($cards, function($card) use ($status_filter, $type_filter, $provider_filter, $search) {
    $matches_status = empty($status_filter) || $card['status'] === $status_filter;
    $matches_type = empty($type_filter) || $card['type'] === $type_filter;
    $matches_provider = empty($provider_filter) || $card['service_provider'] === $provider_filter;
    $matches_search = empty($search) || 
        stripos($card['card_number'], $search) !== false ||
        stripos($card['issued_to'], $search) !== false ||
        stripos($card['service_provider'], $search) !== false;
    
    return $matches_status && $matches_type && $matches_provider && $matches_search;
});

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="cards.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, [
        'Card Number',
        'Type',
        'Fuel Type',
        'Service Provider',
        'Issued To',
        'Allocation Date',
        'Expiry Date',
        'Status',
        'Current Liters',
        'Max Liters'
    ]);
    
    // Add data
    foreach ($filtered_cards as $card) {
        fputcsv($output, [
            $card['card_number'],
            $card['type'],
            $card['fuel_type'] ?? 'N/A',
            $card['service_provider'],
            $card['issued_to'],
            $card['allocation_date'],
            $card['expiry_date'],
            $card['status'],
            $card['current_liters'],
            $card['max_liters']
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
    <title>Fuel Card Management - Transport IMS</title>
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
        <?php DashboardSidebar::render('cards'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Fuel Card Management',
                'Manage and track all fuel cards in the system'
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
                                           placeholder="Search cards..." 
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
                                <a href="allocate-card.php" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i>
                                    Allocate New Card
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
                                    <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="Expired" <?php echo $status_filter === 'Expired' ? 'selected' : ''; ?>>Expired</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Card Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="Fuel" <?php echo $type_filter === 'Fuel' ? 'selected' : ''; ?>>Fuel</option>
                                    <option value="GENERAL CARDS" <?php echo $type_filter === 'GENERAL CARDS' ? 'selected' : ''; ?>>GENERAL CARDS</option>
                                    <option value="PLANTS & EQUIPMENT" <?php echo $type_filter === 'PLANTS & EQUIPMENT' ? 'selected' : ''; ?>>PLANTS & EQUIPMENT</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Service Provider</label>
                                <input type="text" 
                                       name="provider" 
                                       class="form-input" 
                                       value="<?php echo htmlspecialchars($provider_filter); ?>"
                                       placeholder="e.g., Shell, Rubis">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="btn btn-primary w-full">
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Cards Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Card Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fuel Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Service Provider
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Issued To
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Allocation Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expiry Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Current Liters
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($filtered_cards)): ?>
                                    <tr>
                                        <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                                            No cards found matching your criteria.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($filtered_cards as $card): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($card['card_number']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($card['type']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo isset($card['fuel_type']) ? htmlspecialchars($card['fuel_type']) : 'N/A'; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($card['service_provider']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($card['issued_to']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($card['allocation_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($card['expiry_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $card['current_liters']; ?> / <?php echo $card['max_liters']; ?> L
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <a href="card-details.php?id=<?php echo $card['id']; ?>" 
                                                       class="text-primary hover:text-primary-dark"
                                                       title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="text-primary hover:text-primary-dark"
                                                            title="Change Status"
                                                            onclick="changeStatus(<?php echo $card['id']; ?>)">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
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
    function changeStatus(cardId) {
        // Implement status change modal/form
        alert('Status change functionality to be implemented');
    }
    </script>
</body>
</html> 
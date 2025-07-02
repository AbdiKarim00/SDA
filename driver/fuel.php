<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../logistics/mock_data.php';

// Get mock data
$drivers = get_mock_data('drivers');
$fuel_cards = get_mock_data('fuel_cards');
$fuel_purchases = get_mock_data('fuel_purchases');

// Mock current driver (in real app, this would come from session)
$current_driver = $drivers[0];

// Get current fuel card
$current_fuel_card = array_filter($fuel_cards, fn($f) => $f['driver_id'] === $current_driver['id']);
$current_fuel_card = reset($current_fuel_card);

// Get fuel purchase history
$card_purchases = array_filter($fuel_purchases, fn($p) => $p['card_id'] === $current_fuel_card['id']);

// Sort purchases by date
usort($card_purchases, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Management - Driver Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .mobile-nav { 
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            z-index: 50;
        }
    </style>
</head>
<body class="bg-gray-50 pb-16">
    <div class="app-container" x-data="{ 
        activeTab: 'fuel',
        isLoading: true,
        showPurchaseModal: false,
        purchaseAmount: '',
        purchaseLocation: '',
        purchaseOdometer: ''
    }" x-init="setTimeout(() => isLoading = false, 500)">
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white shadow">
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">Fuel Management</h1>
                            <p class="text-sm text-gray-500">Track fuel purchases and card balance</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-gray-600 hover:text-gray-900">
                                <i class="bi bi-bell text-lg"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:text-gray-900">
                                <i class="bi bi-gear text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading" class="p-4">
                <?php if ($current_fuel_card): ?>
                    <!-- Fuel Card Details -->
                    <div class="bg-white rounded-lg shadow mb-4">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Fuel Card</h2>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-credit-card text-gray-500"></i>
                                    <p class="text-sm">Card: <?php echo htmlspecialchars($current_fuel_card['card_number']); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-currency-dollar text-gray-500"></i>
                                    <p class="text-sm">Balance: KES <?php echo number_format($current_fuel_card['current_balance']); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-calendar text-gray-500"></i>
                                    <p class="text-sm">Expires: <?php echo date('M d, Y', strtotime($current_fuel_card['expiry_date'])); ?></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button @click="showPurchaseModal = true" class="w-full btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Log Fuel Purchase
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase History -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Purchase History</h2>
                            <div class="space-y-4">
                                <?php foreach ($card_purchases as $purchase): ?>
                                    <div class="border-b border-gray-200 pb-4 last:border-0 last:pb-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($purchase['station_name']); ?>
                                            </h3>
                                            <span class="text-xs text-gray-500">
                                                <?php echo date('M d, Y', strtotime($purchase['date'])); ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="text-sm text-gray-600">
                                                Amount: KES <?php echo number_format($purchase['amount']); ?>
                                            </span>
                                            <span class="text-sm text-gray-600">
                                                Liters: <?php echo number_format($purchase['liters'], 2); ?> L
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xs text-gray-500">
                                                Odometer: <?php echo number_format($purchase['odometer_reading']); ?> km
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <i class="bi bi-credit-card text-4xl text-gray-400 mb-2"></i>
                        <h2 class="text-lg font-semibold text-gray-900">No Fuel Card Assigned</h2>
                        <p class="text-sm text-gray-500 mt-1">You don't have any fuel card assigned to you at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <nav class="mobile-nav">
            <div class="flex justify-around items-center h-16">
                <button @click="activeTab = 'trips'" 
                        :class="{ 'text-primary': activeTab === 'trips' }"
                        class="flex flex-col items-center p-2 text-gray-600">
                    <i class="bi bi-calendar-check text-xl"></i>
                    <span class="text-xs mt-1">Trips</span>
                </button>
                <button @click="activeTab = 'vehicle'" 
                        :class="{ 'text-primary': activeTab === 'vehicle' }"
                        class="flex flex-col items-center p-2 text-gray-600">
                    <i class="bi bi-truck text-xl"></i>
                    <span class="text-xs mt-1">Vehicle</span>
                </button>
                <button @click="activeTab = 'fuel'" 
                        :class="{ 'text-primary': activeTab === 'fuel' }"
                        class="flex flex-col items-center p-2 text-gray-600">
                    <i class="bi bi-fuel-pump text-xl"></i>
                    <span class="text-xs mt-1">Fuel</span>
                </button>
                <button @click="activeTab = 'profile'" 
                        :class="{ 'text-primary': activeTab === 'profile' }"
                        class="flex flex-col items-center p-2 text-gray-600">
                    <i class="bi bi-person text-xl"></i>
                    <span class="text-xs mt-1">Profile</span>
                </button>
            </div>
        </nav>

        <!-- Fuel Purchase Modal -->
        <div x-show="showPurchaseModal" 
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Log Fuel Purchase</h3>
                                <div class="mt-4">
                                    <form class="space-y-4">
                                        <div>
                                            <label class="form-label">Amount (KES)</label>
                                            <input type="number" 
                                                   x-model="purchaseAmount"
                                                   class="form-input" 
                                                   required>
                                        </div>
                                        <div>
                                            <label class="form-label">Station Name</label>
                                            <input type="text" 
                                                   x-model="purchaseLocation"
                                                   class="form-input" 
                                                   required>
                                        </div>
                                        <div>
                                            <label class="form-label">Odometer Reading (km)</label>
                                            <input type="number" 
                                                   x-model="purchaseOdometer"
                                                   class="form-input" 
                                                   required>
                                        </div>
                                        <div>
                                            <label class="form-label">Upload Receipt (Optional)</label>
                                            <input type="file" class="form-input" accept="image/*">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                class="btn btn-primary"
                                @click="showPurchaseModal = false">
                            Submit Purchase
                        </button>
                        <button type="button" 
                                class="btn btn-secondary"
                                @click="showPurchaseModal = false">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
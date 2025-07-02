<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for available drivers
$drivers = [
    ['id' => 1, 'name' => 'John Doe'],
    ['id' => 2, 'name' => 'Jane Smith'],
    ['id' => 3, 'name' => 'Mike Johnson']
];

// Mock data for vehicles
$vehicles = [
    ['id' => 1, 'registration' => 'KAA 123A'],
    ['id' => 2, 'registration' => 'KAA 456B'],
    ['id' => 3, 'registration' => 'KAA 789C']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, this would validate and save to database
    // For now, we'll just redirect back to the cards list
    $_SESSION['success'] = 'Card has been allocated successfully.';
    header('Location: cards.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocate New Card - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        cardType: '',
        showDriverField: false,
        showVehicleField: false
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('cards'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Allocate New Card',
                'Assign a new card to a driver or service provider'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <div class="bg-white rounded-lg shadow">
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Card Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">Card Information</h3>
                                
                                <div>
                                    <label for="card_number" class="form-label">Card Number *</label>
                                    <input type="text" 
                                           id="card_number" 
                                           name="card_number" 
                                           class="form-input" 
                                           required>
                                </div>

                                <div>
                                    <label for="card_type" class="form-label">Card Type *</label>
                                    <select id="card_type" 
                                            name="card_type" 
                                            class="form-select" 
                                            x-model="cardType"
                                            required>
                                        <option value="">Select Type</option>
                                        <option value="Fuel">Fuel</option>
                                        <option value="GENERAL CARDS">GENERAL CARDS</option>
                                        <option value="PLANTS & EQUIPMENT">PLANTS & EQUIPMENT</option>
                                    </select>
                                </div>

                                <div x-show="cardType === 'Fuel'">
                                    <label for="fuel_type" class="form-label">Fuel Type *</label>
                                    <select id="fuel_type" 
                                            name="fuel_type" 
                                            class="form-select">
                                        <option value="">Select Fuel Type</option>
                                        <option value="Diesel">Diesel</option>
                                        <option value="Petrol">Petrol</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="max_liters" class="form-label">Maximum Liters *</label>
                                    <input type="number" 
                                           id="max_liters" 
                                           name="max_liters" 
                                           class="form-input" 
                                           min="0" 
                                           required>
                                </div>
                            </div>

                            <!-- Assignment Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">Assignment Details</h3>
                                
                                <div>
                                    <label for="service_provider" class="form-label">Service Provider *</label>
                                    <input type="text" 
                                           id="service_provider" 
                                           name="service_provider" 
                                           class="form-input" 
                                           placeholder="e.g., Shell, Rubis"
                                           required>
                                </div>

                                <div>
                                    <label class="form-label">Assignment Type *</label>
                                    <div class="space-y-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                   name="assignment_type" 
                                                   value="driver" 
                                                   class="form-radio"
                                                   x-model="showDriverField"
                                                   @change="showVehicleField = false">
                                            <span class="ml-2">Assign to Driver</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                   name="assignment_type" 
                                                   value="vehicle" 
                                                   class="form-radio"
                                                   x-model="showVehicleField"
                                                   @change="showDriverField = false">
                                            <span class="ml-2">Assign to Vehicle</span>
                                        </label>
                                    </div>
                                </div>

                                <div x-show="showDriverField">
                                    <label for="driver_id" class="form-label">Select Driver *</label>
                                    <select id="driver_id" 
                                            name="driver_id" 
                                            class="form-select">
                                        <option value="">Select Driver</option>
                                        <?php foreach ($drivers as $driver): ?>
                                            <option value="<?php echo $driver['id']; ?>">
                                                <?php echo htmlspecialchars($driver['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div x-show="showVehicleField">
                                    <label for="vehicle_id" class="form-label">Select Vehicle *</label>
                                    <select id="vehicle_id" 
                                            name="vehicle_id" 
                                            class="form-select">
                                        <option value="">Select Vehicle</option>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>">
                                                <?php echo htmlspecialchars($vehicle['registration']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="allocation_date" class="form-label">Allocation Date *</label>
                                    <input type="date" 
                                           id="allocation_date" 
                                           name="allocation_date" 
                                           class="form-input" 
                                           value="<?php echo date('Y-m-d'); ?>"
                                           required>
                                </div>

                                <div>
                                    <label for="expiry_date" class="form-label">Expiry Date *</label>
                                    <input type="date" 
                                           id="expiry_date" 
                                           name="expiry_date" 
                                           class="form-input" 
                                           value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="cards.php" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Allocate Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
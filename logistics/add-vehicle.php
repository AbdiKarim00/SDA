<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, this would validate and save to database
    // For now, we'll just redirect back to the vehicles list
    $_SESSION['success'] = 'Vehicle added successfully.';
        header('Location: vehicles.php');
        exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle - Transport IMS</title>
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
        <?php DashboardSidebar::render('vehicles'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Add New Vehicle',
                'Enter vehicle details to add to the fleet'
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
                            <!-- Basic Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                                
                            <div>
                                    <label for="registration_number" class="form-label">Registration Number *</label>
                                    <input type="text" 
                                           id="registration_number" 
                                           name="registration_number" 
                                           class="form-input" 
                                           required>
                            </div>

                            <div>
                                    <label for="make" class="form-label">Make *</label>
                                    <input type="text" 
                                           id="make" 
                                           name="make" 
                                           class="form-input" 
                                           required>
                            </div>

                            <div>
                                    <label for="model" class="form-label">Model *</label>
                                    <input type="text" 
                                           id="model" 
                                           name="model" 
                                           class="form-input" 
                                           required>
                            </div>

                            <div>
                                    <label for="vehicle_type" class="form-label">Vehicle Type *</label>
                                    <select id="vehicle_type" 
                                            name="vehicle_type" 
                                            class="form-select" 
                                            required>
                                        <option value="">Select Type</option>
                                    <option value="Car">Car</option>
                                    <option value="Van">Van</option>
                                    <option value="Truck">Truck</option>
                                    <option value="Bus">Bus</option>
                                </select>
                            </div>

                            <div>
                                    <label for="capacity" class="form-label">Capacity *</label>
                                    <input type="text" 
                                           id="capacity" 
                                           name="capacity" 
                                           class="form-input" 
                                           placeholder="e.g., 1.5 tons" 
                                           required>
                            </div>

                            <div>
                                    <label for="fuel_type" class="form-label">Fuel Type *</label>
                                    <select id="fuel_type" 
                                            name="fuel_type" 
                                            class="form-select" 
                                            required>
                                        <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Electric">Electric</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                            </div>
                            </div>

                            <!-- Technical Details -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">Technical Details</h3>
                                
                                <div>
                                    <label for="chassis_no" class="form-label">Chassis Number *</label>
                                    <input type="text" 
                                           id="chassis_no" 
                                           name="chassis_no" 
                                           class="form-input" 
                                           required>
                                </div>

                            <div>
                                    <label for="engine_no" class="form-label">Engine Number *</label>
                                    <input type="text" 
                                           id="engine_no" 
                                           name="engine_no" 
                                           class="form-input" 
                                           required>
                            </div>

                            <div>
                                    <label for="purchase_date" class="form-label">Purchase Date *</label>
                                    <input type="date" 
                                           id="purchase_date" 
                                           name="purchase_date" 
                                           class="form-input" 
                                           required>
                            </div>

                            <div>
                                    <label for="initial_mileage" class="form-label">Initial Mileage *</label>
                                    <input type="number" 
                                           id="initial_mileage" 
                                           name="initial_mileage" 
                                           class="form-input" 
                                           min="0" 
                                           required>
                            </div>

                            <div>
                                    <label for="funded_by" class="form-label">Funded By *</label>
                                    <select id="funded_by" 
                                            name="funded_by" 
                                            class="form-select" 
                                            required>
                                        <option value="">Select Source</option>
                                        <option value="Government">Government</option>
                                        <option value="Private">Private</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Compliance Information -->
                        <div class="mt-8 space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Compliance Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="next_service_due" class="form-label">Next Service Due *</label>
                                    <input type="date" 
                                           id="next_service_due" 
                                           name="next_service_due" 
                                           class="form-input" 
                                           required>
                                </div>

                                <div>
                                    <label for="insurance_expiry" class="form-label">Insurance Expiry *</label>
                                    <input type="date" 
                                           id="insurance_expiry" 
                                           name="insurance_expiry" 
                                           class="form-input" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="vehicles.php" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Add Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
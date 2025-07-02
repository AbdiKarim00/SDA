<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once 'mock_data.php';

// Get vehicle ID from URL
$vehicle_id = $_GET['id'] ?? null;
if (!$vehicle_id) {
    header('Location: vehicles.php');
    exit;
}

// Get mock data
$vehicles = get_mock_data('vehicles');
$vehicle = array_filter($vehicles, fn($v) => $v['id'] == $vehicle_id);
$vehicle = reset($vehicle);

if (!$vehicle) {
    $_SESSION['error'] = 'Vehicle not found.';
    header('Location: vehicles.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, this would update the database
    // For mock data, we'll just simulate the action
    $success = true;
    if ($success) {
        $_SESSION['success'] = 'Vehicle has been updated successfully.';
        header('Location: vehicles.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to update vehicle.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - Transport IMS</title>
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
                'Edit Vehicle',
                'Update the details of the selected vehicle'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <div class="bg-white rounded-lg shadow">
                    <form method="POST" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Registration Number -->
                            <div>
                                <label class="form-label">Registration Number <span class="text-red-500">*</span></label>
                                <input type="text" name="registration_number" required
                                       class="form-input" placeholder="Enter registration number"
                                       value="<?php echo htmlspecialchars($vehicle['registration_number']); ?>">
                            </div>

                            <!-- Make -->
                            <div>
                                <label class="form-label">Make <span class="text-red-500">*</span></label>
                                <input type="text" name="make" required
                                       class="form-input" placeholder="Enter vehicle make"
                                       value="<?php echo htmlspecialchars($vehicle['make']); ?>">
                            </div>

                            <!-- Model -->
                            <div>
                                <label class="form-label">Model <span class="text-red-500">*</span></label>
                                <input type="text" name="model" required
                                       class="form-input" placeholder="Enter vehicle model"
                                       value="<?php echo htmlspecialchars($vehicle['model']); ?>">
                            </div>

                            <!-- Chassis Number -->
                            <div>
                                <label class="form-label">Chassis Number <span class="text-red-500">*</span></label>
                                <input type="text" name="chassis_number" required
                                       class="form-input" placeholder="Enter chassis number"
                                       value="<?php echo htmlspecialchars($vehicle['chassis_number']); ?>">
                            </div>

                            <!-- Engine Number -->
                            <div>
                                <label class="form-label">Engine Number <span class="text-red-500">*</span></label>
                                <input type="text" name="engine_number" required
                                       class="form-input" placeholder="Enter engine number"
                                       value="<?php echo htmlspecialchars($vehicle['engine_number']); ?>">
                            </div>

                            <!-- Vehicle Type -->
                            <div>
                                <label class="form-label">Vehicle Type <span class="text-red-500">*</span></label>
                                <select name="vehicle_type" required class="form-select">
                                    <option value="">Select vehicle type</option>
                                    <option value="Car" <?php echo $vehicle['vehicle_type'] === 'Car' ? 'selected' : ''; ?>>Car</option>
                                    <option value="Van" <?php echo $vehicle['vehicle_type'] === 'Van' ? 'selected' : ''; ?>>Van</option>
                                    <option value="Truck" <?php echo $vehicle['vehicle_type'] === 'Truck' ? 'selected' : ''; ?>>Truck</option>
                                    <option value="Bus" <?php echo $vehicle['vehicle_type'] === 'Bus' ? 'selected' : ''; ?>>Bus</option>
                                </select>
                            </div>

                            <!-- Capacity -->
                            <div>
                                <label class="form-label">Capacity <span class="text-red-500">*</span></label>
                                <input type="number" name="capacity" required
                                       class="form-input" placeholder="Enter vehicle capacity"
                                       value="<?php echo htmlspecialchars($vehicle['capacity']); ?>">
                            </div>

                            <!-- Fuel Type -->
                            <div>
                                <label class="form-label">Fuel Type <span class="text-red-500">*</span></label>
                                <select name="fuel_type" required class="form-select">
                                    <option value="">Select fuel type</option>
                                    <option value="Petrol" <?php echo $vehicle['fuel_type'] === 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                                    <option value="Diesel" <?php echo $vehicle['fuel_type'] === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                                    <option value="Electric" <?php echo $vehicle['fuel_type'] === 'Electric' ? 'selected' : ''; ?>>Electric</option>
                                    <option value="Hybrid" <?php echo $vehicle['fuel_type'] === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                </select>
                            </div>

                            <!-- Date of Purchase -->
                            <div>
                                <label class="form-label">Date of Purchase <span class="text-red-500">*</span></label>
                                <input type="date" name="date_of_purchase" required
                                       class="form-input"
                                       value="<?php echo htmlspecialchars($vehicle['date_of_purchase']); ?>">
                            </div>

                            <!-- Funded By -->
                            <div>
                                <label class="form-label">Funded By <span class="text-red-500">*</span></label>
                                <input type="text" name="funded_by" required
                                       class="form-input" placeholder="Enter funding source"
                                       value="<?php echo htmlspecialchars($vehicle['funded_by']); ?>">
                            </div>

                            <!-- Current Odometer -->
                            <div>
                                <label class="form-label">Current Odometer (km) <span class="text-red-500">*</span></label>
                                <input type="number" name="current_odometer" required
                                       class="form-input" placeholder="Enter current odometer reading"
                                       value="<?php echo htmlspecialchars($vehicle['current_odometer']); ?>">
                            </div>

                            <!-- Next Service Odometer -->
                            <div>
                                <label class="form-label">Next Service Due (km) <span class="text-red-500">*</span></label>
                                <input type="number" name="next_service_odometer" required
                                       class="form-input" placeholder="Enter odometer reading for next service"
                                       value="<?php echo htmlspecialchars($vehicle['next_service_odometer']); ?>">
                                <p class="text-sm text-gray-500 mt-1">Enter the odometer reading at which the next service is due</p>
                            </div>

                            <!-- Insurance Expiry -->
                            <div>
                                <label class="form-label">Insurance Expiry <span class="text-red-500">*</span></label>
                                <input type="date" name="insurance_expiry" required
                                       class="form-input"
                                       value="<?php echo htmlspecialchars($vehicle['insurance_expiry']); ?>">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="form-label">Status <span class="text-red-500">*</span></label>
                                <select name="status" required class="form-select">
                                    <option value="Available" <?php echo $vehicle['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="In Maintenance" <?php echo $vehicle['status'] === 'In Maintenance' ? 'selected' : ''; ?>>In Maintenance</option>
                                    <option value="On Trip" <?php echo $vehicle['status'] === 'On Trip' ? 'selected' : ''; ?>>On Trip</option>
                                    <option value="Decommissioned" <?php echo $vehicle['status'] === 'Decommissioned' ? 'selected' : ''; ?>>Decommissioned</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="vehicles.php" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Update Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
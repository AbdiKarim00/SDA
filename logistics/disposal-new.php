<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for available vehicles
$available_vehicles = [
    [
        'id' => 1,
        'registration_number' => 'GKB 673S',
        'make' => 'Toyota',
        'model' => 'Hilux',
        'year' => '2018',
        'current_location' => 'Main Yard',
        'net_book_value' => 3770500,
        'accumulated_depreciation' => 3770500
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'make' => 'Isuzu',
        'model' => 'NPR',
        'year' => '2019',
        'current_location' => 'Service Center',
        'net_book_value' => 2500000,
        'accumulated_depreciation' => 2500000
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would:
    // 1. Validate the input
    // 2. Process file uploads
    // 3. Save to database
    // 4. Redirect to the disposal listing page
    
    // For now, we'll just redirect
    header('Location: disposal.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Disposal Request - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('disposal'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'New Disposal Request',
                'Create a new vehicle disposal request'
            ); ?>

            <div class="max-w-4xl mx-auto">
                <form action="disposal-new.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Vehicle Selection -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Vehicle</h2>
                            <div class="space-y-4">
                                <div>
                                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                                    <select id="vehicle_id" name="vehicle_id" required class="form-select">
                                        <option value="">Select a vehicle</option>
                                        <?php foreach ($available_vehicles as $vehicle): ?>
                                            <option value="<?php echo $vehicle['id']; ?>">
                                                <?php echo htmlspecialchars($vehicle['registration_number'] . ' - ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Disposal Details -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Disposal Details</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Disposal</label>
                                    <textarea id="reason" name="reason" rows="3" required
                                              class="form-textarea"
                                              placeholder="Enter the reason for disposal"></textarea>
                                </div>
                                <div>
                                    <label for="proposed_method" class="block text-sm font-medium text-gray-700 mb-1">Proposed Disposal Method</label>
                                    <select id="proposed_method" name="proposed_method" required class="form-select">
                                        <option value="">Select method</option>
                                        <option value="Auction">Auction</option>
                                        <option value="Sale">Direct Sale</option>
                                        <option value="Scrap">Scrap</option>
                                        <option value="Donation">Donation</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="estimated_value" class="block text-sm font-medium text-gray-700 mb-1">Estimated Value (KES)</label>
                                    <input type="number" id="estimated_value" name="estimated_value" required
                                           class="form-input"
                                           placeholder="Enter estimated value">
                                </div>
                                <div>
                                    <label for="current_location" class="block text-sm font-medium text-gray-700 mb-1">Current Location</label>
                                    <input type="text" id="current_location" name="current_location" required
                                           class="form-input"
                                           placeholder="Enter current location">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supporting Documents -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Supporting Documents</h2>
                            <div class="space-y-4">
                                <div>
                                    <label for="assessment_report" class="block text-sm font-medium text-gray-700 mb-1">Vehicle Assessment Report</label>
                                    <input type="file" id="assessment_report" name="assessment_report" required
                                           class="form-input"
                                           accept=".pdf,.doc,.docx">
                                </div>
                                <div>
                                    <label for="maintenance_history" class="block text-sm font-medium text-gray-700 mb-1">Maintenance History</label>
                                    <input type="file" id="maintenance_history" name="maintenance_history" required
                                           class="form-input"
                                           accept=".pdf,.doc,.docx">
                                </div>
                                <div>
                                    <label for="vehicle_photos" class="block text-sm font-medium text-gray-700 mb-1">Vehicle Photos</label>
                                    <input type="file" id="vehicle_photos" name="vehicle_photos" required
                                           class="form-input"
                                           accept="image/*" multiple>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4">
                        <a href="disposal.php" class="btn btn-outline">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add any necessary JavaScript for form validation and interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const vehicleSelect = document.getElementById('vehicle_id');
            const currentLocationInput = document.getElementById('current_location');

            // Update current location when vehicle is selected
            vehicleSelect.addEventListener('change', function() {
                const selectedVehicle = <?php echo json_encode($available_vehicles); ?>.find(
                    vehicle => vehicle.id === parseInt(this.value)
                );
                if (selectedVehicle) {
                    currentLocationInput.value = selectedVehicle.current_location;
                }
            });
        });
    </script>
</body>
</html> 
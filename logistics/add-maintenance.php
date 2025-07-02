<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for vehicles
$vehicles = [
    ['registration' => 'KAA 123A', 'type' => 'Sedan'],
    ['registration' => 'KAA 456B', 'type' => 'SUV'],
    ['registration' => 'KAA 789C', 'type' => 'Van']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, this would insert into the database
    // For mock data, we'll just redirect back to the maintenance list
    $_SESSION['success'] = 'Maintenance record added successfully.';
    header('Location: maintenance.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Maintenance Record - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('maintenance'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Add Maintenance Record',
                'Create a new maintenance record for a vehicle'
            ); ?>

            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="vehicle_reg" class="block text-sm font-medium text-gray-700">Vehicle</label>
                            <select id="vehicle_reg" name="vehicle_reg" required
                                class="form-select">
                                <option value="">Select Vehicle</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?php echo htmlspecialchars($vehicle['registration']); ?>">
                                        <?php echo htmlspecialchars($vehicle['registration'] . ' (' . $vehicle['type'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="service_type" class="block text-sm font-medium text-gray-700">Service Type</label>
                            <select id="service_type" name="service_type" required
                                class="form-select">
                                <option value="">Select Service Type</option>
                                <option value="Scheduled Service">Scheduled Service</option>
                                <option value="Emergency Repair">Emergency Repair</option>
                                <option value="Tyre Change">Tyre Change</option>
                                <option value="Inspection">Inspection</option>
                            </select>
                        </div>

                        <div>
                            <label for="service_date" class="block text-sm font-medium text-gray-700">Service Date</label>
                            <input type="date" id="service_date" name="service_date" required
                                value="<?php echo date('Y-m-d'); ?>"
                                class="form-input">
                        </div>

                        <div>
                            <label for="odometer" class="block text-sm font-medium text-gray-700">Odometer Reading (km)</label>
                            <input type="number" id="odometer" name="odometer" required min="0"
                                class="form-input">
                        </div>

                        <div>
                            <label for="service_provider" class="block text-sm font-medium text-gray-700">Service Provider</label>
                            <input type="text" id="service_provider" name="service_provider" required
                                class="form-input">
                        </div>

                        <div>
                            <label for="cost" class="block text-sm font-medium text-gray-700">Cost (KES)</label>
                            <input type="number" id="cost" name="cost" required min="0"
                                class="form-input">
                        </div>

                        <div>
                            <label for="next_service" class="block text-sm font-medium text-gray-700">Next Service Due</label>
                            <input type="date" id="next_service" name="next_service" required
                                class="form-input">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Initial Status</label>
                            <select id="status" name="status" required
                                class="form-select">
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description of Work</label>
                        <textarea id="description" name="description" rows="4" required
                            placeholder="Describe the maintenance work to be done..."
                            class="form-input"></textarea>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="maintenance.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Add Maintenance Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
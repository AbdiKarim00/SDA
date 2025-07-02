<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for maintenance records
$maintenance_records = [
    [
        'id' => 1,
        'vehicle_reg' => 'KAA 123A',
        'service_type' => 'Scheduled Service',
        'service_date' => '2024-03-15',
        'odometer' => 50000,
        'service_provider' => 'AutoCare Garage',
        'cost' => 25000,
        'description' => 'Regular maintenance including oil change, filter replacement, and brake inspection',
        'next_service' => '2024-09-15',
        'status' => 'Completed',
        'reported_by' => 'John Doe',
        'date_added' => '2024-03-14'
    ],
    [
        'id' => 2,
        'vehicle_reg' => 'KAA 456B',
        'service_type' => 'Emergency Repair',
        'service_date' => '2024-03-10',
        'odometer' => 75000,
        'service_provider' => 'QuickFix Motors',
        'cost' => 45000,
        'description' => 'Engine overheating issue fixed, coolant system repaired',
        'next_service' => '2024-06-10',
        'status' => 'In Progress',
        'reported_by' => 'Jane Smith',
        'date_added' => '2024-03-09'
    ],
    [
        'id' => 3,
        'vehicle_reg' => 'KAA 789C',
        'service_type' => 'Tyre Change',
        'service_date' => '2024-03-05',
        'odometer' => 60000,
        'service_provider' => 'TyrePro Center',
        'cost' => 80000,
        'description' => 'All four tyres replaced with new set',
        'next_service' => '2024-09-05',
        'status' => 'Completed',
        'reported_by' => 'Robert Johnson',
        'date_added' => '2024-03-04'
    ]
];

// Mock data for vehicles
$vehicles = [
    ['reg' => 'KAA 123A', 'type' => 'Sedan'],
    ['reg' => 'KAA 456B', 'type' => 'SUV'],
    ['reg' => 'KAA 789C', 'type' => 'Van']
];

// Get maintenance ID from URL
$maintenance_id = $_GET['id'] ?? null;

// Find the maintenance record
$record = null;
foreach ($maintenance_records as $r) {
    if ($r['id'] == $maintenance_id) {
        $record = $r;
        break;
    }
}

// If record not found, redirect to maintenance list
if (!$record) {
    $_SESSION['error'] = 'Maintenance record not found.';
    header('Location: maintenance.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would update the record in the database here
    $_SESSION['success'] = 'Maintenance record updated successfully.';
    header('Location: maintenance.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Maintenance - Transport IMS</title>
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
                'Edit Maintenance Record',
                'Update maintenance record information'
            ); ?>

            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="vehicle_reg" class="block text-sm font-medium text-gray-700">Vehicle</label>
                            <select id="vehicle_reg" name="vehicle_reg" required
                                class="form-select">
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?php echo htmlspecialchars($vehicle['reg']); ?>"
                                        <?php echo $vehicle['reg'] === $record['vehicle_reg'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($vehicle['reg']); ?> (<?php echo htmlspecialchars($vehicle['type']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="service_type" class="block text-sm font-medium text-gray-700">Service Type</label>
                            <select id="service_type" name="service_type" required
                                class="form-select">
                                <option value="Scheduled Service" <?php echo $record['service_type'] === 'Scheduled Service' ? 'selected' : ''; ?>>Scheduled Service</option>
                                <option value="Emergency Repair" <?php echo $record['service_type'] === 'Emergency Repair' ? 'selected' : ''; ?>>Emergency Repair</option>
                                <option value="Tyre Change" <?php echo $record['service_type'] === 'Tyre Change' ? 'selected' : ''; ?>>Tyre Change</option>
                                <option value="Battery Replacement" <?php echo $record['service_type'] === 'Battery Replacement' ? 'selected' : ''; ?>>Battery Replacement</option>
                                <option value="Other" <?php echo $record['service_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="service_date" class="block text-sm font-medium text-gray-700">Service Date</label>
                            <input type="date" id="service_date" name="service_date" required
                                value="<?php echo htmlspecialchars($record['service_date']); ?>"
                                class="form-input">
                        </div>

                        <div>
                            <label for="odometer" class="block text-sm font-medium text-gray-700">Odometer Reading (km)</label>
                            <input type="number" id="odometer" name="odometer" required
                                value="<?php echo htmlspecialchars($record['odometer']); ?>"
                                class="form-input">
                        </div>

                        <div>
                            <label for="service_provider" class="block text-sm font-medium text-gray-700">Service Provider</label>
                            <input type="text" id="service_provider" name="service_provider" required
                                value="<?php echo htmlspecialchars($record['service_provider']); ?>"
                                class="form-input">
                        </div>

                        <div>
                            <label for="cost" class="block text-sm font-medium text-gray-700">Cost (KES)</label>
                            <input type="number" id="cost" name="cost" required
                                value="<?php echo htmlspecialchars($record['cost']); ?>"
                                class="form-input">
                        </div>

                        <div>
                            <label for="next_service" class="block text-sm font-medium text-gray-700">Next Service Due</label>
                            <input type="date" id="next_service" name="next_service" required
                                value="<?php echo htmlspecialchars($record['next_service']); ?>"
                                class="form-input">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" required
                                class="form-select">
                                <option value="Pending" <?php echo $record['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo $record['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo $record['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo $record['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description of Work</label>
                        <textarea id="description" name="description" rows="4" required
                            class="form-input"><?php echo htmlspecialchars($record['description']); ?></textarea>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="maintenance.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Update Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
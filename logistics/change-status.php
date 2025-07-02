<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for drivers
$drivers = [
    [
        'id' => 1,
        'personal_number' => 'EMP001',
        'name' => 'John Doe',
        'contact' => '+254 712 345 678',
        'license_number' => 'DL123456',
        'license_expiry' => '2024-12-31',
        'joining_date' => '2020-01-15',
        'department' => 'Transport Unit',
        'status' => 'Active',
        'current_vehicle' => 'KAA 123A',
        'fuel_card' => 'FC001'
    ],
    [
        'id' => 2,
        'personal_number' => 'EMP002',
        'name' => 'Jane Smith',
        'contact' => '+254 723 456 789',
        'license_number' => 'DL234567',
        'license_expiry' => '2024-06-30',
        'joining_date' => '2021-03-20',
        'department' => 'Logistics',
        'status' => 'On Leave',
        'current_vehicle' => null,
        'fuel_card' => 'FC002'
    ],
    [
        'id' => 3,
        'personal_number' => 'EMP003',
        'name' => 'Robert Johnson',
        'contact' => '+254 734 567 890',
        'license_number' => 'DL345678',
        'license_expiry' => '2025-03-15',
        'joining_date' => '2019-11-10',
        'department' => 'Transport Unit',
        'status' => 'Active',
        'current_vehicle' => 'KAA 456B',
        'fuel_card' => 'FC003'
    ]
];

// Get driver ID from URL
$driver_id = $_GET['id'] ?? null;

// Find the driver
$driver = null;
foreach ($drivers as $d) {
    if ($d['id'] == $driver_id) {
        $driver = $d;
        break;
    }
}

// If driver not found, redirect to drivers list
if (!$driver) {
    $_SESSION['error'] = 'Driver not found.';
    header('Location: drivers.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, this would update the database
    // For mock data, we'll just redirect back to the drivers list
    $_SESSION['success'] = 'Driver status updated successfully.';
    header('Location: drivers.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Driver Status - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('drivers'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Change Driver Status',
                'Update the status of a driver'
            ); ?>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Change Status for: <?php echo htmlspecialchars($driver['name']); ?>
                    </h2>
                    <a href="drivers.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to List
                    </a>
                </div>

                <div class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Current Status</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    switch ($driver['status']) {
                                        case 'Active':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'On Leave':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Suspended':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($driver['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Current Vehicle</label>
                            <p class="mt-1">
                                <?php if ($driver['current_vehicle']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($driver['current_vehicle']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500">Not Assigned</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="Active" <?php echo $driver['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="On Leave" <?php echo $driver['status'] === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                            <option value="Suspended" <?php echo $driver['status'] === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                            <option value="Inactive" <?php echo $driver['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Reason for Status Change</label>
                        <textarea name="reason" class="form-input" rows="3" required
                                  placeholder="Please provide a reason for the status change..."></textarea>
                    </div>

                    <div>
                        <label class="form-label">Effective Date</label>
                        <input type="date" name="effective_date" class="form-input" required
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="drivers.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
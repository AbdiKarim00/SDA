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
    $_SESSION['success'] = 'Driver information updated successfully.';
    header('Location: drivers.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver - Transport IMS</title>
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
                'Edit Driver',
                'Update driver information'
            ); ?>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Edit Driver: <?php echo htmlspecialchars($driver['name']); ?>
                    </h2>
                    <a href="drivers.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to List
                    </a>
                </div>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Personal Number</label>
                            <input type="text" name="personal_number" class="form-input" required
                                   value="<?php echo htmlspecialchars($driver['personal_number']); ?>">
                        </div>
                        <div>
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-input" required
                                   value="<?php echo htmlspecialchars($driver['name']); ?>">
                        </div>
                        <div>
                            <label class="form-label">Contact Number</label>
                            <input type="tel" name="contact" class="form-input" required
                                   value="<?php echo htmlspecialchars($driver['contact']); ?>">
                        </div>
                        <div>
                            <label class="form-label">License Number</label>
                            <input type="text" name="license_number" class="form-input" required
                                   value="<?php echo htmlspecialchars($driver['license_number']); ?>">
                        </div>
                        <div>
                            <label class="form-label">License Expiry Date</label>
                            <input type="date" name="license_expiry" class="form-input" required
                                   value="<?php echo $driver['license_expiry']; ?>">
                        </div>
                        <div>
                            <label class="form-label">Date of Joining</label>
                            <input type="date" name="joining_date" class="form-input" required
                                   value="<?php echo $driver['joining_date']; ?>">
                        </div>
                        <div>
                            <label class="form-label">Department</label>
                            <select name="department" class="form-select" required>
                                <option value="">Select Department</option>
                                <option value="Transport Unit" <?php echo $driver['department'] === 'Transport Unit' ? 'selected' : ''; ?>>
                                    Transport Unit
                                </option>
                                <option value="Logistics" <?php echo $driver['department'] === 'Logistics' ? 'selected' : ''; ?>>
                                    Logistics
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Active" <?php echo $driver['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="On Leave" <?php echo $driver['status'] === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                                <option value="Suspended" <?php echo $driver['status'] === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="drivers.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Update Driver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
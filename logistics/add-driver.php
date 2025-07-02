<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, this would insert into the database
    // For mock data, we'll just redirect back to the drivers list
    $_SESSION['success'] = 'Driver added successfully.';
    header('Location: drivers.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Driver - Transport IMS</title>
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
                'Add New Driver',
                'Create a new driver record in the system'
            ); ?>

            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Personal Number</label>
                            <input type="text" name="personal_number" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Contact Number</label>
                            <input type="tel" name="contact" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">License Number</label>
                            <input type="text" name="license_number" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">License Expiry Date</label>
                            <input type="date" name="license_expiry" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Date of Joining</label>
                            <input type="date" name="joining_date" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Department</label>
                            <select name="department" class="form-select" required>
                                <option value="">Select Department</option>
                                <option value="Transport Unit">Transport Unit</option>
                                <option value="Logistics">Logistics</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Initial Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Suspended">Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="drivers.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Add Driver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for assignment history
$assignment_history = [
    [
        'id' => 1,
        'vehicle_reg' => 'KAA 123A',
        'assigned_to' => 'John Doe',
        'assignment_type' => 'Primary Deployment',
        'start_location' => 'CMTE Grounds',
        'end_location' => 'Cabinet Secretary Office',
        'start_date' => '2024-03-01',
        'end_date' => '2024-03-15',
        'assigned_by' => 'Robert Johnson',
        'reason' => 'New Deployment',
        'date_created' => '2024-03-01',
        'last_updated' => '2024-03-15'
    ],
    [
        'id' => 2,
        'vehicle_reg' => 'KAA 456B',
        'assigned_to' => 'Jane Smith',
        'assignment_type' => 'Temporary Assignment',
        'start_location' => 'Makindu Police Station',
        'end_location' => 'Athi River Police Station',
        'start_date' => '2024-03-10',
        'end_date' => null,
        'assigned_by' => 'Mary Williams',
        'reason' => 'Driver Change',
        'date_created' => '2024-03-10',
        'last_updated' => '2024-03-10'
    ],
    [
        'id' => 3,
        'vehicle_reg' => 'KAA 789C',
        'assigned_to' => null,
        'assignment_type' => 'Pool Assignment',
        'start_location' => 'Crown Motors',
        'end_location' => 'Crown Motors',
        'start_date' => '2024-03-05',
        'end_date' => null,
        'assigned_by' => 'David Brown',
        'reason' => 'Vehicle for specific project',
        'date_created' => '2024-03-05',
        'last_updated' => '2024-03-05'
    ]
];

// Get assignment ID from URL
$assignment_id = $_GET['id'] ?? null;

// Find the assignment record
$record = null;
foreach ($assignment_history as $r) {
    if ($r['id'] == $assignment_id) {
        $record = $r;
        break;
    }
}

// If record not found, redirect to assignment history list
if (!$record) {
    $_SESSION['error'] = 'Assignment record not found.';
    header('Location: assignment-history.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Details - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('assignment-history'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Assignment Details',
                'View detailed information about this assignment'
            ); ?>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment Information</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assignment ID</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo $record['id']; ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vehicle Registration</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($record['vehicle_reg']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo $record['assigned_to'] ? htmlspecialchars($record['assigned_to']) : 'Unassigned'; ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assignment Type</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($record['assignment_type']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Location</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($record['start_location']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Location</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($record['end_location']); ?></dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Timeline Information</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo date('M d, Y', strtotime($record['start_date'])); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo $record['end_date'] ? date('M d, Y', strtotime($record['end_date'])) : 'Ongoing'; ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $record['end_date'] ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $record['end_date'] ? 'Completed' : 'Active'; ?>
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assigned By</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($record['assigned_by']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Reason for Assignment</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($record['reason']); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Audit Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date Created</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo date('M d, Y H:i', strtotime($record['date_created'])); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo date('M d, Y H:i', strtotime($record['last_updated'])); ?></dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="assignment-history.php" class="btn btn-secondary">
                        Back to Assignment History
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
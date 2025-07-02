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

// Filter assignment history
$filtered_records = $assignment_history;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $vehicle_filter = $_GET['vehicle'] ?? '';
    $driver_filter = $_GET['driver'] ?? '';
    $type_filter = $_GET['type'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';

    if ($vehicle_filter || $driver_filter || $type_filter || $date_from || $date_to || $search) {
        $filtered_records = array_filter($assignment_history, function($record) use ($vehicle_filter, $driver_filter, $type_filter, $date_from, $date_to, $search) {
            $matches = true;
            
            if ($vehicle_filter && $record['vehicle_reg'] !== $vehicle_filter) {
                $matches = false;
            }
            
            if ($driver_filter && $record['assigned_to'] !== $driver_filter) {
                $matches = false;
            }
            
            if ($type_filter && $record['assignment_type'] !== $type_filter) {
                $matches = false;
            }
            
            if ($date_from && strtotime($record['start_date']) < strtotime($date_from)) {
                $matches = false;
            }
            
            if ($date_to && strtotime($record['start_date']) > strtotime($date_to)) {
                $matches = false;
            }
            
            if ($search) {
                $search = strtolower($search);
                $searchable_fields = [
                    $record['vehicle_reg'],
                    $record['assigned_to'],
                    $record['start_location'],
                    $record['end_location'],
                    $record['reason']
                ];
                
                $found = false;
                foreach ($searchable_fields as $field) {
                    if ($field && strpos(strtolower($field), $search) !== false) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $matches = false;
                }
            }
            
            return $matches;
        });
    }
}

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="assignment_history.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'Assignment ID',
        'Vehicle Registration',
        'Assigned To',
        'Assignment Type',
        'Start Location',
        'End Location',
        'Start Date',
        'End Date',
        'Assigned By',
        'Reason',
        'Date Created',
        'Last Updated'
    ]);
    
    // Add data rows
    foreach ($filtered_records as $record) {
        fputcsv($output, [
            $record['id'],
            $record['vehicle_reg'],
            $record['assigned_to'],
            $record['assignment_type'],
            $record['start_location'],
            $record['end_location'],
            $record['start_date'],
            $record['end_date'],
            $record['assigned_by'],
            $record['reason'],
            $record['date_created'],
            $record['last_updated']
        ]);
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment History - Transport IMS</title>
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
                'Assignment History',
                'View and manage vehicle assignment history'
            ); ?>

            <div class="bg-white rounded-lg shadow">
                <!-- Top Bar -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <input type="text" 
                                       class="form-input pl-10" 
                                       placeholder="Search assignment history..."
                                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                       onchange="this.form.submit()"
                                       form="filter-form">
                                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <button type="button" 
                                    class="btn btn-secondary"
                                    onclick="document.getElementById('filter-form').classList.toggle('hidden')">
                                <i class="bi bi-funnel"></i>
                                Filters
                            </button>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="?export=csv<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-download"></i>
                                Export
                            </a>
                        </div>
                    </div>

                    <!-- Filters Form -->
                    <form id="filter-form" method="GET" class="hidden mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="form-label">Vehicle</label>
                            <select name="vehicle" class="form-select" onchange="this.form.submit()">
                                <option value="">All Vehicles</option>
                                <option value="KAA 123A" <?php echo ($_GET['vehicle'] ?? '') === 'KAA 123A' ? 'selected' : ''; ?>>KAA 123A</option>
                                <option value="KAA 456B" <?php echo ($_GET['vehicle'] ?? '') === 'KAA 456B' ? 'selected' : ''; ?>>KAA 456B</option>
                                <option value="KAA 789C" <?php echo ($_GET['vehicle'] ?? '') === 'KAA 789C' ? 'selected' : ''; ?>>KAA 789C</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Driver</label>
                            <select name="driver" class="form-select" onchange="this.form.submit()">
                                <option value="">All Drivers</option>
                                <option value="John Doe" <?php echo ($_GET['driver'] ?? '') === 'John Doe' ? 'selected' : ''; ?>>John Doe</option>
                                <option value="Jane Smith" <?php echo ($_GET['driver'] ?? '') === 'Jane Smith' ? 'selected' : ''; ?>>Jane Smith</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Assignment Type</label>
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="Primary Deployment" <?php echo ($_GET['type'] ?? '') === 'Primary Deployment' ? 'selected' : ''; ?>>Primary Deployment</option>
                                <option value="Temporary Assignment" <?php echo ($_GET['type'] ?? '') === 'Temporary Assignment' ? 'selected' : ''; ?>>Temporary Assignment</option>
                                <option value="Pool Assignment" <?php echo ($_GET['type'] ?? '') === 'Pool Assignment' ? 'selected' : ''; ?>>Pool Assignment</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Date From</label>
                            <input type="date" 
                                   name="date_from" 
                                   class="form-input"
                                   value="<?php echo $_GET['date_from'] ?? ''; ?>"
                                   onchange="this.form.submit()">
                        </div>
                        <div>
                            <label class="form-label">Date To</label>
                            <input type="date" 
                                   name="date_to" 
                                   class="form-input"
                                   value="<?php echo $_GET['date_to'] ?? ''; ?>"
                                   onchange="this.form.submit()">
                        </div>
                    </form>
                </div>
                
                <!-- Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Vehicle
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Assigned To
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Start Location
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        End Location
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Start Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        End Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($filtered_records as $record): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $record['id']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($record['vehicle_reg']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $record['assigned_to'] ? htmlspecialchars($record['assigned_to']) : 'Unassigned'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($record['assignment_type']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($record['start_location']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($record['end_location']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M d, Y', strtotime($record['start_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $record['end_date'] ? date('M d, Y', strtotime($record['end_date'])) : 'Ongoing'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $record['end_date'] ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800'; ?>">
                                                <?php echo $record['end_date'] ? 'Completed' : 'Active'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <a href="assignment-details.php?id=<?php echo $record['id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
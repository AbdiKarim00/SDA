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

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_status') {
    $maintenance_id = $_POST['maintenance_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    
    if ($maintenance_id && $new_status) {
        // In a real application, this would update the database
        $_SESSION['success'] = 'Maintenance status updated successfully.';
    } else {
        $_SESSION['error'] = 'Invalid request.';
    }
    header('Location: maintenance.php');
    exit;
}

// Filter maintenance records
$filtered_records = $maintenance_records;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $status_filter = $_GET['status'] ?? '';
    $vehicle_filter = $_GET['vehicle'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';

    if ($status_filter || $vehicle_filter || $date_from || $date_to || $search) {
        $filtered_records = array_filter($maintenance_records, function($record) use ($status_filter, $vehicle_filter, $date_from, $date_to, $search) {
            $matches = true;
            
            if ($status_filter && $record['status'] !== $status_filter) {
                $matches = false;
            }
            
            if ($vehicle_filter && $record['vehicle_reg'] !== $vehicle_filter) {
                $matches = false;
            }
            
            if ($date_from && strtotime($record['service_date']) < strtotime($date_from)) {
                $matches = false;
}

            if ($date_to && strtotime($record['service_date']) > strtotime($date_to)) {
                $matches = false;
}

            if ($search) {
                $search = strtolower($search);
                $searchable_fields = [
                    $record['vehicle_reg'],
                    $record['service_type'],
                    $record['service_provider'],
                    $record['description'],
                    $record['reported_by']
                ];
                
                $found = false;
                foreach ($searchable_fields as $field) {
                    if (strpos(strtolower($field), $search) !== false) {
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
    header('Content-Disposition: attachment; filename="maintenance_records.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'Maintenance ID',
        'Vehicle Registration',
        'Service Type',
        'Service Date',
        'Odometer',
        'Service Provider',
        'Cost',
        'Description',
        'Next Service',
        'Status',
        'Reported By',
        'Date Added'
    ]);
    
    // Add data rows
    foreach ($filtered_records as $record) {
        fputcsv($output, [
            $record['id'],
            $record['vehicle_reg'],
            $record['service_type'],
            $record['service_date'],
            $record['odometer'],
            $record['service_provider'],
            $record['cost'],
            $record['description'],
            $record['next_service'],
            $record['status'],
            $record['reported_by'],
            $record['date_added']
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
    <title>Maintenance Management - Transport IMS</title>
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
                'Maintenance Management',
                'Manage vehicle maintenance records and compliance'
            ); ?>
            
            <div class="bg-white rounded-lg shadow">
                <!-- Top Bar -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <input type="text" 
                                       class="form-input pl-10" 
                                       placeholder="Search maintenance records..."
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
                            <a href="add-maintenance.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                New Maintenance Record
                            </a>
                        </div>
                    </div>

                    <!-- Filters Form -->
                    <form id="filter-form" method="GET" class="hidden mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="Completed" <?php echo ($_GET['status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Pending" <?php echo ($_GET['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo ($_GET['status'] ?? '') === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Cancelled" <?php echo ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            </div>
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
                                        Service Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Service Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Odometer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Next Service
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reported By
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
                                            <?php echo htmlspecialchars($record['service_type']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M d, Y', strtotime($record['service_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo number_format($record['odometer']); ?> km
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            KES <?php echo number_format($record['cost']); ?>
                                    </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M d, Y', strtotime($record['next_service'])); ?>
                                    </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                switch ($record['status']) {
                                                    case 'Completed':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'Pending':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'In Progress':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'Cancelled':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                        ?>">
                                                <?php echo htmlspecialchars($record['status']); ?>
                                        </span>
                                    </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($record['reported_by']); ?>
                                    </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <a href="maintenance-details.php?id=<?php echo $record['id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="edit-maintenance.php?id=<?php echo $record['id']; ?>" 
                                                   class="text-yellow-600 hover:text-yellow-900">
                                                    <i class="bi bi-pencil"></i>
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
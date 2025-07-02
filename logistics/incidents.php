<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for incidents
$incidents = [
    [
        'id' => 1,
        'vehicle_reg' => 'KAA 123A',
        'driver' => 'John Doe',
        'incident_type' => 'Accident',
        'severity' => 'Minor',
        'date' => '2024-03-15',
        'location' => 'Mombasa Road, Nairobi',
        'description' => 'Rear-end collision with minor damage to bumper',
        'injuries' => 'None',
        'damage_cost' => 25000,
        'insurance_claim' => 'Pending',
        'status' => 'Under Investigation',
        'reported_by' => 'John Doe',
        'date_reported' => '2024-03-15',
        'last_updated' => '2024-03-15'
    ],
    [
        'id' => 2,
        'vehicle_reg' => 'KAA 456B',
        'driver' => 'Jane Smith',
        'incident_type' => 'Breakdown',
        'severity' => 'Major',
        'date' => '2024-03-10',
        'location' => 'Thika Road, Nairobi',
        'description' => 'Engine failure due to overheating',
        'injuries' => 'None',
        'damage_cost' => 150000,
        'insurance_claim' => 'Not Applicable',
        'status' => 'Resolved',
        'reported_by' => 'Jane Smith',
        'date_reported' => '2024-03-10',
        'last_updated' => '2024-03-12'
    ],
    [
        'id' => 3,
        'vehicle_reg' => 'KAA 789C',
        'driver' => 'Robert Johnson',
        'incident_type' => 'Theft',
        'severity' => 'Critical',
        'date' => '2024-03-05',
        'location' => 'Industrial Area, Nairobi',
        'description' => 'Vehicle stolen from parking lot',
        'injuries' => 'None',
        'damage_cost' => 0,
        'insurance_claim' => 'Filed',
        'status' => 'Under Investigation',
        'reported_by' => 'Robert Johnson',
        'date_reported' => '2024-03-05',
        'last_updated' => '2024-03-15'
    ]
];

// Filter incidents
$filtered_records = $incidents;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $vehicle_filter = $_GET['vehicle'] ?? '';
    $driver_filter = $_GET['driver'] ?? '';
    $type_filter = $_GET['type'] ?? '';
    $severity_filter = $_GET['severity'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';

    if ($vehicle_filter || $driver_filter || $type_filter || $severity_filter || $status_filter || $date_from || $date_to || $search) {
        $filtered_records = array_filter($incidents, function($record) use ($vehicle_filter, $driver_filter, $type_filter, $severity_filter, $status_filter, $date_from, $date_to, $search) {
            $matches = true;
            
            if ($vehicle_filter && $record['vehicle_reg'] !== $vehicle_filter) {
                $matches = false;
            }
            
            if ($driver_filter && $record['driver'] !== $driver_filter) {
                $matches = false;
            }
            
            if ($type_filter && $record['incident_type'] !== $type_filter) {
                $matches = false;
            }
            
            if ($severity_filter && $record['severity'] !== $severity_filter) {
                $matches = false;
            }
            
            if ($status_filter && $record['status'] !== $status_filter) {
                $matches = false;
            }
            
            if ($date_from && strtotime($record['date']) < strtotime($date_from)) {
                $matches = false;
            }
            
            if ($date_to && strtotime($record['date']) > strtotime($date_to)) {
                $matches = false;
            }
            
            if ($search) {
                $search = strtolower($search);
                $searchable_fields = [
                    $record['vehicle_reg'],
                    $record['driver'],
                    $record['incident_type'],
                    $record['location'],
                    $record['description'],
                    $record['reported_by']
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
    header('Content-Disposition: attachment; filename="incident_reports.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'Incident ID',
        'Vehicle Registration',
        'Driver',
        'Incident Type',
        'Severity',
        'Date',
        'Location',
        'Description',
        'Injuries',
        'Damage Cost',
        'Insurance Claim',
        'Status',
        'Reported By',
        'Date Reported',
        'Last Updated'
    ]);
    
    // Add data rows
    foreach ($filtered_records as $record) {
        fputcsv($output, [
            $record['id'],
            $record['vehicle_reg'],
            $record['driver'],
            $record['incident_type'],
            $record['severity'],
            $record['date'],
            $record['location'],
            $record['description'],
            $record['injuries'],
            $record['damage_cost'],
            $record['insurance_claim'],
            $record['status'],
            $record['reported_by'],
            $record['date_reported'],
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
    <title>Incident Reports - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('incidents'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Incident Reports',
                'View and manage vehicle incident reports'
            ); ?>

            <div class="bg-white rounded-lg shadow">
                <!-- Top Bar -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <input type="text" 
                                       class="form-input pl-10" 
                                       placeholder="Search incident reports..."
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
                            <a href="add-incident.php" class="btn btn-primary">
                                <i class="bi bi-plus"></i>
                                Report Incident
                            </a>
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
                                <option value="Robert Johnson" <?php echo ($_GET['driver'] ?? '') === 'Robert Johnson' ? 'selected' : ''; ?>>Robert Johnson</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Incident Type</label>
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="Accident" <?php echo ($_GET['type'] ?? '') === 'Accident' ? 'selected' : ''; ?>>Accident</option>
                                <option value="Breakdown" <?php echo ($_GET['type'] ?? '') === 'Breakdown' ? 'selected' : ''; ?>>Breakdown</option>
                                <option value="Theft" <?php echo ($_GET['type'] ?? '') === 'Theft' ? 'selected' : ''; ?>>Theft</option>
                                <option value="Vandalism" <?php echo ($_GET['type'] ?? '') === 'Vandalism' ? 'selected' : ''; ?>>Vandalism</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Severity</label>
                            <select name="severity" class="form-select" onchange="this.form.submit()">
                                <option value="">All Severities</option>
                                <option value="Minor" <?php echo ($_GET['severity'] ?? '') === 'Minor' ? 'selected' : ''; ?>>Minor</option>
                                <option value="Major" <?php echo ($_GET['severity'] ?? '') === 'Major' ? 'selected' : ''; ?>>Major</option>
                                <option value="Critical" <?php echo ($_GET['severity'] ?? '') === 'Critical' ? 'selected' : ''; ?>>Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="Under Investigation" <?php echo ($_GET['status'] ?? '') === 'Under Investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                                <option value="Resolved" <?php echo ($_GET['status'] ?? '') === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="Closed" <?php echo ($_GET['status'] ?? '') === 'Closed' ? 'selected' : ''; ?>>Closed</option>
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
                                    Driver
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Severity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location
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
                                        <?php echo htmlspecialchars($record['driver']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($record['incident_type']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch ($record['severity']) {
                                                case 'Minor':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'Major':
                                                    echo 'bg-orange-100 text-orange-800';
                                                    break;
                                                case 'Critical':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($record['severity']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M d, Y', strtotime($record['date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($record['location']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch ($record['status']) {
                                                case 'Under Investigation':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'Resolved':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Closed':
                                                    echo 'bg-gray-100 text-gray-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($record['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex space-x-3">
                                            <a href="incident-details.php?id=<?php echo $record['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit-incident.php?id=<?php echo $record['id']; ?>" 
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
</body>
</html> 
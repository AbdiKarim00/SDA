<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for vehicles marked for disposal
$vehicles_for_disposal = [
    [
        'id' => 1,
        'registration_number' => 'GKB 673S',
        'make' => 'Toyota',
        'model' => 'Hilux',
        'asset_condition' => 'Marked for Disposal',
        'reason' => 'Exceeded useful life, unserviceable',
        'net_book_value' => 3770500,
        'accumulated_depreciation' => 3770500,
        'current_location' => 'MULEI UNSERVICEABLE YARD',
        'disposal_status' => 'Pending Approval',
        'marked_date' => '2024-03-15',
        'approval_chain' => [
            ['role' => 'Logistics Officer', 'status' => 'Approved', 'date' => '2024-03-15'],
            ['role' => 'Maintenance Manager', 'status' => 'Pending', 'date' => null],
            ['role' => 'Finance Officer', 'status' => 'Pending', 'date' => null],
            ['role' => 'Senior Management', 'status' => 'Pending', 'date' => null]
        ]
    ],
    [
        'id' => 2,
        'registration_number' => 'KAA 456B',
        'make' => 'Isuzu',
        'model' => 'NPR',
        'asset_condition' => 'Marked for Disposal',
        'reason' => 'High maintenance costs',
        'net_book_value' => 2500000,
        'accumulated_depreciation' => 2500000,
        'current_location' => 'Main Yard',
        'disposal_status' => 'In Assessment',
        'marked_date' => '2024-03-14',
        'approval_chain' => [
            ['role' => 'Logistics Officer', 'status' => 'Approved', 'date' => '2024-03-14'],
            ['role' => 'Maintenance Manager', 'status' => 'In Progress', 'date' => '2024-03-14'],
            ['role' => 'Finance Officer', 'status' => 'Pending', 'date' => null],
            ['role' => 'Senior Management', 'status' => 'Pending', 'date' => null]
        ]
    ]
];

// Calculate statistics
$total_pending = count(array_filter($vehicles_for_disposal, fn($v) => $v['disposal_status'] === 'Pending Approval'));
$total_in_assessment = count(array_filter($vehicles_for_disposal, fn($v) => $v['disposal_status'] === 'In Assessment'));
$total_approved = count(array_filter($vehicles_for_disposal, fn($v) => $v['disposal_status'] === 'Approved'));
$total_completed = count(array_filter($vehicles_for_disposal, fn($v) => $v['disposal_status'] === 'Completed'));

// Filter vehicles
$filtered_vehicles = $vehicles_for_disposal;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';

    if ($status_filter || $search) {
        $filtered_vehicles = array_filter($vehicles_for_disposal, function($vehicle) use ($status_filter, $search) {
            $matches = true;
            
            if ($status_filter && $vehicle['disposal_status'] !== $status_filter) {
                $matches = false;
            }
            
            if ($search) {
                $search = strtolower($search);
                $searchable_fields = [
                    $vehicle['registration_number'],
                    $vehicle['make'],
                    $vehicle['model'],
                    $vehicle['reason'],
                    $vehicle['current_location']
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
    header('Content-Disposition: attachment; filename="disposal_records.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID',
        'Registration Number',
        'Make',
        'Model',
        'Reason',
        'Net Book Value',
        'Current Location',
        'Disposal Status',
        'Marked Date'
    ]);
    
    // Add data rows
    foreach ($filtered_vehicles as $vehicle) {
        fputcsv($output, [
            $vehicle['id'],
            $vehicle['registration_number'],
            $vehicle['make'],
            $vehicle['model'],
            $vehicle['reason'],
            $vehicle['net_book_value'],
            $vehicle['current_location'],
            $vehicle['disposal_status'],
            $vehicle['marked_date']
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
    <title>Vehicle Disposal Management - Transport IMS</title>
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
                'Vehicle Disposal Management',
                'Manage vehicle disposal process and approvals'
            ); ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="bi bi-clock-history text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Pending Approval</h3>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $total_pending; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="bi bi-clipboard-check text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">In Assessment</h3>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $total_in_assessment; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="bi bi-check-circle text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Approved</h3>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $total_approved; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="bi bi-archive text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Completed</h3>
                            <p class="text-2xl font-semibold text-gray-800"><?php echo $total_completed; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow">
                <!-- Top Bar -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <input type="text" 
                                       class="form-input pl-10" 
                                       placeholder="Search disposal records..."
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
                            <a href="disposal-new.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                New Disposal Request
                            </a>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form id="filter-form" class="hidden mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="Pending Approval" <?php echo ($_GET['status'] ?? '') === 'Pending Approval' ? 'selected' : ''; ?>>Pending Approval</option>
                                <option value="In Assessment" <?php echo ($_GET['status'] ?? '') === 'In Assessment' ? 'selected' : ''; ?>>In Assessment</option>
                                <option value="Approved" <?php echo ($_GET['status'] ?? '') === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Completed" <?php echo ($_GET['status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Vehicles List -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($filtered_vehicles as $vehicle): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['registration_number']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($vehicle['reason']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">KES <?php echo number_format($vehicle['net_book_value'], 2); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($vehicle['current_location']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch ($vehicle['disposal_status']) {
                                                case 'Pending Approval':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'In Assessment':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'Approved':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Completed':
                                                    echo 'bg-purple-100 text-purple-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo $vehicle['disposal_status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <?php foreach ($vehicle['approval_chain'] as $approval): ?>
                                                <div class="flex flex-col items-center">
                                                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                                                        <?php
                                                        switch ($approval['status']) {
                                                            case 'Approved':
                                                                echo 'bg-green-100 text-green-600';
                                                                break;
                                                            case 'In Progress':
                                                                echo 'bg-blue-100 text-blue-600';
                                                                break;
                                                            case 'Pending':
                                                                echo 'bg-gray-100 text-gray-400';
                                                                break;
                                                            default:
                                                                echo 'bg-gray-100 text-gray-400';
                                                        }
                                                        ?>">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <span class="text-xs text-gray-500 mt-1"><?php echo $approval['role']; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="disposal-details.php?id=<?php echo $vehicle['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="disposal-edit.php?id=<?php echo $vehicle['id']; ?>" 
                                           class="text-yellow-600 hover:text-yellow-900">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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
<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once 'mock_data.php';

// Get vehicle ID from URL
$vehicle_id = $_GET['id'] ?? null;
if (!$vehicle_id) {
    header('Location: vehicles.php');
    exit;
}

// Get mock data
$vehicles = get_mock_data('vehicles');
$vehicle = array_filter($vehicles, fn($v) => $v['id'] == $vehicle_id);
$vehicle = reset($vehicle);

if (!$vehicle) {
    $_SESSION['error'] = 'Vehicle not found.';
    header('Location: vehicles.php');
    exit;
}

// Get related data
$maintenance_records = array_filter(get_mock_data('maintenance'), fn($m) => $m['vehicle_id'] == $vehicle_id);
$incidents = array_filter(get_mock_data('incidents'), fn($i) => $i['vehicle_id'] == $vehicle_id);
$trips = array_filter(get_mock_data('trips'), fn($t) => $t['vehicle_id'] == $vehicle_id);
$fuel_cards = array_filter(get_mock_data('fuel_cards'), fn($f) => $f['vehicle_id'] == $vehicle_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Details - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        activeTab: 'details'
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('vehicles'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Vehicle Details',
                'View detailed information about the selected vehicle'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Vehicle Overview -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    <?php echo htmlspecialchars($vehicle['registration_number']); ?>
                                </h2>
                                <p class="text-gray-600">
                                    <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="edit-vehicle.php?id=<?php echo $vehicle['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>
                                    Edit Vehicle
                                </a>
                                <button class="btn btn-secondary">
                                    <i class="bi bi-printer me-2"></i>
                                    Print Details
                                </button>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mt-4">
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                <?php
                                echo match($vehicle['status']) {
                                    'Available' => 'bg-green-100 text-green-800',
                                    'In Maintenance' => 'bg-yellow-100 text-yellow-800',
                                    'On Trip' => 'bg-blue-100 text-blue-800',
                                    'Decommissioned' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>">
                                <?php echo htmlspecialchars($vehicle['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'details'"
                                    :class="{ 'border-primary text-primary': activeTab === 'details' }"
                                    class="px-4 py-2 border-b-2 font-medium text-sm">
                                Vehicle Details
                            </button>
                            <button @click="activeTab = 'maintenance'"
                                    :class="{ 'border-primary text-primary': activeTab === 'maintenance' }"
                                    class="px-4 py-2 border-b-2 font-medium text-sm">
                                Maintenance History
                            </button>
                            <button @click="activeTab = 'incidents'"
                                    :class="{ 'border-primary text-primary': activeTab === 'incidents' }"
                                    class="px-4 py-2 border-b-2 font-medium text-sm">
                                Incidents
                            </button>
                            <button @click="activeTab = 'trips'"
                                    :class="{ 'border-primary text-primary': activeTab === 'trips' }"
                                    class="px-4 py-2 border-b-2 font-medium text-sm">
                                Trip History
                            </button>
                            <button @click="activeTab = 'fuel'"
                                    :class="{ 'border-primary text-primary': activeTab === 'fuel' }"
                                    class="px-4 py-2 border-b-2 font-medium text-sm">
                                Fuel Usage
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Vehicle Details Tab -->
                        <div x-show="activeTab === 'details'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                                    <dl class="grid grid-cols-1 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($vehicle['registration_number']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Make & Model</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Vehicle Type</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($vehicle['vehicle_type']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Current Odometer</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo number_format($vehicle['current_odometer']); ?> km</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Next Service Due</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <?php 
                                                $remaining = $vehicle['next_service_odometer'] - $vehicle['current_odometer'];
                                                $status_class = $remaining <= 1000 ? 'text-red-600' : ($remaining <= 2000 ? 'text-yellow-600' : 'text-green-600');
                                                echo '<span class="' . $status_class . '">' . number_format($vehicle['next_service_odometer']) . ' km</span>';
                                                echo ' <span class="text-gray-500">(' . number_format($remaining) . ' km remaining)</span>';
                                                ?>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                                            <dd class="mt-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    <?php echo match($vehicle['status']) {
                                                        'Available' => 'bg-green-100 text-green-800',
                                                        'In Service' => 'bg-yellow-100 text-yellow-800',
                                                        'In Maintenance' => 'bg-red-100 text-red-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    }; ?>">
                                                    <?php echo htmlspecialchars($vehicle['status']); ?>
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Technical Details -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900">Technical Details</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Chassis Number</p>
                                            <p class="text-sm font-medium"><?php echo htmlspecialchars($vehicle['chassis_number']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Engine Number</p>
                                            <p class="text-sm font-medium"><?php echo htmlspecialchars($vehicle['engine_number']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Current Mileage</p>
                                            <p class="text-sm font-medium"><?php echo number_format($vehicle['current_mileage']); ?> km</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Date of Purchase</p>
                                            <p class="text-sm font-medium"><?php echo date('M d, Y', strtotime($vehicle['date_of_purchase'])); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Funded By</p>
                                            <p class="text-sm font-medium"><?php echo htmlspecialchars($vehicle['funded_by']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Assigned Driver</p>
                                            <p class="text-sm font-medium"><?php echo htmlspecialchars($vehicle['assigned_driver']); ?></p>
                                </div>
                            </div>
                        </div>

                                <!-- Compliance Information -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900">Compliance Information</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Insurance Expiry</p>
                                            <p class="text-sm font-medium"><?php echo date('M d, Y', strtotime($vehicle['insurance_expiry'])); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Next Service Due</p>
                                            <p class="text-sm font-medium"><?php echo date('M d, Y', strtotime($vehicle['next_service_due'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                        </div>

                        <!-- Maintenance History Tab -->
                        <div x-show="activeTab === 'maintenance'">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Maintenance History</h3>
                                    <a href="add-maintenance.php?vehicle_id=<?php echo $vehicle['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus me-2"></i>
                                        Add Maintenance Record
                                    </a>
                                </div>

                                <?php if (empty($maintenance_records)): ?>
                                    <p class="text-gray-500">No maintenance records found.</p>
                                <?php else: ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service Type</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php foreach ($maintenance_records as $record): ?>
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo date('M d, Y', strtotime($record['service_date'])); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($record['service_type']); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($record['service_provider']); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            KES <?php echo number_format($record['cost']); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                <?php
                                                                echo match($record['status']) {
                                                                    'Completed' => 'bg-green-100 text-green-800',
                                                                    'In Progress' => 'bg-yellow-100 text-yellow-800',
                                                                    'Scheduled' => 'bg-blue-100 text-blue-800',
                                                                    default => 'bg-gray-100 text-gray-800'
                                                                };
                                                                ?>">
                                                                <?php echo htmlspecialchars($record['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <a href="maintenance-details.php?id=<?php echo $record['id']; ?>" 
                                                               class="text-blue-600 hover:text-blue-900">
                                                                View Details
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Incidents Tab -->
                        <div x-show="activeTab === 'incidents'">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Incident History</h3>
                                    <a href="add-incident.php?vehicle_id=<?php echo $vehicle['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus me-2"></i>
                                        Report Incident
                                    </a>
                                </div>

                                <?php if (empty($incidents)): ?>
                                    <p class="text-gray-500">No incidents reported.</p>
                                <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Damage</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                                <?php foreach ($incidents as $incident): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo date('M d, Y', strtotime($incident['incident_date'])); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($incident['incident_type']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($incident['location']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($incident['damage_assessment']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                <?php
                                                                echo match($incident['status']) {
                                                                    'Reported' => 'bg-yellow-100 text-yellow-800',
                                                                    'Under Investigation' => 'bg-blue-100 text-blue-800',
                                                                    'Resolved' => 'bg-green-100 text-green-800',
                                                                    default => 'bg-gray-100 text-gray-800'
                                                                };
                                                                ?>">
                                                                <?php echo htmlspecialchars($incident['status']); ?>
                                                    </span>
                                                </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <a href="incident-details.php?id=<?php echo $incident['id']; ?>" 
                                                               class="text-blue-600 hover:text-blue-900">
                                                                View Details
                                                            </a>
                                                        </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Trip History Tab -->
                        <div x-show="activeTab === 'trips'">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Trip History</h3>
                                    <a href="trips.php?vehicle_id=<?php echo $vehicle['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus me-2"></i>
                                        New Trip
                                    </a>
                                </div>

                                <?php if (empty($trips)): ?>
                                    <p class="text-gray-500">No trips recorded.</p>
                                <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                                <?php foreach ($trips as $trip): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo date('M d, Y', strtotime($trip['start_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($trip['origin'] . ' → ' . $trip['destination']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($trip['driver_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <?php echo htmlspecialchars($trip['purpose']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                <?php
                                                                echo match($trip['status']) {
                                                                    'In Progress' => 'bg-yellow-100 text-yellow-800',
                                                                    'Completed' => 'bg-green-100 text-green-800',
                                                                    'Scheduled' => 'bg-blue-100 text-blue-800',
                                                                    default => 'bg-gray-100 text-gray-800'
                                                                };
                                                                ?>">
                                                        <?php echo htmlspecialchars($trip['status']); ?>
                                                    </span>
                                                </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <a href="trip-details.php?id=<?php echo $trip['id']; ?>" 
                                                               class="text-blue-600 hover:text-blue-900">
                                                                View Details
                                                            </a>
                                                        </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Fuel Usage Tab -->
                        <div x-show="activeTab === 'fuel'">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Fuel Usage</h3>
                                    <a href="allocate-card.php?vehicle_id=<?php echo $vehicle['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus me-2"></i>
                                        Allocate Fuel Card
                                    </a>
                                </div>

                                <?php if (empty($fuel_cards)): ?>
                                    <p class="text-gray-500">No fuel cards allocated.</p>
                                <?php else: ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <?php foreach ($fuel_cards as $card): ?>
                                            <div class="bg-white border rounded-lg p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="text-lg font-medium text-gray-900">
                                                            Card <?php echo htmlspecialchars($card['card_number']); ?>
                                                        </h4>
                                                        <p class="text-sm text-gray-500">
                                                            Issued: <?php echo date('M d, Y', strtotime($card['issue_date'])); ?>
                                                        </p>
                                                    </div>
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                        <?php
                                                        echo match($card['status']) {
                                                            'Active' => 'bg-green-100 text-green-800',
                                                            'Suspended' => 'bg-red-100 text-red-800',
                                                            default => 'bg-gray-100 text-gray-800'
                                                        };
                                                        ?>">
                                                        <?php echo htmlspecialchars($card['status']); ?>
                                                    </span>
                                                </div>
                                                <div class="mt-4 space-y-2">
                                                    <div class="flex justify-between">
                                                        <span class="text-sm text-gray-500">Monthly Limit</span>
                                                        <span class="text-sm font-medium">KES <?php echo number_format($card['monthly_limit']); ?></span>
                            </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-sm text-gray-500">Current Balance</span>
                                                        <span class="text-sm font-medium">KES <?php echo number_format($card['current_balance']); ?></span>
                        </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-sm text-gray-500">Last Transaction</span>
                                                        <span class="text-sm font-medium">
                                                            <?php echo date('M d, Y', strtotime($card['last_transaction'])); ?>
                                                            (KES <?php echo number_format($card['last_transaction_amount']); ?>)
                                                        </span>
                    </div>
                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
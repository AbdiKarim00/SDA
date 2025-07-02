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
        'time' => '14:30',
        'location' => 'Mombasa Road, Nairobi',
        'description' => 'Rear-end collision with minor damage to bumper',
        'injuries' => 'None',
        'damage_cost' => 25000,
        'insurance_claim' => 'Pending',
        'status' => 'Under Investigation',
        'reported_by' => 'John Doe',
        'date_reported' => '2024-03-15',
        'last_updated' => '2024-03-15',
        'witnesses' => [
            [
                'name' => 'Sarah Johnson',
                'contact' => '+254 712 345 678',
                'statement' => 'I saw the vehicle being hit from behind by a speeding truck.'
            ]
        ],
        'photos' => [
            'damage_1.jpg',
            'damage_2.jpg',
            'scene_1.jpg'
        ],
        'police_report' => 'PR-2024-001',
        'insurance_details' => [
            'policy_number' => 'INS-2024-001',
            'provider' => 'Kenya Insurance Co.',
            'coverage' => 'Comprehensive'
        ],
        'repair_status' => 'Pending',
        'estimated_repair_time' => '5 days',
        'cost_breakdown' => [
            'parts' => 15000,
            'labor' => 8000,
            'other' => 2000
        ]
    ]
];

// Get incident ID from URL
$incident_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Find the incident record
$incident = null;
foreach ($incidents as $record) {
    if ($record['id'] === $incident_id) {
        $incident = $record;
        break;
    }
}

// If incident not found, redirect to list
if (!$incident) {
    header('Location: incidents.php?error=incident_not_found');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Details - Transport IMS</title>
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
                'Incident Details',
                'View detailed information about incident #' . $incident['id']
            ); ?>

            <div class="space-y-6">
                <!-- Back Button -->
                <div>
                    <a href="incidents.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to Incidents
                    </a>
                </div>

                <!-- Incident Information -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-900">
                                    Incident #<?php echo $incident['id']; ?>
                                </h2>
                                <p class="text-gray-600">
                                    <?php echo htmlspecialchars($incident['incident_type']); ?> - 
                                    <?php echo date('F d, Y', strtotime($incident['date'])); ?> at 
                                    <?php echo $incident['time']; ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold
                                    <?php
                                    switch ($incident['severity']) {
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
                                    <?php echo htmlspecialchars($incident['severity']); ?> Severity
                                </span>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold
                                    <?php
                                    switch ($incident['status']) {
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
                                    <?php echo htmlspecialchars($incident['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Vehicle</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['vehicle_reg']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Driver</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['driver']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Location</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['location']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Reported By</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['reported_by']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Description</h3>
                                <p class="text-sm text-gray-900">
                                    <?php echo nl2br(htmlspecialchars($incident['description'])); ?>
                                </p>
                            </div>

                            <!-- Damage & Cost Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Damage & Cost Information</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Total Damage Cost</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            KES <?php echo number_format($incident['damage_cost'], 2); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Insurance Claim</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['insurance_claim']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Repair Status</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['repair_status']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Estimated Repair Time</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['estimated_repair_time']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Insurance Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Insurance Information</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Policy Number</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['insurance_details']['policy_number']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Provider</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['insurance_details']['provider']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Coverage Type</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['insurance_details']['coverage']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Police Report</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($incident['police_report']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Witness Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Witness Information</h3>
                                <?php foreach ($incident['witnesses'] as $witness): ?>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Name</label>
                                                <p class="mt-1 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($witness['name']); ?>
                                                </p>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-500">Contact</label>
                                                <p class="mt-1 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($witness['contact']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <label class="text-sm font-medium text-gray-500">Statement</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                <?php echo htmlspecialchars($witness['statement']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Cost Breakdown -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Cost Breakdown</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Parts</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                KES <?php echo number_format($incident['cost_breakdown']['parts'], 2); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Labor</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                KES <?php echo number_format($incident['cost_breakdown']['labor'], 2); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Other Costs</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                KES <?php echo number_format($incident['cost_breakdown']['other'], 2); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Total</label>
                                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                                KES <?php echo number_format($incident['damage_cost'], 2); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Photos -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Incident Photos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <?php foreach ($incident['photos'] as $photo): ?>
                                    <div class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="../assets/images/incidents/<?php echo htmlspecialchars($photo); ?>" 
                                             alt="Incident photo"
                                             class="absolute inset-0 w-full h-full object-cover">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex justify-end gap-4">
                            <a href="edit-incident.php?id=<?php echo $incident['id']; ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-pencil"></i>
                                Edit Incident
                            </a>
                            <button type="button" 
                                    class="btn btn-primary"
                                    onclick="window.print()">
                                <i class="bi bi-printer"></i>
                                Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
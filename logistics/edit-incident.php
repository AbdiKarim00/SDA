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
    <title>Edit Incident - Transport IMS</title>
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
                'Edit Incident',
                'Update incident report #' . $incident['id']
            ); ?>

            <div class="space-y-6">
                <!-- Back Button -->
                <div>
                    <a href="incident-details.php?id=<?php echo $incident['id']; ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to Incident Details
                    </a>
                </div>

                <!-- Incident Form -->
                <div class="bg-white rounded-lg shadow">
                    <form action="process-incident.php" method="POST" enctype="multipart/form-data" class="p-6">
                        <input type="hidden" name="id" value="<?php echo $incident['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                                
                                <div>
                                    <label for="vehicle_reg" class="form-label">Vehicle</label>
                                    <select name="vehicle_reg" id="vehicle_reg" class="form-select" required>
                                        <option value="">Select Vehicle</option>
                                        <option value="KAA 123A" <?php echo $incident['vehicle_reg'] === 'KAA 123A' ? 'selected' : ''; ?>>KAA 123A</option>
                                        <option value="KAA 456B" <?php echo $incident['vehicle_reg'] === 'KAA 456B' ? 'selected' : ''; ?>>KAA 456B</option>
                                        <option value="KAA 789C" <?php echo $incident['vehicle_reg'] === 'KAA 789C' ? 'selected' : ''; ?>>KAA 789C</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="driver" class="form-label">Driver</label>
                                    <select name="driver" id="driver" class="form-select" required>
                                        <option value="">Select Driver</option>
                                        <option value="John Doe" <?php echo $incident['driver'] === 'John Doe' ? 'selected' : ''; ?>>John Doe</option>
                                        <option value="Jane Smith" <?php echo $incident['driver'] === 'Jane Smith' ? 'selected' : ''; ?>>Jane Smith</option>
                                        <option value="Robert Johnson" <?php echo $incident['driver'] === 'Robert Johnson' ? 'selected' : ''; ?>>Robert Johnson</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="incident_type" class="form-label">Incident Type</label>
                                    <select name="incident_type" id="incident_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="Accident" <?php echo $incident['incident_type'] === 'Accident' ? 'selected' : ''; ?>>Accident</option>
                                        <option value="Breakdown" <?php echo $incident['incident_type'] === 'Breakdown' ? 'selected' : ''; ?>>Breakdown</option>
                                        <option value="Theft" <?php echo $incident['incident_type'] === 'Theft' ? 'selected' : ''; ?>>Theft</option>
                                        <option value="Vandalism" <?php echo $incident['incident_type'] === 'Vandalism' ? 'selected' : ''; ?>>Vandalism</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="severity" class="form-label">Severity</label>
                                    <select name="severity" id="severity" class="form-select" required>
                                        <option value="">Select Severity</option>
                                        <option value="Minor" <?php echo $incident['severity'] === 'Minor' ? 'selected' : ''; ?>>Minor</option>
                                        <option value="Major" <?php echo $incident['severity'] === 'Major' ? 'selected' : ''; ?>>Major</option>
                                        <option value="Critical" <?php echo $incident['severity'] === 'Critical' ? 'selected' : ''; ?>>Critical</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" 
                                           name="date" 
                                           id="date" 
                                           class="form-input" 
                                           value="<?php echo $incident['date']; ?>"
                                           required>
                                </div>

                                <div>
                                    <label for="time" class="form-label">Time</label>
                                    <input type="time" 
                                           name="time" 
                                           id="time" 
                                           class="form-input" 
                                           value="<?php echo $incident['time']; ?>"
                                           required>
                                </div>

                                <div>
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" 
                                           name="location" 
                                           id="location" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($incident['location']); ?>"
                                           required>
                                </div>
                            </div>

                            <!-- Description & Details -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Description & Details</h3>

                                <div>
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" 
                                              id="description" 
                                              class="form-input" 
                                              rows="4"
                                              required><?php echo htmlspecialchars($incident['description']); ?></textarea>
                                </div>

                                <div>
                                    <label for="injuries" class="form-label">Injuries</label>
                                    <textarea name="injuries" 
                                              id="injuries" 
                                              class="form-input" 
                                              rows="2"><?php echo htmlspecialchars($incident['injuries']); ?></textarea>
                                </div>

                                <div>
                                    <label for="damage_cost" class="form-label">Estimated Damage Cost (KES)</label>
                                    <input type="number" 
                                           name="damage_cost" 
                                           id="damage_cost" 
                                           class="form-input" 
                                           value="<?php echo $incident['damage_cost']; ?>"
                                           min="0"
                                           step="0.01">
                                </div>

                                <div>
                                    <label for="insurance_claim" class="form-label">Insurance Claim Status</label>
                                    <select name="insurance_claim" id="insurance_claim" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="Not Filed" <?php echo $incident['insurance_claim'] === 'Not Filed' ? 'selected' : ''; ?>>Not Filed</option>
                                        <option value="Pending" <?php echo $incident['insurance_claim'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Filed" <?php echo $incident['insurance_claim'] === 'Filed' ? 'selected' : ''; ?>>Filed</option>
                                        <option value="Approved" <?php echo $incident['insurance_claim'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?php echo $incident['insurance_claim'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="Not Applicable" <?php echo $incident['insurance_claim'] === 'Not Applicable' ? 'selected' : ''; ?>>Not Applicable</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="">Select Status</option>
                                        <option value="Under Investigation" <?php echo $incident['status'] === 'Under Investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                                        <option value="Resolved" <?php echo $incident['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="Closed" <?php echo $incident['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Insurance Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Insurance Information</h3>

                                <div>
                                    <label for="policy_number" class="form-label">Policy Number</label>
                                    <input type="text" 
                                           name="policy_number" 
                                           id="policy_number" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($incident['insurance_details']['policy_number']); ?>">
                                </div>

                                <div>
                                    <label for="insurance_provider" class="form-label">Insurance Provider</label>
                                    <input type="text" 
                                           name="insurance_provider" 
                                           id="insurance_provider" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($incident['insurance_details']['provider']); ?>">
                                </div>

                                <div>
                                    <label for="coverage_type" class="form-label">Coverage Type</label>
                                    <select name="coverage_type" id="coverage_type" class="form-select">
                                        <option value="">Select Coverage</option>
                                        <option value="Comprehensive" <?php echo $incident['insurance_details']['coverage'] === 'Comprehensive' ? 'selected' : ''; ?>>Comprehensive</option>
                                        <option value="Third Party" <?php echo $incident['insurance_details']['coverage'] === 'Third Party' ? 'selected' : ''; ?>>Third Party</option>
                                        <option value="Third Party Fire & Theft" <?php echo $incident['insurance_details']['coverage'] === 'Third Party Fire & Theft' ? 'selected' : ''; ?>>Third Party Fire & Theft</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="police_report" class="form-label">Police Report Number</label>
                                    <input type="text" 
                                           name="police_report" 
                                           id="police_report" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($incident['police_report']); ?>">
                                </div>
                            </div>

                            <!-- Witness Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Witness Information</h3>

                                <div id="witnesses-container">
                                    <?php foreach ($incident['witnesses'] as $index => $witness): ?>
                                        <div class="witness-entry bg-gray-50 p-4 rounded-lg mb-4">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="form-label">Name</label>
                                                    <input type="text" 
                                                           name="witness_name[]" 
                                                           class="form-input" 
                                                           value="<?php echo htmlspecialchars($witness['name']); ?>">
                                                </div>
                                                <div>
                                                    <label class="form-label">Contact</label>
                                                    <input type="tel" 
                                                           name="witness_contact[]" 
                                                           class="form-input" 
                                                           value="<?php echo htmlspecialchars($witness['contact']); ?>">
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <label class="form-label">Statement</label>
                                                <textarea name="witness_statement[]" 
                                                          class="form-input" 
                                                          rows="2"><?php echo htmlspecialchars($witness['statement']); ?></textarea>
                                            </div>
                                            <?php if ($index > 0): ?>
                                                <div class="mt-4 flex justify-end">
                                                    <button type="button" 
                                                            class="btn btn-danger"
                                                            onclick="this.parentElement.parentElement.remove()">
                                                        <i class="bi bi-trash"></i>
                                                        Remove Witness
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="button" 
                                        class="btn btn-secondary"
                                        onclick="addWitness()">
                                    <i class="bi bi-plus"></i>
                                    Add Witness
                                </button>
                            </div>

                            <!-- Photos -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Incident Photos</h3>

                                <!-- Current Photos -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <?php foreach ($incident['photos'] as $photo): ?>
                                        <div class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                            <img src="../assets/images/incidents/<?php echo htmlspecialchars($photo); ?>" 
                                                 alt="Incident photo"
                                                 class="absolute inset-0 w-full h-full object-cover">
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div>
                                    <label class="form-label">Add More Photos</label>
                                    <input type="file" 
                                           name="photos[]" 
                                           class="form-input" 
                                           accept="image/*"
                                           multiple>
                                    <p class="mt-1 text-sm text-gray-500">
                                        You can select multiple photos. Supported formats: JPG, PNG, GIF
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Update Incident Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addWitness() {
            const container = document.getElementById('witnesses-container');
            const witnessEntry = document.createElement('div');
            witnessEntry.className = 'witness-entry bg-gray-50 p-4 rounded-lg mb-4';
            witnessEntry.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Name</label>
                        <input type="text" 
                               name="witness_name[]" 
                               class="form-input" 
                               placeholder="Enter witness name">
                    </div>
                    <div>
                        <label class="form-label">Contact</label>
                        <input type="tel" 
                               name="witness_contact[]" 
                               class="form-input" 
                               placeholder="Enter contact number">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="form-label">Statement</label>
                    <textarea name="witness_statement[]" 
                              class="form-input" 
                              rows="2"
                              placeholder="Enter witness statement"></textarea>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" 
                            class="btn btn-danger"
                            onclick="this.parentElement.parentElement.remove()">
                        <i class="bi bi-trash"></i>
                        Remove Witness
                    </button>
                </div>
            `;
            container.appendChild(witnessEntry);
        }
    </script>
</body>
</html> 
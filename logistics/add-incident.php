<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Incident - Transport IMS</title>
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
                'Report Incident',
                'Create a new incident report'
            ); ?>

            <div class="space-y-6">
                <!-- Back Button -->
                <div>
                    <a href="incidents.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back to Incidents
                    </a>
                </div>

                <!-- Incident Form -->
                <div class="bg-white rounded-lg shadow">
                    <form action="process-incident.php" method="POST" enctype="multipart/form-data" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                                
                                <div>
                                    <label for="vehicle_reg" class="form-label">Vehicle</label>
                                    <select name="vehicle_reg" id="vehicle_reg" class="form-select" required>
                                        <option value="">Select Vehicle</option>
                                        <option value="KAA 123A">KAA 123A</option>
                                        <option value="KAA 456B">KAA 456B</option>
                                        <option value="KAA 789C">KAA 789C</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="driver" class="form-label">Driver</label>
                                    <select name="driver" id="driver" class="form-select" required>
                                        <option value="">Select Driver</option>
                                        <option value="John Doe">John Doe</option>
                                        <option value="Jane Smith">Jane Smith</option>
                                        <option value="Robert Johnson">Robert Johnson</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="incident_type" class="form-label">Incident Type</label>
                                    <select name="incident_type" id="incident_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="Accident">Accident</option>
                                        <option value="Breakdown">Breakdown</option>
                                        <option value="Theft">Theft</option>
                                        <option value="Vandalism">Vandalism</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="severity" class="form-label">Severity</label>
                                    <select name="severity" id="severity" class="form-select" required>
                                        <option value="">Select Severity</option>
                                        <option value="Minor">Minor</option>
                                        <option value="Major">Major</option>
                                        <option value="Critical">Critical</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" 
                                           name="date" 
                                           id="date" 
                                           class="form-input" 
                                           required>
                                </div>

                                <div>
                                    <label for="time" class="form-label">Time</label>
                                    <input type="time" 
                                           name="time" 
                                           id="time" 
                                           class="form-input" 
                                           required>
                                </div>

                                <div>
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" 
                                           name="location" 
                                           id="location" 
                                           class="form-input" 
                                           placeholder="Enter incident location"
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
                                              placeholder="Provide a detailed description of the incident"
                                              required></textarea>
                                </div>

                                <div>
                                    <label for="injuries" class="form-label">Injuries</label>
                                    <textarea name="injuries" 
                                              id="injuries" 
                                              class="form-input" 
                                              rows="2"
                                              placeholder="Describe any injuries sustained"></textarea>
                                </div>

                                <div>
                                    <label for="damage_cost" class="form-label">Estimated Damage Cost (KES)</label>
                                    <input type="number" 
                                           name="damage_cost" 
                                           id="damage_cost" 
                                           class="form-input" 
                                           placeholder="Enter estimated cost"
                                           min="0"
                                           step="0.01">
                                </div>

                                <div>
                                    <label for="insurance_claim" class="form-label">Insurance Claim Status</label>
                                    <select name="insurance_claim" id="insurance_claim" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="Not Filed">Not Filed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Filed">Filed</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Rejected">Rejected</option>
                                        <option value="Not Applicable">Not Applicable</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="">Select Status</option>
                                        <option value="Under Investigation">Under Investigation</option>
                                        <option value="Resolved">Resolved</option>
                                        <option value="Closed">Closed</option>
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
                                           placeholder="Enter policy number">
                                </div>

                                <div>
                                    <label for="insurance_provider" class="form-label">Insurance Provider</label>
                                    <input type="text" 
                                           name="insurance_provider" 
                                           id="insurance_provider" 
                                           class="form-input" 
                                           placeholder="Enter insurance provider">
                                </div>

                                <div>
                                    <label for="coverage_type" class="form-label">Coverage Type</label>
                                    <select name="coverage_type" id="coverage_type" class="form-select">
                                        <option value="">Select Coverage</option>
                                        <option value="Comprehensive">Comprehensive</option>
                                        <option value="Third Party">Third Party</option>
                                        <option value="Third Party Fire & Theft">Third Party Fire & Theft</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="police_report" class="form-label">Police Report Number</label>
                                    <input type="text" 
                                           name="police_report" 
                                           id="police_report" 
                                           class="form-input" 
                                           placeholder="Enter police report number">
                                </div>
                            </div>

                            <!-- Witness Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Witness Information</h3>

                                <div id="witnesses-container">
                                    <div class="witness-entry bg-gray-50 p-4 rounded-lg mb-4">
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
                                    </div>
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

                                <div>
                                    <label class="form-label">Upload Photos</label>
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
                                Save Incident Report
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
<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

$vehicle_id = $_GET['id'] ?? null;
if (!$vehicle_id) {
    // $_SESSION['error_message'] = 'No vehicle ID specified.';
    header('Location: vehicles.php');
    exit;
}
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
    <style>
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
        .status-badge.available { background-color: #dcfce7; color: #166534; }
        .status-badge.maintenance, .status-badge.in-maintenance { background-color: #fef9c3; color: #713f12; } /* yellow */
        .status-badge.on-trip, .status-badge.in-service { background-color: #ccfbf1; color: #0f766e; } /* teal for on trip */
        .status-badge.decommissioned { background-color: #fee2e2; color: #991b1b; } /* red */
        .status-badge.other, .status-badge.unavailable { background-color: #f3f4f6; color: #374151; } /* gray */

        .status-badge.completed { background-color: #dcfce7; color: #166534; } /* green */
        .status-badge.pending, .status-badge.scheduled, .status-badge.reported { background-color: #fef9c3; color: #713f12; } /* yellow */
        .status-badge.in-progress, .status-badge.under-investigation { background-color: #ccfbf1; color: #0f766e; } /* teal */
        .status-badge.resolved { background-color: #dbeafe; color: #1e40af; } /* blue */


        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .empty-state { text-align: center; padding: 2rem 0; color: #6b7280; }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: 1rem; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="vehicleDetailsPage(<?php echo htmlspecialchars(json_encode($vehicle_id)); ?>)">
        <?php DashboardSidebar::render('vehicles'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <template x-if="vehicle && vehicle.registration_no">
                <?php ReportHeader::render('Vehicle Details: ', ''); // Title will be dynamic ?>
            </template>
            <template x-if="!vehicle && !isFetching">
                 <?php ReportHeader::render('Vehicle Details', 'Vehicle not found or error loading details.'); ?>
            </template>
             <template x-if="isFetching">
                 <?php ReportHeader::render('Vehicle Details', 'Loading vehicle information...'); ?>
            </template>


            <!-- Global Message Display -->
            <template x-if="globalMessage.text">
                <div class="p-4 md:px-6">
                    <div class="alert" :class="globalMessage.type === 'success' ? 'alert-success' : 'alert-error'" x-text="globalMessage.text" x-init="setTimeout(() => globalMessage.text = '', 5000)"></div>
                </div>
            </template>

            <!-- Loading State -->
            <div x-show="isFetching" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <div x-show="!isFetching && !vehicle && globalMessage.type === 'error'" class="p-4 md:px-6 text-center">
                <p class="empty-state-text">Could not load vehicle data. <a href="vehicles.php" class="text-primary hover:underline">Return to list</a>.</p>
            </div>


            <!-- Content -->
            <div x-show="!isFetching && vehicle">
                <!-- Vehicle Overview -->
                <div class="bg-white rounded-lg shadow m-4 md:m-6">
                    <div class="p-6">
                        <div class="flex flex-wrap justify-between items-start gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900" x-text="vehicle.registration_no || 'N/A'"></h2>
                                <p class="text-gray-600" x-text="(vehicle.make || 'N/A') + ' ' + (vehicle.model || 'N/A')"></p>
                            </div>
                            <div class="flex space-x-2 flex-wrap gap-2">
                                <a :href="'edit-vehicle.php?id=' + vehicleId" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i> Edit Vehicle
                                </a>
                                <button @click="printDetails()" class="btn btn-secondary">
                                    <i class="bi bi-printer me-2"></i> Print Details
                                </button>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="status-badge" :class="getStatusClass(vehicle.status)" x-text="vehicle.status || 'Unknown'"></span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow m-4 md:m-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px overflow-x-auto">
                            <button @click="changeTab('details')" :class="{ 'border-primary text-primary': activeTab === 'details' }" class="px-4 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Vehicle Details</button>
                            <button @click="changeTab('maintenance')" :class="{ 'border-primary text-primary': activeTab === 'maintenance' }" class="px-4 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Maintenance</button>
                            <button @click="changeTab('incidents')" :class="{ 'border-primary text-primary': activeTab === 'incidents' }" class="px-4 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Incidents</button>
                            <button @click="changeTab('trips')" :class="{ 'border-primary text-primary': activeTab === 'trips' }" class="px-4 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Trip History</button>
                            <button @click="changeTab('fuel')" :class="{ 'border-primary text-primary': activeTab === 'fuel' }" class="px-4 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Fuel Usage</button>
                             <button @click="changeTab('assignment')" :class="{ 'border-primary text-primary': activeTab === 'assignment' }" class="px-4 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Assignment</button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <div x-show="isLoadingTabData" class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>

                        <!-- Vehicle Details Tab -->
                        <div x-show="activeTab === 'details' && !isLoadingTabData">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div class="space-y-3">
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">Vehicle Information</h3>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Registration #</dt><dd class="text-sm text-gray-900" x-text="vehicle.registration_no"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Make & Model</dt><dd class="text-sm text-gray-900" x-text="vehicle.make + ' ' + vehicle.model"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Type</dt><dd class="text-sm text-gray-900" x-text="vehicle.vehicle_type"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Capacity</dt><dd class="text-sm text-gray-900" x-text="vehicle.capacity || 'N/A'"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Odometer</dt><dd class="text-sm text-gray-900"><span x-text="formatNumber(vehicle.current_mileage)"></span> km</dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Status</dt><dd><span class="status-badge" :class="getStatusClass(vehicle.status)" x-text="vehicle.status"></span></dd></div>
                                    </dl>
                                </div>
                                <div class="space-y-3">
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">Technical Details</h3>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Chassis #</dt><dd class="text-sm text-gray-900" x-text="vehicle.chassis_no"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Engine #</dt><dd class="text-sm text-gray-900" x-text="vehicle.engine_no"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Purchase Date</dt><dd class="text-sm text-gray-900" x-text="formatDate(vehicle.purchase_date)"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Initial Mileage</dt><dd class="text-sm text-gray-900"><span x-text="formatNumber(vehicle.initial_mileage)"></span> km</dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Funded By</dt><dd class="text-sm text-gray-900" x-text="vehicle.funded_by || 'N/A'"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Fuel Type</dt><dd class="text-sm text-gray-900" x-text="vehicle.fuel_type"></dd></div>
                                    </dl>
                                </div>
                                <div class="md:col-span-2 space-y-3 mt-3">
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">Compliance Information</h3>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Insurance Expiry</dt><dd class="text-sm text-gray-900" x-text="formatDate(vehicle.insurance_expiry)"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Road License Expiry</dt><dd class="text-sm text-gray-900" x-text="formatDate(vehicle.road_license_expiry)"></dd></div>
                                        <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Next Service Due</dt><dd class="text-sm text-gray-900" x-text="formatDate(vehicle.next_service_due)"></dd></div>
                                    </dl>
                                </div>
                                 <div class="md:col-span-2 space-y-3 mt-3" x-show="vehicle.notes">
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">Notes</h3>
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="vehicle.notes"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance History Tab -->
                        <div x-show="activeTab === 'maintenance' && !isLoadingTabData">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Maintenance History</h3>
                                <a :href="'add-maintenance.php?vehicle_id=' + vehicleId" class="btn btn-primary btn-sm"><i class="bi bi-plus me-2"></i> Add Record</a>
                            </div>
                            <template x-if="maintenanceHistory && maintenanceHistory.length > 0">
                                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50"><tr>
                                        <th class="tab-th">Date</th><th class="tab-th">Type</th><th class="tab-th">Description</th><th class="tab-th">Cost</th><th class="tab-th">Status</th><th class="tab-th">Actions</th>
                                    </tr></thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="record in maintenanceHistory" :key="record.id"><tr>
                                            <td class="tab-td" x-text="formatDate(record.service_date)"></td>
                                            <td class="tab-td" x-text="record.type"></td>
                                            <td class="tab-td" x-text="record.description"></td>
                                            <td class="tab-td" x-text="'KES ' + formatNumber(record.cost, 2)"></td>
                                            <td class="tab-td"><span class="status-badge" :class="getStatusClass(record.status)" x-text="record.status"></span></td>
                                            <td class="tab-td"><a :href="'maintenance-details.php?id=' + record.id" class="text-primary hover:underline">View</a></td>
                                        </tr></template>
                                    </tbody>
                                </table></div>
                            </template>
                            <template x-if="!maintenanceHistory || maintenanceHistory.length === 0"><p class="empty-state">No maintenance records found.</p></template>
                        </div>

                        <!-- Incidents Tab -->
                        <div x-show="activeTab === 'incidents' && !isLoadingTabData">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Incident Reports</h3>
                                <a :href="'add-incident.php?vehicle_id=' + vehicleId" class="btn btn-primary btn-sm"><i class="bi bi-plus me-2"></i> Report Incident</a>
                            </div>
                            <template x-if="incidents && incidents.length > 0">
                               <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50"><tr>
                                        <th class="tab-th">Date</th><th class="tab-th">Type</th><th class="tab-th">Description</th><th class="tab-th">Status</th><th class="tab-th">Actions</th>
                                    </tr></thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="incident in incidents" :key="incident.id"><tr>
                                            <td class="tab-td" x-text="formatDate(incident.incident_date)"></td>
                                            <td class="tab-td" x-text="incident.type"></td>
                                            <td class="tab-td" x-text="incident.description"></td>
                                            <td class="tab-td"><span class="status-badge" :class="getStatusClass(incident.status)" x-text="incident.status"></span></td>
                                            <td class="tab-td"><a :href="'incident-details.php?id=' + incident.id" class="text-primary hover:underline">View</a></td>
                                        </tr></template>
                                    </tbody>
                                </table></div>
                            </template>
                            <template x-if="!incidents || incidents.length === 0"><p class="empty-state">No incidents reported for this vehicle.</p></template>
                        </div>

                        <!-- Trip History Tab -->
                        <div x-show="activeTab === 'trips' && !isLoadingTabData">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Trip History</h3>
                                 <a :href="'trips.php?vehicle_id=' + vehicleId" class="btn btn-primary btn-sm"><i class="bi bi-plus me-2"></i> New Trip</a>
                            </div>
                           <template x-if="tripHistory && tripHistory.length > 0">
                               <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50"><tr>
                                        <th class="tab-th">Start Date</th><th class="tab-th">Route</th><th class="tab-th">Driver</th><th class="tab-th">Purpose</th><th class="tab-th">Status</th><th class="tab-th">Actions</th>
                                    </tr></thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="trip in tripHistory" :key="trip.id"><tr>
                                            <td class="tab-td" x-text="formatDateTime(trip.start_time)"></td>
                                            <td class="tab-td" x-text="trip.start_location + ' → ' + trip.end_location"></td>
                                            <td class="tab-td" x-text="trip.driver ? trip.driver.name : 'N/A'"></td>
                                            <td class="tab-td" x-text="trip.purpose"></td>
                                            <td class="tab-td"><span class="status-badge" :class="getStatusClass(trip.status)" x-text="trip.status"></span></td>
                                            <td class="tab-td"><a :href="'trip-details.php?id=' + trip.id" class="text-primary hover:underline">View</a></td>
                                        </tr></template>
                                    </tbody>
                                </table></div>
                            </template>
                            <template x-if="!tripHistory || tripHistory.length === 0"><p class="empty-state">No trip history found for this vehicle.</p></template>
                        </div>

                        <!-- Fuel Usage Tab -->
                        <div x-show="activeTab === 'fuel' && !isLoadingTabData">
                             <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Fuel Consumption & Card Info</h3>
                                <!-- Link to allocate/manage fuel card for this vehicle -->
                                <a :href="'fuel-cards.php?vehicle_id=' + vehicleId" class="btn btn-primary btn-sm"><i class="bi bi-fuel-pump me-2"></i> Manage Fuel Card</a>
                            </div>
                            <template x-if="fuelData">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="card">
                                        <div class="card-header"><h4 class="font-medium">Fuel Card Details</h4></div>
                                        <div class="card-body" x-show="fuelData.fuel_card">
                                            <dl class="space-y-2">
                                                <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Card Number</dt><dd class="text-sm" x-text="fuelData.fuel_card.card_number"></dd></div>
                                                <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Status</dt><dd><span class="status-badge" :class="getStatusClass(fuelData.fuel_card.status)" x-text="fuelData.fuel_card.status"></span></dd></div>
                                                <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Balance</dt><dd class="text-sm" x-text="formatNumber(fuelData.fuel_card.balance, 2) + ' L'"></dd></div>
                                                <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Daily Limit</dt><dd class="text-sm" x-text="formatNumber(fuelData.fuel_card.daily_limit, 2) + ' L'"></dd></div>
                                            </dl>
                                        </div>
                                         <div class="card-body" x-show="!fuelData.fuel_card"><p class="empty-state-text">No fuel card assigned to this vehicle.</p></div>
                                    </div>
                                     <div class="card">
                                        <div class="card-header"><h4 class="font-medium">Consumption Overview</h4></div>
                                        <div class="card-body">
                                            <dl class="space-y-2">
                                                <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Avg. Consumption</dt><dd class="text-sm" x-text="fuelData.average_consumption ? formatNumber(fuelData.average_consumption, 2) + ' L/100km' : 'N/A'"></dd></div>
                                                <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Total Fuel Used</dt><dd class="text-sm" x-text="fuelData.total_fuel_used ? formatNumber(fuelData.total_fuel_used, 2) + ' L' : 'N/A'"></dd></div>
                                                <div class="grid grid-cols-2"><dt class="text-sm font-medium text-gray-500">Total Cost</dt><dd class="text-sm" x-text="fuelData.total_fuel_cost ? 'KES ' + formatNumber(fuelData.total_fuel_cost, 2) : 'N/A'"></dd></div>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <h4 class="text-md font-medium text-gray-900 mt-6 mb-2">Recent Fuel Transactions</h4>
                                <template x-if="fuelData.transactions && fuelData.transactions.length > 0">
                                   <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50"><tr>
                                            <th class="tab-th">Date</th><th class="tab-th">Type</th><th class="tab-th">Quantity (L)</th><th class="tab-th">Cost (KES)</th><th class="tab-th">Location</th>
                                        </tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="tx in fuelData.transactions" :key="tx.id"><tr>
                                                <td class="tab-td" x-text="formatDateTime(tx.transaction_date)"></td>
                                                <td class="tab-td" x-text="tx.type"></td>
                                                <td class="tab-td" x-text="formatNumber(tx.quantity, 2)"></td>
                                                <td class="tab-td" x-text="formatNumber(tx.cost, 2)"></td>
                                                <td class="tab-td" x-text="tx.location || 'N/A'"></td>
                                            </tr></template>
                                        </tbody>
                                    </table></div>
                                </template>
                                <template x-if="!fuelData.transactions || fuelData.transactions.length === 0"><p class="empty-state">No fuel transactions found.</p></template>
                            </template>
                             <template x-if="!fuelData && !isLoadingTabData"><p class="empty-state">Fuel data not available for this vehicle.</p></template>
                        </div>

                        <!-- Assignment Tab -->
                        <div x-show="activeTab === 'assignment' && !isLoadingTabData">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Driver Assignment</h3>
                                <!-- Button to trigger assignment modal or page -->
                            </div>
                            <template x-if="vehicle && vehicle.driver">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium">Currently Assigned Driver</h4>
                                    <p>Name: <span x-text="vehicle.driver.name"></span></p>
                                    <p>License: <span x-text="vehicle.driver.license_number"></span></p>
                                    <button @click="unassignDriver()" class="btn btn-danger btn-sm mt-2">Unassign Driver</button>
                                </div>
                            </template>
                             <template x-if="vehicle && !vehicle.driver">
                                <p class="empty-state-text">No driver currently assigned to this vehicle.</p>
                                <!-- Form/select to assign a driver -->
                                <div class="mt-4">
                                    <label for="assign_driver_id" class="form-label">Assign New Driver:</label>
                                    <select id="assign_driver_id" x-model="selectedDriverToAssign" class="form-select mt-1">
                                        <option value="">Select a driver</option>
                                        <template x-for="driver in availableDrivers" :key="driver.id">
                                            <option :value="driver.id" x-text="driver.name + ' (' + driver.license_number + ')'"></option>
                                        </template>
                                    </select>
                                    <button @click="assignDriver()" class="btn btn-primary mt-2" :disabled="!selectedDriverToAssign || isSubmitting">Assign</button>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener('alpine:initializing', () => {
    Alpine.data('vehicleDetailsPage', (vehicleId) => ({
        isFetching: true, // For initial vehicle load
        isLoadingTabData: false,
        vehicleId: vehicleId,
        vehicle: null,
        activeTab: 'details',
        maintenanceHistory: null,
        incidents: null,
        tripHistory: null,
        fuelData: null, // To store { fuel_card: {}, transactions: [], average_consumption: X, ... }
        availableDrivers: [],
        selectedDriverToAssign: '',
        isSubmitting: false, // For assignment actions

        globalMessage: { text: '', type: '' },
        apiBaseUrl: '/api/v1',
        token: localStorage.getItem('admin_token') || localStorage.getItem('logistics_token'),

        init() {
            if (!this.token) { this.setGlobalMessage('Authentication token not found. Please login.', 'error'); this.isFetching = false; return; }
            if (!this.vehicleId) { this.setGlobalMessage('Vehicle ID not provided.', 'error'); this.isFetching = false; return; }

            this.fetchVehicleOverview().then(() => {
                // Once vehicle overview is loaded, you might want to load data for the default tab
                // For 'details', data is already in `this.vehicle`. For others, call explicitly if needed.
                // Example: if default tab was 'maintenance': this.changeTab('maintenance');
            });
            // Prepare dynamic parts of the report header
            const reportHeaderTitle = document.querySelector('.report-header-title');
            if(reportHeaderTitle) { // Check if element exists
                 this.$watch('vehicle.registration_no', (value) => {
                    if(value) reportHeaderTitle.innerHTML = `Vehicle Details: <span class="text-primary">${value}</span>`;
                });
            }
        },

        async fetchVehicleOverview() {
            this.isFetching = true;
            try {
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    if (response.status === 401) return this.handleUnauthorized();
                    if (response.status === 404) throw new Error('Vehicle not found.');
                    const errData = await response.json();
                    throw new Error(errData.message || `HTTP error ${response.status}`);
                }
                const result = await response.json();
                this.vehicle = result.data;
            } catch (error) {
                this.setGlobalMessage(error.message || 'Failed to load vehicle details.', 'error');
                this.vehicle = null;
            } finally {
                this.isFetching = false;
            }
        },

        async changeTab(tabName) {
            this.activeTab = tabName;
            this.isLoadingTabData = true;
            this.globalMessage.text = ''; // Clear previous messages

            try {
                switch (tabName) {
                    case 'maintenance':
                        if (this.maintenanceHistory === null) await this.fetchMaintenanceHistory();
                        break;
                    case 'incidents':
                        if (this.incidents === null) await this.fetchIncidents();
                        break;
                    case 'trips':
                        if (this.tripHistory === null) await this.fetchTripHistory();
                        break;
                    case 'fuel':
                        if (this.fuelData === null) await this.fetchFuelData();
                        break;
                    case 'assignment':
                         if (this.availableDrivers.length === 0) await this.fetchAvailableDrivers();
                        break;
                }
            } catch (error) {
                 this.setGlobalMessage(`Failed to load data for ${tabName} tab.`, 'error');
            } finally {
                this.isLoadingTabData = false;
            }
        },

        async fetchMaintenanceHistory() {
            const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/maintenance-history`, { headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }});
            if (!response.ok) { console.error('Failed to fetch maintenance history'); this.maintenanceHistory = []; return; }
            const result = await response.json();
            this.maintenanceHistory = result.data.sort((a,b) => new Date(b.service_date) - new Date(a.service_date));
        },
        async fetchIncidents() {
            const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/incidents`, { headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }});
            if (!response.ok) { console.error('Failed to fetch incidents'); this.incidents = []; return; }
            const result = await response.json();
            this.incidents = result.data.sort((a,b) => new Date(b.incident_date) - new Date(a.incident_date));
        },
        async fetchTripHistory() {
            const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/trips`, { headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }});
            if (!response.ok) { console.error('Failed to fetch trip history'); this.tripHistory = []; return; }
            const result = await response.json();
            this.tripHistory = result.data.sort((a,b) => new Date(b.start_time) - new Date(a.start_time));
        },
        async fetchFuelData() {
            // This might involve multiple calls or a dedicated endpoint.
            // For now, let's assume an endpoint /vehicles/{id}/fuel-summary exists
            // Or fetch fuel card then transactions. Using /fuel-consumption as per routes.
            const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/fuel-consumption`, { headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }});
            if (!response.ok) { console.error('Failed to fetch fuel data'); this.fuelData = { transactions: [] }; return; }
            const result = await response.json();
            this.fuelData = result.data; // Assuming result.data = { fuel_card: {}, transactions: [], average_consumption: X }
            if(this.fuelData && this.fuelData.transactions) {
                this.fuelData.transactions.sort((a,b) => new Date(b.transaction_date) - new Date(a.transaction_date));
            }
        },
        async fetchAvailableDrivers() {
            const response = await fetch(`${this.apiBaseUrl}/drivers/available`, { headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }});
            if (!response.ok) { console.error('Failed to fetch available drivers'); this.availableDrivers = []; return; }
            const result = await response.json();
            this.availableDrivers = result.data;
        },
        async assignDriver() {
            if(!this.selectedDriverToAssign) { this.setGlobalMessage('Please select a driver to assign.', 'error'); return; }
            this.isSubmitting = true;
            try {
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/assign-driver`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ driver_id: this.selectedDriverToAssign })
                });
                if(!response.ok) {
                    const err = await response.json();
                    throw new Error(err.message || "Failed to assign driver.");
                }
                this.setGlobalMessage('Driver assigned successfully.', 'success');
                await this.fetchVehicleOverview(); // Refresh vehicle details to show new driver
                this.selectedDriverToAssign = ''; // Reset select
            } catch(error) {
                this.setGlobalMessage(error.message, 'error');
            } finally {
                this.isSubmitting = false;
            }
        },
        async unassignDriver() {
            if(!confirm('Are you sure you want to unassign the current driver?')) return;
            this.isSubmitting = true;
             try {
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/unassign-driver`, { // Assuming this endpoint exists
                    method: 'POST', // Or DELETE, depends on API design
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if(!response.ok) {
                    const err = await response.json();
                    throw new Error(err.message || "Failed to unassign driver.");
                }
                this.setGlobalMessage('Driver unassigned successfully.', 'success');
                await this.fetchVehicleOverview(); // Refresh vehicle details
            } catch(error) {
                this.setGlobalMessage(error.message, 'error');
            } finally {
                this.isSubmitting = false;
            }
        },


        getStatusClass(status) {
            if (!status) return 'other';
            const s = status.toLowerCase().replace(/ /g, '-');
            const knownStatuses = ['available', 'maintenance', 'in-maintenance', 'on-trip', 'in-service', 'decommissioned', 'completed', 'pending', 'scheduled', 'reported', 'in-progress', 'under-investigation', 'resolved'];
            if (knownStatuses.includes(s)) return s;
            return 'other';
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try { return new Date(dateString + (dateString.includes('T') ? '' : 'T00:00:00')).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }); }
            catch (e) { return dateString; }
        },
        formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'N/A';
            try { return new Date(dateTimeString).toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }); }
            catch (e) { return dateTimeString; }
        },
        formatNumber(value, decimals = 0) {
            const num = parseFloat(value);
            return isNaN(num) ? 'N/A' : num.toFixed(decimals);
        },
        setGlobalMessage(text, type) { this.globalMessage.text = text; this.globalMessage.type = type; },
        handleUnauthorized() { this.setGlobalMessage('Session expired.', 'error'); localStorage.removeItem(this.token === localStorage.getItem('admin_token') ? 'admin_token' : 'logistics_token'); this.token = null; this.isFetching = false; this.isLoadingTabData = false;},
        printDetails() { window.print(); }
    }));
});
</script>
</body>
</html>
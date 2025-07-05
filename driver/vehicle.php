<?php
// Mock data includes removed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vehicle - Driver Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #10B981;
            --primary-hover: #059669;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --error-color: #EF4444;
            --text-color: #1F2937;
            --text-muted: #6B7280;
            --border-color: #E5E7EB;
            --background-color: #F9FAFB;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: system-ui, -apple-system, sans-serif;
        }

        [x-cloak] { display: none !important; }

        .mobile-nav { 
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid var(--border-color);
            z-index: 50;
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            font-size: 0.875rem;
        }

        .nav-item.active {
            color: var(--primary-color);
        }

        .nav-item:not(.active) {
            color: var(--text-muted);
        }

        .card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-body {
            padding: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .status-badge {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
        }

        .status-badge.active {
            background-color: #dcfce7;
            color: #166534;
        }
         .status-badge.available { /* Alias for active for vehicles */
            background-color: #dcfce7;
            color: #166534;
        }

        .status-badge.warning, .status-badge.maintenance {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-badge.in-use, .status-badge.on-trip {
             background-color: #ccfbf1; /* teal-100 */
            color: #0f766e; /* teal-700 */
        }

        .status-badge.error, .status-badge.unavailable, .status-badge.decommissioned {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .main-content {
            max-width: 48rem;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 640px) {
            .main-content {
                margin-left: 0;
                margin-right: 0;
            }
        }

        /* Loading Animation */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* Form Elements */
        .form-input {
            margin-top: 0.25rem;
            display: block;
            width: 100%;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color);
            padding: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .form-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-select {
            margin-top: 0.25rem;
            display: block;
            width: 100%;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color);
            padding: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .form-select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            margin-top: 0.25rem;
            display: block;
            width: 100%;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color);
            padding: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .form-textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            z-index: 100;
        }

        .modal-content {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 28rem;
            width: 100%;
            padding: 1.5rem;
        }

        /* Icon Styles */
        .icon-blue {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .icon-green {
            color: var(--success-color);
            font-size: 1.25rem;
        }

        .icon-gray {
            color: var(--text-muted);
            font-size: 1.25rem;
        }

        /* List Styles */
        .list-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
        }

        .list-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .list-item-content {
            flex: 1;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem 0;
        }

        .empty-state-icon {
            font-size: 2.5rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .empty-state-text {
            color: var(--text-muted);
        }
         /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-error {
            background-color: #fee2e2; /* red-100 */
            color: #991b1b; /* red-800 */
            border: 1px solid #fecaca; /* red-300 */
        }
        .alert-success {
            background-color: #dcfce7; /* green-100 */
            color: #166534; /* green-800 */
            border: 1px solid #bbf7d0; /* green-300 */
        }
    </style>
</head>
<body class="pb-20">
    <div class="app-container" x-data="driverVehiclePage()" x-init="init()">
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white shadow-sm sticky top-0 z-40">
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">My Vehicle</h1>
                            <p class="text-sm text-gray-500">Vehicle details and maintenance</p>
                        </div>
                         <button @click="fetchAssignedVehicleAndDetails()" title="Refresh Data" class="p-2 text-gray-600 hover:text-gray-900 rounded-full hover:bg-gray-100">
                            <i class="bi bi-arrow-clockwise text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Global Error/Success Messages -->
            <template x-if="globalError">
                <div class="p-4">
                    <div class="alert alert-error" x-text="globalError"></div>
                </div>
            </template>
            <template x-if="globalSuccess">
                <div class="p-4">
                    <div class="alert alert-success" x-text="globalSuccess" x-init="setTimeout(() => globalSuccess = '', 3000)"></div>
                </div>
            </template>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading && !globalError" class="p-4 space-y-4">
                <template x-if="!vehicleData">
                    <div class="empty-state">
                        <i class="bi bi-car-front empty-state-icon"></i>
                        <p class="empty-state-text" x-text="vehicleId ? 'Loading vehicle data...' : 'No vehicle assigned or found.'"></p>
                        <template x-if="!vehicleId && !isLoading">
                             <button class="btn btn-primary mt-4" @click="init()">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Retry
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="vehicleData">
                    <!-- Vehicle Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Vehicle Details</h2>
                                <span class="status-badge"
                                      :class="{
                                        'available': vehicleData.status === 'Available',
                                        'maintenance': vehicleData.status === 'Maintenance' || vehicleData.status === 'In Maintenance',
                                        'in-use': vehicleData.status === 'In Use' || vehicleData.status === 'On Trip',
                                        'unavailable': vehicleData.status === 'Unavailable' || vehicleData.status === 'Decommissioned'
                                      }"
                                      x-text="vehicleData.status || 'Unknown'">
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="list-item">
                                        <i class="bi bi-truck icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium" x-text="vehicleData.registration_no || 'N/A'"></p>
                                            <p class="text-xs text-gray-500" x-text="(vehicleData.make || 'N/A') + ' ' + (vehicleData.model || 'N/A')"></p>
                                        </div>
                                    </div>
                                    <div class="list-item">
                                        <i class="bi bi-speedometer2 icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Current Odometer</p>
                                            <p class="text-xs text-gray-500"><span x-text="formatNumber(vehicleData.current_mileage)"></span> km</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="list-item">
                                        <i class="bi bi-calendar-check icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Last Service</p>
                                            <p class="text-xs text-gray-500" x-text="formatDate(vehicleData.last_service_date) || 'N/A'"></p>
                                        </div>
                                    </div>
                                    <div class="list-item">
                                        <i class="bi bi-calendar-event icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Next Service Due</p>
                                            <p class="text-xs text-gray-500" x-text="formatDate(vehicleData.next_service_due) || 'N/A'"></p>
                                        </div>
                                    </div>
                                </div>
                                 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="list-item">
                                        <i class="bi bi-droplet-fill icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Fuel Type</p>
                                            <p class="text-xs text-gray-500" x-text="vehicleData.fuel_type || 'N/A'"></p>
                                        </div>
                                    </div>
                                    <div class="list-item">
                                        <i class="bi bi-people-fill icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Capacity</p>
                                            <p class="text-xs text-gray-500" x-text="vehicleData.capacity || 'N/A'"></p>
                                        </div>
                                    </div>
                                </div>


                                <div class="flex flex-col sm:flex-row gap-2">
                                    <button class="btn btn-secondary flex-1" @click="openOdometerModal(vehicleData.current_mileage)">
                                        <i class="bi bi-pencil me-2"></i>
                                        Update Odometer
                                    </button>
                                    <button class="btn btn-secondary flex-1" @click="openMaintenanceModal()">
                                        <i class="bi bi-tools me-2"></i>
                                        Report Issue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance History Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-900">Maintenance History</h2>
                        </div>
                        <div class="card-body">
                            <template x-if="maintenanceHistory && maintenanceHistory.length === 0">
                                <div class="empty-state">
                                    <p class="empty-state-text">No maintenance records found</p>
                                </div>
                            </template>
                            <template x-if="maintenanceHistory && maintenanceHistory.length > 0">
                                <div class="space-y-4">
                                    <template x-for="record in maintenanceHistory" :key="record.id">
                                        <div class="list-item">
                                            <div class="list-item-content">
                                                <div class="list-item-header">
                                                    <p class="font-medium" x-text="record.type"></p>
                                                    <span class="status-badge"
                                                          :class="{
                                                              'active': record.status === 'Completed',
                                                              'warning': record.status === 'Pending' || record.status === 'In Progress'
                                                          }"
                                                          x-text="record.status">
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1" x-text="record.description"></p>
                                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                    <span x-text="formatDate(record.service_date)"></span>
                                                    <span x-text="'KES ' + formatNumber(record.cost, 2)"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                             <template x-if="maintenanceHistory === null && !isLoading"> <!-- Error loading history -->
                                 <div class="empty-state">
                                    <p class="empty-state-text text-red-500">Could not load maintenance history.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <nav class="mobile-nav">
            <div class="grid grid-cols-4 h-16">
                <a href="index.php" class="nav-item">
                    <i class="bi bi-house-door text-xl"></i>
                    <span>Home</span>
                </a>
                <a href="trips.php" class="nav-item">
                    <i class="bi bi-calendar-check text-xl"></i>
                    <span>Trips</span>
                </a>
                <a href="vehicle.php" class="nav-item active">
                    <i class="bi bi-truck text-xl"></i>
                    <span>Vehicle</span>
                </a>
                <a href="profile.php" class="nav-item">
                    <i class="bi bi-person text-xl"></i>
                    <span>Profile</span>
                </a>
            </div>
        </nav>

        <!-- Odometer Update Modal -->
        <div x-show="showOdometerModal" class="modal-overlay" x-cloak @click.self="showOdometerModal = false">
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Update Odometer</h3>
                 <template x-if="modalError">
                    <div class="alert alert-error mb-3" x-text="modalError"></div>
                </template>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">New Odometer Reading (km)</label>
                        <input type="number" x-model="odometerData.mileage" class="form-input" placeholder="e.g., 12345">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button class="btn btn-secondary" @click="showOdometerModal = false">Cancel</button>
                        <button class="btn btn-primary" @click="submitOdometerUpdate()" :disabled="isSubmitting">
                             <span x-text="isSubmitting ? 'Updating...' : 'Update'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Issue Modal -->
        <div x-show="showMaintenanceModal" class="modal-overlay" x-cloak @click.self="showMaintenanceModal = false">
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Report Maintenance Issue</h3>
                <template x-if="modalError">
                    <div class="alert alert-error mb-3" x-text="modalError"></div>
                </template>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Issue Type</label>
                        <select class="form-select" x-model="maintenanceData.type">
                            <option value="">Select type</option>
                            <option value="Mechanical">Mechanical</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Body Damage">Body Damage</option>
                            <option value="Routine Service">Routine Service</option>
                            <option value="Tires">Tires</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" rows="3" x-model="maintenanceData.description" placeholder="Describe the issue..."></textarea>
                    </div>
                     <div>
                        <label class="form-label">Reported Date</label>
                        <input type="date" x-model="maintenanceData.service_date" class="form-input">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button class="btn btn-secondary" @click="showMaintenanceModal = false">Cancel</button>
                        <button class="btn btn-primary" @click="submitMaintenanceReport()" :disabled="isSubmitting">
                            <span x-text="isSubmitting ? 'Submitting...' : 'Submit Report'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener('alpine:initializing', () => {
    Alpine.data('driverVehiclePage', () => ({
        isLoading: true,
        isSubmitting: false,
        vehicleId: null,
        vehicleData: null,
        maintenanceHistory: null, // null initially, then array or empty array
        globalError: '',
        globalSuccess: '',
        modalError: '',
        apiBaseUrl: '/api/v1',
        token: localStorage.getItem('driver_token'),

        showOdometerModal: false,
        odometerData: { mileage: '' },
        showMaintenanceModal: false,
        maintenanceData: {
            type: '',
            description: '',
            status: 'Pending',
            service_date: new Date().toISOString().split('T')[0]
        },

        init() {
            if (!this.token) {
                this.globalError = 'Authentication token not found. Please login.';
                this.isLoading = false;
                return;
            }
            this.fetchAssignedVehicleAndDetails();
        },

        async fetchAssignedVehicleAndDetails() {
            this.isLoading = true;
            this.globalError = '';
            this.globalSuccess = '';
            try {
                const dashboardResponse = await fetch(`${this.apiBaseUrl}/dashboard/driver`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });

                if (!dashboardResponse.ok) {
                    if (dashboardResponse.status === 401) return this.handleUnauthorized();
                    const errorData = await dashboardResponse.json();
                    throw new Error(errorData.message || 'Failed to fetch driver dashboard data.');
                }
                const dashboardResult = await dashboardResponse.json();

                if (dashboardResult.data && dashboardResult.data.vehicle && dashboardResult.data.vehicle.id) {
                    this.vehicleId = dashboardResult.data.vehicle.id;
                    // We can use vehicle data from dashboard directly or fetch more details
                    this.vehicleData = dashboardResult.data.vehicle; // Initial set from dashboard
                    await this.fetchVehicleData(); // Fetch more details and history
                } else {
                    this.globalError = 'No vehicle is currently assigned to you.';
                    this.vehicleData = null;
                    this.maintenanceHistory = [];
                }
            } catch (error) {
                console.error('Error fetching assigned vehicle:', error);
                this.globalError = error.message || 'Failed to load vehicle data.';
            } finally {
                this.isLoading = false; // Overall loading might finish after sub-fetches
            }
        },

        async fetchVehicleData() { // Fetches detailed vehicle data and its maintenance history
            if (!this.vehicleId) return;
            this.isLoading = true; // Specific loading for this part
            try {
                 // Fetch full vehicle details
                const vehicleDetailsResponse = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!vehicleDetailsResponse.ok) {
                     if (vehicleDetailsResponse.status === 401) return this.handleUnauthorized();
                    throw new Error('Failed to fetch vehicle details.');
                }
                const vehicleDetailsData = await vehicleDetailsResponse.json();
                this.vehicleData = vehicleDetailsData.data;


                // Fetch maintenance history
                const historyResponse = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/maintenance-history`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!historyResponse.ok) {
                     if (historyResponse.status === 401) return this.handleUnauthorized(); // Should be caught by vehicle details first
                    console.error('Failed to fetch maintenance history.');
                    this.maintenanceHistory = []; // Error but show empty
                    return; // Don't throw, let vehicle details show
                }
                const historyData = await historyResponse.json();
                this.maintenanceHistory = historyData.data.sort((a,b) => new Date(b.service_date) - new Date(a.service_date));

            } catch (error) {
                 console.error('Error in fetchVehicleData:', error);
                 this.globalError = (this.globalError ? this.globalError + ' ' : '') + error.message;
                 // vehicleData might be partially populated from dashboard, keep it
                 if(this.maintenanceHistory === null) this.maintenanceHistory = []; // Ensure it's an array for template
            } finally {
                this.isLoading = false;
            }
        },


        openOdometerModal(currentMileage) {
            this.modalError = '';
            this.odometerData.mileage = currentMileage > 0 ? currentMileage : '';
            this.showOdometerModal = true;
        },

        async submitOdometerUpdate() {
            if (!this.odometerData.mileage || this.odometerData.mileage <= 0) {
                this.modalError = "Please enter a valid odometer reading.";
                return;
            }
            if (!this.vehicleId) {
                 this.modalError = "Vehicle ID is missing.";
                 return;
            }
            this.isSubmitting = true;
            this.modalError = '';
            this.globalSuccess = '';
            this.globalError = '';
            try {
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}/update-mileage`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ mileage: this.odometerData.mileage })
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `Failed to update odometer (status ${response.status})`);
                }
                this.globalSuccess = 'Odometer updated successfully!';
                this.showOdometerModal = false;
                // Refresh vehicle data
                if(this.vehicleData) this.vehicleData.current_mileage = this.odometerData.mileage; // Optimistic update
                await this.fetchVehicleData();
            } catch (error) {
                this.modalError = error.message || 'An error occurred.';
            } finally {
                this.isSubmitting = false;
            }
        },

        openMaintenanceModal() {
            this.modalError = '';
            this.maintenanceData.type = '';
            this.maintenanceData.description = '';
            this.maintenanceData.service_date = new Date().toISOString().split('T')[0];
            this.showMaintenanceModal = true;
        },

        async submitMaintenanceReport() {
            if (!this.maintenanceData.type) { this.modalError = "Please select an issue type."; return; }
            if (!this.maintenanceData.description.trim()) { this.modalError = "Please provide a description."; return; }
            if (!this.vehicleId) { this.modalError = "Vehicle ID is missing."; return; }

            this.isSubmitting = true;
            this.modalError = '';
            this.globalSuccess = '';
            this.globalError = '';
            const payload = {
                vehicle_id: this.vehicleId,
                type: this.maintenanceData.type,
                description: this.maintenanceData.description,
                status: this.maintenanceData.status, // 'Pending'
                service_date: this.maintenanceData.service_date,
            };
            try {
                const response = await fetch(`${this.apiBaseUrl}/maintenance`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `Failed to report maintenance (status ${response.status})`);
                }
                this.globalSuccess = 'Maintenance issue reported successfully!';
                this.showMaintenanceModal = false;
                await this.fetchVehicleData(); // Refresh maintenance history
            } catch (error) {
                this.modalError = error.message || 'An error occurred.';
            } finally {
                this.isSubmitting = false;
            }
        },

        handleUnauthorized() {
            this.globalError = 'Session expired or invalid. Please login again.';
            localStorage.removeItem('driver_token');
            this.isLoading = false;
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString + (dateString.includes('T') ? '' : 'T00:00:00') ); // Handle date or datetime
                return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            } catch (e) { return dateString; }
        },
        formatNumber(value, decimals = 0) {
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';
            return num.toFixed(decimals);
        }
    }));
});
</script>
</body>
</html>
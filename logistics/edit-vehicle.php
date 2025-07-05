<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// require_once 'mock_data.php'; // Mock data removed

// Get vehicle ID from URL
$vehicle_id = $_GET['id'] ?? null;
if (!$vehicle_id) {
    // Optionally set a session message for the user if redirecting
    // $_SESSION['error_message'] = 'No vehicle ID provided.';
    header('Location: vehicles.php');
    exit;
}
// Mock data fetching removed, will be handled by Alpine.js
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        /* Alerts */
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .form-error-message { color: #EF4444; font-size: 0.875em; margin-top: 0.25rem; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="editVehicleForm(<?php echo htmlspecialchars(json_encode($vehicle_id)); ?>)">
        <?php DashboardSidebar::render('vehicles'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Edit Vehicle',
                'Update the details of the selected vehicle'
            ); ?>

            <!-- Global Message Display -->
            <template x-if="globalMessage.text">
                <div class="p-4 md:px-6">
                    <div class="alert" :class="globalMessage.type === 'success' ? 'alert-success' : 'alert-error'" x-text="globalMessage.text"></div>
                </div>
            </template>

            <!-- Loading State for initial data fetch -->
            <div x-show="isFetching" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content: Form -->
            <div x-show="!isFetching && vehicleData">
                <div class="bg-white rounded-lg shadow m-4 md:m-6">
                    <form @submit.prevent="submitForm" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <!-- Basic Information Section -->
                            <div class="md:col-span-2"><h3 class="text-lg font-medium text-gray-900 mb-2">Basic Information</h3></div>

                            <div>
                                <label for="registration_no" class="form-label">Registration Number *</label>
                                <input type="text" id="registration_no" x-model="vehicleData.registration_no" class="form-input">
                                <span x-show="formErrors.registration_no" x-text="formErrors.registration_no" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="make" class="form-label">Make *</label>
                                <input type="text" id="make" x-model="vehicleData.make" class="form-input">
                                <span x-show="formErrors.make" x-text="formErrors.make" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="model" class="form-label">Model *</label>
                                <input type="text" id="model" x-model="vehicleData.model" class="form-input">
                                <span x-show="formErrors.model" x-text="formErrors.model" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="vehicle_type" class="form-label">Vehicle Type *</label>
                                <select id="vehicle_type" x-model="vehicleData.vehicle_type" class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="Car">Car</option>
                                    <option value="Van">Van</option>
                                    <option value="Truck">Truck</option>
                                    <option value="Bus">Bus</option>
                                    <option value="SUV">SUV</option>
                                    <option value="Motorbike">Motorbike</option>
                                </select>
                                <span x-show="formErrors.vehicle_type" x-text="formErrors.vehicle_type" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="text" id="capacity" x-model="vehicleData.capacity" class="form-input" placeholder="e.g., 1.5 tons or 5 passengers">
                                <span x-show="formErrors.capacity" x-text="formErrors.capacity" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="fuel_type" class="form-label">Fuel Type *</label>
                                <select id="fuel_type" x-model="vehicleData.fuel_type" class="form-select">
                                    <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Electric">Electric</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                                <span x-show="formErrors.fuel_type" x-text="formErrors.fuel_type" class="form-error-message"></span>
                            </div>
                             <div>
                                <label for="status" class="form-label">Status *</label>
                                <select id="status" x-model="vehicleData.status" class="form-select">
                                    <option value="Available">Available</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="On Trip">On Trip</option>
                                    <option value="Unavailable">Unavailable</option>
                                    <option value="Decommissioned">Decommissioned</option>
                                </select>
                                <span x-show="formErrors.status" x-text="formErrors.status" class="form-error-message"></span>
                            </div>

                            <!-- Technical Details Section -->
                             <div class="md:col-span-2 mt-4"><h3 class="text-lg font-medium text-gray-900 mb-2">Technical Details</h3></div>

                            <div>
                                <label for="chassis_no" class="form-label">Chassis Number *</label>
                                <input type="text" id="chassis_no" x-model="vehicleData.chassis_no" class="form-input">
                                <span x-show="formErrors.chassis_no" x-text="formErrors.chassis_no" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="engine_no" class="form-label">Engine Number *</label>
                                <input type="text" id="engine_no" x-model="vehicleData.engine_no" class="form-input">
                                <span x-show="formErrors.engine_no" x-text="formErrors.engine_no" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="purchase_date" class="form-label">Purchase Date</label>
                                <input type="date" id="purchase_date" x-model="vehicleData.purchase_date" class="form-input">
                                <span x-show="formErrors.purchase_date" x-text="formErrors.purchase_date" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="initial_mileage" class="form-label">Initial Mileage *</label>
                                <input type="number" id="initial_mileage" x-model.number="vehicleData.initial_mileage" class="form-input" min="0">
                                 <span x-show="formErrors.initial_mileage" x-text="formErrors.initial_mileage" class="form-error-message"></span>
                            </div>
                             <div>
                                <label for="current_mileage" class="form-label">Current Mileage *</label>
                                <input type="number" id="current_mileage" x-model.number="vehicleData.current_mileage" class="form-input" min="0">
                                <span x-show="formErrors.current_mileage" x-text="formErrors.current_mileage" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="funded_by" class="form-label">Funded By</label>
                                <select id="funded_by" x-model="vehicleData.funded_by" class="form-select">
                                    <option value="">Select Source</option>
                                    <option value="Government">Government</option>
                                    <option value="Private">Private</option>
                                    <option value="Donor">Donor</option>
                                </select>
                                <span x-show="formErrors.funded_by" x-text="formErrors.funded_by" class="form-error-message"></span>
                            </div>


                        <!-- Compliance Information Section -->
                        <div class="md:col-span-2 mt-4"><h3 class="text-lg font-medium text-gray-900 mb-2">Compliance Information</h3></div>

                            <div>
                                <label for="insurance_expiry" class="form-label">Insurance Expiry Date</label>
                                <input type="date" id="insurance_expiry" x-model="vehicleData.insurance_expiry" class="form-input">
                                <span x-show="formErrors.insurance_expiry" x-text="formErrors.insurance_expiry" class="form-error-message"></span>
                            </div>
                             <div>
                                <label for="road_license_expiry" class="form-label">Road License Expiry Date</label>
                                <input type="date" id="road_license_expiry" x-model="vehicleData.road_license_expiry" class="form-input">
                                 <span x-show="formErrors.road_license_expiry" x-text="formErrors.road_license_expiry" class="form-error-message"></span>
                            </div>

                            <div>
                                <label for="next_service_due" class="form-label">Next Service Due Date</label>
                                <input type="date" id="next_service_due" x-model="vehicleData.next_service_due" class="form-input">
                                <span x-show="formErrors.next_service_due" x-text="formErrors.next_service_due" class="form-error-message"></span>
                            </div>
                             <div class="md:col-span-2">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" x-model="vehicleData.notes" class="form-input" rows="3" placeholder="Any additional notes about the vehicle..."></textarea>
                                <span x-show="formErrors.notes" x-text="formErrors.notes" class="form-error-message"></span>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end gap-4">
                            <a href="vehicles.php" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                <span x-show="isSubmitting" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                                <span x-text="isSubmitting ? 'Updating...' : 'Update Vehicle'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
             <div x-show="!isFetching && !vehicleData && globalMessage.text && globalMessage.type === 'error'" class="p-4 md:px-6 text-center">
                <p>Could not load vehicle data. <a href="vehicles.php" class="text-primary hover:underline">Return to list</a>.</p>
            </div>
        </div>
    </div>
<script>
document.addEventListener('alpine:initializing', () => {
    Alpine.data('editVehicleForm', (vehicleId) => ({
        isFetching: true, // For initial data load
        isSubmitting: false, // For form submission
        vehicleId: vehicleId,
        vehicleData: null, // Will be populated from API
        formErrors: {},
        globalMessage: { text: '', type: '' },
        apiBaseUrl: '/api/v1',
        token: localStorage.getItem('admin_token') || localStorage.getItem('logistics_token'),

        init() {
            if (!this.token) {
                this.setGlobalMessage('Authentication token not found. Please login.', 'error');
                this.isFetching = false;
                return;
            }
            if (!this.vehicleId) {
                this.setGlobalMessage('Vehicle ID not provided.', 'error');
                this.isFetching = false;
                // Redirect or disable form
                return;
            }
            this.fetchVehicleDetails();
        },

        async fetchVehicleDetails() {
            this.isFetching = true;
            this.setGlobalMessage('', '');
            try {
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    if (response.status === 401) this.handleUnauthorized();
                    if (response.status === 404) throw new Error('Vehicle not found.');
                    const errorData = await response.json();
                    throw new Error(errorData.message || `HTTP error ${response.status}`);
                }
                const result = await response.json();
                this.vehicleData = result.data;
                 // Ensure current_mileage is at least initial_mileage if not already set properly
                if (this.vehicleData && (this.vehicleData.current_mileage === null || this.vehicleData.current_mileage < this.vehicleData.initial_mileage)) {
                    this.vehicleData.current_mileage = this.vehicleData.initial_mileage;
                }

            } catch (error) {
                this.setGlobalMessage(error.message || 'Failed to load vehicle details.', 'error');
                this.vehicleData = null; // Prevent form rendering if load fails
            } finally {
                this.isFetching = false;
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            this.formErrors = {};
            this.setGlobalMessage('', '');

            if (!this.vehicleData.registration_no || !this.vehicleData.make || !this.vehicleData.model) {
                 this.setGlobalMessage('Please fill in all required fields.', 'error');
                 this.isSubmitting = false;
                 return;
            }
             // Ensure current_mileage is at least initial_mileage
            if (this.vehicleData.current_mileage < this.vehicleData.initial_mileage) {
                this.vehicleData.current_mileage = this.vehicleData.initial_mileage;
            }

            try {
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.vehicleId}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.vehicleData)
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        this.formErrors = result.errors;
                        this.setGlobalMessage('Please correct the errors in the form.', 'error');
                    } else if (response.status === 401) {
                        this.handleUnauthorized();
                    } else {
                        throw new Error(result.message || `HTTP error ${response.status}`);
                    }
                } else {
                    this.setGlobalMessage('Vehicle updated successfully!', 'success');
                    // Optionally redirect after a delay
                    // setTimeout(() => { window.location.href = 'vehicles.php'; }, 2000);
                }
            } catch (error) {
                this.setGlobalMessage(error.message || 'An unexpected error occurred.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        setGlobalMessage(text, type) {
            this.globalMessage.text = text;
            this.globalMessage.type = type;
        },
        handleUnauthorized() {
            this.setGlobalMessage('Session expired or invalid. Please login again.', 'error');
            localStorage.removeItem(this.token === localStorage.getItem('admin_token') ? 'admin_token' : 'logistics_token');
            this.token = null;
        }
    }));
});
</script>
</body>
</html>
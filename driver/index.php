<?php
// Remove mock data includes as data will come from API
// require_once '../components/layout/DashboardHeader.php';
// require_once '../components/layout/DashboardSidebar.php';
// require_once '../components/common/ReportHeader.php';
// require_once '../logistics/mock_data.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Portal - Transport IMS</title>
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
        .status-badge.in-progress { /* Added for trips */
            background-color: #ccfbf1; /* teal-100 */
            color: #0f766e; /* teal-700 */
        }


        .status-badge.warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-badge.error {
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
    <div class="app-container" x-data="driverPortal()" x-init="init()">
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white shadow-sm sticky top-0 z-40">
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">Driver Portal</h1>
                            <p class="text-sm text-gray-500" x-text="isLoading ? 'Loading...' : 'Welcome, ' + (user ? user.name : 'Driver')"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="fetchDashboardData()" title="Refresh Data" class="p-2 text-gray-600 hover:text-gray-900 rounded-full hover:bg-gray-100">
                                <i class="bi bi-arrow-clockwise text-lg"></i>
                            </button>
                            <button @click="logout()" title="Logout" class="p-2 text-gray-600 hover:text-gray-900 rounded-full hover:bg-gray-100">
                                <i class="bi bi-box-arrow-right text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Error Message -->
            <template x-if="globalError">
                <div class="p-4">
                    <div class="alert alert-error" x-text="globalError"></div>
                </div>
            </template>
             <!-- Global Success Message -->
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
                <!-- Active Trip Card -->
                <template x-if="dashboardData && dashboardData.active_trip">
                    <div class="card">
                        <div class="card-header">
                            <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Active Trip</h2>
                                <span class="status-badge in-progress" x-text="dashboardData.active_trip.status || 'In Progress'"></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="list-item">
                                        <i class="bi bi-geo-alt icon-blue"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">From</p>
                                        <p class="font-medium" x-text="dashboardData.active_trip.start_location"></p>
                                        </div>
                                    </div>
                                    <div class="list-item">
                                        <i class="bi bi-geo-alt-fill icon-green"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">To</p>
                                        <p class="font-medium" x-text="dashboardData.active_trip.end_location"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <button class="btn btn-primary w-full" @click="alert('Navigation feature coming soon!')">
                                        <i class="bi bi-navigation me-2"></i>
                                        Start Navigation
                                    </button>
                                    <button class="btn btn-success w-full" @click="completeTrip(dashboardData.active_trip.id)" :disabled="isSubmitting">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <span x-text="isSubmitting ? 'Completing...' : 'Complete Trip'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="dashboardData && !dashboardData.active_trip">
                     <div class="card">
                        <div class="card-body empty-state">
                            <i class="bi bi-moon-stars empty-state-icon"></i>
                            <p class="empty-state-text">No active trip at the moment.</p>
                        </div>
                    </div>
                </template>

                <!-- Vehicle Status Card -->
                <template x-if="dashboardData && dashboardData.vehicle">
                    <div class="card">
                        <div class="card-header">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Current Vehicle</h2>
                                <span class="status-badge" :class="{
                                    'active': dashboardData.vehicle.status === 'Available' || dashboardData.vehicle.status === 'Active',
                                    'warning': dashboardData.vehicle.status === 'Maintenance',
                                    'error': dashboardData.vehicle.status === 'Unavailable' || dashboardData.vehicle.status === 'Decommissioned'
                                }" x-text="dashboardData.vehicle.status"></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="list-item">
                                        <i class="bi bi-truck icon-blue"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Vehicle Details</p>
                                            <p class="font-medium" x-text="dashboardData.vehicle.registration_no"></p>
                                            <p class="text-xs text-gray-500" x-text="dashboardData.vehicle.make + ' ' + dashboardData.vehicle.model"></p>
                                        </div>
                                    </div>
                                    <div class="list-item">
                                        <i class="bi bi-speedometer2 icon-blue"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Current Odometer</p>
                                            <p class="font-medium"><span x-text="formatNumber(dashboardData.vehicle.current_mileage)"></span> km</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <button class="btn btn-secondary w-full" @click="openOdometerModal(dashboardData.vehicle.id, dashboardData.vehicle.current_mileage)">
                                        <i class="bi bi-pencil me-2"></i>
                                        Update Odometer
                                    </button>
                                    <button class="btn btn-secondary w-full" @click="openMaintenanceModal(dashboardData.vehicle.id)">
                                        <i class="bi bi-tools me-2"></i>
                                        Report Issue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                 <template x-if="dashboardData && !dashboardData.vehicle">
                     <div class="card">
                        <div class="card-body empty-state">
                            <i class="bi bi-cone-striped empty-state-icon"></i>
                            <p class="empty-state-text">No vehicle currently assigned.</p>
                        </div>
                    </div>
                </template>

                <!-- Fuel Card Information -->
                <template x-if="dashboardData && dashboardData.fuel_card">
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold">Fuel Card</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-500">Card Number</p>
                                        <p class="font-medium" x-text="dashboardData.fuel_card.card_number"></p>
                                    </div>
                                    <span class="status-badge" :class="{
                                        'active': dashboardData.fuel_card.status === 'Active',
                                        'error': dashboardData.fuel_card.status !== 'Active'
                                    }" x-text="dashboardData.fuel_card.status"></span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Available Balance</p>
                                        <p class="font-medium"><span x-text="formatNumber(dashboardData.fuel_card.balance, 1)"></span> L</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Daily Limit</p>
                                        <p class="font-medium"><span x-text="formatNumber(dashboardData.fuel_card.daily_limit, 1)"></span> L</p>
                                    </div>
                                </div>

                                <template x-if="dashboardData.fuel_card.transactions && dashboardData.fuel_card.transactions.length > 0">
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm text-gray-500 mb-2">Recent Fuel History</p>
                                        <div class="space-y-2">
                                            <template x-for="transaction in dashboardData.fuel_card.transactions.slice(0, 2)" :key="transaction.id">
                                                <div class="flex justify-between items-center text-sm">
                                                    <span x-text="transaction.type + ' (' + formatDate(transaction.transaction_date) + ')'"></span>
                                                    <span class="font-medium" x-text="formatNumber(transaction.amount,1) + ' L'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!dashboardData.fuel_card.transactions || dashboardData.fuel_card.transactions.length === 0">
                                     <div class="mt-4 p-3 bg-gray-50 rounded-lg text-center">
                                        <p class="text-sm text-gray-500">No recent fuel history.</p>
                                     </div>
                                </template>


                                <template x-if="dashboardData.vehicle && dashboardData.vehicle.fuel_tank_capacity">
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm text-gray-500">Vehicle Fuel Information</p>
                                        <div class="mt-2 grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Tank Capacity</p>
                                                <p class="font-medium"><span x-text="formatNumber(dashboardData.vehicle.fuel_tank_capacity, 1)"></span> L</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Fuel Type</p>
                                                <p class="font-medium" x-text="dashboardData.vehicle.fuel_type"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="dashboardData && !dashboardData.fuel_card">
                     <div class="card">
                        <div class="card-body empty-state">
                            <i class="bi bi-fuel-pump empty-state-icon"></i>
                            <p class="empty-state-text">No fuel card information available.</p>
                        </div>
                    </div>
                </template>
                <div x-show="!isLoading && !globalError && !dashboardData" class="p-4 space-y-4 text-center">
                     <div class="card">
                        <div class="card-body empty-state">
                            <i class="bi bi-emoji-dizzy empty-state-icon"></i>
                            <p class="empty-state-text">Could not load dashboard data. Please try again.</p>
                             <button class="btn btn-primary mt-4" @click="fetchDashboardData()">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Retry
                            </button>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <!-- Mobile Navigation -->
        <nav class="mobile-nav">
            <div class="grid grid-cols-4 h-16">
                <a href="index.php" class="nav-item active">
                    <i class="bi bi-house-door text-xl"></i>
                    <span>Home</span>
                </a>
                <a href="trips.php" class="nav-item">
                    <i class="bi bi-calendar-check text-xl"></i>
                    <span>Trips</span>
                </a>
                <a href="vehicle.php" class="nav-item">
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
    Alpine.data('driverPortal', () => ({
        isLoading: true,
        isSubmitting: false,
        dashboardData: null,
        user: null,
        globalError: '',
        globalSuccess: '',
        modalError: '',
        apiBaseUrl: '/api/v1', // Configurable API base URL
        token: localStorage.getItem('driver_token'),

        showOdometerModal: false,
        odometerData: {
            vehicle_id: null,
            mileage: ''
        },
        showMaintenanceModal: false,
        maintenanceData: {
            vehicle_id: null,
            type: '',
            description: '',
            status: 'Pending', // Default status
            service_date: new Date().toISOString().split('T')[0] // Default to today
        },

        init() {
            if (!this.token) {
                this.globalError = 'Authentication token not found. Please login.';
                // In a real app, redirect to login: window.location.href = '/login.php';
                console.error('Token missing, redirect to login would happen here.');
                this.isLoading = false;
                // For now, allow proceeding to see UI structure, but API calls will fail.
                // To test with API, manually set localStorage.setItem('driver_token', 'YOUR_TOKEN');
                return;
            }
            this.fetchDashboardData();
            this.fetchUserProfile();
        },

        async fetchDashboardData() {
            this.isLoading = true;
            this.globalError = '';
            try {
                const response = await fetch(`${this.apiBaseUrl}/dashboard/driver`, {
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Accept': 'application/json',
                    }
                });
                if (!response.ok) {
                    if (response.status === 401) {
                        this.globalError = 'Session expired or invalid. Please login again.';
                        localStorage.removeItem('driver_token');
                        // window.location.href = '/login.php'; // Redirect to login
                    } else {
                        const errorData = await response.json();
                        throw new Error(errorData.message || `HTTP error ${response.status}`);
                    }
                    return;
                }
                const data = await response.json();
                this.dashboardData = data.data; // Assuming data is wrapped in a 'data' object
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
                this.globalError = error.message || 'Failed to load dashboard data.';
                this.dashboardData = null; // Ensure dashboardData is null on error
            } finally {
                this.isLoading = false;
            }
        },

        async fetchUserProfile() {
            // Fetches basic user info like name for the welcome message
            try {
                const response = await fetch(`${this.apiBaseUrl}/auth/profile`, {
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Accept': 'application/json',
                    }
                });
                if (!response.ok) return; // Silently fail if profile can't be fetched
                const data = await response.json();
                this.user = data.data;
            } catch (error) {
                console.warn('Could not fetch user profile:', error);
            }
        },

        async completeTrip(tripId) {
            if (!tripId) {
                this.globalError = 'No active trip to complete.';
                return;
            }
            if (!confirm('Are you sure you want to complete this trip?')) return;

            this.isSubmitting = true;
            this.globalError = '';
            this.globalSuccess = '';
            try {
                const response = await fetch(`${this.apiBaseUrl}/trips/${tripId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json' // Though no body needed for this one typically
                    },
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `Failed to complete trip (status ${response.status})`);
                }
                this.globalSuccess = 'Trip completed successfully!';
                await this.fetchDashboardData(); // Refresh data
            } catch (error) {
                console.error('Error completing trip:', error);
                this.globalError = error.message || 'An error occurred while completing the trip.';
            } finally {
                this.isSubmitting = false;
            }
        },

        openOdometerModal(vehicleId, currentMileage) {
            this.modalError = '';
            this.odometerData.vehicle_id = vehicleId;
            this.odometerData.mileage = currentMileage > 0 ? currentMileage : '';
            this.showOdometerModal = true;
        },

        async submitOdometerUpdate() {
            if (!this.odometerData.mileage || this.odometerData.mileage <= 0) {
                this.modalError = "Please enter a valid odometer reading.";
                return;
            }
            if (!this.odometerData.vehicle_id) {
                 this.modalError = "Vehicle ID is missing.";
                 return;
            }

            this.isSubmitting = true;
            this.modalError = '';
            try {
                // The API route is POST /vehicles/{vehicle}/update-mileage
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${this.odometerData.vehicle_id}/update-mileage`, {
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
                await this.fetchDashboardData(); // Refresh data
            } catch (error) {
                console.error('Error updating odometer:', error);
                this.modalError = error.message || 'An error occurred while updating odometer.';
            } finally {
                this.isSubmitting = false;
            }
        },

        openMaintenanceModal(vehicleId) {
            this.modalError = '';
            this.maintenanceData.vehicle_id = vehicleId;
            this.maintenanceData.type = '';
            this.maintenanceData.description = '';
            this.maintenanceData.service_date = new Date().toISOString().split('T')[0];
            this.showMaintenanceModal = true;
        },

        async submitMaintenanceReport() {
            if (!this.maintenanceData.type) {
                this.modalError = "Please select an issue type.";
                return;
            }
            if (!this.maintenanceData.description.trim()) {
                this.modalError = "Please provide a description for the issue.";
                return;
            }
            if (!this.maintenanceData.vehicle_id) {
                 this.modalError = "Vehicle ID is missing.";
                 return;
            }

            this.isSubmitting = true;
            this.modalError = '';
            const payload = {
                vehicle_id: this.maintenanceData.vehicle_id,
                type: this.maintenanceData.type,
                description: this.maintenanceData.description,
                status: this.maintenanceData.status, // 'Pending'
                service_date: this.maintenanceData.service_date, // API expects service_date for when it's reported/serviced
                // cost: 0 // Cost can be updated later
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
                // No need to refresh dashboard data unless maintenance issues appear on it
            } catch (error) {
                console.error('Error reporting maintenance:', error);
                this.modalError = error.message || 'An error occurred while reporting maintenance.';
            } finally {
                this.isSubmitting = false;
            }
        },

        logout() {
            // Basic logout, ideally call API to invalidate token
            localStorage.removeItem('driver_token');
            this.token = null;
            this.user = null;
            this.dashboardData = null;
            this.globalSuccess = "You have been logged out.";
            this.globalError = "Please login to continue."; // Show login prompt
            // window.location.href = '/index.php'; // Redirect to a main page or login page
        },

        formatNumber(value, decimals = 0) {
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';
            return num.toFixed(decimals);
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            } catch (e) {
                return dateString; // return original if parsing fails
            }
        },
        alert(message) { // Simple alert for features not yet implemented
            window.alert(message);
        }
    }));
});
</script>
</body>
</html> 
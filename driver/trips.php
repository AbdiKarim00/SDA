<?php
// Remove mock data includes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips - Driver Portal</title>
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

        .status-badge.active { /* General active/valid status */
            background-color: #dcfce7; /* green-100 */
            color: #166534; /* green-800 */
        }
        .status-badge.completed { /* More specific for trips */
             background-color: #dcfce7; /* green-100 */
            color: #166534; /* green-800 */
        }
        .status-badge.in-progress {
             background-color: #ccfbf1; /* teal-100 */
            color: #0f766e; /* teal-700 */
        }
        .status-badge.pending {
            background-color: #fef9c3; /* yellow-100 */
            color: #713f12; /* yellow-800 */
        }
        .status-badge.cancelled {
            background-color: #fee2e2; /* red-100 */
            color: #991b1b; /* red-800 */
        }


        .status-badge.warning { /* General warning */
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-badge.error { /* General error/expired */
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
    </style>
</head>
<body class="pb-20">
    <div class="app-container" x-data="driverTrips()" x-init="init()">
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white shadow-sm sticky top-0 z-40">
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">My Trips</h1>
                            <p class="text-sm text-gray-500">View and manage your trips</p>
                        </div>
                         <button @click="fetchTrips()" title="Refresh Data" class="p-2 text-gray-600 hover:text-gray-900 rounded-full hover:bg-gray-100">
                            <i class="bi bi-arrow-clockwise text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Global Error Message -->
            <template x-if="globalError">
                <div class="p-4">
                    <div class="alert alert-error" x-text="globalError"></div>
                </div>
            </template>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading && !globalError" class="p-4 space-y-4">
                <template x-if="trips.length === 0">
                    <div class="empty-state">
                        <i class="bi bi-calendar-x empty-state-icon"></i>
                        <p class="empty-state-text">No trips found</p>
                         <button class="btn btn-primary mt-4" @click="fetchTrips()">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Retry
                        </button>
                    </div>
                </template>
                <template x-for="trip in trips" :key="trip.id">
                    <div class="card">
                        <div class="card-body">
                            <div class="space-y-4">
                                <!-- Trip Route -->
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-500">From</p>
                                        <p class="font-medium" x-text="trip.start_location"></p>
                                    </div>
                                    <div class="text-gray-400">
                                        <i class="bi bi-arrow-right"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-500">To</p>
                                        <p class="font-medium" x-text="trip.end_location"></p>
                                    </div>
                                </div>

                                <!-- Trip Details -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Purpose</p>
                                        <p class="font-medium" x-text="trip.purpose"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <span class="status-badge" :class="{
                                            'completed': trip.status === 'Completed',
                                            'in-progress': trip.status === 'In Progress' || trip.status === 'Active',
                                            'pending': trip.status === 'Pending' || trip.status === 'Scheduled',
                                            'cancelled': trip.status === 'Cancelled'
                                        }" x-text="trip.status">
                                        </span>
                                    </div>
                                </div>

                                <!-- Trip Timing -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Start</p>
                                        <p class="font-medium" x-text="formatDateTime(trip.start_time)"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">End</p>
                                        <p class="font-medium" x-text="trip.end_time ? formatDateTime(trip.end_time) : (trip.status === 'In Progress' || trip.status === 'Active' ? 'Ongoing' : 'N/A')"></p>
                                    </div>
                                </div>

                                <!-- Vehicle Info -->
                                <template x-if="trip.vehicle">
                                    <div class="list-item">
                                        <i class="bi bi-truck icon-blue"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Vehicle</p>
                                            <p class="font-medium" x-text="trip.vehicle.registration_no + ' - ' + trip.vehicle.make + ' ' + trip.vehicle.model"></p>
                                        </div>
                                    </div>
                                </template>


                                <!-- Action Buttons -->
                                <template x-if="trip.status === 'In Progress' || trip.status === 'Active'">
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button @click="alert('Navigation for trip ' + trip.id + ' coming soon!')" class="btn btn-primary flex-1">
                                            <i class="bi bi-navigation me-2"></i>
                                            Navigate
                                        </button>
                                        <button @click="completeTrip(trip.id)" class="btn btn-success flex-1" :disabled="isSubmitting === trip.id">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <span x-text="isSubmitting === trip.id ? 'Completing...' : 'Complete Trip'"></span>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="trip.status === 'Pending' || trip.status === 'Scheduled'">
                                     <div class="flex flex-col sm:flex-row gap-2">
                                        <button @click="startTrip(trip.id)" class="btn btn-primary flex-1" :disabled="isSubmitting === trip.id">
                                            <i class="bi bi-play-circle me-2"></i>
                                             <span x-text="isSubmitting === trip.id ? 'Starting...' : 'Start Trip'"></span>
                                        </button>
                                         <button @click="cancelTrip(trip.id)" class="btn btn-secondary flex-1 bg-red-100 text-red-700 hover:bg-red-200" :disabled="isSubmitting === trip.id">
                                            <i class="bi bi-x-circle me-2"></i>
                                            <span x-text="isSubmitting === trip.id ? 'Cancelling...' : 'Cancel Trip'"></span>
                                        </button>
                                    </div>
                                </template>
                            </div>
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
                <a href="trips.php" class="nav-item active">
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
    </div>
<script>
document.addEventListener('alpine:initializing', () => {
    Alpine.data('driverTrips', () => ({
        isLoading: true,
        isSubmitting: null, // Will store tripId when submitting for a specific trip
        trips: [],
        user: null,
        driverId: null,
        globalError: '',
        apiBaseUrl: '/api/v1',
        token: localStorage.getItem('driver_token'),

        init() {
            if (!this.token) {
                this.globalError = 'Authentication token not found. Please login.';
                this.isLoading = false;
                // For testing, manually set localStorage.setItem('driver_token', 'YOUR_TOKEN');
                return;
            }
            this.fetchUserProfileAndTrips();
        },

        async fetchUserProfileAndTrips() {
            this.isLoading = true;
            this.globalError = '';
            try {
                const profileResponse = await fetch(`${this.apiBaseUrl}/auth/profile`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!profileResponse.ok) {
                     if (profileResponse.status === 401) this.handleUnauthorized();
                    throw new Error('Failed to fetch user profile.');
                }
                const profileData = await profileResponse.json();
                this.user = profileData.data;

                // Attempt to get driver_id from the user's driver profile relation
                if (this.user && this.user.driver && this.user.driver.id) {
                    this.driverId = this.user.driver.id;
                    await this.fetchTrips();
                } else {
                    // Fallback: if driver relation is not directly available, try to get driver_id from dashboard/driver endpoint
                    // This is a less ideal way but can serve as a backup if /auth/profile is not comprehensive for drivers
                    console.warn("Driver ID not directly in /auth/profile driver relation. Attempting fallback via /dashboard/driver.");
                    const dashboardResponse = await fetch(`${this.apiBaseUrl}/dashboard/driver`, {
                         headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json'}
                    });
                    if(!dashboardResponse.ok) {
                        if (dashboardResponse.status === 401) this.handleUnauthorized();
                        throw new Error('Failed to get driver details from dashboard for fetching trips.');
                    }
                    const dashboardData = await dashboardResponse.json();
                    if(dashboardData.data && dashboardData.data.driver_details && dashboardData.data.driver_details.id){
                        this.driverId = dashboardData.data.driver_details.id;
                        await this.fetchTrips();
                    } else {
                         this.globalError = "Could not determine your Driver ID to fetch trips. Ensure your profile is correctly set up as a driver.";
                         this.isLoading = false;
                         return;
                    }
                }

            } catch (error) {
                console.error('Error initializing page:', error);
                this.globalError = error.message || 'Failed to load page data.';
                 this.isLoading = false; // Ensure loading is false on error
            }
            // isLoading is set to false within fetchTrips or if an error occurs before it.
        },

        async fetchTrips() {
            if (!this.driverId) {
                this.globalError = 'Driver ID not available to fetch trips.';
                this.isLoading = false;
                return;
            }
            this.isLoading = true; // Ensure loading is true before this specific fetch
            try {
                const response = await fetch(`${this.apiBaseUrl}/drivers/${this.driverId}/trips`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    if (response.status === 401) this.handleUnauthorized();
                    const errorData = await response.json();
                    throw new Error(errorData.message || `HTTP error ${response.status}`);
                }
                const data = await response.json();
                this.trips = data.data.sort((a, b) => new Date(b.start_time) - new Date(a.start_time)); // Sort by date
            } catch (error) {
                console.error('Error fetching trips:', error);
                this.globalError = error.message || 'Failed to load trips.';
                this.trips = [];
            } finally {
                this.isLoading = false;
            }
        },

        async completeTrip(tripId) {
            if (!tripId || !confirm('Are you sure you want to complete this trip?')) return;
            this.isSubmitting = tripId;
            this.globalError = ''; // Clear previous global errors
            try {
                const response = await fetch(`${this.apiBaseUrl}/trips/${tripId}/complete`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to complete trip.');
                }
                await this.fetchTrips(); // Refresh list
            } catch (error) {
                this.globalError = error.message;
            } finally {
                this.isSubmitting = null;
            }
        },

        async startTrip(tripId) {
            if (!tripId || !confirm('Are you sure you want to start this trip?')) return;
            this.isSubmitting = tripId;
            this.globalError = '';
            try {
                const response = await fetch(`${this.apiBaseUrl}/trips/${tripId}/start`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to start trip.');
                }
                await this.fetchTrips(); // Refresh list
            } catch (error) {
                this.globalError = error.message;
            } finally {
                this.isSubmitting = null;
            }
        },
        async cancelTrip(tripId) {
            if (!tripId || !confirm('Are you sure you want to cancel this trip? This action cannot be undone.')) return;
            this.isSubmitting = tripId;
            this.globalError = '';
            try {
                const response = await fetch(`${this.apiBaseUrl}/trips/${tripId}/cancel`, {
                    method: 'POST', // Laravel typically uses POST for state changes even if destructive like cancel
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to cancel trip.');
                }
                await this.fetchTrips(); // Refresh list
            } catch (error) {
                this.globalError = error.message;
            } finally {
                this.isSubmitting = null;
            }
        },


        handleUnauthorized() {
            this.globalError = 'Session expired or invalid. Please login again.';
            localStorage.removeItem('driver_token');
            // Consider redirect: window.location.href = '/login.php';
        },

        formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'N/A';
            try {
                const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                return new Date(dateTimeString).toLocaleString(undefined, options);
            } catch (e) { return dateTimeString; }
        },
        alert(message) { window.alert(message); }
    }));
});
</script>
</body>
</html>
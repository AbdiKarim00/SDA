<?php
// Remove mock data includes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Driver Portal</title>
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

        .status-badge {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
        }

        .status-badge.active, .status-badge.valid {
            background-color: #dcfce7; /* green-100 */
            color: #166534; /* green-800 */
        }
        .status-badge.resolved {
             background-color: #dcfce7; /* green-100 */
            color: #166534; /* green-800 */
        }

        .status-badge.warning, .status-badge.expiring-soon {
            background-color: #fef3c7; /* yellow-100 */
            color: #92400e; /* yellow-700 */
        }
        .status-badge.pending, .status-badge.open {
             background-color: #fef9c3; /* yellow-100 */
            color: #713f12; /* yellow-800 */
        }

        .status-badge.error, .status-badge.expired {
            background-color: #fee2e2; /* red-100 */
            color: #991b1b; /* red-800 */
        }
         .status-badge.not-available {
            background-color: #e5e7eb; /* gray-200 */
            color: #4b5563; /* gray-600 */
        }


        .compliance-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
        }

        .compliance-item.expired { /* Class for styling directly if needed, though badge handles color */
            /* background-color: #fee2e2; */
        }

        .compliance-item.warning { /* Class for styling directly if needed */
            /* background-color: #fef3c7; */
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
    </style>
</head>
<body class="pb-20">
    <div class="app-container" x-data="driverProfile()" x-init="init()">
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="bg-white shadow-sm sticky top-0 z-40">
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">My Profile</h1>
                            <p class="text-sm text-gray-500">Personal details and compliance</p>
                        </div>
                        <button @click="alert('Edit profile coming soon!')" title="Edit Profile" class="p-2 text-gray-600 hover:text-gray-900 rounded-full hover:bg-gray-100">
                            <i class="bi bi-pencil text-lg"></i>
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
                <!-- Personal Details Card -->
                <template x-if="userProfile">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-900">Personal Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="bi bi-person text-2xl text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium" x-text="userProfile.name || 'N/A'"></h3>
                                        <p class="text-sm text-gray-500">User ID: <span x-text="userProfile.id || 'N/A'"></span></p>
                                        <template x-if="userProfile.driver && userProfile.driver.id">
                                            <p class="text-sm text-gray-500">Driver ID: <span x-text="userProfile.driver.id"></span></p>
                                        </template>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-3">
                                    <div class="compliance-item">
                                        <i class="bi bi-envelope text-blue-600 text-xl"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Email</p>
                                            <p class="font-medium" x-text="userProfile.email || 'N/A'"></p>
                                        </div>
                                    </div>
                                    <div class="compliance-item">
                                        <i class="bi bi-telephone text-blue-600 text-xl"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Phone</p>
                                            <p class="font-medium" x-text="userProfile.phone || (userProfile.driver && userProfile.driver.phone_number) || 'N/A'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Compliance Information Card -->
                 <template x-if="userProfile && userProfile.driver">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-900">Compliance Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-3">
                                <div class="compliance-item" :class="getComplianceItemClass(userProfile.driver.license_expiry_date)">
                                    <i class="bi bi-card-text text-blue-600 text-xl"></i>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-gray-500">Driver's License</p>
                                                <p class="font-medium" x-text="userProfile.driver.license_number || 'N/A'"></p>
                                                <p class="text-xs text-gray-500">Expires: <span x-text="formatDate(userProfile.driver.license_expiry_date) || 'N/A'"></span></p>
                                            </div>
                                            <span class="status-badge" :class="getComplianceBadgeClass(userProfile.driver.license_expiry_date)" x-text="getComplianceStatusText(userProfile.driver.license_expiry_date)"></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Add other compliance items here if available from API -->
                                <template x-if="complianceDetails && complianceDetails.certifications">
                                     <template x-for="cert in complianceDetails.certifications" :key="cert.type">
                                        <div class="compliance-item" :class="getComplianceItemClass(cert.expiry_date)">
                                            <i class="bi bi-patch-check-fill text-blue-600 text-xl"></i>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="text-sm text-gray-500" x-text="cert.type"></p>
                                                        <p class="font-medium" x-text="cert.reference_number || 'N/A'"></p>
                                                        <p class="text-xs text-gray-500">Expires: <span x-text="formatDate(cert.expiry_date) || 'N/A'"></span></p>
                                                    </div>
                                                    <span class="status-badge" :class="getComplianceBadgeClass(cert.expiry_date)" x-text="getComplianceStatusText(cert.expiry_date)"></span>
                                                </div>
                                            </div>
                                        </div>
                                     </template>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Incident Reports Card -->
                <template x-if="incidents !== null">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold text-gray-900">My Incident Reports</h2>
                        </div>
                        <div class="card-body">
                            <template x-if="incidents.length === 0">
                                <div class="empty-state">
                                    <i class="bi bi-shield-exclamation empty-state-icon"></i>
                                    <p class="empty-state-text">No incident reports found</p>
                                </div>
                            </template>
                            <template x-if="incidents.length > 0">
                                <div class="space-y-3">
                                    <template x-for="incident in incidents" :key="incident.id">
                                        <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <p class="font-medium" x-text="incident.type || 'Incident'"></p>
                                                    <span class="status-badge" :class="{
                                                        'resolved': incident.status === 'Resolved' || incident.status === 'Closed',
                                                        'open': incident.status === 'Open' || incident.status === 'Pending Investigation',
                                                        'warning': incident.status === 'Under Investigation'
                                                    }" x-text="incident.status"></span
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1" x-text="incident.description"></p>
                                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                    <span x-text="'Reported: ' + formatDateTime(incident.incident_date)"></span>
                                                    <template x-if="incident.location">
                                                        <span>Location: <span x-text="incident.location"></span></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                 <div x-show="!isLoading && !globalError && !userProfile" class="p-4 space-y-4 text-center">
                     <div class="card">
                        <div class="card-body empty-state">
                            <i class="bi bi-person-x empty-state-icon"></i>
                            <p class="empty-state-text">Could not load profile data. Please try again.</p>
                             <button class="btn btn-primary mt-4" @click="init()">
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
                <a href="index.php" class="nav-item">
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
                <a href="profile.php" class="nav-item active">
                    <i class="bi bi-person text-xl"></i>
                    <span>Profile</span>
                </a>
            </div>
        </nav>
    </div>

<script>
document.addEventListener('alpine:initializing', () => {
    Alpine.data('driverProfile', () => ({
        isLoading: true,
        userProfile: null, // Will store data from /auth/profile (User and nested Driver model)
        complianceDetails: null, // Store data from /drivers/{id}/compliance
        incidents: null, // Store data from /drivers/{id}/incidents
        driverId: null,
        globalError: '',
        apiBaseUrl: '/api/v1',
        token: localStorage.getItem('driver_token'),

        init() {
            if (!this.token) {
                this.globalError = 'Authentication token not found. Please login.';
                this.isLoading = false;
                return;
            }
            this.fetchAllProfileData();
        },

        async fetchAllProfileData() {
            this.isLoading = true;
            this.globalError = '';
            try {
                // Fetch main user profile (which should include driver relation)
                const profileResponse = await fetch(`${this.apiBaseUrl}/auth/profile`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!profileResponse.ok) {
                    if (profileResponse.status === 401) return this.handleUnauthorized();
                    const errorData = await profileResponse.json();
                    throw new Error(errorData.message || 'Failed to fetch user profile.');
                }
                const profileData = await profileResponse.json();
                this.userProfile = profileData.data;

                if (this.userProfile && this.userProfile.driver && this.userProfile.driver.id) {
                    this.driverId = this.userProfile.driver.id;
                    // Now fetch compliance and incidents in parallel
                    await Promise.all([
                        this.fetchComplianceDetails(this.driverId),
                        this.fetchIncidents(this.driverId)
                    ]);
                } else {
                     // Attempt to get driver_id from dashboard/driver if not in profile
                    console.warn("Driver relation not found in /auth/profile. Attempting fallback via /dashboard/driver.");
                    const dashboardResp = await fetch(`${this.apiBaseUrl}/dashboard/driver`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json'}
                    });
                    if(!dashboardResp.ok) {
                         if (dashboardResp.status === 401) this.handleUnauthorized();
                        throw new Error('Failed to get driver details from dashboard.');
                    }
                    const dashboardData = await dashboardResp.json();
                    if(dashboardData.data && dashboardData.data.driver_details && dashboardData.data.driver_details.id){
                        this.driverId = dashboardData.data.driver_details.id;
                        // Populate userProfile.driver if possible from dashboard data for consistency
                        if (!this.userProfile.driver) this.userProfile.driver = {};
                        this.userProfile.driver.id = this.driverId;
                        this.userProfile.driver.license_number = dashboardData.data.driver_details.license_number;
                        this.userProfile.driver.license_expiry_date = dashboardData.data.driver_details.license_expiry_date;
                         if(!this.userProfile.phone && dashboardData.data.driver_details.phone_number) {
                            this.userProfile.phone = dashboardData.data.driver_details.phone_number;
                        }


                        await Promise.all([
                            this.fetchComplianceDetails(this.driverId),
                            this.fetchIncidents(this.driverId)
                        ]);
                    } else {
                        this.globalError = 'Driver details not found. Cannot fetch compliance or incidents.';
                        this.incidents = []; // Set to empty array to show "no incidents"
                    }
                }
            } catch (error) {
                console.error('Error fetching profile data:', error);
                this.globalError = error.message || 'Failed to load profile data.';
                this.userProfile = null; // Clear profile on error
                this.incidents = []; // Clear incidents on error
            } finally {
                this.isLoading = false;
            }
        },

        async fetchComplianceDetails(driverId) {
            if (!driverId) return;
            try {
                const response = await fetch(`${this.apiBaseUrl}/drivers/${driverId}/compliance`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) {
                     if (response.status === 401) this.handleUnauthorized(); // Already handled but good practice
                    console.error(`Failed to fetch compliance details (status ${response.status})`);
                    return; // Don't throw, allow other data to load
                }
                const data = await response.json();
                this.complianceDetails = data.data;
                 // If userProfile.driver exists, merge compliance data into it for easier templating
                if (this.userProfile && this.userProfile.driver && data.data) {
                    if(data.data.license_details){ // API returns license_details object
                        this.userProfile.driver.license_number = data.data.license_details.license_number || this.userProfile.driver.license_number;
                        this.userProfile.driver.license_expiry_date = data.data.license_details.license_expiry_date || this.userProfile.driver.license_expiry_date;
                    }
                    // any other specific compliance fields like medical_expiry_date etc.
                    if(data.data.certifications) this.userProfile.driver.certifications = data.data.certifications;

                }


            } catch (error) {
                console.error('Error fetching compliance details:', error);
                 // Don't set globalError here, let the main profile load if possible
            }
        },

        async fetchIncidents(driverId) {
            if (!driverId) {
                this.incidents = [];
                return;
            }
            try {
                const response = await fetch(`${this.apiBaseUrl}/drivers/${driverId}/incidents`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    if (response.status === 401) this.handleUnauthorized();
                    console.error(`Failed to fetch incidents (status ${response.status})`);
                    this.incidents = [];
                    return;
                }
                const data = await response.json();
                this.incidents = data.data.sort((a,b) => new Date(b.incident_date) - new Date(a.incident_date));
            } catch (error) {
                console.error('Error fetching incidents:', error);
                this.incidents = []; // Set to empty on error to show "no incidents"
            }
        },

        getComplianceStatusText(expiryDateStr) {
            if (!expiryDateStr) return 'Not Available';
            const expiryDate = new Date(expiryDateStr);
            const today = new Date();
            const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);

            if (expiryDate < today) return 'Expired';
            if (expiryDate < thirtyDaysFromNow) return 'Expiring Soon';
            return 'Valid';
        },

        getComplianceBadgeClass(expiryDateStr) {
            const status = this.getComplianceStatusText(expiryDateStr).toLowerCase().replace(' ', '-');
            return status; // e.g., 'valid', 'expired', 'expiring-soon', 'not-available'
        },
        getComplianceItemClass(expiryDateStr) {
            const statusText = this.getComplianceStatusText(expiryDateStr);
            if (statusText === 'Expired') return 'expired';
            if (statusText === 'Expiring Soon') return 'warning';
            return '';
        },

        handleUnauthorized() {
            this.globalError = 'Session expired or invalid. Please login again.';
            localStorage.removeItem('driver_token');
            this.isLoading = false; // Stop loading as we can't proceed
            // window.location.href = '/login.php'; // Or root index
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                // Assuming dateString is YYYY-MM-DD
                const date = new Date(dateString + 'T00:00:00'); // Ensure it's parsed as local date
                return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
            } catch (e) { return dateString; }
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
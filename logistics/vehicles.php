<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// Mock data and PHP logic removed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        .status-badge.available { background-color: #dcfce7; color: #166534; }
        .status-badge.maintenance, .status-badge.in-maintenance { background-color: #fef9c3; color: #713f12; }
        .status-badge.on-trip { background-color: #e0e7ff; color: #3730a3; }
        .status-badge.decommissioned { background-color: #fee2e2; color: #991b1b; }
        .status-badge.other { background-color: #f3f4f6; color: #374151; }

        /* Alerts */
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="logisticsVehicles()"
         x-init="init()">
        <?php DashboardSidebar::render('vehicles'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Vehicle Management',
                'Manage and track all vehicles in the fleet'
            ); ?>

            <!-- Global Error/Success Messages -->
            <template x-if="globalMessage.text">
                <div class="p-4">
                    <div class="alert" :class="globalMessage.type === 'success' ? 'alert-success' : 'alert-error'" x-text="globalMessage.text" x-init="setTimeout(() => globalMessage.text = '', 3000)"></div>
                </div>
            </template>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Filters and Actions -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <button @click="showFilters = !showFilters" 
                                        class="btn btn-secondary">
                                    <i class="bi bi-funnel"></i>
                                    <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
                                </button>
                                <div class="relative">
                                    <input type="text" 
                                           x-model.debounce.500ms="filters.search"
                                           @input="applyFiltersDebounced"
                                           placeholder="Search by Reg#, Make, Model..."
                                           class="form-input pl-10">
                                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <button @click="exportCSV()" class="btn btn-secondary">
                                    <i class="bi bi-download"></i>
                                    Export CSV
                                </button>
                                <a href="add-vehicle.php" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i>
                                    Add Vehicle
                                </a>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form @submit.prevent="applyFilters"
                              x-show="showFilters"
                              x-transition
                              class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="form-label">Status</label>
                                <select x-model="filters.status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="Available">Available</option>
                                    <option value="In Maintenance">In Maintenance</option>
                                    <option value="On Trip">On Trip</option>
                                    <option value="Decommissioned">Decommissioned</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Vehicle Type</label>
                                <select x-model="filters.type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="Car">Car</option>
                                    <option value="Van">Van</option>
                                    <option value="Truck">Truck</option>
                                    <option value="Bus">Bus</option>
                                    <option value="SUV">SUV</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Funded By</label>
                                <select x-model="filters.funded_by" class="form-select">
                                    <option value="">All Sources</option>
                                    <option value="Government">Government</option>
                                    <option value="Private">Private</option>
                                    <option value="Donor">Donor</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="btn btn-primary w-full">
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Vehicles Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Make & Model</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Driver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insurance Expiry</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funded By</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="vehicles.length === 0">
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                            No vehicles found matching your criteria.
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="vehicle in vehicles" :key="vehicle.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900" x-text="vehicle.registration_no"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="vehicle.make + ' ' + vehicle.model"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="vehicle.vehicle_type"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-badge"
                                                  :class="getStatusClass(vehicle.status)"
                                                  x-text="vehicle.status">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="vehicle.driver ? vehicle.driver.name : 'N/A'"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(vehicle.next_service_due)"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(vehicle.insurance_expiry)"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="vehicle.funded_by"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <a :href="'vehicle-details.php?id=' + vehicle.id" class="text-primary hover:text-primary-dark" title="View Details"><i class="bi bi-eye"></i></a>
                                                <a :href="'edit-vehicle.php?id=' + vehicle.id" class="text-primary hover:text-primary-dark" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <button @click="alert('Change status for ' + vehicle.registration_no)" type="button" class="text-primary hover:text-primary-dark" title="Change Status"><i class="bi bi-arrow-repeat"></i></button>
                                                <button @click="alert('Assign driver for ' + vehicle.registration_no)" type="button" class="text-primary hover:text-primary-dark" title="Assign Driver"><i class="bi bi-person-plus"></i></button>
                                                <template x-if="vehicle.status !== 'Decommissioned'">
                                                    <button @click="decommissionVehicle(vehicle.id)" type="button" class="text-red-600 hover:text-red-900" title="Decommission"><i class="bi bi-trash"></i></button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                     <!-- Pagination -->
                    <div x-show="pagination.total > pagination.per_page" class="px-6 py-3 bg-white border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-700">
                            Showing <span x-text="pagination.from || 0"></span> to <span x-text="pagination.to || 0"></span> of <span x-text="pagination.total || 0"></span> results
                        </p>
                        <div class="flex space-x-1">
                            <button @click="changePage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="btn btn-secondary btn-sm">&laquo; Previous</button>
                            <template x-for="pageLink in pagination.links">
                                <template x-if="pageLink.url">
                                    <button @click="changePage(pageLink.label)"
                                            :class="{'btn-primary': pageLink.active, 'btn-secondary': !pageLink.active}"
                                            class="btn btn-sm"
                                            x-text="pageLink.label.replace('&laquo;', '').replace('&raquo;', '')"
                                            :disabled="!isNumeric(pageLink.label) && !pageLink.url">
                                    </button>
                                </template>
                            </template>
                            <button @click="changePage(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="btn btn-secondary btn-sm">Next &raquo;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('alpine:initializing', () => {
    Alpine.data('logisticsVehicles', () => ({
        isLoading: true,
        isSubmitting: false,
        vehicles: [],
        filters: {
            search: '',
            status: '',
            type: '',
            funded_by: '',
            page: 1,
            per_page: 15
        },
        pagination: {},
        showFilters: false,
        globalMessage: { text: '', type: '' }, // type can be 'success' or 'error'
        apiBaseUrl: '/api/v1',
        token: localStorage.getItem('admin_token') || localStorage.getItem('logistics_token'), // Assuming admin/logistics might share a token or have their own

        init() {
            if (!this.token) {
                this.setGlobalMessage('Authentication token not found. Please login.', 'error');
                this.isLoading = false;
                return;
            }
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.search = urlParams.get('search') || '';
            this.filters.status = urlParams.get('status') || '';
            this.filters.type = urlParams.get('type') || '';
            this.filters.funded_by = urlParams.get('funded_by') || '';
            this.filters.page = parseInt(urlParams.get('page')) || 1;

            this.fetchVehicles();
        },

        applyFiltersDebounced() {
            clearTimeout(this._filterTimeout);
            this._filterTimeout = setTimeout(() => {
                this.filters.page = 1; // Reset to first page on new search/filter
                this.applyFilters();
            }, 500);
        },

        applyFilters() {
            this.filters.page = 1; // Reset to first page on manual filter application
            const newUrl = new URL(window.location.pathname, window.location.origin);
            for (const key in this.filters) {
                if (this.filters[key] && this.filters[key] !== '') {
                    newUrl.searchParams.set(key, this.filters[key]);
                }
            }
            history.pushState({}, '', newUrl);
            this.fetchVehicles();
        },

        changePage(page) {
            if (page < 1 || (this.pagination.last_page && page > this.pagination.last_page)) return;
            if (!this.isNumeric(page)) return; // Avoid issues with 'Previous', 'Next' labels if not handled by URL
            this.filters.page = Number(page);
            this.applyFilters();
        },

        async fetchVehicles() {
            this.isLoading = true;
            this.globalMessage.text = '';
            try {
                const params = new URLSearchParams();
                for (const key in this.filters) {
                    if (this.filters[key] !== '') {
                        params.append(key, this.filters[key]);
                    }
                }

                const response = await fetch(`${this.apiBaseUrl}/vehicles?${params.toString()}`, {
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    if (response.status === 401) this.handleUnauthorized();
                    const errorData = await response.json();
                    throw new Error(errorData.message || `HTTP error ${response.status}`);
                }
                const result = await response.json();
                this.vehicles = result.data;
                this.pagination = {
                    current_page: result.meta.current_page,
                    from: result.meta.from,
                    last_page: result.meta.last_page,
                    links: result.meta.links.filter(link => link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'), // simplified links for now
                    path: result.meta.path,
                    per_page: result.meta.per_page,
                    to: result.meta.to,
                    total: result.meta.total,
                    prev_page_url: result.links.prev,
                    next_page_url: result.links.next
                };
            } catch (error) {
                this.setGlobalMessage(error.message || 'Failed to load vehicles.', 'error');
                this.vehicles = [];
                this.pagination = {};
            } finally {
                this.isLoading = false;
            }
        },

        async decommissionVehicle(vehicleId) {
            if (!confirm('Are you sure you want to decommission this vehicle? This action might be irreversible.')) return;

            this.isSubmitting = true; // Consider adding specific loading state for this action
            this.globalMessage.text = '';
            try {
                const response = await fetch(`${this.apiBaseUrl}/vehicles/${vehicleId}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    if (response.status === 401) this.handleUnauthorized();
                     const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to decommission vehicle.');
                }
                this.setGlobalMessage('Vehicle decommissioned successfully.', 'success');
                this.fetchVehicles(); // Refresh the list
            } catch (error) {
                this.setGlobalMessage(error.message, 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        exportCSV() {
            const params = new URLSearchParams();
            for (const key in this.filters) {
                if (this.filters[key] !== '' && key !== 'page' && key !== 'per_page') { // Usually export all, not paginated
                    params.append(key, this.filters[key]);
                }
            }
            // Add token to query params if API requires it for direct link access, or use a server-side proxy.
            // For simplicity, assuming public GET or server handles auth if needed for direct downloads.
            // If token is strictly needed in header, CSV export must be handled by fetching data then generating CSV client-side or via blob.
            // The current API /vehicles/export might be a GET that streams CSV.
            window.location.href = `${this.apiBaseUrl}/vehicles/export?${params.toString()}&token=${this.token}`; // Token in query is not ideal but common for simple cases.
            this.setGlobalMessage('CSV export initiated.', 'success');
        },

        getStatusClass(status) {
            if (!status) return 'other';
            const s = status.toLowerCase().replace(/ /g, '-');
            if (['available', 'in-maintenance', 'on-trip', 'decommissioned'].includes(s)) {
                return s;
            }
             if (s === 'maintenance') return 'maintenance'; // Alias
            return 'other';
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString + (dateString.includes('T') ? '' : 'T00:00:00'));
                return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            } catch (e) { return dateString; }
        },

        setGlobalMessage(text, type) {
            this.globalMessage.text = text;
            this.globalMessage.type = type;
        },

        handleUnauthorized() {
            this.setGlobalMessage('Session expired or invalid. Please login again.', 'error');
            localStorage.removeItem(this.token === localStorage.getItem('admin_token') ? 'admin_token' : 'logistics_token');
            this.token = null;
            // Optional: redirect to login page
            // window.location.href = '/login.php';
        },
        alert(message) { window.alert(message); },
        isNumeric(n) { return !isNaN(parseFloat(n)) && isFinite(n); }

    }));
});
</script>
</body>
</html>
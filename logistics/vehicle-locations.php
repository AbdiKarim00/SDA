<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
// require_once 'mock_data.php'; // Mock data system removed

// Get mock data
// $vehicles = get_mock_data('vehicles'); // Mock data system removed
$vehicles = []; // Placeholder

// Calculate statistics
$total_vehicles = count($vehicles);
$in_service = count(array_filter($vehicles, fn($v) => $v['status'] === 'In Service'));
$in_maintenance = count(array_filter($vehicles, fn($v) => $v['status'] === 'In Maintenance'));
$available = count(array_filter($vehicles, fn($v) => $v['status'] === 'Available'));

// Filter vehicles based on search and status
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$filtered_vehicles = array_filter($vehicles, function($vehicle) use ($search, $status_filter) {
    $matches_search = empty($search) || 
        stripos($vehicle['registration_number'], $search) !== false ||
        stripos($vehicle['current_location'], $search) !== false ||
        stripos($vehicle['assigned_driver'], $search) !== false;
    
    $matches_status = empty($status_filter) || $vehicle['status'] === $status_filter;
    
    return $matches_search && $matches_status;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Locations - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <!-- Add Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Add Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('vehicle-locations'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Vehicle Locations',
                'Track and manage vehicle locations'
            ); ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="bi bi-truck text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Vehicles</p>
                            <p class="text-lg font-semibold text-gray-900"><?php echo $total_vehicles; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="bi bi-check-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">In Service</p>
                            <p class="text-lg font-semibold text-gray-900"><?php echo $in_service; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="bi bi-tools text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">In Maintenance</p>
                            <p class="text-lg font-semibold text-gray-900"><?php echo $in_maintenance; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="bi bi-check2-square text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Available</p>
                            <p class="text-lg font-semibold text-gray-900"><?php echo $available; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle List -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <!-- Search and Filter -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0 mb-6">
                        <div class="flex-1 max-w-md">
                            <form action="" method="GET" class="flex space-x-2">
                                <div class="flex-1">
                                    <input type="text" 
                                           name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Search by registration, location, or driver..."
                                           class="form-input w-full">
                                </div>
                                <div>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="In Service" <?php echo $status_filter === 'In Service' ? 'selected' : ''; ?>>In Service</option>
                                        <option value="In Maintenance" <?php echo $status_filter === 'In Maintenance' ? 'selected' : ''; ?>>In Maintenance</option>
                                        <option value="Available" <?php echo $status_filter === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>
                        <button onclick="exportToCSV()" class="btn btn-secondary">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>

                    <!-- Vehicle Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($filtered_vehicles as $vehicle): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($vehicle['registration_number']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($vehicle['current_location'] ?? 'Not Available'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php echo match($vehicle['status']) {
                                                    'In Service' => 'bg-green-100 text-green-800',
                                                    'In Maintenance' => 'bg-yellow-100 text-yellow-800',
                                                    'Available' => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                }; ?>">
                                                <?php echo htmlspecialchars($vehicle['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($vehicle['last_updated'] ?? '1970-01-01 00:00:00')); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($vehicle['assigned_driver']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <button onclick="openUpdateModal(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)" 
                                                    class="text-primary hover:text-primary-dark">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Vehicle Locations Map</h2>
                        <div class="flex space-x-2">
                            <input type="text" 
                                   id="mapSearch" 
                                   placeholder="Search location..." 
                                   class="form-input w-64">
                            <button onclick="searchLocation()" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="map" class="h-[500px] rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Update Modal -->
    <div id="updateModal" class="modal-backdrop hidden">
        <div class="modal-container">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Update Vehicle Location</h3>
                    <button class="modal-close" onclick="closeUpdateModal()">
                        <i class="bi bi-x text-xl"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="updateLocationForm" class="space-y-4">
                        <input type="hidden" id="vehicle_id" name="vehicle_id">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                            <div class="text-sm text-gray-900" id="vehicle_details"></div>
                        </div>

                        <div>
                            <label for="current_location" class="block text-sm font-medium text-gray-700 mb-1">Current Location</label>
                            <input type="text" 
                                   id="current_location" 
                                   name="current_location" 
                                   class="form-input w-full"
                                   required>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="form-select w-full" required>
                                <option value="In Service">In Service</option>
                                <option value="In Maintenance">In Maintenance</option>
                                <option value="Available">Available</option>
                            </select>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      class="form-textarea w-full"
                                      placeholder="Add any additional notes about the location update..."></textarea>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" 
                            onclick="updateLocation()" 
                            class="btn btn-primary">
                        Update Location
                    </button>
                    <button type="button" 
                            onclick="closeUpdateModal()" 
                            class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Map initialization
        let map;
        let markers = {};
        let searchMarker;

        function initMap() {
            // Initialize map centered on Kenya
            map = L.map('map').setView([-0.0236, 37.9062], 7);
            
            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add markers for each vehicle
            <?php foreach ($vehicles as $vehicle): ?>
                addVehicleMarker(<?php echo json_encode($vehicle); ?>);
            <?php endforeach; ?>
        }

        function addVehicleMarker(vehicle) {
            // In a real implementation, you would have coordinates for each location
            // For now, we'll use some sample coordinates for demonstration
            const coordinates = getCoordinatesForLocation(vehicle.current_location);
            
            // Create custom marker icon based on status
            const markerIcon = L.divIcon({
                className: `vehicle-marker ${vehicle.status.toLowerCase().replace(' ', '-')}`,
                html: `<div class="marker-content">
                        <i class="bi bi-truck"></i>
                        <span class="marker-tooltip">${vehicle.registration_number}</span>
                      </div>`,
                iconSize: [30, 30]
            });
            
            const marker = L.marker(coordinates, { icon: markerIcon }).addTo(map);
            
            // Create popup content with update button
            const popupContent = `
                <div class="p-2 min-w-[200px]">
                    <h3 class="font-semibold mb-2">${vehicle.registration_number}</h3>
                    <p class="text-sm mb-1">${vehicle.make} ${vehicle.model}</p>
                    <p class="text-sm mb-1">Status: ${vehicle.status}</p>
                    <p class="text-sm mb-1">Driver: ${vehicle.assigned_driver}</p>
                    <p class="text-sm mb-3">Last Updated: ${new Date(vehicle.last_updated).toLocaleString()}</p>
                    <button onclick="openUpdateModal(${JSON.stringify(vehicle)})" 
                            class="w-full btn btn-primary btn-sm">
                        Update Location
                    </button>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers[vehicle.id] = marker;
        }

        function getCoordinatesForLocation(location) {
            // In a real implementation, you would use a geocoding service
            // For now, we'll use some sample coordinates for demonstration
            const coordinates = {
                'Nairobi CBD': [-1.2921, 36.8219],
                'Mombasa Port': [-4.0435, 39.6682],
                'Kisumu Depot': [-0.1022, 34.7617],
                'Eldoret': [0.5204, 35.2697],
                'Nakuru': [-0.3031, 36.0800]
            };
            
            return coordinates[location] || [-0.0236, 37.9062]; // Default to Kenya center
        }

        function searchLocation() {
            const searchInput = document.getElementById('mapSearch').value;
            if (!searchInput) return;

            // In a real implementation, you would use a geocoding service
            // For now, we'll use our sample coordinates
            const coordinates = getCoordinatesForLocation(searchInput);
            
            // Remove existing search marker
            if (searchMarker) {
                map.removeLayer(searchMarker);
            }
            
            // Add new search marker
            searchMarker = L.marker(coordinates).addTo(map);
            
            // Create popup content
            const popupContent = `
                <div class="p-2 min-w-[200px]">
                    <h3 class="font-semibold mb-2">${searchInput}</h3>
                    <p class="text-sm mb-3">Select a vehicle to update its location</p>
                    <select id="vehicleSelect" class="form-select w-full mb-2">
                        <option value="">Select Vehicle...</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value='${JSON.stringify(<?php echo json_encode($vehicle); ?>)}'>
                                ${<?php echo json_encode($vehicle['registration_number']); ?>} - 
                                ${<?php echo json_encode($vehicle['make'] . ' ' . $vehicle['model']); ?>}
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="updateVehicleLocationFromMap()" 
                            class="w-full btn btn-primary btn-sm">
                        Update Location
                    </button>
                </div>
            `;
            
            searchMarker.bindPopup(popupContent).openPopup();
            
            // Center map on the searched location
            map.setView(coordinates, 13);
        }

        function updateVehicleLocationFromMap() {
            const vehicleSelect = document.getElementById('vehicleSelect');
            if (!vehicleSelect.value) {
                alert('Please select a vehicle');
                return;
            }

            const vehicle = JSON.parse(vehicleSelect.value);
            const searchInput = document.getElementById('mapSearch').value;
            
            // Update the vehicle's location
            vehicle.current_location = searchInput;
            
            // Open the update modal with the new location
            openUpdateModal(vehicle);
            
            // Close the search marker popup
            if (searchMarker) {
                searchMarker.closePopup();
            }
        }

        function exportToCSV() {
            // Get the table data
            const table = document.querySelector('table');
            const rows = Array.from(table.querySelectorAll('tr'));
            
            // Convert to CSV
            const csvContent = rows.map(row => {
                const cells = Array.from(row.querySelectorAll('th, td'));
                return cells.map(cell => {
                    // Remove HTML tags and escape quotes
                    const text = cell.textContent.trim().replace(/"/g, '""');
                    return `"${text}"`;
                }).join(',');
            }).join('\n');
            
            // Create and download the file
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'vehicle-locations.csv';
            link.click();
        }

        // Location Update Modal Functions
        function openUpdateModal(vehicle) {
            document.getElementById('vehicle_id').value = vehicle.id;
            document.getElementById('vehicle_details').textContent = 
                `${vehicle.registration_number} - ${vehicle.make} ${vehicle.model}`;
            document.getElementById('current_location').value = vehicle.current_location;
            document.getElementById('status').value = vehicle.status;
            document.getElementById('notes').value = '';
            
            // Add hidden fields for vehicle details
            const form = document.getElementById('updateLocationForm');
            ['registration_number', 'make', 'model', 'assigned_driver'].forEach(field => {
                let input = form.querySelector(`input[name="${field}"]`);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = field;
                    form.appendChild(input);
                }
                input.value = vehicle[field];
            });
            
            document.getElementById('updateModal').classList.remove('hidden');
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').classList.add('hidden');
        }

        function updateLocation() {
            const form = document.getElementById('updateLocationForm');
            const formData = new FormData(form);
            const vehicleId = formData.get('vehicle_id');
            const newLocation = formData.get('current_location');
            const newStatus = formData.get('status');
            const notes = formData.get('notes');
            
            // Here you would typically send this to your backend
            // For now, we'll just update the marker and show a success message
            if (markers[vehicleId]) {
                const coordinates = getCoordinatesForLocation(newLocation);
                markers[vehicleId].setLatLng(coordinates);
                
                // Update marker icon based on new status
                const markerIcon = L.divIcon({
                    className: `vehicle-marker ${newStatus.toLowerCase().replace(' ', '-')}`,
                    html: `<div class="marker-content">
                            <i class="bi bi-truck"></i>
                            <span class="marker-tooltip">${formData.get('registration_number')}</span>
                          </div>`,
                    iconSize: [30, 30]
                });
                markers[vehicleId].setIcon(markerIcon);
                
                // Update popup content
                const popupContent = `
                    <div class="p-2 min-w-[200px]">
                        <h3 class="font-semibold mb-2">${formData.get('registration_number')}</h3>
                        <p class="text-sm mb-1">${formData.get('make')} ${formData.get('model')}</p>
                        <p class="text-sm mb-1">Status: ${newStatus}</p>
                        <p class="text-sm mb-1">Driver: ${formData.get('assigned_driver')}</p>
                        <p class="text-sm mb-3">Last Updated: ${new Date().toLocaleString()}</p>
                        <button onclick="openUpdateModal(${JSON.stringify({
                            id: vehicleId,
                            registration_number: formData.get('registration_number'),
                            make: formData.get('make'),
                            model: formData.get('model'),
                            current_location: newLocation,
                            status: newStatus,
                            assigned_driver: formData.get('assigned_driver'),
                            last_updated: new Date().toISOString()
                        })})" 
                                class="w-full btn btn-primary btn-sm">
                            Update Location
                        </button>
                    </div>
                `;
                markers[vehicleId].setPopupContent(popupContent);
                
                // Center map on the new location
                map.setView(coordinates, 13);
            }
            
            // Show success message
            alert('Location updated successfully!');
            closeUpdateModal();
        }

        // Close modal when clicking outside
        document.getElementById('updateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUpdateModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUpdateModal();
            }
        });

        // Add custom styles for markers
        const style = document.createElement('style');
        style.textContent = `
            .vehicle-marker {
                background: none;
                border: none;
            }
            .vehicle-marker .marker-content {
                width: 30px;
                height: 30px;
                background: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                position: relative;
            }
            .vehicle-marker .marker-tooltip {
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
                opacity: 0;
                transition: opacity 0.2s;
                pointer-events: none;
            }
            .vehicle-marker:hover .marker-tooltip {
                opacity: 1;
            }
            .vehicle-marker.in-service .marker-content {
                background: #10B981;
                color: white;
            }
            .vehicle-marker.in-maintenance .marker-content {
                background: #F59E0B;
                color: white;
            }
            .vehicle-marker.available .marker-content {
                background: #3B82F6;
                color: white;
            }
        `;
        document.head.appendChild(style);

        // Initialize map when the page loads
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>
</html> 
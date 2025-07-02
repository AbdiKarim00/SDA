<?php
require_once '../includes/db.php';

$db = Database::getInstance();

// Get vehicle ID from URL
$vehicleId = $_GET['id'] ?? null;
if (!$vehicleId) {
    header('Location: vehicles.php');
    exit;
}

// Get vehicle details
$vehicle = $db->query("
    SELECT 
        v.*,
        u.email as created_by_email,
        COUNT(DISTINCT t.id) as total_trips,
        COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_trips,
        COUNT(DISTINCT m.id) as maintenance_count,
        COALESCE(SUM(m.cost), 0) as total_maintenance_cost
    FROM vehicles v
    LEFT JOIN users u ON v.created_by = u.id
    LEFT JOIN trips t ON v.id = t.vehicle_id
    LEFT JOIN maintenance_records m ON v.id = m.vehicle_id
    WHERE v.id = :id
    GROUP BY v.id, u.email
", ['id' => $vehicleId])->fetch();

if (!$vehicle) {
    header('Location: vehicles.php');
    exit;
}

// Get recent trips
$trips = $db->query("
    SELECT 
        t.*,
        u.email as driver_email,
        ST_AsText(t.start_location) as start_location_text,
        ST_AsText(t.end_location) as end_location_text
    FROM trips t
    JOIN users u ON t.driver_id = u.id
    WHERE t.vehicle_id = :vehicle_id
    ORDER BY t.start_time DESC
    LIMIT 5
", ['vehicle_id' => $vehicleId])->fetchAll();

// Get recent maintenance records
$maintenance = $db->query("
    SELECT *
    FROM maintenance_records
    WHERE vehicle_id = :vehicle_id
    ORDER BY maintenance_date DESC
    LIMIT 5
", ['vehicle_id' => $vehicleId])->fetchAll();

// Get current location if available
$location = $db->query("
    SELECT 
        ST_X(current_location::geometry) as longitude,
        ST_Y(current_location::geometry) as latitude
    FROM vehicles
    WHERE id = :id AND current_location IS NOT NULL
", ['id' => $vehicleId])->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Details - SDATIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../components/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Vehicle Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="vehicles.php" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Back to Vehicles
                        </a>
                        <button type="button" class="btn btn-warning me-2" onclick="editVehicle(<?php echo $vehicle['id']; ?>)">
                            <i class="bi bi-pencil"></i> Edit Vehicle
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)">
                            <i class="bi bi-trash"></i> Delete Vehicle
                        </button>
                    </div>
                </div>
                
                <!-- Vehicle Information -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Vehicle Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th>Registration Number</th>
                                        <td><?php echo htmlspecialchars($vehicle['registration_number']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Make</th>
                                        <td><?php echo htmlspecialchars($vehicle['make']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Model</th>
                                        <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Year</th>
                                        <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Capacity</th>
                                        <td><?php echo htmlspecialchars($vehicle['capacity']); ?> tons</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($vehicle['status']) {
                                                    'active' => 'success',
                                                    'maintenance' => 'warning',
                                                    'inactive' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($vehicle['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created By</th>
                                        <td><?php echo htmlspecialchars($vehicle['created_by_email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td><?php echo date('M d, Y H:i', strtotime($vehicle['created_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Total Trips</h6>
                                                <h2><?php echo $vehicle['total_trips']; ?></h2>
                                                <small class="text-muted">
                                                    <?php echo $vehicle['completed_trips']; ?> completed
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Maintenance Records</h6>
                                                <h2><?php echo $vehicle['maintenance_count']; ?></h2>
                                                <small class="text-muted">
                                                    Total cost: $<?php echo number_format($vehicle['total_maintenance_cost'], 2); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($location): ?>
                                <div class="mt-3">
                                    <h6>Current Location</h6>
                                    <div id="map" style="height: 200px;"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Trips -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Trips</h5>
                        <a href="trips.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-primary">
                            View All Trips
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($trips)): ?>
                            <p class="text-muted">No trips found for this vehicle.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th>Start Location</th>
                                            <th>End Location</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($trips as $trip): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($trip['driver_email']); ?></td>
                                                <td><?php echo htmlspecialchars($trip['start_location_text']); ?></td>
                                                <td><?php echo htmlspecialchars($trip['end_location_text']); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($trip['start_time'])); ?></td>
                                                <td>
                                                    <?php 
                                                    echo $trip['end_time'] 
                                                        ? date('M d, Y H:i', strtotime($trip['end_time']))
                                                        : '-';
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($trip['status']) {
                                                            'completed' => 'success',
                                                            'in_progress' => 'primary',
                                                            'cancelled' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($trip['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Maintenance -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Maintenance</h5>
                        <a href="maintenance.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-primary">
                            View All Maintenance
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($maintenance)): ?>
                            <p class="text-muted">No maintenance records found for this vehicle.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Vendor</th>
                                            <th>Cost</th>
                                            <th>Next Maintenance</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenance as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['maintenance_type']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($record['maintenance_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($record['vendor'] ?? '-'); ?></td>
                                                <td>
                                                    <?php 
                                                    echo $record['cost'] 
                                                        ? '$' . number_format($record['cost'], 2)
                                                        : '-';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    echo $record['next_maintenance_date']
                                                        ? date('M d, Y', strtotime($record['next_maintenance_date']))
                                                        : '-';
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($record['status']) {
                                                            'completed' => 'success',
                                                            'pending' => 'warning',
                                                            'cancelled' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($record['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
    <?php if ($location): ?>
    // Initialize map
    const map = L.map('map').setView([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add marker
    L.marker([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>])
        .addTo(map)
        .bindPopup('<?php echo htmlspecialchars($vehicle['registration_number']); ?>')
        .openPopup();
    <?php endif; ?>
    
    async function editVehicle(id) {
        try {
            // Get current vehicle data
            const response = await fetch(`/api/vehicles/get.php?id=${id}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to get vehicle data');
            }
            
            const vehicle = data.vehicle;
            
            // Create and show edit modal
            const modal = new bootstrap.Modal(document.createElement('div'));
            modal.element.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Vehicle</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editVehicleForm">
                                <input type="hidden" name="id" value="${vehicle.id}">
                                <div class="mb-3">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" class="form-control" name="registration_number" value="${vehicle.registration_number}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Make</label>
                                    <input type="text" class="form-control" name="make" value="${vehicle.make}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Model</label>
                                    <input type="text" class="form-control" name="model" value="${vehicle.model}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Year</label>
                                    <input type="number" class="form-control" name="year" value="${vehicle.year}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Capacity</label>
                                    <input type="number" class="form-control" name="capacity" value="${vehicle.capacity}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="active" ${vehicle.status === 'active' ? 'selected' : ''}>Active</option>
                                        <option value="maintenance" ${vehicle.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                        <option value="inactive" ${vehicle.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="saveVehicleEdit()">Save Changes</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal.element);
            modal.show();
            
            // Clean up modal when hidden
            modal.element.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal.element);
            });
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load vehicle data');
        }
    }
    
    async function saveVehicleEdit() {
        const form = document.getElementById('editVehicleForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch('/api/vehicles/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.reload();
            } else {
                throw new Error(result.error || 'Failed to update vehicle');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to update vehicle');
        }
    }
    
    async function deleteVehicle(id) {
        if (confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')) {
            try {
                const response = await fetch('/api/vehicles/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = 'vehicles.php';
                } else {
                    throw new Error(result.error || 'Failed to delete vehicle');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to delete vehicle');
            }
        }
    }
    </script>
</body>
</html> 
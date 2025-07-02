<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !in_array($auth->getCurrentUser()['role'], ['admin', 'logistics'])) {
    header('Location: /auth/login.php');
    exit;
}

$db = Database::getInstance();

// Get trip ID from query string
$tripId = $_GET['id'] ?? null;
if (!$tripId) {
    header('Location: trips.php');
    exit;
}

// Get trip details
$trip = $db->query("
    SELECT 
        t.*,
        v.registration_number,
        v.make,
        v.model,
        v.status as vehicle_status,
        u.email as driver_email,
        u.phone as driver_phone,
        ST_AsText(t.start_location) as start_location_text,
        ST_AsText(t.end_location) as end_location_text,
        ST_AsText(v.current_location) as vehicle_location_text
    FROM trips t
    JOIN vehicles v ON t.vehicle_id = v.id
    JOIN users u ON t.driver_id = u.id
    WHERE t.id = :trip_id
", ['trip_id' => $tripId])->fetch();

if (!$trip) {
    header('Location: trips.php');
    exit;
}

// Get trip history
$history = $db->query("
    SELECT 
        th.*,
        u.email as updated_by_email
    FROM trip_history th
    JOIN users u ON th.updated_by = u.id
    WHERE th.trip_id = :trip_id
    ORDER BY th.updated_at DESC
", ['trip_id' => $tripId])->fetchAll();

// Get vehicles
$vehicles = $db->query("
    SELECT id, registration_number, make, model
    FROM vehicles
")->fetchAll();

// Get drivers
$drivers = $db->query("
    SELECT id, email
    FROM users
    WHERE role = 'driver'
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Details - SDATIMS</title>
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
                    <h1 class="h2">Trip Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="trips.php" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Back to Trips
                        </a>
                        <?php if ($trip['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-warning me-2" onclick="editTrip(<?php echo $trip['id']; ?>)">
                                <i class="bi bi-pencil"></i> Edit Trip
                            </button>
                            <button type="button" class="btn btn-danger" onclick="cancelTrip(<?php echo $trip['id']; ?>)">
                                <i class="bi bi-x-circle"></i> Cancel Trip
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Trip Information -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Trip Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($trip['status']) {
                                                    'completed' => 'success',
                                                    'in_progress' => 'primary',
                                                    'pending' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($trip['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle</th>
                                        <td>
                                            <?php echo htmlspecialchars($trip['registration_number']); ?>
                                            (<?php echo htmlspecialchars($trip['make'] . ' ' . $trip['model']); ?>)
                                            <span class="badge bg-<?php 
                                                echo match($trip['vehicle_status']) {
                                                    'active' => 'success',
                                                    'maintenance' => 'warning',
                                                    'inactive' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($trip['vehicle_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Driver</th>
                                        <td>
                                            <?php echo htmlspecialchars($trip['driver_email']); ?>
                                            <?php if ($trip['driver_phone']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    Phone: <?php echo htmlspecialchars($trip['driver_phone']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Start Location</th>
                                        <td><?php echo htmlspecialchars($trip['start_location_text']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>End Location</th>
                                        <td><?php echo htmlspecialchars($trip['end_location_text']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Start Time</th>
                                        <td><?php echo date('M d, Y H:i', strtotime($trip['start_time'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>End Time</th>
                                        <td>
                                            <?php 
                                            echo $trip['end_time'] 
                                                ? date('M d, Y H:i', strtotime($trip['end_time']))
                                                : '-';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php if ($trip['notes']): ?>
                                    <tr>
                                        <th>Notes</th>
                                        <td><?php echo nl2br(htmlspecialchars($trip['notes'])); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Map -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Trip Map</h5>
                            </div>
                            <div class="card-body">
                                <div id="tripMap" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Trip History -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Trip History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($history)): ?>
                            <p class="text-muted">No history available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                            <th>Updated By</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $entry): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y H:i', strtotime($entry['updated_at'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($entry['status']) {
                                                            'completed' => 'success',
                                                            'in_progress' => 'primary',
                                                            'pending' => 'warning',
                                                            'cancelled' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($entry['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($entry['updated_by_email']); ?></td>
                                                <td><?php echo htmlspecialchars($entry['notes']); ?></td>
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
    
    <!-- Add Edit Trip Modal -->
    <div class="modal fade" id="editTripModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Trip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editTripForm">
                        <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Vehicle</label>
                                <select class="form-select" name="vehicle_id" required>
                                    <?php foreach ($vehicles as $v): ?>
                                        <option value="<?php echo $v['id']; ?>" <?php echo $v['id'] == $trip['vehicle_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($v['registration_number'] . ' (' . $v['make'] . ' ' . $v['model'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Driver</label>
                                <select class="form-select" name="driver_id" required>
                                    <?php foreach ($drivers as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" <?php echo $d['id'] == $trip['driver_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['email']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Location</label>
                                <input type="text" class="form-control" name="start_location" value="<?php echo htmlspecialchars($trip['start_location_text']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Location</label>
                                <input type="text" class="form-control" name="end_location" value="<?php echo htmlspecialchars($trip['end_location_text']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" name="start_time" value="<?php echo date('Y-m-d\TH:i', strtotime($trip['start_time'])); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($trip['notes']); ?></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveTrip()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Trip Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="pending" <?php echo $trip['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $trip['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="delayed" <?php echo $trip['status'] === 'delayed' ? 'selected' : ''; ?>>Delayed</option>
                                <option value="completed" <?php echo $trip['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $trip['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="status_notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateStatus()">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Update Location Modal -->
    <div class="modal fade" id="updateLocationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Vehicle Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateLocationForm">
                        <input type="hidden" name="vehicle_id" value="<?php echo $trip['vehicle_id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateLocation()">Update Location</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
    // Initialize map
    const map = L.map('tripMap').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add start and end markers
    const startCoords = <?php echo $trip['start_location_text']; ?>;
    const endCoords = <?php echo $trip['end_location_text']; ?>;
    
    L.marker([startCoords[1], startCoords[0]])
        .addTo(map)
        .bindPopup('Start Location')
        .openPopup();
    
    L.marker([endCoords[1], endCoords[0]])
        .addTo(map)
        .bindPopup('End Location');
    
    // Add vehicle marker if available
    <?php if ($trip['vehicle_location_text']): ?>
    const vehicleCoords = <?php echo $trip['vehicle_location_text']; ?>;
    L.marker([vehicleCoords[1], vehicleCoords[0]])
        .addTo(map)
        .bindPopup('Current Vehicle Location')
        .openPopup();
    <?php endif; ?>
    
    // Fit map to show all markers
    const bounds = L.latLngBounds([
        [startCoords[1], startCoords[0]],
        [endCoords[1], endCoords[0]]
        <?php if ($trip['vehicle_location_text']): ?>,
        [vehicleCoords[1], vehicleCoords[0]]
        <?php endif; ?>
    ]);
    map.fitBounds(bounds);
    
    function editTrip(id) {
        const modal = new bootstrap.Modal(document.getElementById('editTripModal'));
        modal.show();
    }
    
    async function cancelTrip(id) {
        if (confirm('Are you sure you want to cancel this trip?')) {
            try {
                const response = await fetch('/api/trips/cancel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ trip_id: id })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    throw new Error(result.error || 'Failed to cancel trip');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Failed to cancel trip');
            }
        }
    }

    function updateStatus() {
        const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
        modal.show();
    }

    async function saveStatus() {
        const form = document.getElementById('updateStatusForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch('/api/trips/update.php', {
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
                throw new Error(result.error || 'Failed to update status');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to update status');
        }
    }

    function updateLocation() {
        const modal = new bootstrap.Modal(document.getElementById('updateLocationModal'));
        modal.show();
    }

    async function saveLocation() {
        const form = document.getElementById('updateLocationForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch('/api/vehicles/update_location.php', {
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
                throw new Error(result.error || 'Failed to update location');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to update location');
        }
    }
    </script>
</body>
</html> 
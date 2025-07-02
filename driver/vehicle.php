<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../logistics/mock_data.php';

// Get mock data
$drivers = get_mock_data('drivers');
$vehicles = get_mock_data('vehicles');
$maintenance = get_mock_data('maintenance');

// Mock current driver (in real app, this would come from session)
$current_driver = $drivers[0];

// Get current vehicle assignment
$current_vehicle = array_filter($vehicles, fn($v) => $v['assigned_driver'] === $current_driver['name']);
$current_vehicle = reset($current_vehicle);

// Get maintenance records for current vehicle
$vehicle_maintenance = $current_vehicle ? array_filter($maintenance, fn($m) => $m['vehicle_id'] === $current_vehicle['id']) : [];
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
    </style>
</head>
<body class="pb-20">
    <div class="app-container" x-data="{ 
        isLoading: true,
        showOdometerModal: false,
        showMaintenanceModal: false,
        currentOdometer: '<?php echo $current_vehicle['current_odometer'] ?? 0; ?>'
    }" x-init="setTimeout(() => isLoading = false, 500)">
        
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
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading" class="p-4 space-y-4">
                <?php if (!$current_vehicle): ?>
                    <div class="empty-state">
                        <i class="bi bi-car-front empty-state-icon"></i>
                        <p class="empty-state-text">No vehicle assigned</p>
                    </div>
                <?php else: ?>
                    <!-- Vehicle Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Vehicle Details</h2>
                                <span class="status-badge active">
                                    <?php echo htmlspecialchars($current_vehicle['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="list-item">
                                        <i class="bi bi-truck icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium"><?php echo htmlspecialchars($current_vehicle['registration_number']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($current_vehicle['make'] . ' ' . $current_vehicle['model']); ?></p>
                                        </div>
                                    </div>
                                    <div class="list-item">
                                        <i class="bi bi-speedometer2 icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Current Odometer</p>
                                            <p class="text-xs text-gray-500"><?php echo number_format($current_vehicle['current_odometer']); ?> km</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="list-item">
                                        <i class="bi bi-calendar-check icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Last Service</p>
                                            <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($current_vehicle['last_maintenance'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="list-item">
                                        <i class="bi bi-calendar-event icon-blue"></i>
                                        <div>
                                            <p class="text-sm font-medium">Next Service</p>
                                            <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($current_vehicle['next_maintenance'])); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2">
                                    <button class="btn btn-secondary flex-1" @click="showOdometerModal = true">
                                        <i class="bi bi-pencil me-2"></i>
                                        Update Odometer
                                    </button>
                                    <button class="btn btn-secondary flex-1" @click="showMaintenanceModal = true">
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
                            <?php if (empty($vehicle_maintenance)): ?>
                                <div class="empty-state">
                                    <p class="empty-state-text">No maintenance records found</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($vehicle_maintenance as $record): ?>
                                        <div class="list-item">
                                            <div class="list-item-content">
                                                <div class="list-item-header">
                                                    <p class="font-medium"><?php echo htmlspecialchars($record['type']); ?></p>
                                                    <span class="status-badge <?php echo $record['status'] === 'Completed' ? 'active' : 'warning'; ?>">
                                                        <?php echo htmlspecialchars($record['status']); ?>
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($record['description']); ?></p>
                                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                    <span><?php echo date('M d, Y', strtotime($record['date'])); ?></span>
                                                    <span>KES <?php echo number_format($record['cost'], 2); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
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
        <div x-show="showOdometerModal" class="modal-overlay" x-cloak>
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Update Odometer</h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Current Reading</label>
                        <input type="number" x-model="currentOdometer" class="form-input">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button class="btn btn-secondary" @click="showOdometerModal = false">Cancel</button>
                        <button class="btn btn-primary">Update</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Issue Modal -->
        <div x-show="showMaintenanceModal" class="modal-overlay" x-cloak>
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Report Maintenance Issue</h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Issue Type</label>
                        <select class="form-select">
                            <option>Mechanical</option>
                            <option>Electrical</option>
                            <option>Body</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" rows="3"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button class="btn btn-secondary" @click="showMaintenanceModal = false">Cancel</button>
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
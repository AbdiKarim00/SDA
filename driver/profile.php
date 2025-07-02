<?php
require_once '../logistics/mock_data.php';

// Get mock data
$drivers = get_mock_data('drivers');
$incidents = get_mock_data('incidents');

// Mock current driver (in real app, this would come from session)
$current_driver = $drivers[0];

// Get incidents for current driver
$driver_incidents = array_filter($incidents, fn($i) => $i['driver_id'] === $current_driver['id']);
usort($driver_incidents, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
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

        .compliance-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
        }

        .compliance-item.expired {
            background-color: #fee2e2;
        }

        .compliance-item.warning {
            background-color: #fef3c7;
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
    </style>
</head>
<body class="pb-20">
    <div class="app-container" x-data="{ 
        isLoading: true
    }" x-init="setTimeout(() => isLoading = false, 500)">
        
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
                        <button class="p-2 text-gray-600 hover:text-gray-900 rounded-full hover:bg-gray-100">
                            <i class="bi bi-pencil text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading" class="space-y-3">
                <!-- Personal Details Card -->
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
                                    <h3 class="text-lg font-medium"><?php echo htmlspecialchars($current_driver['name']); ?></h3>
                                    <p class="text-sm text-gray-500">Driver ID: <?php echo htmlspecialchars($current_driver['id']); ?></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <div class="compliance-item">
                                    <i class="bi bi-envelope text-blue-600 text-xl"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($current_driver['email']); ?></p>
                                    </div>
                                </div>
                                <div class="compliance-item">
                                    <i class="bi bi-telephone text-blue-600 text-xl"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Phone</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($current_driver['phone']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compliance Information Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-900">Compliance Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <?php
                            // Helper function to check document status
                            function getDocumentStatus($expiryDate) {
                                if (empty($expiryDate)) return ['status' => 'error', 'message' => 'Not Available'];
                                
                                $expiry = strtotime($expiryDate);
                                $now = time();
                                $thirtyDays = 30 * 24 * 60 * 60;
                                
                                if ($expiry < $now) {
                                    return ['status' => 'error', 'message' => 'Expired'];
                                } elseif ($expiry - $now < $thirtyDays) {
                                    return ['status' => 'warning', 'message' => 'Expiring Soon'];
                                }
                                return ['status' => 'active', 'message' => 'Valid'];
                            }

                            // Get status for each document
                            $licenseStatus = getDocumentStatus($current_driver['license_expiry'] ?? null);
                            ?>

                            <div class="compliance-item <?php echo $licenseStatus['status'] === 'error' ? 'expired' : ($licenseStatus['status'] === 'warning' ? 'warning' : ''); ?>">
                                <i class="bi bi-card-text text-blue-600 text-xl"></i>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500">Driver's License</p>
                                            <p class="font-medium">
                                                <?php 
                                                if (!empty($current_driver['license_expiry'])) {
                                                    echo date('M d, Y', strtotime($current_driver['license_expiry']));
                                                } else {
                                                    echo 'Not Available';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <span class="status-badge <?php echo $licenseStatus['status']; ?>">
                                            <?php echo $licenseStatus['message']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incident Reports Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-900">Incident Reports</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($driver_incidents)): ?>
                            <div class="text-center py-4">
                                <p class="text-gray-500">No incident reports found</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($driver_incidents as $incident): ?>
                                    <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <p class="font-medium"><?php echo htmlspecialchars($incident['type']); ?></p>
                                                <span class="status-badge <?php echo $incident['status'] === 'Resolved' ? 'active' : 'warning'; ?>">
                                                    <?php echo htmlspecialchars($incident['status']); ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($incident['description']); ?></p>
                                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                <span><?php echo date('M d, Y', strtotime($incident['date'])); ?></span>
                                                <?php if (!empty($incident['location'])): ?>
                                                    <span>Location: <?php echo htmlspecialchars($incident['location']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
</body>
</html> 
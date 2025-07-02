<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Get the disposal request ID from the URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Mock data for a vehicle marked for disposal
$vehicle = [
    'id' => 1,
    'registration_number' => 'GKB 673S',
    'make' => 'Toyota',
    'model' => 'Hilux',
    'year' => '2018',
    'asset_condition' => 'Marked for Disposal',
    'reason' => 'Exceeded useful life, unserviceable',
    'net_book_value' => 3770500,
    'accumulated_depreciation' => 3770500,
    'current_location' => 'MULEI UNSERVICEABLE YARD',
    'disposal_status' => 'Pending Approval',
    'marked_date' => '2024-03-15',
    'proposed_disposal_method' => 'Auction',
    'estimated_value' => 2500000,
    'documents' => [
        [
            'name' => 'Vehicle Assessment Report',
            'type' => 'PDF',
            'size' => '2.5 MB',
            'uploaded_by' => 'John Doe',
            'upload_date' => '2024-03-15'
        ],
        [
            'name' => 'Maintenance History',
            'type' => 'PDF',
            'size' => '1.8 MB',
            'uploaded_by' => 'John Doe',
            'upload_date' => '2024-03-15'
        ],
        [
            'name' => 'Vehicle Photos',
            'type' => 'ZIP',
            'size' => '15.2 MB',
            'uploaded_by' => 'John Doe',
            'upload_date' => '2024-03-15'
        ]
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would:
    // 1. Validate the input
    // 2. Process file uploads
    // 3. Update database
    // 4. Redirect to the disposal details page
    
    // For now, we'll just redirect
    header('Location: disposal-details.php?id=' . $id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Disposal Request - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('disposal'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Edit Disposal Request',
                'Update vehicle disposal request details'
            ); ?>

            <div class="max-w-4xl mx-auto">
                <form action="disposal-edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Vehicle Information -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Registration Number</label>
                                    <input type="text" value="<?php echo htmlspecialchars($vehicle['registration_number']); ?>" 
                                           class="form-input bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Make & Model</label>
                                    <input type="text" value="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>" 
                                           class="form-input bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Net Book Value</label>
                                    <input type="text" value="KES <?php echo number_format($vehicle['net_book_value'], 2); ?>" 
                                           class="form-input bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Accumulated Depreciation</label>
                                    <input type="text" value="KES <?php echo number_format($vehicle['accumulated_depreciation'], 2); ?>" 
                                           class="form-input bg-gray-50" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Disposal Details -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Disposal Details</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Disposal</label>
                                    <textarea id="reason" name="reason" rows="3" required
                                              class="form-textarea"><?php echo htmlspecialchars($vehicle['reason']); ?></textarea>
                                </div>
                                <div>
                                    <label for="proposed_method" class="block text-sm font-medium text-gray-700 mb-1">Proposed Disposal Method</label>
                                    <select id="proposed_method" name="proposed_method" required class="form-select">
                                        <option value="Auction" <?php echo $vehicle['proposed_disposal_method'] === 'Auction' ? 'selected' : ''; ?>>Auction</option>
                                        <option value="Sale" <?php echo $vehicle['proposed_disposal_method'] === 'Sale' ? 'selected' : ''; ?>>Direct Sale</option>
                                        <option value="Scrap" <?php echo $vehicle['proposed_disposal_method'] === 'Scrap' ? 'selected' : ''; ?>>Scrap</option>
                                        <option value="Donation" <?php echo $vehicle['proposed_disposal_method'] === 'Donation' ? 'selected' : ''; ?>>Donation</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="estimated_value" class="block text-sm font-medium text-gray-700 mb-1">Estimated Value (KES)</label>
                                    <input type="number" id="estimated_value" name="estimated_value" required
                                           class="form-input"
                                           value="<?php echo $vehicle['estimated_value']; ?>">
                                </div>
                                <div>
                                    <label for="current_location" class="block text-sm font-medium text-gray-700 mb-1">Current Location</label>
                                    <input type="text" id="current_location" name="current_location" required
                                           class="form-input"
                                           value="<?php echo htmlspecialchars($vehicle['current_location']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supporting Documents -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Supporting Documents</h2>
                            
                            <!-- Existing Documents -->
                            <div class="space-y-4 mb-6">
                                <?php foreach ($vehicle['documents'] as $document): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <i class="bi bi-file-earmark-text text-gray-400 text-xl mr-3"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($document['name']); ?></p>
                                                <p class="text-xs text-gray-500">
                                                    <?php echo $document['type']; ?> • <?php echo $document['size']; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button type="button" class="text-blue-600 hover:text-blue-900">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <button type="button" class="text-red-600 hover:text-red-900">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Upload New Documents -->
                            <div class="space-y-4">
                                <div>
                                    <label for="assessment_report" class="block text-sm font-medium text-gray-700 mb-1">Vehicle Assessment Report</label>
                                    <input type="file" id="assessment_report" name="assessment_report"
                                           class="form-input"
                                           accept=".pdf,.doc,.docx">
                                </div>
                                <div>
                                    <label for="maintenance_history" class="block text-sm font-medium text-gray-700 mb-1">Maintenance History</label>
                                    <input type="file" id="maintenance_history" name="maintenance_history"
                                           class="form-input"
                                           accept=".pdf,.doc,.docx">
                                </div>
                                <div>
                                    <label for="vehicle_photos" class="block text-sm font-medium text-gray-700 mb-1">Vehicle Photos</label>
                                    <input type="file" id="vehicle_photos" name="vehicle_photos"
                                           class="form-input"
                                           accept="image/*" multiple>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4">
                        <a href="disposal-details.php?id=<?php echo $id; ?>" class="btn btn-outline">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add any necessary JavaScript for form validation and interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Handle document deletion
            const deleteButtons = document.querySelectorAll('.bi-trash').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this document?')) {
                        // In a real application, you would:
                        // 1. Send an AJAX request to delete the document
                        // 2. Remove the document element from the DOM
                        this.closest('.flex').remove();
                    }
                });
            });

            // Handle document download
            const downloadButtons = document.querySelectorAll('.bi-download').forEach(button => {
                button.addEventListener('click', function() {
                    // In a real application, you would:
                    // 1. Get the document ID or URL
                    // 2. Trigger the download
                    alert('Document download would start here');
                });
            });
        });
    </script>
</body>
</html> 
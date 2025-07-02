<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

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
    'approval_chain' => [
        [
            'role' => 'Logistics Officer',
            'status' => 'Approved',
            'date' => '2024-03-15',
            'comment' => 'Vehicle has exceeded useful life and requires significant repairs',
            'name' => 'John Doe'
        ],
        [
            'role' => 'Maintenance Manager',
            'status' => 'Pending',
            'date' => null,
            'comment' => null,
            'name' => null
        ],
        [
            'role' => 'Finance Officer',
            'status' => 'Pending',
            'date' => null,
            'comment' => null,
            'name' => null
        ],
        [
            'role' => 'Senior Management',
            'status' => 'Pending',
            'date' => null,
            'comment' => null,
            'name' => null
        ]
    ],
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
    ],
    'maintenance_history' => [
        [
            'date' => '2024-02-15',
            'description' => 'Major engine overhaul',
            'cost' => 850000
        ],
        [
            'date' => '2023-12-10',
            'description' => 'Transmission replacement',
            'cost' => 450000
        ],
        [
            'date' => '2023-09-05',
            'description' => 'Suspension system repair',
            'cost' => 320000
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Disposal Details - Transport IMS</title>
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
                'Vehicle Disposal Details',
                'View and manage vehicle disposal process'
            ); ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Vehicle Information -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Vehicle Information</h2>
                                    <p class="text-sm text-gray-500">Details about the vehicle marked for disposal</p>
                                </div>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                    <?php
                                    switch ($vehicle['disposal_status']) {
                                        case 'Pending Approval':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'In Assessment':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'Approved':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'Completed':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo $vehicle['disposal_status']; ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Vehicle Details</h3>
                                    <dl class="mt-2 space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Registration Number</dt>
                                            <dd class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['registration_number']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Make & Model</dt>
                                            <dd class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Year</dt>
                                            <dd class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['year']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Current Location</dt>
                                            <dd class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['current_location']); ?></dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Financial Information</h3>
                                    <dl class="mt-2 space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Net Book Value</dt>
                                            <dd class="text-sm font-medium text-gray-900">KES <?php echo number_format($vehicle['net_book_value'], 2); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Accumulated Depreciation</dt>
                                            <dd class="text-sm font-medium text-gray-900">KES <?php echo number_format($vehicle['accumulated_depreciation'], 2); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Proposed Disposal Method</dt>
                                            <dd class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['proposed_disposal_method']); ?></dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Estimated Value</dt>
                                            <dd class="text-sm font-medium text-gray-900">KES <?php echo number_format($vehicle['estimated_value'], 2); ?></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-sm font-medium text-gray-500">Reason for Disposal</h3>
                                <p class="mt-2 text-sm text-gray-900"><?php echo htmlspecialchars($vehicle['reason']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Chain -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Approval Chain</h2>
                            <div class="space-y-6">
                                <?php foreach ($vehicle['approval_chain'] as $approval): ?>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                <?php
                                                switch ($approval['status']) {
                                                    case 'Approved':
                                                        echo 'bg-green-100 text-green-600';
                                                        break;
                                                    case 'In Progress':
                                                        echo 'bg-blue-100 text-blue-600';
                                                        break;
                                                    case 'Pending':
                                                        echo 'bg-gray-100 text-gray-400';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-400';
                                                }
                                                ?>">
                                                <i class="bi bi-person text-xl"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($approval['role']); ?></h3>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    <?php
                                                    switch ($approval['status']) {
                                                        case 'Approved':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'In Progress':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'Pending':
                                                            echo 'bg-gray-100 text-gray-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>">
                                                    <?php echo $approval['status']; ?>
                                                </span>
                                            </div>
                                            <?php if ($approval['date']): ?>
                                                <p class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($approval['name']); ?> - 
                                                    <?php echo date('M d, Y', strtotime($approval['date'])); ?>
                                                </p>
                                                <?php if ($approval['comment']): ?>
                                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($approval['comment']); ?></p>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-500">Pending approval</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance History -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Maintenance History</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($vehicle['maintenance_history'] as $maintenance): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($maintenance['date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($maintenance['description']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    KES <?php echo number_format($maintenance['cost'], 2); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                            <div class="space-y-3">
                                <a href="disposal-edit.php?id=<?php echo $vehicle['id']; ?>" 
                                   class="btn btn-primary w-full">
                                    <i class="bi bi-pencil mr-2"></i>
                                    Edit Disposal Request
                                </a>
                                <button type="button" 
                                        class="btn btn-secondary w-full"
                                        onclick="window.print()">
                                    <i class="bi bi-printer mr-2"></i>
                                    Print Details
                                </button>
                                <a href="disposal.php" 
                                   class="btn btn-outline w-full">
                                    <i class="bi bi-arrow-left mr-2"></i>
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Documents</h2>
                            <div class="space-y-4">
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
                                            <button type="button" class="text-blue-600 hover:text-blue-900" onclick="downloadDocument('<?php echo htmlspecialchars($document['name']); ?>')">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <button type="button" class="text-red-600 hover:text-red-900" onclick="deleteDocument('<?php echo htmlspecialchars($document['name']); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <button type="button" class="btn btn-outline w-full" onclick="openUploadModal()">
                                    <i class="bi bi-upload mr-2"></i>
                                    Upload Document
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" x-data="{ open: false }">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Upload Document</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeUploadModal()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <form id="uploadForm" class="space-y-4">
                        <div>
                            <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                            <select id="document_type" name="document_type" required class="form-select">
                                <option value="">Select document type</option>
                                <option value="assessment_report">Vehicle Assessment Report</option>
                                <option value="maintenance_history">Maintenance History</option>
                                <option value="vehicle_photos">Vehicle Photos</option>
                                <option value="other">Other Document</option>
                            </select>
                        </div>
                        <div>
                            <label for="document_file" class="block text-sm font-medium text-gray-700 mb-1">File</label>
                            <input type="file" id="document_file" name="document_file" required
                                   class="form-input"
                                   accept=".pdf,.doc,.docx,image/*">
                            <p class="mt-1 text-xs text-gray-500">
                                Supported formats: PDF, DOC, DOCX, Images
                            </p>
                        </div>
                        <div>
                            <label for="document_description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea id="document_description" name="document_description" rows="2"
                                      class="form-textarea"
                                      placeholder="Add a description for this document"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" class="btn btn-outline" onclick="closeUploadModal()">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Document upload modal functions
        function openUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
            document.getElementById('uploadForm').reset();
        }

        // Document handling functions
        function downloadDocument(documentName) {
            // In a real application, this would trigger a file download
            alert(`Downloading ${documentName}...`);
        }

        function deleteDocument(documentName) {
            if (confirm(`Are you sure you want to delete ${documentName}?`)) {
                // In a real application, this would send a delete request to the server
                alert(`Deleting ${documentName}...`);
            }
        }

        // Handle form submission
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('vehicle_id', '<?php echo $vehicle['id']; ?>');
            
            // In a real application, this would send the file to the server
            alert('Uploading document...');
            
            // Close the modal after successful upload
            closeUploadModal();
        });

        // Close modal when clicking outside
        document.getElementById('uploadModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUploadModal();
            }
        });
    </script>
</body>
</html> 
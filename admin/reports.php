<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';

// Mock data for filters
$reportTypes = [
    'fleet' => 'Fleet Reports',
    'maintenance' => 'Maintenance Reports',
    'incidents' => 'Incident Reports',
    'costs' => 'Cost Analysis',
    'drivers' => 'Driver Reports',
    'fuel' => 'Fuel Consumption',
    'trips' => 'Trip Reports'
];

$departments = [
    'all' => 'All Departments',
    'logistics' => 'Logistics',
    'operations' => 'Operations',
    'maintenance' => 'Maintenance',
    'fleet' => 'Fleet Management'
];

$vehicleStatuses = [
    'all' => 'All Statuses',
    'active' => 'Active',
    'maintenance' => 'In Maintenance',
    'inactive' => 'Inactive',
    'reserved' => 'Reserved'
];

$vehicleTypes = [
    'all' => 'All Types',
    'truck' => 'Trucks',
    'van' => 'Vans',
    'car' => 'Cars',
    'bus' => 'Buses'
];

$exportFormats = [
    'pdf' => 'PDF Document',
    'docx' => 'Microsoft Word Document'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Generator - SDATIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        /* Windows XP style form elements */
        .form-select, .form-input {
            background-color: #ffffff;
            border: 1px solid #7f9db9;
            border-radius: 2px;
            padding: 4px 8px;
            width: 100%;
            cursor: pointer;
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23000' d='M6 8.825L1.175 4 2.05 3.125 6 7.075 9.95 3.125 10.825 4z'/%3E%3C/svg%3E");
            background-position: right 8px center;
            background-repeat: no-repeat;
            padding-right: 24px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        .form-select:hover, .form-input:hover {
            border-color: #2b579a;
        }
        
        .form-select:focus, .form-input:focus {
            border-color: #2b579a;
            outline: 1px solid #2b579a;
            box-shadow: 0 0 0 1px #2b579a;
        }
        
        .form-select option {
            background-color: #ffffff;
            color: #000000;
        }

        .form-label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            color: #333;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: false,
        reportType: 'fleet',
        startDate: '',
        endDate: '',
        exportFormat: 'pdf',
        passwordProtect: false,
        includeRawData: false,
        generateReport() {
            this.isLoading = true;
            // Simulate report generation
            setTimeout(() => {
                this.isLoading = false;
                // Handle report generation here
            }, 1000);
        }
    }">
        <?php DashboardSidebar::render('reports'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <div class="p-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Report Generator</h1>
                
                <!-- Report Generation Interface -->
                <div class="dashboard-card">
                    <div class="grid grid-cols-12 gap-6">
                        <!-- Basic Information -->
                        <div class="col-span-4 space-y-4">
                            <div>
                                <label class="form-label">Report Title</label>
                                <input type="text" class="form-input" placeholder="Enter report title">
                            </div>

                            <div>
                                <label class="form-label">Report Type</label>
                                <select x-model="reportType" class="form-select">
                                    <?php foreach ($reportTypes as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Date Range</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <input type="date" x-model="startDate" class="form-input" placeholder="Start Date">
                                    </div>
                                    <div>
                                        <input type="date" x-model="endDate" class="form-input" placeholder="End Date">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Filters -->
                        <div class="col-span-8 space-y-4">
                            <h3 class="font-medium text-gray-900 mb-4">Data Filters</h3>
                            
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Department</label>
                                    <select class="form-select">
                                        <?php foreach ($departments as $value => $label): ?>
                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Vehicle Status</label>
                                    <select class="form-select">
                                        <?php foreach ($vehicleStatuses as $value => $label): ?>
                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Vehicle Type</label>
                                    <select class="form-select">
                                        <?php foreach ($vehicleTypes as $value => $label): ?>
                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Vehicle Age</label>
                                    <select class="form-select">
                                        <option value="all">All Ages</option>
                                        <option value="0-1">Less than 1 year</option>
                                        <option value="1-3">1-3 years</option>
                                        <option value="3-5">3-5 years</option>
                                        <option value="5+">More than 5 years</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="mt-6 pt-6 border-t">
                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-4">
                                <label class="form-label">Export Format</label>
                                <select x-model="exportFormat" class="form-select">
                                    <?php foreach ($exportFormats as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-span-8 flex items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" x-model="passwordProtect" class="form-checkbox">
                                    <label class="form-label mb-0">Password Protect</label>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" x-model="includeRawData" class="form-checkbox">
                                    <label class="form-label mb-0">Include Raw Data</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex justify-end gap-4">
                        <button class="btn btn-secondary flex items-center gap-2">
                            <i class="bi bi-printer"></i>
                            Print Preview
                        </button>
                        <button @click="generateReport" 
                                class="btn btn-primary flex items-center gap-2 px-6 py-3 text-lg"
                                :disabled="isLoading">
                            <template x-if="isLoading">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                            </template>
                            <template x-if="!isLoading">
                                <i class="bi bi-file-earmark-text"></i>
                            </template>
                            Generate Report
                        </button>
                    </div>
                </div>

                <!-- Report Preview Section (shown after generation) -->
                <div x-show="!isLoading" class="mt-6">
                    <!-- Report preview content will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
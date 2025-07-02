<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';

// Mock data for vehicles
$vehicles = [
    [
        'id' => 1,
        'registration_number' => 'GKB 673S',
        'make' => 'Toyota',
        'model' => 'Hilux',
        'year' => '2018',
        'purchase_price' => 7541000,
        'purchase_date' => '2018-01-15',
        'current_value' => 3770500,
        'depreciation_rate' => 20, // 20% per year
        'depreciation_method' => 'Straight Line'
    ],
    [
        'id' => 2,
        'registration_number' => 'GKB 674S',
        'make' => 'Toyota',
        'model' => 'Land Cruiser',
        'year' => '2019',
        'purchase_price' => 12500000,
        'purchase_date' => '2019-03-20',
        'current_value' => 7500000,
        'depreciation_rate' => 20,
        'depreciation_method' => 'Straight Line'
    ]
];

// Calculate statistics
$total_vehicles = count($vehicles);
$total_value = array_sum(array_column($vehicles, 'current_value'));
$total_depreciation = array_sum(array_column($vehicles, 'purchase_price')) - $total_value;
$average_depreciation_rate = array_sum(array_column($vehicles, 'depreciation_rate')) / $total_vehicles;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depreciation Calculator - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ sidebarOpen: true }">
        <?php DashboardSidebar::render('depreciation-calculator'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'Depreciation Calculator',
                'Calculate and track vehicle depreciation'
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
                            <i class="bi bi-currency-dollar text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Current Value</p>
                            <p class="text-lg font-semibold text-gray-900">KES <?php echo number_format($total_value, 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="bi bi-graph-down text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Depreciation</p>
                            <p class="text-lg font-semibold text-gray-900">KES <?php echo number_format($total_depreciation, 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="bi bi-percent text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg. Depreciation Rate</p>
                            <p class="text-lg font-semibold text-gray-900"><?php echo number_format($average_depreciation_rate, 1); ?>%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculator Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Calculator Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Calculate Depreciation</h2>
                            <form id="depreciationForm" class="space-y-4">
                                <!-- Vehicle Search -->
                                <div x-data="{ 
                                    query: '',
                                    results: [],
                                    showResults: false,
                                    isLoading: false,
                                    selectedVehicle: null,
                                    async search() {
                                        if (this.query.length < 2) {
                                            this.results = [];
                                            return;
                                        }
                                        this.isLoading = true;
                                        try {
                                            const response = await fetch(`../api/search-vehicles.php?q=${encodeURIComponent(this.query)}`);
                                            const data = await response.json();
                                            this.results = data;
                                            this.showResults = true;
                                        } catch (error) {
                                            console.error('Search failed:', error);
                                            this.results = [];
                                        } finally {
                                            this.isLoading = false;
                                        }
                                    },
                                    selectVehicle(vehicle) {
                                        this.selectedVehicle = vehicle;
                                        this.query = vehicle.registration_number + ' - ' + vehicle.make + ' ' + vehicle.model;
                                        this.showResults = false;
                                    }
                                }">
                                    <label for="vehicle_search" class="block text-sm font-medium text-gray-700 mb-1">Search Vehicle</label>
                                    <div class="relative">
                                        <input type="text" 
                                               id="vehicle_search" 
                                               x-model="query"
                                               @input.debounce.300ms="search()"
                                               @focus="showResults = true"
                                               placeholder="Enter registration number or vehicle details"
                                               class="form-input w-full"
                                               required>
                                        <div x-show="isLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <i class="bi bi-arrow-repeat animate-spin text-gray-400"></i>
                                        </div>
                                        
                                        <!-- Search Results -->
                                        <div class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                             x-show="showResults && query.length >= 2"
                                             @click.away="showResults = false"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100 transform scale-100"
                                             x-transition:leave-end="opacity-0 transform scale-95">
                                            <template x-if="results.length === 0">
                                                <div class="px-4 py-2 text-gray-500 text-sm">No vehicles found</div>
                                            </template>
                                            <template x-for="vehicle in results" :key="vehicle.id">
                                                <button type="button"
                                                        @click="selectVehicle(vehicle)"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-100">
                                                    <div class="font-medium" x-text="vehicle.registration_number"></div>
                                                    <div class="text-sm text-gray-500" x-text="vehicle.make + ' ' + vehicle.model"></div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Batch Calculation Toggle -->
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-gray-700">Batch Calculation</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="batch_calculation" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <!-- Batch Selection (hidden by default) -->
                                <div id="batch_selection" class="hidden space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">Selected Vehicles: <span id="selected_count">0</span></span>
                                        <button type="button" class="text-sm text-blue-600 hover:text-blue-800" onclick="selectAllVehicles()">
                                            Select All
                                        </button>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto border rounded-lg">
                                        <div id="vehicle_list" class="divide-y">
                                            <!-- Vehicle list will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="depreciation_method" class="block text-sm font-medium text-gray-700 mb-1">Depreciation Method</label>
                                    <select id="depreciation_method" name="depreciation_method" class="form-select" required>
                                        <option value="straight_line">Straight Line</option>
                                        <option value="declining_balance">Declining Balance</option>
                                        <option value="sum_of_years">Sum of Years' Digits</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="depreciation_rate" class="block text-sm font-medium text-gray-700 mb-1">Depreciation Rate (%)</label>
                                    <input type="number" id="depreciation_rate" name="depreciation_rate" 
                                           class="form-input" min="0" max="100" step="0.1" required>
                                </div>

                                <div>
                                    <label for="calculation_date" class="block text-sm font-medium text-gray-700 mb-1">Calculation Date</label>
                                    <input type="date" id="calculation_date" name="calculation_date" 
                                           class="form-input" required>
                                </div>

                                <div class="flex space-x-3">
                                    <button type="submit" class="btn btn-primary flex-1">
                                        Calculate Depreciation
                                    </button>
                                    <button type="button" onclick="exportToPDF()" class="btn btn-secondary">
                                        <i class="bi bi-file-pdf"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Depreciation Results</h2>
                                <button type="button" onclick="exportToPDF()" class="btn btn-secondary">
                                    <i class="bi bi-file-pdf mr-2"></i>
                                    Export to PDF
                                </button>
                            </div>
                            <div id="results" class="space-y-6">
                                <!-- Results will be populated by JavaScript -->
                                <div class="text-center text-gray-500 py-8">
                                    Select a vehicle and calculate depreciation to see results
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Batch calculation toggle
        document.getElementById('batch_calculation').addEventListener('change', function(e) {
            const batchSelection = document.getElementById('batch_selection');
            batchSelection.classList.toggle('hidden', !e.target.checked);
        });

        // Select all vehicles
        function selectAllVehicles() {
            const checkboxes = document.querySelectorAll('#vehicle_list input[type="checkbox"]');
            checkboxes.forEach(checkbox => checkbox.checked = true);
            updateSelectedCount();
        }

        // Update selected vehicles count
        function updateSelectedCount() {
            const selected = document.querySelectorAll('#vehicle_list input[type="checkbox"]:checked').length;
            document.getElementById('selected_count').textContent = selected;
        }

        // Export to PDF
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add title
            doc.setFontSize(16);
            doc.text('Depreciation Calculation Report', 14, 20);
            
            // Add date
            doc.setFontSize(10);
            doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 14, 30);
            
            // Add results content
            const resultsDiv = document.getElementById('results');
            const content = resultsDiv.innerText;
            
            // Split content into lines and add to PDF
            const lines = content.split('\n');
            let y = 40;
            lines.forEach(line => {
                if (line.trim()) {
                    doc.text(line, 14, y);
                    y += 7;
                }
            });
            
            // Save the PDF
            doc.save('depreciation-report.pdf');
        }

        // Handle form submission
        document.getElementById('depreciationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isBatch = document.getElementById('batch_calculation').checked;
            
            if (isBatch) {
                // Handle batch calculation
                const selectedVehicles = Array.from(document.querySelectorAll('#vehicle_list input[type="checkbox"]:checked'))
                    .map(checkbox => checkbox.value);
                
                if (selectedVehicles.length === 0) {
                    alert('Please select at least one vehicle for batch calculation');
                    return;
                }
                
                // Calculate for each selected vehicle
                const results = selectedVehicles.map(vehicleId => {
                    const vehicle = <?php echo json_encode($vehicles); ?>.find(v => v.id === parseInt(vehicleId));
                    return calculateDepreciation(vehicle);
                });
                
                displayBatchResults(results);
            } else {
                // Handle single vehicle calculation
                const vehicleId = document.querySelector('[x-data]').__x.$data.selectedVehicle?.id;
                if (!vehicleId) {
                    alert('Please select a vehicle');
                    return;
                }
                
                const vehicle = <?php echo json_encode($vehicles); ?>.find(v => v.id === parseInt(vehicleId));
                const result = calculateDepreciation(vehicle);
                displaySingleResult(result);
            }
        });

        function calculateDepreciation(vehicle) {
            const method = document.getElementById('depreciation_method').value;
            const rate = parseFloat(document.getElementById('depreciation_rate').value);
            const date = document.getElementById('calculation_date').value;
            
            const purchaseDate = new Date(vehicle.purchase_date);
            const calculationDate = new Date(date);
            const yearsElapsed = (calculationDate - purchaseDate) / (1000 * 60 * 60 * 24 * 365);
            
            let currentValue, totalDepreciation;
            
            switch (method) {
                case 'straight_line':
                    const annualDepreciation = vehicle.purchase_price * (rate / 100);
                    totalDepreciation = annualDepreciation * yearsElapsed;
                    currentValue = vehicle.purchase_price - totalDepreciation;
                    break;
                    
                case 'declining_balance':
                    currentValue = vehicle.purchase_price * Math.pow(1 - (rate / 100), yearsElapsed);
                    totalDepreciation = vehicle.purchase_price - currentValue;
                    break;
                    
                case 'sum_of_years':
                    const usefulLife = 5;
                    const sumOfYears = (usefulLife * (usefulLife + 1)) / 2;
                    const remainingYears = Math.max(0, usefulLife - yearsElapsed);
                    const depreciationFactor = (remainingYears * (remainingYears + 1)) / (2 * sumOfYears);
                    currentValue = vehicle.purchase_price * depreciationFactor;
                    totalDepreciation = vehicle.purchase_price - currentValue;
                    break;
            }
            
            return {
                vehicle,
                currentValue,
                totalDepreciation,
                yearsElapsed,
                rate,
                method
            };
        }

        function displaySingleResult(result) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Vehicle Details</h3>
                        <dl class="mt-2 space-y-2">
                            <div>
                                <dt class="text-sm text-gray-500">Registration Number</dt>
                                <dd class="text-sm font-medium text-gray-900">${result.vehicle.registration_number}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Make & Model</dt>
                                <dd class="text-sm font-medium text-gray-900">${result.vehicle.make} ${result.vehicle.model}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Purchase Date</dt>
                                <dd class="text-sm font-medium text-gray-900">${new Date(result.vehicle.purchase_date).toLocaleDateString()}</dd>
                            </div>
                        </dl>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Depreciation Details</h3>
                        <dl class="mt-2 space-y-2">
                            <div>
                                <dt class="text-sm text-gray-500">Original Value</dt>
                                <dd class="text-sm font-medium text-gray-900">KES ${result.vehicle.purchase_price.toLocaleString()}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Current Value</dt>
                                <dd class="text-sm font-medium text-gray-900">KES ${Math.round(result.currentValue).toLocaleString()}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Total Depreciation</dt>
                                <dd class="text-sm font-medium text-gray-900">KES ${Math.round(result.totalDepreciation).toLocaleString()}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Depreciation Timeline</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Years Elapsed</span>
                                <span class="text-sm font-medium text-gray-900">${result.yearsElapsed.toFixed(1)} years</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Annual Depreciation Rate</span>
                                <span class="text-sm font-medium text-gray-900">${result.rate}%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Depreciation Method</span>
                                <span class="text-sm font-medium text-gray-900">${result.method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function displayBatchResults(results) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Depreciation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Years Elapsed</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${results.map(result => `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${result.vehicle.registration_number}</div>
                                        <div class="text-sm text-gray-500">${result.vehicle.make} ${result.vehicle.model}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        KES ${result.vehicle.purchase_price.toLocaleString()}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        KES ${Math.round(result.currentValue).toLocaleString()}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        KES ${Math.round(result.totalDepreciation).toLocaleString()}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${result.yearsElapsed.toFixed(1)} years
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Summary</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Total Original Value</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                KES ${results.reduce((sum, r) => sum + r.vehicle.purchase_price, 0).toLocaleString()}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Total Current Value</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                KES ${results.reduce((sum, r) => sum + r.currentValue, 0).toLocaleString()}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Total Depreciation</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                KES ${results.reduce((sum, r) => sum + r.totalDepreciation, 0).toLocaleString()}
                            </dd>
                        </div>
                    </dl>
                </div>
            `;
        }
    </script>
</body>
</html> 
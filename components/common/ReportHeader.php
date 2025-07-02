<?php
class ReportHeader {
    public static function render($title, $description = '') {
        ?>
        <div class="p-6">
            <div class="dashboard-card">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-8">
                        <h1 class="text-2xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($title); ?></h1>
                        <?php if ($description): ?>
                            <p class="text-gray-600"><?php echo htmlspecialchars($description); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-span-4 flex items-center justify-end">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <select class="form-select" id="dateRange" onchange="handleDateRangeChange(this.value)">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="week">Last 7 Days</option>
                                    <option value="month">Last 30 Days</option>
                                    <option value="quarter">Last 90 Days</option>
                                    <option value="year">Last 365 Days</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <button onclick="exportToExcel()" class="btn btn-secondary flex items-center gap-2">
                                <i class="bi bi-file-earmark-excel"></i>
                                Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add SheetJS library -->
        <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

        <script>
        // Handle date range changes
        function handleDateRangeChange(value) {
            if (value === 'custom') {
                showCustomDateRangePicker();
            } else {
                updateContent(value);
            }
        }

        // Show custom date range picker
        function showCustomDateRangePicker() {
            console.log('Custom date range picker to be implemented');
        }

        // Update content based on date range
        function updateContent(dateRange) {
            console.log('Updating content for date range:', dateRange);
        }

        // Export to Excel functionality
        function exportToExcel() {
            try {
                // Get the table element (assuming your data is in a table)
                const table = document.querySelector('table');
                if (!table) {
                    throw new Error('No table found to export');
                }

                // Get the current date for filename
                const date = new Date();
                const fileName = `Report_${date.toISOString().split('T')[0]}.xlsx`;

                // Convert table to worksheet
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(table);

                // Add the worksheet to the workbook
                XLSX.utils.book_append_sheet(wb, ws, "Report");

                // Generate Excel file and trigger download
                XLSX.writeFile(wb, fileName);

                // Show success message
                showNotification('Export successful!', 'success');
            } catch (error) {
                console.error('Export failed:', error);
                showNotification('Export failed: ' + error.message, 'error');
            }
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white z-50`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        </script>
        <?php
    }
} 
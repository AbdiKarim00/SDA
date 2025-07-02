<?php
class ChartCard {
    public static function render($title, $chartId, $chartType, $chartData, $chartOptions = []) {
        ?>
        <div class="dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($title); ?></h3>
                <div class="flex items-center gap-2">
                    <button class="btn btn-secondary btn-sm flex items-center gap-1">
                        <i class="bi bi-printer"></i>
                        Print
                    </button>
                    <button class="btn btn-secondary btn-sm flex items-center gap-1">
                        <i class="bi bi-download"></i>
                        Export
                    </button>
                </div>
            </div>
            <div id="<?php echo htmlspecialchars($chartId); ?>" class="w-full" style="height: 300px;"></div>
            <script>
                window.addEventListener('load', () => {
                    const options = {
                        chart: {
                            type: '<?php echo htmlspecialchars($chartType); ?>',
                            height: 300,
                            toolbar: {
                                show: false
                            }
                        },
                        series: <?php echo json_encode($chartData['series']); ?>,
                        <?php 
                        foreach ($chartOptions as $key => $value) {
                            if (is_string($value) && strpos($value, 'function') === 0) {
                                // For function formatters, we need to evaluate them
                                echo $key . ': ' . $value . ',';
                            } else if (is_array($value)) {
                                // For arrays, we need to handle nested functions
                                $processedValue = array_map(function($item) {
                                    if (is_string($item) && strpos($item, 'function') === 0) {
                                        return $item;
                                    }
                                    return $item;
                                }, $value);
                                echo $key . ': ' . json_encode($processedValue, JSON_UNESCAPED_UNICODE) . ',';
                            } else {
                                echo $key . ': ' . json_encode($value, JSON_UNESCAPED_UNICODE) . ',';
                            }
                        }
                        ?>
                        grid: {
                            borderColor: '#e5e7eb',
                            strokeDashArray: 4,
                            xaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        },
                        tooltip: {
                            theme: 'light'
                        }
                    };

                    // Ensure all function formatters are properly evaluated
                    const processOptions = (obj) => {
                        for (let key in obj) {
                            if (typeof obj[key] === 'string' && obj[key].startsWith('function')) {
                                try {
                                    obj[key] = eval('(' + obj[key] + ')');
                                } catch (e) {
                                    console.warn('Failed to evaluate function for key:', key, e);
                                }
                            } else if (typeof obj[key] === 'object' && obj[key] !== null) {
                                processOptions(obj[key]);
                            }
                        }
                    };

                    processOptions(options);

                    const chartElement = document.querySelector("#<?php echo htmlspecialchars($chartId); ?>");
                    if (chartElement) {
                        const chart = new ApexCharts(chartElement, options);
                        chart.render();
                    }
                });
            </script>
        </div>
        <?php
    }
} 
<?php
class KpiCard {
    public static function render($title, $value, $icon = null) {
        ?>
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($title); ?></h3>
                    <p class="text-2xl font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($value); ?></p>
                </div>
                <?php if ($icon): ?>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-<?php echo htmlspecialchars($icon); ?> text-xl text-gray-600"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
} 
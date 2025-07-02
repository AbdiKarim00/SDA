<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    exit;
}

$db = Database::getInstance();
$notifications = $db->query("
    SELECT * FROM notifications 
    WHERE user_id = :user_id
    ORDER BY created_at DESC
    LIMIT 5
", ['user_id' => $auth->getUserId()])->fetchAll();
?>

<div class="dropdown">
    <button class="btn btn-link position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        <?php if (count($notifications) > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo count($notifications); ?>
            </span>
        <?php endif; ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
        <?php if (empty($notifications)): ?>
            <li><span class="dropdown-item-text">No new notifications</span></li>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <li>
                    <a class="dropdown-item" href="<?php echo htmlspecialchars($notification['link'] ?? '#'); ?>">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small text-gray-500"><?php echo htmlspecialchars($notification['created_at']); ?></div>
                                <div><?php echo htmlspecialchars($notification['message']); ?></div>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<style>
.notification-item {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f7ff;
}

.notification-item .small {
    font-size: 0.875rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark notification as read when clicked
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', async function() {
            const notificationId = this.dataset.id;
            try {
                const response = await fetch('/api/notifications/mark_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notification_id: notificationId })
                });
                
                if (response.ok) {
                    this.classList.remove('unread');
                    const badge = this.querySelector('.badge');
                    if (badge) badge.remove();
                    
                    // Update unread count
                    const unreadBadge = document.querySelector('#notificationsDropdown .badge');
                    if (unreadBadge) {
                        const count = parseInt(unreadBadge.textContent) - 1;
                        if (count > 0) {
                            unreadBadge.textContent = count;
                        } else {
                            unreadBadge.remove();
                        }
                    }
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        });
    });
});
</script> 
<?php
class Notifications {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new notification
     * 
     * @param int $userId The user ID to send the notification to
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string $type The notification type (info, warning, success, error)
     * @return bool Whether the notification was created successfully
     */
    public function create($userId, $title, $message, $type = 'info') {
        try {
            $this->db->query("
                INSERT INTO notifications (user_id, title, message, type, created_at)
                VALUES (:user_id, :title, :message, :type, NOW())
            ", [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create notifications for multiple users
     * 
     * @param array $userIds Array of user IDs to send notifications to
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string $type The notification type
     * @return int Number of notifications created
     */
    public function createForUsers($userIds, $title, $message, $type = 'info') {
        $count = 0;
        foreach ($userIds as $userId) {
            if ($this->create($userId, $title, $message, $type)) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Create a notification for all users with a specific role
     * 
     * @param string $role The role to send notifications to
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string $type The notification type
     * @return int Number of notifications created
     */
    public function createForRole($role, $title, $message, $type = 'info') {
        try {
            $admins = $this->db->query("SELECT user_id FROM users WHERE role = :role", ['role' => 'admin'])->fetchAll();
            
            $userIds = array_column($admins, 'user_id');
            return $this->createForUsers($userIds, $title, $message, $type);
        } catch (Exception $e) {
            error_log("Failed to create notifications for role: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get unread notifications count for a user
     * 
     * @param int $userId The user ID
     * @return int Number of unread notifications
     */
    public function getUnreadCount($userId) {
        try {
            $result = $this->db->query("
                SELECT COUNT(*) as count
                FROM notifications
                WHERE user_id = :user_id AND is_read = false
            ", ['user_id' => $userId])->fetch();
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Failed to get unread notifications count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mark a notification as read
     * 
     * @param int $notificationId The notification ID
     * @param int $userId The user ID
     * @return bool Whether the notification was marked as read successfully
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $this->db->query("
                UPDATE notifications
                SET is_read = true
                WHERE id = :id AND user_id = :user_id
            ", [
                'id' => $notificationId,
                'user_id' => $userId
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to mark notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId The user ID
     * @return bool Whether the notifications were marked as read successfully
     */
    public function markAllAsRead($userId) {
        try {
            $this->db->query("
                UPDATE notifications
                SET is_read = true
                WHERE user_id = :user_id
            ", ['user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to mark all notifications as read: " . $e->getMessage());
            return false;
        }
    }
} 
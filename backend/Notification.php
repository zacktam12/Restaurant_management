<?php
/**
 * Notification Class
 * Manages user notifications
 */

require_once __DIR__ . '/config.php';

class Notification {
    private $conn;
    private $table = 'notifications';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Create notification
     */
    public function create($userId, $type, $title, $message, $relatedEntityType = null, $relatedEntityId = null) {
        $sql = "INSERT INTO {$this->table} (user_id, type, title, message, related_entity_type, related_entity_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssssi", $userId, $type, $title, $message, $relatedEntityType, $relatedEntityId);
        
        return $stmt->execute();
    }
    
    /**
     * Get unread notifications
     */
    public function getUnread($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $notificationId);
        return $stmt->execute();
    }
    
    /**
     * Get notification count
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }
}
?>

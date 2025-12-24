<?php
/**
 * ChangeHistory Class
 * Tracks modifications to restaurants, menus, and reservations
 */

require_once __DIR__ . '/config.php';

class ChangeHistory {
    private $conn;
    private $table = 'change_history';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Log a change
     */
    public function logChange($restaurantId, $userId, $changeType, $description, $beforeSnapshot, $afterSnapshot) {
        $beforeJson = json_encode($beforeSnapshot);
        $afterJson = json_encode($afterSnapshot);
        
        $sql = "INSERT INTO {$this->table} (restaurant_id, changed_by, change_type, change_description, before_snapshot, after_snapshot) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissss", $restaurantId, $userId, $changeType, $description, $beforeJson, $afterJson);
        
        return $stmt->execute();
    }
    
    /**
     * Get change history for restaurant
     */
    public function getHistory($restaurantId, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $restaurantId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<?php
/**
 * AuditLog Class
 * Tracks all changes made by users
 */

require_once __DIR__ . '/config.php';

class AuditLog {
    private $conn;
    private $table = 'audit_logs';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Log an action
     */
    public function logAction($userId, $action, $entityType, $entityId = null, $oldValues = null, $newValues = null) {
        $ipAddress = Security::getClientIP();
        $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
        $newValuesJson = $newValues ? json_encode($newValues) : null;
        
        $sql = "INSERT INTO {$this->table} (user_id, action, entity_type, entity_id, old_values, new_values, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ississs", $userId, $action, $entityType, $entityId, $oldValuesJson, $newValuesJson, $ipAddress);
        
        return $stmt->execute();
    }
    
    /**
     * Get audit logs by user
     */
    public function getLogsByUser($userId, $limit = 100) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get audit logs by entity
     */
    public function getLogsByEntity($entityType, $entityId) {
        $sql = "SELECT * FROM {$this->table} WHERE entity_type = ? AND entity_id = ? ORDER BY timestamp DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $entityType, $entityId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

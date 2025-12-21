<?php
/**
 * API Key Management Class
 * Handles API key generation, validation, and management for service-to-service communication
 */

require_once 'Database.php';

class ApiKey {
    private $db;
    private $table = 'api_keys';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Generate a new API key for a service consumer
     */
    public function generateApiKey($serviceName, $consumerGroup, $permissions = 'read') {
        // Generate a secure API key
        $apiKey = bin2hex(random_bytes(32));
        
        $query = "INSERT INTO {$this->table} (api_key, service_name, consumer_group, permissions, created_at) VALUES (?, ?, ?, ?, NOW())";
        $params = [$apiKey, $serviceName, $consumerGroup, $permissions];
        $paramTypes = "ssss";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'api_key' => $apiKey,
                    'message' => 'API key generated successfully'
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to generate API key'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Validate an API key
     */
    public function validateApiKey($apiKey) {
        $query = "SELECT * FROM {$this->table} WHERE api_key = ? AND is_active = 1";
        $params = [$apiKey];
        $paramTypes = "s";

        try {
            $result = $this->db->select($query, $params, $paramTypes);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all API keys
     */
    public function getAllApiKeys() {
        $query = "SELECT id, api_key, service_name, consumer_group, permissions, is_active, created_at, last_used FROM {$this->table} ORDER BY created_at DESC";

        try {
            return $this->db->select($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get API key by ID
     */
    public function getApiKeyById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->select($query, $params, $paramTypes);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update API key status
     */
    public function updateApiKeyStatus($id, $isActive) {
        $query = "UPDATE {$this->table} SET is_active = ?, updated_at = NOW() WHERE id = ?";
        $params = [$isActive ? 1 : 0, $id];
        $paramTypes = "ii";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return ['success' => true, 'message' => 'API key status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update API key status'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete API key
     */
    public function deleteApiKey($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return ['success' => true, 'message' => 'API key deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete API key'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Log API key usage
     */
    public function logApiKeyUsage($apiKey) {
        $query = "UPDATE {$this->table} SET last_used = NOW(), usage_count = usage_count + 1 WHERE api_key = ?";
        $params = [$apiKey];
        $paramTypes = "s";

        try {
            $this->db->execute($query, $params, $paramTypes);
        } catch (Exception $e) {
            // Silently fail - logging shouldn't break the main functionality
        }
    }

    /**
     * Close database connection
     */
    public function close() {
        $this->db->close();
    }
}
?>
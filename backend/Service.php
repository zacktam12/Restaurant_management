<?php
/**
 * Service Class
 * Handles external service operations (tours, hotels, taxis)
 */

require_once 'Database.php';

class Service {
    private $db;
    private $table = 'external_services';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all external services
     */
    public function getAllServices() {
        $query = "SELECT * FROM {$this->table} ORDER BY type, name";
        
        try {
            return $this->db->select($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get service by ID
     */
    public function getServiceById($id) {
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
     * Create a new external service
     */
    public function createService($type, $name, $description, $price, $image = null, $rating = 0.0, $available = 1) {
        $query = "INSERT INTO {$this->table} (type, name, description, price, image, rating, available) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [$type, $name, $description, $price, $image, $rating, $available];
        $paramTypes = "sssdssi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Service created successfully',
                    'service_id' => $result['insert_id']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create service'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update service
     */
    public function updateService($id, $type, $name, $description, $price, $image = null, $rating = 0.0, $available = 1) {
        $query = "UPDATE {$this->table} SET type = ?, name = ?, description = ?, price = ?, image = ?, rating = ?, available = ?, updated_at = NOW() WHERE id = ?";
        $params = [$type, $name, $description, $price, $image, $rating, $available, $id];
        $paramTypes = "sssdssii";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Service updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update service'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete service
     */
    public function deleteService($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Service deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete service'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get services by type
     */
    public function getServicesByType($type) {
        $query = "SELECT * FROM {$this->table} WHERE type = ? ORDER BY name";
        $params = [$type];
        $paramTypes = "s";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Toggle service availability
     */
    public function toggleAvailability($id) {
        // First get current availability
        $service = $this->getServiceById($id);
        if (!$service) {
            return ['success' => false, 'message' => 'Service not found'];
        }

        $newAvailability = $service['available'] ? 0 : 1;
        $query = "UPDATE {$this->table} SET available = ?, updated_at = NOW() WHERE id = ?";
        $params = [$newAvailability, $id];
        $paramTypes = "ii";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Service availability updated successfully',
                    'available' => $newAvailability
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update service availability'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
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
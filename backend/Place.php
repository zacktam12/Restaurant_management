<?php
/**
 * Place Class
 * Handles tourist places operations
 */

require_once 'Database.php';

class Place {
    private $db;
    private $table = 'places';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all places
     */
    public function getAllPlaces() {
        $query = "SELECT * FROM {$this->table} ORDER BY rating DESC, name ASC";

        try {
            return $this->db->select($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get place by ID
     */
    public function getPlaceById($id) {
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
     * Create a new place
     */
    public function createPlace($name, $description, $country, $city, $image = null, $rating = 0.0, $category = 'historical') {
        $query = "INSERT INTO {$this->table} (name, description, country, city, image, rating, category) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [$name, $description, $country, $city, $image, $rating, $category];
        $paramTypes = "sssssds";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Place created successfully',
                    'place_id' => $result['insert_id']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create place'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update place
     */
    public function updatePlace($id, $name, $description, $country, $city, $image = null, $rating = 0.0, $category = 'historical') {
        $query = "UPDATE {$this->table} SET name = ?, description = ?, country = ?, city = ?, image = ?, rating = ?, category = ?, updated_at = NOW() WHERE id = ?";
        $params = [$name, $description, $country, $city, $image, $rating, $category, $id];
        $paramTypes = "sssssdsi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Place updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update place'
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
     * Delete place
     */
    public function deletePlace($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Place deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete place'
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
     * Search places
     */
    public function searchPlaces($searchTerm) {
        $query = "SELECT * FROM {$this->table} WHERE name LIKE ? OR city LIKE ? OR country LIKE ? ORDER BY rating DESC, name ASC";
        $params = ["%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"];
        $paramTypes = "sss";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Filter places by category
     */
    public function filterByCategory($category) {
        $query = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY rating DESC, name ASC";
        $params = [$category];
        $paramTypes = "s";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
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
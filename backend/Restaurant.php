<?php
/**
 * Restaurant Class
 * Handles restaurant management operations
 */

require_once 'Database.php';

class Restaurant {
    private $db;
    private $table = 'restaurants';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all restaurants
     */
    public function getAllRestaurants() {
        $query = "SELECT * FROM {$this->table} ORDER BY rating DESC, name ASC";

        try {
            return $this->db->select($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get restaurant by ID
     */
    public function getRestaurantById($id) {
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
     * Create a new restaurant
     */
    public function createRestaurant($name, $description, $cuisine, $address, $phone, $priceRange, $image = null, $seatingCapacity = 0) {
        $query = "INSERT INTO {$this->table} (name, description, cuisine, address, phone, price_range, image, seating_capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$name, $description, $cuisine, $address, $phone, $priceRange, $image, $seatingCapacity];
        $paramTypes = "sssssssi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Restaurant created successfully',
                    'restaurant_id' => $result['insert_id']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create restaurant'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update restaurant
     */
    public function updateRestaurant($id, $name, $description, $cuisine, $address, $phone, $priceRange, $image = null, $seatingCapacity = 0) {
        $query = "UPDATE {$this->table} SET name = ?, description = ?, cuisine = ?, address = ?, phone = ?, price_range = ?, image = ?, seating_capacity = ?, updated_at = NOW() WHERE id = ?";
        $params = [$name, $description, $cuisine, $address, $phone, $priceRange, $image, $seatingCapacity, $id];
        $paramTypes = "sssssssii";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Restaurant updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update restaurant'
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
     * Delete restaurant
     */
    public function deleteRestaurant($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Restaurant deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete restaurant'
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
     * Search restaurants
     */
    public function searchRestaurants($searchTerm) {
        $query = "SELECT * FROM {$this->table} WHERE name LIKE ? OR description LIKE ? OR cuisine LIKE ? ORDER BY rating DESC, name ASC";
        $params = ["%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"];
        $paramTypes = "sss";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Filter restaurants by cuisine
     */
    public function filterByCuisine($cuisine) {
        $query = "SELECT * FROM {$this->table} WHERE cuisine = ? ORDER BY rating DESC, name ASC";
        $params = [$cuisine];
        $paramTypes = "s";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get top restaurants by reservation count
     */
    public function getTopRestaurantsByReservations($limit = 5) {
        $query = "SELECT r.name, COUNT(res.id) as reservation_count 
                  FROM {$this->table} r 
                  LEFT JOIN reservations res ON r.id = res.restaurant_id 
                  GROUP BY r.id, r.name 
                  ORDER BY reservation_count DESC 
                  LIMIT ?";
        $params = [$limit];
        $paramTypes = "i";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get average ratings by cuisine
     */
    public function getRestaurantRatingsByCuisine() {
        $query = "SELECT cuisine, AVG(rating) as avg_rating, COUNT(*) as restaurant_count 
                  FROM {$this->table} 
                  GROUP BY cuisine 
                  ORDER BY avg_rating DESC";

        try {
            return $this->db->select($query);
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
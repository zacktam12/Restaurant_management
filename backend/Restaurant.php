<?php
/**
 * Restaurant Class
 * Handles restaurant management operations
 */

require_once __DIR__ . '/config.php';

class Restaurant {
    private $conn;
    private $table = 'restaurants';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Create new restaurant
     */
    public function createRestaurant($name, $description, $cuisine, $address, $phone, $price_range, $seating_capacity, $manager_id, $image = null) {
        $sql = "INSERT INTO {$this->table} (manager_id, name, description, cuisine, address, phone, price_range, seating_capacity, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssssss", $manager_id, $name, $description, $cuisine, $address, $phone, $price_range, $seating_capacity, $image);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Restaurant created successfully',
                'restaurant_id' => $this->conn->insert_id
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to create restaurant: ' . $stmt->error];
    }
    
    /**
     * Get all restaurants (excluding deleted)
     */
    public function getAllRestaurants() {
        $sql = "SELECT r.*, u.name as manager_name FROM {$this->table} r 
                LEFT JOIN users u ON r.manager_id = u.id 
                WHERE r.is_deleted = 0
                ORDER BY r.rating DESC, r.name ASC";
        $result = $this->conn->query($sql);
        
        $restaurants = [];
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row;
        }
        
        return $restaurants;
    }
    
    /**
     * Get restaurant by ID
     */
    public function getRestaurantById($id) {
        $sql = "SELECT r.*, u.name as manager_name FROM {$this->table} r 
                LEFT JOIN users u ON r.manager_id = u.id 
                WHERE r.id = ? AND r.is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get restaurants by manager ID
     */
    public function getRestaurantsByManager($manager_id) {
        $sql = "SELECT * FROM {$this->table} WHERE manager_id = ? ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $manager_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $restaurants = [];
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row;
        }
        
        return $restaurants;
    }
    
    /**
     * Search restaurants
     */
    public function searchRestaurants($query) {
        $searchTerm = "%{$query}%";
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR description LIKE ? OR cuisine LIKE ? OR address LIKE ?
                ORDER BY rating DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $restaurants = [];
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row;
        }
        
        return $restaurants;
    }
    
    /**
     * Filter restaurants by cuisine
     */
    public function filterByCuisine($cuisine) {
        $sql = "SELECT * FROM {$this->table} WHERE cuisine = ? ORDER BY rating DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cuisine);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $restaurants = [];
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row;
        }
        
        return $restaurants;
    }
    
    /**
     * Update restaurant with audit trail
     */
    public function updateRestaurant($id, $name, $description, $cuisine, $address, $phone, $price_range, $seating_capacity, $image = null, $manager_id = null, $userId = null) {
        $oldRestaurant = $this->getRestaurantById($id);
        
        // Build SQL query based on provided parameters
        if ($image && $manager_id) {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, cuisine = ?, address = ?, phone = ?, price_range = ?, seating_capacity = ?, image = ?, manager_id = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssssii", $name, $description, $cuisine, $address, $phone, $price_range, $seating_capacity, $image, $manager_id, $id);
        } elseif ($manager_id) {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, cuisine = ?, address = ?, phone = ?, price_range = ?, seating_capacity = ?, manager_id = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssii", $name, $description, $cuisine, $address, $phone, $price_range, $seating_capacity, $manager_id, $id);
        } elseif ($image) {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, cuisine = ?, address = ?, phone = ?, price_range = ?, seating_capacity = ?, image = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $name, $description, $cuisine, $address, $phone, $price_range, $seating_capacity, $image, $id);
        } else {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, cuisine = ?, address = ?, phone = ?, price_range = ?, seating_capacity = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssi", $name, $description, $cuisine, $address, $phone, $price_range, $seating_capacity, $id);
        }
        
        if ($stmt->execute()) {
            // Log change for audit
            if ($userId) {
                $changeHistory = new ChangeHistory();
                $changeHistory->logChange($id, $userId, 'restaurant', 'Restaurant updated', $oldRestaurant, compact('name', 'description', 'cuisine', 'address', 'phone', 'price_range', 'seating_capacity', 'manager_id'));
            }
            
            return ['success' => true, 'message' => 'Restaurant updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update restaurant'];
    }
    
    /**
     * Update restaurant rating
     */
    public function updateRating($id, $rating) {
        $sql = "UPDATE {$this->table} SET rating = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $rating, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Rating updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update rating'];
    }
    
    /**
     * Delete restaurant with soft delete
     */
    public function deleteRestaurant($id, $userId = null) {
        $sql = "UPDATE {$this->table} SET is_deleted = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($userId) {
                $auditLog = new AuditLog();
                $auditLog->logAction($userId, 'DELETE', 'restaurant', $id);
            }
            return ['success' => true, 'message' => 'Restaurant deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete restaurant'];
    }
    
    /**
     * Get unique cuisines
     */
    public function getCuisines() {
        $sql = "SELECT DISTINCT cuisine FROM {$this->table} ORDER BY cuisine ASC";
        $result = $this->conn->query($sql);
        
        $cuisines = [];
        while ($row = $result->fetch_assoc()) {
            $cuisines[] = $row['cuisine'];
        }
        
        return $cuisines;
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>

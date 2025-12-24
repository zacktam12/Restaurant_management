<?php
/**
 * Menu Class
 * Handles menu item management
 */

require_once __DIR__ . '/config.php';

class Menu {
    private $conn;
    private $table = 'menu_items';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Add menu item
     */
    public function addMenuItem($restaurant_id, $name, $description, $price, $category, $image = null) {
        $sql = "INSERT INTO {$this->table} (restaurant_id, name, description, price, category, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issdss", $restaurant_id, $name, $description, $price, $category, $image);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Menu item added successfully',
                'item_id' => $this->conn->insert_id
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to add menu item'];
    }
    
    /**
     * Get menu items by restaurant
     */
    public function getMenuByRestaurant($restaurant_id) {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY category, name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
    
    /**
     * Get menu items by category
     */
    public function getMenuByCategory($restaurant_id, $category) {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND category = ? ORDER BY name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $restaurant_id, $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
    
    /**
     * Get menu item by ID
     */
    public function getMenuItemById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
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
     * Update menu item
     */
    public function updateMenuItem($id, $name, $description, $price, $category, $available, $image = null) {
        if ($image) {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, price = ?, category = ?, available = ?, image = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssdsssi", $name, $description, $price, $category, $available, $image, $id);
        } else {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, price = ?, category = ?, available = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssdssi", $name, $description, $price, $category, $available, $id);
        }
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Menu item updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update menu item'];
    }
    
    /**
     * Toggle item availability
     */
    public function toggleAvailability($id) {
        $sql = "UPDATE {$this->table} SET available = NOT available WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Availability toggled successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to toggle availability'];
    }
    
    /**
     * Delete menu item
     */
    public function deleteMenuItem($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Menu item deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete menu item'];
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

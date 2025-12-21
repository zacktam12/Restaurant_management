<?php
/**
 * Menu Class
 * Handles menu item operations
 */

require_once 'Database.php';

class Menu {
    private $db;
    private $table = 'menu_items';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all menu items for a restaurant
     */
    public function getMenuItemsByRestaurant($restaurantId) {
        $query = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY category, name";
        $params = [$restaurantId];
        $paramTypes = "i";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get menu items by category for a restaurant
     */
    public function getMenuItemsByCategory($restaurantId, $category) {
        $query = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND category = ? ORDER BY name";
        $params = [$restaurantId, $category];
        $paramTypes = "is";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get menu item by ID
     */
    public function getMenuItemById($id) {
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
     * Create a new menu item
     */
    public function createMenuItem($restaurantId, $name, $description, $price, $category, $image = null, $available = 1) {
        $query = "INSERT INTO {$this->table} (restaurant_id, name, description, price, category, image, available) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [$restaurantId, $name, $description, $price, $category, $image, $available];
        $paramTypes = "issdssi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Menu item created successfully',
                    'menu_item_id' => $result['insert_id']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create menu item'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update menu item
     */
    public function updateMenuItem($id, $name, $description, $price, $category, $image = null, $available = 1) {
        $query = "UPDATE {$this->table} SET name = ?, description = ?, price = ?, category = ?, image = ?, available = ?, updated_at = NOW() WHERE id = ?";
        $params = [$name, $description, $price, $category, $image, $available, $id];
        $paramTypes = "ssdsisi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Menu item updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update menu item'
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
     * Delete menu item
     */
    public function deleteMenuItem($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Menu item deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete menu item'
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
     * Toggle menu item availability
     */
    public function toggleAvailability($id) {
        // First get current availability
        $menuItem = $this->getMenuItemById($id);
        if (!$menuItem) {
            return ['success' => false, 'message' => 'Menu item not found'];
        }

        $newAvailability = $menuItem['available'] ? 0 : 1;
        $query = "UPDATE {$this->table} SET available = ?, updated_at = NOW() WHERE id = ?";
        $params = [$newAvailability, $id];
        $paramTypes = "ii";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Menu item availability updated successfully',
                    'available' => $newAvailability
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update menu item availability'
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
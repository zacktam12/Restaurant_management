<?php
/**
 * Menu API
 * Handles menu item operations
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Menu.php';

$menuManager = new Menu();

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    
    // Parse path info to get ID if present
    $pathParts = explode('/', trim($pathInfo, '/'));
    $id = isset($pathParts[0]) && is_numeric($pathParts[0]) ? (int)$pathParts[0] : null;
    
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific menu item
                $menuItem = $menuManager->getMenuItemById($id);
                if ($menuItem) {
                    echo json_encode([
                        'success' => true,
                        'data' => $menuItem
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Menu item not found'
                    ]);
                }
            } else {
                // Get menu items by restaurant or category
                $restaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : null;
                $category = isset($_GET['category']) ? $_GET['category'] : null;
                
                if ($restaurantId && $category) {
                    $menuItems = $menuManager->getMenuItemsByCategory($restaurantId, $category);
                } else if ($restaurantId) {
                    $menuItems = $menuManager->getMenuItemsByRestaurant($restaurantId);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Restaurant ID is required'
                    ]);
                    exit();
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $menuItems
                ]);
            }
            break;
            
        case 'POST':
            // Create new menu item
            if (!isset($input['restaurant_id']) || !isset($input['name']) || !isset($input['description']) || 
                !isset($input['price']) || !isset($input['category'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Restaurant ID, name, description, price, and category are required'
                ]);
                exit();
            }
            
            $result = $menuManager->createMenuItem(
                $input['restaurant_id'],
                $input['name'],
                $input['description'],
                $input['price'],
                $input['category'],
                isset($input['image']) ? $input['image'] : null,
                isset($input['available']) ? $input['available'] : 1
            );
            
            echo json_encode($result);
            break;
            
        case 'PUT':
            // Update menu item
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Menu item ID is required'
                ]);
                exit();
            }
            
            if (!isset($input['name']) || !isset($input['description']) || !isset($input['price']) || 
                !isset($input['category'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Name, description, price, and category are required'
                ]);
                exit();
            }
            
            $result = $menuManager->updateMenuItem(
                $id,
                $input['name'],
                $input['description'],
                $input['price'],
                $input['category'],
                isset($input['image']) ? $input['image'] : null,
                isset($input['available']) ? $input['available'] : 1
            );
            
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete menu item
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Menu item ID is required'
                ]);
                exit();
            }
            
            $result = $menuManager->deleteMenuItem($id);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

$menuManager->close();
?>
<?php
/**
 * Restaurants API
 * Handles restaurant-related operations
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
require_once '../backend/Restaurant.php';
require_once '../backend/Menu.php';

$restaurantManager = new Restaurant();
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
                // Get specific restaurant
                $restaurant = $restaurantManager->getRestaurantById($id);
                if ($restaurant) {
                    // Also get menu items for this restaurant
                    $menuItems = $menuManager->getMenuItemsByRestaurant($id);
                    $restaurant['menu_items'] = $menuItems;
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $restaurant
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Restaurant not found'
                    ]);
                }
            } else {
                // Get all restaurants or search
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $cuisine = isset($_GET['cuisine']) ? $_GET['cuisine'] : '';
                
                if ($search) {
                    $restaurants = $restaurantManager->searchRestaurants($search);
                } else if ($cuisine) {
                    $restaurants = $restaurantManager->filterByCuisine($cuisine);
                } else {
                    $restaurants = $restaurantManager->getAllRestaurants();
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $restaurants
                ]);
            }
            break;
            
        case 'POST':
            // Create new restaurant
            if (!isset($input['name']) || !isset($input['description']) || !isset($input['cuisine']) || 
                !isset($input['address']) || !isset($input['phone']) || !isset($input['price_range'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Name, description, cuisine, address, phone, and price_range are required'
                ]);
                exit();
            }
            
            $result = $restaurantManager->createRestaurant(
                $input['name'],
                $input['description'],
                $input['cuisine'],
                $input['address'],
                $input['phone'],
                $input['price_range'],
                isset($input['image']) ? $input['image'] : null,
                isset($input['seating_capacity']) ? $input['seating_capacity'] : 0
            );
            
            echo json_encode($result);
            break;
            
        case 'PUT':
            // Update restaurant
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Restaurant ID is required'
                ]);
                exit();
            }
            
            if (!isset($input['name']) || !isset($input['description']) || !isset($input['cuisine']) || 
                !isset($input['address']) || !isset($input['phone']) || !isset($input['price_range'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Name, description, cuisine, address, phone, and price_range are required'
                ]);
                exit();
            }
            
            $result = $restaurantManager->updateRestaurant(
                $id,
                $input['name'],
                $input['description'],
                $input['cuisine'],
                $input['address'],
                $input['phone'],
                $input['price_range'],
                isset($input['image']) ? $input['image'] : null,
                isset($input['seating_capacity']) ? $input['seating_capacity'] : 0
            );
            
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete restaurant
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Restaurant ID is required'
                ]);
                exit();
            }
            
            $result = $restaurantManager->deleteRestaurant($id);
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

$restaurantManager->close();
$menuManager->close();
?>
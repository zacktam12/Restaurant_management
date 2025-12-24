<?php
/**
 * Menu API Endpoint
 * Handles REST API requests for menu items
 */

require_once '../backend/config.php';
require_once '../backend/Menu.php';
require_once '../backend/ApiResponse.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$menuManager = new Menu();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$restaurant_id = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $item = $menuManager->getMenuItemById($id);
            if ($item) {
                ApiResponse::sendSuccess($item, 'Menu item retrieved successfully');
            } else {
                ApiResponse::sendError('Menu item not found', 404);
            }
        } else if ($restaurant_id) {
            if (isset($_GET['category'])) {
                $items = $menuManager->getMenuByCategory($restaurant_id, $_GET['category']);
            } else {
                $items = $menuManager->getMenuByRestaurant($restaurant_id);
            }
            ApiResponse::sendSuccess($items, 'Menu items retrieved');
        } else {
            ApiResponse::sendError('Restaurant ID is required', 400);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            ApiResponse::sendError('Invalid JSON input', 400);
        }
        
        $required = ['restaurant_id', 'name', 'description', 'price', 'category'];
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                ApiResponse::sendError("Missing required field: {$field}", 400);
            }
        }
        
        $result = $menuManager->addMenuItem(
            $input['restaurant_id'],
            $input['name'],
            $input['description'],
            $input['price'],
            $input['category'],
            $input['image'] ?? null
        );
        
        if ($result['success']) {
            ApiResponse::sendSuccess(['item_id' => $result['item_id']], $result['message'], 201);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            ApiResponse::sendError('Menu item ID is required', 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            ApiResponse::sendError('Invalid JSON input', 400);
        }
        
        if (isset($input['toggle_availability'])) {
            $result = $menuManager->toggleAvailability($id);
        } else {
            $result = $menuManager->updateMenuItem(
                $id,
                $input['name'],
                $input['description'],
                $input['price'],
                $input['category'],
                $input['available'] ?? 1,
                $input['image'] ?? null
            );
        }
        
        if ($result['success']) {
            ApiResponse::sendSuccess(null, $result['message']);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    case 'DELETE':
        if (!$id) {
            ApiResponse::sendError('Menu item ID is required', 400);
        }
        
        $result = $menuManager->deleteMenuItem($id);
        
        if ($result['success']) {
            ApiResponse::sendSuccess(null, $result['message']);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    default:
        ApiResponse::sendError('Method not allowed', 405);
}

$menuManager->close();
?>

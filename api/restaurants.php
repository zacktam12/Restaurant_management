<?php
/**
 * Restaurant API Endpoint
 * Handles REST API requests for restaurants
 */

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/ApiResponse.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$restaurantManager = new Restaurant();

// Get restaurant ID from URL if provided
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Get specific restaurant
            $restaurant = $restaurantManager->getRestaurantById($id);
            if ($restaurant) {
                ApiResponse::sendSuccess($restaurant, 'Restaurant retrieved successfully');
            } else {
                ApiResponse::sendError('Restaurant not found', 404);
            }
        } else if (isset($_GET['search'])) {
            // Search restaurants
            $restaurants = $restaurantManager->searchRestaurants($_GET['search']);
            ApiResponse::sendSuccess($restaurants, 'Search results retrieved');
        } else if (isset($_GET['cuisine'])) {
            // Filter by cuisine
            $restaurants = $restaurantManager->filterByCuisine($_GET['cuisine']);
            ApiResponse::sendSuccess($restaurants, 'Filtered restaurants retrieved');
        } else if (isset($_GET['manager_id'])) {
            // Get restaurants by manager
            $restaurants = $restaurantManager->getRestaurantsByManager($_GET['manager_id']);
            ApiResponse::sendSuccess($restaurants, 'Manager restaurants retrieved');
        } else {
            // Get all restaurants
            $restaurants = $restaurantManager->getAllRestaurants();
            ApiResponse::sendSuccess($restaurants, 'All restaurants retrieved');
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            ApiResponse::sendError('Invalid JSON input', 400);
        }
        
        // Validate required fields
        $required = ['name', 'description', 'cuisine', 'address', 'phone', 'price_range', 'seating_capacity', 'manager_id'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                ApiResponse::sendError("Missing required field: {$field}", 400);
            }
        }
        
        $result = $restaurantManager->createRestaurant(
            $input['name'],
            $input['description'],
            $input['cuisine'],
            $input['address'],
            $input['phone'],
            $input['price_range'],
            $input['seating_capacity'],
            $input['manager_id'],
            $input['image'] ?? null
        );
        
        if ($result['success']) {
            ApiResponse::sendSuccess(['restaurant_id' => $result['restaurant_id']], $result['message'], 201);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            ApiResponse::sendError('Restaurant ID is required', 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            ApiResponse::sendError('Invalid JSON input', 400);
        }
        
        $result = $restaurantManager->updateRestaurant(
            $id,
            $input['name'],
            $input['description'],
            $input['cuisine'],
            $input['address'],
            $input['phone'],
            $input['price_range'],
            $input['seating_capacity'],
            $input['image'] ?? null
        );
        
        if ($result['success']) {
            ApiResponse::sendSuccess(null, $result['message']);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    case 'DELETE':
        if (!$id) {
            ApiResponse::sendError('Restaurant ID is required', 400);
        }
        
        $result = $restaurantManager->deleteRestaurant($id);
        
        if ($result['success']) {
            ApiResponse::sendSuccess(null, $result['message']);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    default:
        ApiResponse::sendError('Method not allowed', 405);
}

$restaurantManager->close();
?>

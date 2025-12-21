<?php
/**
 * Service Provider Interface
 * API endpoints for external groups to consume restaurant services
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Menu.php';
require_once '../backend/Reservation.php';
require_once '../backend/ApiKey.php';
require_once '../backend/ApiResponse.php';

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    
    // Parse path info to get endpoint and ID if present
    $pathParts = explode('/', trim($pathInfo, '/'));
    $endpoint = isset($pathParts[0]) ? $pathParts[0] : '';
    $id = isset($pathParts[1]) && is_numeric($pathParts[1]) ? (int)$pathParts[1] : null;
    
    // Initialize managers
    $restaurantManager = new Restaurant();
    $menuManager = new Menu();
    $reservationManager = new Reservation();
    $apiKeyManager = new ApiKey();
    
    // Check API key authentication for non-GET requests
    if ($method !== 'GET') {
        $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        
        // Handle both "Bearer token" and "token" formats
        if (strpos($authHeader, 'Bearer ') === 0) {
            $apiKey = substr($authHeader, 7);
        } else {
            $apiKey = $authHeader;
        }
        
        // If no API key provided, check for it in the request body or query params
        if (empty($apiKey)) {
            $apiKey = isset($input['api_key']) ? $input['api_key'] : (isset($_GET['api_key']) ? $_GET['api_key'] : '');
        }
        
        // Validate API key
        if (empty($apiKey)) {
            ApiResponse::unauthorized('API key is required for this operation');
        }
        
        $apiKeyRecord = $apiKeyManager->validateApiKey($apiKey);
        if (!$apiKeyRecord) {
            ApiResponse::unauthorized('Invalid or inactive API key');
        }
        
        // Log API key usage
        $apiKeyManager->logApiKeyUsage($apiKey);
        
        // Check permissions
        if (($method === 'POST' || $method === 'PUT' || $method === 'DELETE') && 
            $apiKeyRecord['permissions'] === 'read') {
            ApiResponse::forbidden('Insufficient permissions for this operation');
        }
    }
    
    switch ($endpoint) {
        case 'restaurants':
            handleRestaurants($method, $id, $input, $restaurantManager, $menuManager);
            break;
            
        case 'menu':
            handleMenu($method, $id, $input, $menuManager);
            break;
            
        case 'reservations':
            handleReservations($method, $id, $input, $reservationManager);
            break;
            
        case 'availability':
            handleAvailability($method, $id, $input, $restaurantManager, $reservationManager);
            break;
            
        default:
            ApiResponse::notFound('Invalid endpoint. Available endpoints: restaurants, menu, reservations, availability');
            break;
    }
    
    // Close connections
    $restaurantManager->close();
    $menuManager->close();
    $reservationManager->close();
    $apiKeyManager->close();
    
} catch (Exception $e) {
    ApiResponse::serverError('Server error: ' . $e->getMessage());
}

/**
 * Handle restaurants endpoint
 */
function handleRestaurants($method, $id, $input, $restaurantManager, $menuManager) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific restaurant
                $restaurant = $restaurantManager->getRestaurantById($id);
                if ($restaurant) {
                    // Also get menu items for this restaurant
                    $menuItems = $menuManager->getMenuItemsByRestaurant($id);
                    $restaurant['menu_items'] = $menuItems;
                    
                    ApiResponse::success($restaurant, 'Restaurant retrieved successfully');
                } else {
                    ApiResponse::notFound('Restaurant not found');
                }
            } else {
                // Get all restaurants with pagination and filters
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $cuisine = isset($_GET['cuisine']) ? $_GET['cuisine'] : '';
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
                
                if ($search) {
                    $restaurants = $restaurantManager->searchRestaurants($search);
                } else if ($cuisine) {
                    $restaurants = $restaurantManager->filterByCuisine($cuisine);
                } else {
                    $restaurants = $restaurantManager->getAllRestaurants();
                }
                
                // Apply pagination
                $total = count($restaurants);
                $offset = ($page - 1) * $perPage;
                $paged = array_slice($restaurants, $offset, $perPage);
                
                ApiResponse::paginated(
                    $paged,
                    $total,
                    $page,
                    $perPage,
                    'Restaurants retrieved successfully'
                );
            }
            break;
            
        case 'POST':
            // Create new restaurant
            $required = ['name', 'description', 'cuisine', 'address', 'phone', 'price_range'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $errors[$field] = ucfirst($field) . ' is required';
                }
            }
            
            if (!empty($errors)) {
                ApiResponse::validationError($errors);
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
            
            if ($result['success']) {
                ApiResponse::created($result['data'], 'Restaurant created successfully');
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        case 'PUT':
            // Update restaurant
            if (!$id) {
                ApiResponse::validationError(['id' => 'Restaurant ID is required']);
            }
            
            $required = ['name', 'description', 'cuisine', 'address', 'phone', 'price_range'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $errors[$field] = ucfirst($field) . ' is required';
                }
            }
            
            if (!empty($errors)) {
                ApiResponse::validationError($errors);
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
            
            if ($result['success']) {
                ApiResponse::success($result['data'], 'Restaurant updated successfully');
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        case 'DELETE':
            // Delete restaurant
            if (!$id) {
                ApiResponse::validationError(['id' => 'Restaurant ID is required']);
            }
            
            $result = $restaurantManager->deleteRestaurant($id);
            if ($result['success']) {
                ApiResponse::noContent();
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        default:
            ApiResponse::error('Method not allowed for restaurants endpoint', [], 405);
            break;
    }
}

/**
 * Handle menu endpoint
 */
function handleMenu($method, $id, $input, $menuManager) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific menu item
                $menuItem = $menuManager->getMenuItemById($id);
                if ($menuItem) {
                    ApiResponse::success($menuItem, 'Menu item retrieved successfully');
                } else {
                    ApiResponse::notFound('Menu item not found');
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
                    ApiResponse::validationError(['restaurant_id' => 'Restaurant ID is required']);
                    return;
                }
                
                ApiResponse::success($menuItems, 'Menu items retrieved successfully');
            }
            break;
            
        case 'POST':
            // Create new menu item
            $required = ['restaurant_id', 'name', 'description', 'price', 'category'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
            
            if (!empty($errors)) {
                ApiResponse::validationError($errors);
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
            
            if ($result['success']) {
                ApiResponse::created($result['data'], 'Menu item created successfully');
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        case 'PUT':
            // Update menu item
            if (!$id) {
                ApiResponse::validationError(['id' => 'Menu item ID is required']);
            }
            
            $required = ['name', 'description', 'price', 'category'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $errors[$field] = ucfirst($field) . ' is required';
                }
            }
            
            if (!empty($errors)) {
                ApiResponse::validationError($errors);
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
            
            if ($result['success']) {
                ApiResponse::success($result['data'], 'Menu item updated successfully');
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        case 'DELETE':
            // Delete menu item
            if (!$id) {
                ApiResponse::validationError(['id' => 'Menu item ID is required']);
            }
            
            $result = $menuManager->deleteMenuItem($id);
            if ($result['success']) {
                ApiResponse::noContent();
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        default:
            ApiResponse::error('Method not allowed for menu endpoint', [], 405);
            break;
    }
}

 * Handle reservations endpoint
 */
function handleReservations($method, $id, $input, $reservationManager) {
    switch ($method) {
        case 'POST':
            // Create new reservation
            $required = ['restaurant_id', 'customer_name', 'customer_email', 'customer_phone', 'date', 'time', 'guests'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
            
            if (!empty($errors)) {
                ApiResponse::validationError($errors);
            }
            
            $result = $reservationManager->createReservation(
                $input['restaurant_id'],
                $input['customer_name'],
                $input['customer_email'],
                $input['customer_phone'],
                $input['date'],
                $input['time'],
                $input['guests'],
                isset($input['special_requests']) ? $input['special_requests'] : null
            );
            
            if ($result['success']) {
                ApiResponse::created($result['data'], 'Reservation created successfully');
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        case 'GET':
            if ($id) {
                // Get specific reservation
                $reservation = $reservationManager->getReservationById($id);
                if ($reservation) {
                    ApiResponse::success($reservation, 'Reservation retrieved successfully');
                } else {
                    ApiResponse::notFound('Reservation not found');
                }
            } else {
                // Get reservations by restaurant or status with pagination
                $restaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : null;
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
                
                if ($restaurantId && $status) {
                    $reservations = $reservationManager->getReservationsByRestaurantAndStatus($restaurantId, $status);
                } else if ($restaurantId) {
                    $reservations = $reservationManager->getReservationsByRestaurant($restaurantId);
                } else if ($status) {
                    $reservations = $reservationManager->getReservationsByStatus($status);
                } else {
                    $reservations = $reservationManager->getAllReservations();
                }
                
                // Apply pagination
                $total = count($reservations);
                $offset = ($page - 1) * $perPage;
                $paged = array_slice($reservations, $offset, $perPage);
                
                ApiResponse::paginated(
                    $paged,
                    $total,
                    $page,
                    $perPage,
                    'Reservations retrieved successfully'
                );
            }
            break;
            
        case 'PUT':
            // Update reservation
            if (!$id) {
                ApiResponse::validationError(['id' => 'Reservation ID is required']);
            }
            
            // Check if updating status or details
            if (isset($input['status'])) {
                $result = $reservationManager->updateReservationStatus($id, $input['status']);
            } else if (isset($input['date']) && isset($input['time']) && isset($input['guests'])) {
                $result = $reservationManager->updateReservation(
                    $id,
                    $input['date'],
                    $input['time'],
                    $input['guests'],
                    isset($input['special_requests']) ? $input['special_requests'] : null
                );
            } else {
                ApiResponse::validationError(['update_data' => 'Either status or date/time/guests are required for update']);
            }
            
            if ($result['success']) {
                ApiResponse::success($result['data'], 'Reservation updated successfully');
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        case 'DELETE':
            // Delete reservation
            if (!$id) {
                ApiResponse::validationError(['id' => 'Reservation ID is required']);
            }
            
            $result = $reservationManager->deleteReservation($id);
            if ($result['success']) {
                ApiResponse::noContent();
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        default:
            ApiResponse::error('Method not allowed for reservations endpoint', [], 405);
            break;
    }
}

/**
 * Handle availability endpoint
 */
function handleAvailability($method, $id, $input, $restaurantManager, $reservationManager) {
    switch ($method) {
        case 'GET':
            if (!$id) {
                ApiResponse::validationError(['id' => 'Restaurant ID is required for availability check']);
            }
            
            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            $time = isset($_GET['time']) ? $_GET['time'] : null;
            
            // Get restaurant details
            $restaurant = $restaurantManager->getRestaurantById($id);
            if (!$restaurant) {
                ApiResponse::notFound('Restaurant not found');
            }
            
            // Get reservations for the date
            $reservations = $reservationManager->getReservationsByRestaurant($id);
            
            // Filter by date
            $dailyReservations = [];
            foreach ($reservations as $reservation) {
                if ($reservation['date'] == $date) {
                    $dailyReservations[] = $reservation;
                }
            }
            
            // Calculate availability
            $totalSeats = $restaurant['seating_capacity'];
            $reservedSeats = 0;
            
            foreach ($dailyReservations as $reservation) {
                $reservedSeats += $reservation['guests'];
            }
            
            $availableSeats = $totalSeats - $reservedSeats;
            
            ApiResponse::success([
                'restaurant_id' => $id,
                'date' => $date,
                'total_seats' => $totalSeats,
                'reserved_seats' => $reservedSeats,
                'available_seats' => $availableSeats,
                'availability_percentage' => $totalSeats > 0 ? round(($availableSeats / $totalSeats) * 100, 2) : 0
            ], 'Availability retrieved successfully');
            break;
            
        default:
            ApiResponse::error('Method not allowed for availability endpoint', [], 405);
            break;
    }
}
?>
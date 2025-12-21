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
            echo json_encode([
                'success' => false,
                'message' => 'API key is required for this operation'
            ]);
            exit();
        }
        
        $apiKeyRecord = $apiKeyManager->validateApiKey($apiKey);
        if (!$apiKeyRecord) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or inactive API key'
            ]);
            exit();
        }
        
        // Log API key usage
        $apiKeyManager->logApiKeyUsage($apiKey);
        
        // Check permissions
        if (($method === 'POST' || $method === 'PUT' || $method === 'DELETE') && 
            $apiKeyRecord['permissions'] === 'read') {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient permissions for this operation'
            ]);
            exit();
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
            echo json_encode([
                'success' => false,
                'message' => 'Invalid endpoint. Available endpoints: restaurants, menu, reservations, availability'
            ]);
            break;
    }
    
    // Close connections
    $restaurantManager->close();
    $menuManager->close();
    $reservationManager->close();
    $apiKeyManager->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
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
                'message' => 'Method not allowed for restaurants endpoint'
            ]);
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
                    return;
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
                'message' => 'Method not allowed for menu endpoint'
            ]);
            break;
    }
}

/**
 * Handle reservations endpoint
 */
function handleReservations($method, $id, $input, $reservationManager) {
    switch ($method) {
        case 'POST':
            // Create new reservation
            if (!isset($input['restaurant_id']) || !isset($input['customer_name']) || 
                !isset($input['customer_email']) || !isset($input['customer_phone']) ||
                !isset($input['date']) || !isset($input['time']) || !isset($input['guests'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Restaurant ID, customer name, email, phone, date, time, and guests are required'
                ]);
                return;
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
            
            echo json_encode($result);
            break;
            
        case 'GET':
            if ($id) {
                // Get specific reservation
                $reservation = $reservationManager->getReservationById($id);
                if ($reservation) {
                    echo json_encode([
                        'success' => true,
                        'data' => $reservation
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Reservation not found'
                    ]);
                }
            } else {
                // Get reservations by restaurant or status
                $restaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : null;
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                
                if ($restaurantId && $status) {
                    // This would require a new method in Reservation class
                    $reservations = [];
                    // For now, we'll just get by restaurant and filter in PHP
                    $allReservations = $reservationManager->getReservationsByRestaurant($restaurantId);
                    foreach ($allReservations as $reservation) {
                        if ($reservation['status'] == $status) {
                            $reservations[] = $reservation;
                        }
                    }
                } else if ($restaurantId) {
                    $reservations = $reservationManager->getReservationsByRestaurant($restaurantId);
                } else if ($status) {
                    $reservations = $reservationManager->getReservationsByStatus($status);
                } else {
                    $reservations = $reservationManager->getAllReservations();
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $reservations
                ]);
            }
            break;
            
        case 'PUT':
            // Update reservation
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Reservation ID is required'
                ]);
                exit();
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Either status or date/time/guests are required for update'
                ]);
                exit();
            }
            
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete reservation
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Reservation ID is required'
                ]);
                exit();
            }
            
            $result = $reservationManager->deleteReservation($id);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed for reservations endpoint'
            ]);
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Restaurant ID is required for availability check'
                ]);
                return;
            }
            
            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            $time = isset($_GET['time']) ? $_GET['time'] : null;
            
            // Get restaurant details
            $restaurant = $restaurantManager->getRestaurantById($id);
            if (!$restaurant) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ]);
                return;
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
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'restaurant_id' => $id,
                    'date' => $date,
                    'total_seats' => $totalSeats,
                    'reserved_seats' => $reservedSeats,
                    'available_seats' => $availableSeats,
                    'availability_percentage' => $totalSeats > 0 ? round(($availableSeats / $totalSeats) * 100, 2) : 0
                ]
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed for availability endpoint'
            ]);
            break;
    }
}
?>
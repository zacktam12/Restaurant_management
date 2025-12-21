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
<?php
/**
 * Service Provider API
 * Exposes restaurant services to external systems (other groups)
 * 
 * This endpoint allows other services (tours, hotels, taxis) to:
 * - Discover restaurants
 * - Make reservations on behalf of their users
 * - Check availability
 * - Retrieve menus and details
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For cross-domain requests
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/Restaurant.php';
require_once __DIR__ . '/../backend/Reservation.php';
require_once __DIR__ . '/../backend/Menu.php';
require_once __DIR__ . '/../backend/ApiResponse.php';

// Simple API Key Authentication (for external services)
function authenticateApiKey() {
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? null;
    
    // Predefined API keys for different service groups
    $validApiKeys = [
        'TOUR_SERVICE_KEY_2025' => 'Tour Operator Group',
        'HOTEL_SERVICE_KEY_2025' => 'Hotel Booking Group',
        'TAXI_SERVICE_KEY_2025' => 'Taxi Service Group',
        'TICKET_SERVICE_KEY_2025' => 'Travel Ticket Group',
        'DEV_TEST_KEY' => 'Development Testing'
    ];
    
    if (!$apiKey || !isset($validApiKeys[$apiKey])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized - Invalid or missing API key',
            'hint' => 'Include X-API-Key header with your service API key'
        ]);
        exit();
    }
    
    return $validApiKeys[$apiKey];
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        
        // GET /service-provider.php?action=restaurants
        case 'restaurants':
            if ($method !== 'GET') {
                ApiResponse::error('Method not allowed', 405);
                break;
            }
            
            $restaurantManager = new Restaurant();
            
            // Optional filtering
            $cuisine = $_GET['cuisine'] ?? null;
            $priceRange = $_GET['price_range'] ?? null;
            
            if ($cuisine) {
                $restaurants = $restaurantManager->filterByCuisine($cuisine);
            } elseif ($priceRange) {
                $restaurants = $restaurantManager->filterByPriceRange($priceRange);
            } else {
                $restaurants = $restaurantManager->getAllRestaurants();
            }
            
            // Format for external consumption
            $formattedRestaurants = array_map(function($r) {
                return [
                    'id' => (int)$r['id'],
                    'name' => $r['name'],
                    'cuisine' => $r['cuisine'],
                    'address' => $r['address'],
                    'phone' => $r['phone'],
                    'price_range' => $r['price_range'] ?? '$$$',
                    'rating' => (float)$r['rating'],
                    'image' => $r['image'],
                    'service_url' => 'http://localhost/rest/api/service-provider.php?action=restaurant_details&id=' . $r['id']
                ];
            }, $restaurants);
            
            ApiResponse::success($formattedRestaurants, 'Restaurants retrieved successfully');
            break;
            
        // GET /service-provider.php?action=restaurant_details&id=1
        case 'restaurant_details':
            if ($method !== 'GET') {
                ApiResponse::error('Method not allowed', 405);
                break;
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) {
                ApiResponse::error('Restaurant ID required', 400);
                break;
            }
            
            $restaurantManager = new Restaurant();
            $restaurant = $restaurantManager->getRestaurantById($id);
            
            if (!$restaurant) {
                ApiResponse::error('Restaurant not found', 404);
                break;
            }
            
            // Include available time slots
            $timeSlots = [
                '11:00', '11:30', '12:00', '12:30',
                '13:00', '13:30', '14:00',
                '18:00', '18:30', '19:00',
                '19:30', '20:00', '20:30', '21:00'
            ];
            
            $detailedInfo = [
                'id' => (int)$restaurant['id'],
                'name' => $restaurant['name'],
                'cuisine' => $restaurant['cuisine'],
                'description' => $restaurant['description'],
                'address' => $restaurant['address'],
                'phone' => $restaurant['phone'],
                'price_range' => $restaurant['price_range'] ?? '$$$',
                'rating' => (float)$restaurant['rating'],
                'seating_capacity' => (int)$restaurant['seating_capacity'],
                'image' => $restaurant['image'],
                'available_time_slots' => $timeSlots,
                'opening_hours' => '11:00 AM - 10:00 PM'
            ];
            
            ApiResponse::success($detailedInfo, 'Restaurant details retrieved');
            break;
            
        // POST /service-provider.php?action=create_reservation
        case 'create_reservation':
            if ($method !== 'POST') {
                ApiResponse::error('Method not allowed', 405);
                break;
            }
            
            // Authenticate external service
            $serviceProvider = authenticateApiKey();
            
            // Get JSON payload
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['restaurant_id', 'customer_name', 'customer_email', 'customer_phone', 'date', 'time', 'guests'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    ApiResponse::error("Missing required field: $field", 400);
                    exit();
                }
            }
            
            $reservationManager = new Reservation();
            
            // Create reservation with external booking reference
            $result = $reservationManager->createReservation(
                $input['restaurant_id'],
                $input['customer_name'],
                $input['customer_email'],
                $input['customer_phone'],
                $input['date'],
                $input['time'],
                $input['guests'],
                $input['special_requests'] ?? null,
                null // customer_id is null for external bookings
            );
            
            if ($result['success']) {
                // Generate confirmation code
                $confirmationCode = 'REST-' . date('Y') . '-' . strtoupper(substr(md5($result['reservation_id']), 0, 6));
                
                // Store external booking ID if provided
                if (!empty($input['external_booking_id'])) {
                    // Update reservation with external reference
                    $conn = (new Database())->getConnection();
                    $sql = "UPDATE reservations SET special_requests = CONCAT(COALESCE(special_requests, ''), '\nExternal Ref: " . $conn->real_escape_string($input['external_booking_id']) . "') WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $result['reservation_id']);
                    $stmt->execute();
                }
                
                ApiResponse::success([
                    'reservation_id' => $result['reservation_id'],
                    'confirmation_code' => $confirmationCode,
                    'status' => 'pending',
                    'message' => 'Reservation request received. Awaiting restaurant confirmation.',
                    'service_provider' => $serviceProvider
                ], 'Reservation created successfully', 201);
            } else {
                ApiResponse::error($result['message'], 400);
            }
            break;
            
        // GET /service-provider.php?action=check_availability
        case 'check_availability':
            if ($method !== 'GET') {
                ApiResponse::error('Method not allowed', 405);
                break;
            }
            
            $restaurantId = $_GET['restaurant_id'] ?? null;
            $date = $_GET['date'] ?? null;
            $time = $_GET['time'] ?? null;
            $guests = $_GET['guests'] ?? null;
            
            if (!$restaurantId || !$date || !$time || !$guests) {
                ApiResponse::error('Missing required parameters', 400);
                break;
            }
            
            // Simple availability check (can be enhanced with real capacity tracking)
            $reservationManager = new Reservation();
            $existingReservations = $reservationManager->getReservationsByRestaurant($restaurantId);
            
            // Count reservations for the same date/time
            $count = 0;
            foreach ($existingReservations as $res) {
                if ($res['date'] === $date && $res['time'] === $time && $res['status'] !== 'cancelled') {
                    $count += $res['guests'];
                }
            }
            
            // Assume capacity of 80 (should be fetched from restaurant)
            $available = $count < 60; // 60 seats threshold
            
            ApiResponse::success([
                'available' => $available,
                'current_bookings' => $count,
                'message' => $available ? 'Table available' : 'Fully booked for this time slot'
            ], 'Availability checked');
            break;
            
        // GET /service-provider.php?action=menu&restaurant_id=1
        case 'menu':
            if ($method !== 'GET') {
                ApiResponse::error('Method not allowed', 405);
                break;
            }
            
            $restaurantId = $_GET['restaurant_id'] ?? null;
            if (!$restaurantId) {
                ApiResponse::error('Restaurant ID required', 400);
                break;
            }
            
            $menuManager = new Menu();
            $menuItems = $menuManager->getMenuByRestaurant($restaurantId);
            
            $formattedMenu = array_map(function($item) {
                return [
                    'id' => (int)$item['id'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => (float)$item['price'],
                    'category' => $item['category'],
                    'available' => (bool)$item['available']
                ];
            }, $menuItems);
            
            ApiResponse::success($formattedMenu, 'Menu retrieved');
            break;
            
        // GET /service-provider.php?action=service_info
        case 'service_info':
            ApiResponse::success([
                'service_name' => 'Restaurant Management Service',
                'service_type' => 'Restaurant Booking',
                'provider' => 'Restaurant Management Group',
                'version' => '1.0.0',
                'capabilities' => [
                    'restaurant_discovery',
                    'table_reservations',
                    'menu_browsing',
                    'availability_checking'
                ],
                'authentication' => 'API Key (X-API-Key header)',
                'documentation' => 'http://localhost/rest/api/openapi.yaml',
                'endpoints' => [
                    'GET /api/service-provider.php?action=restaurants',
                    'GET /api/service-provider.php?action=restaurant_details&id={id}',
                    'POST /api/service-provider.php?action=create_reservation',
                    'GET /api/service-provider.php?action=check_availability',
                    'GET /api/service-provider.php?action=menu&restaurant_id={id}'
                ]
            ], 'Service information');
            break;
            
        default:
            ApiResponse::error('Invalid action. Use: restaurants, restaurant_details, create_reservation, check_availability, menu, or service_info', 400);
            break;
    }
    
} catch (Exception $e) {
    ApiResponse::error('Server error: ' . $e->getMessage(), 500);
}

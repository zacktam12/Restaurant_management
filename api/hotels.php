<?php
/**
 * Hotels API
 * Handles hotel management operations for Group 2 and 6
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
require_once '../backend/ApiResponse.php';

// Mock Hotel class for demonstration (in real implementation, this would connect to database)
class HotelManager {
    private $hotels = [];
    
    public function __construct() {
        // Initialize with sample data
        $this->hotels = [
            1 => [
                'id' => 1,
                'name' => 'Grand Palace Hotel',
                'description' => '5-star luxury accommodation in the heart of the city',
                'location' => 'City Center',
                'address' => '123 Main Street, Downtown',
                'phone' => '+1-555-0101',
                'email' => 'info@grandpalace.com',
                'rating' => 4.9,
                'price_range' => '$$$',
                'amenities' => ['WiFi', 'Pool', 'Spa', 'Restaurant', 'Gym', 'Bar'],
                'room_types' => [
                    ['type' => 'Standard', 'price' => 199.99, 'capacity' => 2, 'available' => 15],
                    ['type' => 'Deluxe', 'price' => 299.99, 'capacity' => 2, 'available' => 8],
                    ['type' => 'Suite', 'price' => 499.99, 'capacity' => 4, 'available' => 3]
                ],
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'policies' => ['No smoking', 'Pets allowed with fee', 'Free cancellation 24h before'],
                'created_at' => '2023-01-01 10:00:00',
                'updated_at' => '2023-01-01 10:00:00'
            ],
            2 => [
                'id' => 2,
                'name' => 'Cozy Inn',
                'description' => 'Budget-friendly comfortable stay with excellent service',
                'location' => 'Suburb',
                'address' => '456 Oak Avenue, Suburbia',
                'phone' => '+1-555-0202',
                'email' => 'info@cozyinn.com',
                'rating' => 4.2,
                'price_range' => '$$',
                'amenities' => ['WiFi', 'Parking', 'Breakfast', 'Laundry'],
                'room_types' => [
                    ['type' => 'Single', 'price' => 79.99, 'capacity' => 1, 'available' => 20],
                    ['type' => 'Double', 'price' => 99.99, 'capacity' => 2, 'available' => 12]
                ],
                'check_in_time' => '14:00',
                'check_out_time' => '12:00',
                'policies' => ['No smoking', 'No pets', '48h cancellation policy'],
                'created_at' => '2023-01-02 11:00:00',
                'updated_at' => '2023-01-02 11:00:00'
            ]
        ];
    }
    
    public function getAllHotels($search = null, $location = null, $minPrice = null, $maxPrice = null, $page = 1, $perPage = 10) {
        $filtered = $this->hotels;
        
        // Apply filters
        if ($search) {
            $filtered = array_filter($filtered, function($hotel) use ($search) {
                return stripos($hotel['name'], $search) !== false || 
                       stripos($hotel['description'], $search) !== false;
            });
        }
        
        if ($location) {
            $filtered = array_filter($filtered, function($hotel) use ($location) {
                return stripos($hotel['location'], $location) !== false;
            });
        }
        
        if ($minPrice !== null) {
            $filtered = array_filter($filtered, function($hotel) use ($minPrice) {
                return min(array_column($hotel['room_types'], 'price')) >= $minPrice;
            });
        }
        
        if ($maxPrice !== null) {
            $filtered = array_filter($filtered, function($hotel) use ($maxPrice) {
                return min(array_column($hotel['room_types'], 'price')) <= $maxPrice;
            });
        }
        
        // Apply pagination
        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $paged = array_slice($filtered, $offset, $perPage, true);
        
        return [
            'hotels' => array_values($paged),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total / $perPage)
        ];
    }
    
    public function getHotelById($id) {
        return isset($this->hotels[$id]) ? $this->hotels[$id] : null;
    }
    
    public function createHotel($name, $description, $location, $address, $phone, $email, $rating, $priceRange, $amenities, $roomTypes, $checkInTime, $checkOutTime, $policies) {
        $newId = max(array_keys($this->hotels)) + 1;
        
        $this->hotels[$newId] = [
            'id' => $newId,
            'name' => $name,
            'description' => $description,
            'location' => $location,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'rating' => $rating,
            'price_range' => $priceRange,
            'amenities' => $amenities,
            'room_types' => $roomTypes,
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'policies' => $policies,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->hotels[$newId];
    }
    
    public function updateHotel($id, $name, $description, $location, $address, $phone, $email, $rating, $priceRange, $amenities, $roomTypes, $checkInTime, $checkOutTime, $policies) {
        if (!isset($this->hotels[$id])) {
            return null;
        }
        
        $this->hotels[$id] = array_merge($this->hotels[$id], [
            'name' => $name,
            'description' => $description,
            'location' => $location,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'rating' => $rating,
            'price_range' => $priceRange,
            'amenities' => $amenities,
            'room_types' => $roomTypes,
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'policies' => $policies,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->hotels[$id];
    }
    
    public function deleteHotel($id) {
        if (!isset($this->hotels[$id])) {
            return false;
        }
        
        unset($this->hotels[$id]);
        return true;
    }
    
    public function checkAvailability($hotelId, $checkin, $checkout, $roomType = null) {
        $hotel = $this->getHotelById($hotelId);
        if (!$hotel) {
            return null;
        }
        
        // In a real implementation, this would check against actual bookings
        // For now, we'll return mock availability
        $availability = [];
        foreach ($hotel['room_types'] as $room) {
            if ($roomType === null || $room['type'] === $roomType) {
                $availability[] = [
                    'type' => $room['type'],
                    'price' => $room['price'],
                    'capacity' => $room['capacity'],
                    'available_rooms' => $room['available'],
                    'total_price' => $room['price'] * $this->calculateNights($checkin, $checkout)
                ];
            }
        }
        
        return $availability;
    }
    
    private function calculateNights($checkin, $checkout) {
        $checkinDate = new DateTime($checkin);
        $checkoutDate = new DateTime($checkout);
        return $checkoutDate->diff($checkinDate)->days;
    }
    
    public function createBooking($hotelId, $customerName, $customerEmail, $customerPhone, $checkin, $checkout, $roomType, $guests, $specialRequests = null) {
        $hotel = $this->getHotelById($hotelId);
        if (!$hotel) {
            return null;
        }
        
        $bookingId = 'HTL' . strtoupper(uniqid());
        $nights = $this->calculateNights($checkin, $checkout);
        
        // Find room type and calculate price
        $roomPrice = 0;
        foreach ($hotel['room_types'] as $room) {
            if ($room['type'] === $roomType) {
                $roomPrice = $room['price'];
                break;
            }
        }
        
        $totalPrice = $roomPrice * $nights;
        
        return [
            'booking_id' => $bookingId,
            'hotel_id' => $hotelId,
            'hotel_name' => $hotel['name'],
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'room_type' => $roomType,
            'guests' => $guests,
            'nights' => $nights,
            'room_price' => $roomPrice,
            'total_price' => $totalPrice,
            'special_requests' => $specialRequests,
            'status' => 'confirmed',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}

$hotelManager = new HotelManager();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $pathParts = explode('/', trim($pathInfo, '/'));
    $endpoint = isset($pathParts[0]) ? $pathParts[0] : '';
    $id = isset($pathParts[1]) && is_numeric($pathParts[1]) ? (int)$pathParts[1] : null;
    
    switch ($method) {
        case 'GET':
            if ($endpoint === 'hotels') {
                if ($id) {
                    $hotel = $hotelManager->getHotelById($id);
                    if ($hotel) {
                        ApiResponse::success($hotel, 'Hotel retrieved successfully');
                    } else {
                        ApiResponse::notFound('Hotel not found');
                    }
                } else {
                    // Get all hotels with filters and pagination
                    $search = isset($_GET['search']) ? $_GET['search'] : null;
                    $location = isset($_GET['location']) ? $_GET['location'] : null;
                    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
                    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
                    
                    $result = $hotelManager->getAllHotels($search, $location, $minPrice, $maxPrice, $page, $perPage);
                    ApiResponse::paginated(
                        $result['hotels'],
                        $result['total'],
                        $result['page'],
                        $result['per_page'],
                        'Hotels retrieved successfully'
                    );
                }
            } elseif ($endpoint === 'availability' && $id) {
                $checkin = isset($_GET['checkin']) ? $_GET['checkin'] : null;
                $checkout = isset($_GET['checkout']) ? $_GET['checkout'] : null;
                $roomType = isset($_GET['room_type']) ? $_GET['room_type'] : null;
                
                if (!$checkin || !$checkout) {
                    ApiResponse::validationError([
                        'checkin' => 'Check-in date is required',
                        'checkout' => 'Check-out date is required'
                    ]);
                }
                
                $availability = $hotelManager->checkAvailability($id, $checkin, $checkout, $roomType);
                if ($availability) {
                    ApiResponse::success($availability, 'Availability checked successfully');
                } else {
                    ApiResponse::notFound('Hotel not found');
                }
            } else {
                ApiResponse::notFound('Invalid endpoint');
            }
            break;
            
        case 'POST':
            if ($endpoint === 'hotels') {
                // Create new hotel
                $required = ['name', 'description', 'location', 'address', 'phone', 'email', 'rating', 'price_range', 'amenities', 'room_types'];
                $errors = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    }
                }
                
                if (!empty($errors)) {
                    ApiResponse::validationError($errors);
                }
                
                $hotel = $hotelManager->createHotel(
                    $input['name'],
                    $input['description'],
                    $input['location'],
                    $input['address'],
                    $input['phone'],
                    $input['email'],
                    $input['rating'],
                    $input['price_range'],
                    $input['amenities'],
                    $input['room_types'],
                    $input['check_in_time'] ?? '15:00',
                    $input['check_out_time'] ?? '11:00',
                    $input['policies'] ?? []
                );
                
                ApiResponse::created($hotel, 'Hotel created successfully');
            } elseif ($endpoint === 'bookings') {
                // Create hotel booking
                $required = ['hotel_id', 'customer_name', 'customer_email', 'customer_phone', 'checkin', 'checkout', 'room_type', 'guests'];
                $errors = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    }
                }
                
                if (!empty($errors)) {
                    ApiResponse::validationError($errors);
                }
                
                $booking = $hotelManager->createBooking(
                    $input['hotel_id'],
                    $input['customer_name'],
                    $input['customer_email'],
                    $input['customer_phone'],
                    $input['checkin'],
                    $input['checkout'],
                    $input['room_type'],
                    $input['guests'],
                    $input['special_requests'] ?? null
                );
                
                if ($booking) {
                    ApiResponse::created($booking, 'Hotel booking created successfully');
                } else {
                    ApiResponse::error('Failed to create booking', [], 400);
                }
            } else {
                ApiResponse::notFound('Invalid endpoint');
            }
            break;
            
        case 'PUT':
            if ($endpoint === 'hotels' && $id) {
                // Update hotel
                $required = ['name', 'description', 'location', 'address', 'phone', 'email', 'rating', 'price_range', 'amenities', 'room_types'];
                $errors = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    }
                }
                
                if (!empty($errors)) {
                    ApiResponse::validationError($errors);
                }
                
                $hotel = $hotelManager->updateHotel(
                    $id,
                    $input['name'],
                    $input['description'],
                    $input['location'],
                    $input['address'],
                    $input['phone'],
                    $input['email'],
                    $input['rating'],
                    $input['price_range'],
                    $input['amenities'],
                    $input['room_types'],
                    $input['check_in_time'] ?? '15:00',
                    $input['check_out_time'] ?? '11:00',
                    $input['policies'] ?? []
                );
                
                if ($hotel) {
                    ApiResponse::success($hotel, 'Hotel updated successfully');
                } else {
                    ApiResponse::notFound('Hotel not found');
                }
            } else {
                ApiResponse::notFound('Invalid endpoint or missing ID');
            }
            break;
            
        case 'DELETE':
            if ($endpoint === 'hotels' && $id) {
                // Delete hotel
                $success = $hotelManager->deleteHotel($id);
                if ($success) {
                    ApiResponse::noContent();
                } else {
                    ApiResponse::notFound('Hotel not found');
                }
            } else {
                ApiResponse::notFound('Invalid endpoint or missing ID');
            }
            break;
            
        default:
            ApiResponse::error('Method not allowed', [], 405);
            break;
    }
    
} catch (Exception $e) {
    ApiResponse::serverError('Server error: ' . $e->getMessage());
}
?>

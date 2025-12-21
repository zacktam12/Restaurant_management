<?php
/**
 * Taxis API
 * Handles taxi service operations for Group 4 and 8
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

// Mock Taxi class for demonstration (in real implementation, this would connect to database)
class TaxiManager {
    private $taxis = [];
    private $bookings = [];
    
    public function __construct() {
        // Initialize with sample data
        $this->taxis = [
            1 => [
                'id' => 1,
                'name' => 'Premium Taxi Service',
                'description' => '24/7 reliable transportation with professional drivers',
                'company' => 'Premium Cabs Inc.',
                'phone' => '+1-555-0101',
                'email' => 'book@premiumtaxis.com',
                'rating' => 4.5,
                'base_fare' => 3.00,
                'per_km_rate' => 2.50,
                'per_minute_rate' => 0.50,
                'vehicle_types' => [
                    ['type' => 'Sedan', 'capacity' => 4, 'base_fare' => 3.00, 'per_km' => 2.50],
                    ['type' => 'SUV', 'capacity' => 6, 'base_fare' => 5.00, 'per_km' => 3.50],
                    ['type' => 'Van', 'capacity' => 8, 'base_fare' => 7.00, 'per_km' => 4.50]
                ],
                'services' => ['Airport Transfer', 'City Tours', 'Hourly Hire', 'Long Distance'],
                'payment_methods' => ['Cash', 'Credit Card', 'Mobile Pay'],
                'features' => ['GPS Tracking', 'AC', 'Music System', 'WiFi'],
                'operating_hours' => '24/7',
                'service_areas' => ['City Center', 'Airport', 'Suburbs', 'Industrial Zone'],
                'created_at' => '2023-01-01 10:00:00',
                'updated_at' => '2023-01-01 10:00:00'
            ],
            2 => [
                'id' => 2,
                'name' => 'Economy Rides',
                'description' => 'Affordable transportation option for budget-conscious travelers',
                'company' => 'Economy Transport Co.',
                'phone' => '+1-555-0202',
                'email' => 'info@economyrides.com',
                'rating' => 4.0,
                'base_fare' => 2.00,
                'per_km_rate' => 1.50,
                'per_minute_rate' => 0.30,
                'vehicle_types' => [
                    ['type' => 'Hatchback', 'capacity' => 4, 'base_fare' => 2.00, 'per_km' => 1.50],
                    ['type' => 'Sedan', 'capacity' => 4, 'base_fare' => 2.50, 'per_km' => 1.80]
                ],
                'services' => ['Point to Point', 'Airport Drop'],
                'payment_methods' => ['Cash', 'Mobile Pay'],
                'features' => ['GPS Tracking', 'AC'],
                'operating_hours' => '06:00 - 23:00',
                'service_areas' => ['City Center', 'Suburbs'],
                'created_at' => '2023-01-02 11:00:00',
                'updated_at' => '2023-01-02 11:00:00'
            ]
        ];
        
        // Initialize sample bookings
        $this->bookings = [
            1 => [
                'booking_id' => 'TX1001',
                'taxi_id' => 1,
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'customer_phone' => '+1-555-1234',
                'pickup_location' => '123 Main Street',
                'pickup_coordinates' => ['lat' => 40.7128, 'lng' => -74.0060],
                'destination' => '456 Oak Avenue',
                'destination_coordinates' => ['lat' => 40.7580, 'lng' => -73.9855],
                'pickup_time' => '2023-12-21 14:30:00',
                'vehicle_type' => 'Sedan',
                'distance_km' => 15.5,
                'estimated_duration_minutes' => 25,
                'base_fare' => 3.00,
                'distance_fare' => 38.75,
                'time_fare' => 12.50,
                'total_fare' => 54.25,
                'payment_method' => 'Credit Card',
                'status' => 'confirmed',
                'driver_name' => 'Mike Johnson',
                'driver_phone' => '+1-555-9999',
                'vehicle_plate' => 'ABC-123',
                'created_at' => '2023-12-20 10:00:00'
            ]
        ];
    }
    
    public function getAllTaxis($search = null, $serviceArea = null, $vehicleType = null, $page = 1, $perPage = 10) {
        $filtered = $this->taxis;
        
        // Apply filters
        if ($search) {
            $filtered = array_filter($filtered, function($taxi) use ($search) {
                return stripos($taxi['name'], $search) !== false || 
                       stripos($taxi['description'], $search) !== false ||
                       stripos($taxi['company'], $search) !== false;
            });
        }
        
        if ($serviceArea) {
            $filtered = array_filter($filtered, function($taxi) use ($serviceArea) {
                return in_array($serviceArea, $taxi['service_areas']);
            });
        }
        
        if ($vehicleType) {
            $filtered = array_filter($filtered, function($taxi) use ($vehicleType) {
                foreach ($taxi['vehicle_types'] as $type) {
                    if ($type['type'] === $vehicleType) {
                        return true;
                    }
                }
                return false;
            });
        }
        
        // Apply pagination
        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $paged = array_slice($filtered, $offset, $perPage, true);
        
        return [
            'taxis' => array_values($paged),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total / $perPage)
        ];
    }
    
    public function getTaxiById($id) {
        return isset($this->taxis[$id]) ? $this->taxis[$id] : null;
    }
    
    public function createTaxi($name, $description, $company, $phone, $email, $rating, $baseFare, $perKmRate, $perMinuteRate, $vehicleTypes, $services, $paymentMethods, $features, $operatingHours, $serviceAreas) {
        $newId = max(array_keys($this->taxis)) + 1;
        
        $this->taxis[$newId] = [
            'id' => $newId,
            'name' => $name,
            'description' => $description,
            'company' => $company,
            'phone' => $phone,
            'email' => $email,
            'rating' => $rating,
            'base_fare' => $baseFare,
            'per_km_rate' => $perKmRate,
            'per_minute_rate' => $perMinuteRate,
            'vehicle_types' => $vehicleTypes,
            'services' => $services,
            'payment_methods' => $paymentMethods,
            'features' => $features,
            'operating_hours' => $operatingHours,
            'service_areas' => $serviceAreas,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->taxis[$newId];
    }
    
    public function updateTaxi($id, $name, $description, $company, $phone, $email, $rating, $baseFare, $perKmRate, $perMinuteRate, $vehicleTypes, $services, $paymentMethods, $features, $operatingHours, $serviceAreas) {
        if (!isset($this->taxis[$id])) {
            return null;
        }
        
        $this->taxis[$id] = array_merge($this->taxis[$id], [
            'name' => $name,
            'description' => $description,
            'company' => $company,
            'phone' => $phone,
            'email' => $email,
            'rating' => $rating,
            'base_fare' => $baseFare,
            'per_km_rate' => $perKmRate,
            'per_minute_rate' => $perMinuteRate,
            'vehicle_types' => $vehicleTypes,
            'services' => $services,
            'payment_methods' => $paymentMethods,
            'features' => $features,
            'operating_hours' => $operatingHours,
            'service_areas' => $serviceAreas,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->taxis[$id];
    }
    
    public function deleteTaxi($id) {
        if (!isset($this->taxis[$id])) {
            return false;
        }
        
        unset($this->taxis[$id]);
        return true;
    }
    
    public function calculateFare($taxiId, $distanceKm, $durationMinutes, $vehicleType = null) {
        $taxi = $this->getTaxiById($taxiId);
        if (!$taxi) {
            return null;
        }
        
        // Find vehicle type pricing
        $baseFare = $taxi['base_fare'];
        $perKmRate = $taxi['per_km_rate'];
        
        if ($vehicleType) {
            foreach ($taxi['vehicle_types'] as $type) {
                if ($type['type'] === $vehicleType) {
                    $baseFare = $type['base_fare'];
                    $perKmRate = $type['per_km'];
                    break;
                }
            }
        }
        
        $distanceFare = $distanceKm * $perKmRate;
        $timeFare = $durationMinutes * $taxi['per_minute_rate'];
        $totalFare = $baseFare + $distanceFare + $timeFare;
        
        return [
            'base_fare' => $baseFare,
            'distance_km' => $distanceKm,
            'distance_fare' => $distanceFare,
            'duration_minutes' => $durationMinutes,
            'time_fare' => $timeFare,
            'total_fare' => round($totalFare, 2)
        ];
    }
    
    public function createBooking($taxiId, $customerName, $customerEmail, $customerPhone, $pickupLocation, $pickupCoordinates, $destination, $destinationCoordinates, $pickupTime, $vehicleType, $paymentMethod, $specialRequests = null) {
        $taxi = $this->getTaxiById($taxiId);
        if (!$taxi) {
            return null;
        }
        
        $bookingId = 'TX' . strtoupper(uniqid());
        
        // Calculate distance and duration (mock calculation)
        $distanceKm = $this->calculateDistance($pickupCoordinates, $destinationCoordinates);
        $durationMinutes = max(15, $distanceKm * 2); // Rough estimate
        
        // Calculate fare
        $fare = $this->calculateFare($taxiId, $distanceKm, $durationMinutes, $vehicleType);
        
        // Assign mock driver
        $driver = $this->assignDriver($taxiId);
        
        $booking = [
            'booking_id' => $bookingId,
            'taxi_id' => $taxiId,
            'taxi_name' => $taxi['name'],
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'pickup_location' => $pickupLocation,
            'pickup_coordinates' => $pickupCoordinates,
            'destination' => $destination,
            'destination_coordinates' => $destinationCoordinates,
            'pickup_time' => $pickupTime,
            'vehicle_type' => $vehicleType,
            'distance_km' => $distanceKm,
            'estimated_duration_minutes' => $durationMinutes,
            'base_fare' => $fare['base_fare'],
            'distance_fare' => $fare['distance_fare'],
            'time_fare' => $fare['time_fare'],
            'total_fare' => $fare['total_fare'],
            'payment_method' => $paymentMethod,
            'special_requests' => $specialRequests,
            'status' => 'confirmed',
            'driver_name' => $driver['name'],
            'driver_phone' => $driver['phone'],
            'vehicle_plate' => $driver['plate'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->bookings[] = $booking;
        return $booking;
    }
    
    public function getBookings($customerId = null, $status = null, $page = 1, $perPage = 10) {
        $filtered = $this->bookings;
        
        if ($customerId !== null) {
            $filtered = array_filter($filtered, function($booking) use ($customerId) {
                return $booking['customer_email'] === $customerId;
            });
        }
        
        if ($status !== null) {
            $filtered = array_filter($filtered, function($booking) use ($status) {
                return $booking['status'] === $status;
            });
        }
        
        // Apply pagination
        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $paged = array_slice($filtered, $offset, $perPage, true);
        
        return [
            'bookings' => array_values($paged),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total / $perPage)
        ];
    }
    
    private function calculateDistance($pickup, $destination) {
        // Simple distance calculation (in real implementation, use Google Maps API)
        $latDiff = abs($pickup['lat'] - $destination['lat']);
        $lngDiff = abs($pickup['lng'] - $destination['lng']);
        return sqrt($latDiff * $latDiff + $lngDiff * $lngDiff) * 111; // Rough km conversion
    }
    
    private function assignDriver($taxiId) {
        // Mock driver assignment
        $drivers = [
            ['name' => 'Mike Johnson', 'phone' => '+1-555-9999', 'plate' => 'ABC-123'],
            ['name' => 'Sarah Williams', 'phone' => '+1-555-8888', 'plate' => 'XYZ-789'],
            ['name' => 'Tom Brown', 'phone' => '+1-555-7777', 'plate' => 'DEF-456']
        ];
        
        return $drivers[array_rand($drivers)];
    }
    
    public function updateBookingStatus($bookingId, $status) {
        foreach ($this->bookings as &$booking) {
            if ($booking['booking_id'] === $bookingId) {
                $booking['status'] = $status;
                $booking['updated_at'] = date('Y-m-d H:i:s');
                return $booking;
            }
        }
        return null;
    }
    
    public function cancelBooking($bookingId) {
        return $this->updateBookingStatus($bookingId, 'cancelled');
    }
}

$taxiManager = new TaxiManager();

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
            if ($endpoint === 'taxis') {
                if ($id) {
                    $taxi = $taxiManager->getTaxiById($id);
                    if ($taxi) {
                        ApiResponse::success($taxi, 'Taxi service retrieved successfully');
                    } else {
                        ApiResponse::notFound('Taxi service not found');
                    }
                } else {
                    // Get all taxis with filters and pagination
                    $search = isset($_GET['search']) ? $_GET['search'] : null;
                    $serviceArea = isset($_GET['service_area']) ? $_GET['service_area'] : null;
                    $vehicleType = isset($_GET['vehicle_type']) ? $_GET['vehicle_type'] : null;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
                    
                    $result = $taxiManager->getAllTaxis($search, $serviceArea, $vehicleType, $page, $perPage);
                    ApiResponse::paginated(
                        $result['taxis'],
                        $result['total'],
                        $result['page'],
                        $result['per_page'],
                        'Taxi services retrieved successfully'
                    );
                }
            } elseif ($endpoint === 'bookings') {
                // Get bookings with filters and pagination
                $customerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
                
                $result = $taxiManager->getBookings($customerId, $status, $page, $perPage);
                ApiResponse::paginated(
                    $result['bookings'],
                    $result['total'],
                    $result['page'],
                    $result['per_page'],
                    'Bookings retrieved successfully'
                );
            } elseif ($endpoint === 'fare' && $id) {
                $distanceKm = isset($_GET['distance_km']) ? (float)$_GET['distance_km'] : null;
                $durationMinutes = isset($_GET['duration_minutes']) ? (int)$_GET['duration_minutes'] : null;
                $vehicleType = isset($_GET['vehicle_type']) ? $_GET['vehicle_type'] : null;
                
                if ($distanceKm === null || $durationMinutes === null) {
                    ApiResponse::validationError([
                        'distance_km' => 'Distance is required',
                        'duration_minutes' => 'Duration is required'
                    ]);
                }
                
                $fare = $taxiManager->calculateFare($id, $distanceKm, $durationMinutes, $vehicleType);
                if ($fare) {
                    ApiResponse::success($fare, 'Fare calculated successfully');
                } else {
                    ApiResponse::notFound('Taxi service not found');
                }
            } else {
                ApiResponse::notFound('Invalid endpoint');
            }
            break;
            
        case 'POST':
            if ($endpoint === 'taxis') {
                // Create new taxi service
                $required = ['name', 'description', 'company', 'phone', 'email', 'rating', 'base_fare', 'per_km_rate', 'per_minute_rate', 'vehicle_types', 'services', 'payment_methods', 'features', 'operating_hours', 'service_areas'];
                $errors = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    }
                }
                
                if (!empty($errors)) {
                    ApiResponse::validationError($errors);
                }
                
                $taxi = $taxiManager->createTaxi(
                    $input['name'],
                    $input['description'],
                    $input['company'],
                    $input['phone'],
                    $input['email'],
                    $input['rating'],
                    $input['base_fare'],
                    $input['per_km_rate'],
                    $input['per_minute_rate'],
                    $input['vehicle_types'],
                    $input['services'],
                    $input['payment_methods'],
                    $input['features'],
                    $input['operating_hours'],
                    $input['service_areas']
                );
                
                ApiResponse::created($taxi, 'Taxi service created successfully');
            } elseif ($endpoint === 'bookings') {
                // Create taxi booking
                $required = ['taxi_id', 'customer_name', 'customer_email', 'customer_phone', 'pickup_location', 'pickup_coordinates', 'destination', 'destination_coordinates', 'pickup_time', 'vehicle_type', 'payment_method'];
                $errors = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    }
                }
                
                if (!empty($errors)) {
                    ApiResponse::validationError($errors);
                }
                
                $booking = $taxiManager->createBooking(
                    $input['taxi_id'],
                    $input['customer_name'],
                    $input['customer_email'],
                    $input['customer_phone'],
                    $input['pickup_location'],
                    $input['pickup_coordinates'],
                    $input['destination'],
                    $input['destination_coordinates'],
                    $input['pickup_time'],
                    $input['vehicle_type'],
                    $input['payment_method'],
                    $input['special_requests'] ?? null
                );
                
                if ($booking) {
                    ApiResponse::created($booking, 'Taxi booking created successfully');
                } else {
                    ApiResponse::error('Failed to create booking', [], 400);
                }
            } else {
                ApiResponse::notFound('Invalid endpoint');
            }
            break;
            
        case 'PUT':
            if ($endpoint === 'taxis' && $id) {
                // Update taxi service
                $required = ['name', 'description', 'company', 'phone', 'email', 'rating', 'base_fare', 'per_km_rate', 'per_minute_rate', 'vehicle_types', 'services', 'payment_methods', 'features', 'operating_hours', 'service_areas'];
                $errors = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    }
                }
                
                if (!empty($errors)) {
                    ApiResponse::validationError($errors);
                }
                
                $taxi = $taxiManager->updateTaxi(
                    $id,
                    $input['name'],
                    $input['description'],
                    $input['company'],
                    $input['phone'],
                    $input['email'],
                    $input['rating'],
                    $input['base_fare'],
                    $input['per_km_rate'],
                    $input['per_minute_rate'],
                    $input['vehicle_types'],
                    $input['services'],
                    $input['payment_methods'],
                    $input['features'],
                    $input['operating_hours'],
                    $input['service_areas']
                );
                
                if ($taxi) {
                    ApiResponse::success($taxi, 'Taxi service updated successfully');
                } else {
                    ApiResponse::notFound('Taxi service not found');
                }
            } elseif ($endpoint === 'bookings') {
                // Update booking status
                if (!isset($input['status'])) {
                    ApiResponse::validationError(['status' => 'Status is required']);
                }
                
                $booking = $taxiManager->updateBookingStatus($id, $input['status']);
                if ($booking) {
                    ApiResponse::success($booking, 'Booking status updated successfully');
                } else {
                    ApiResponse::notFound('Booking not found');
                }
            } else {
                ApiResponse::notFound('Invalid endpoint or missing ID');
            }
            break;
            
        case 'DELETE':
            if ($endpoint === 'taxis' && $id) {
                // Delete taxi service
                $success = $taxiManager->deleteTaxi($id);
                if ($success) {
                    ApiResponse::noContent();
                } else {
                    ApiResponse::notFound('Taxi service not found');
                }
            } elseif ($endpoint === 'bookings') {
                // Cancel booking
                $booking = $taxiManager->cancelBooking($id);
                if ($booking) {
                    ApiResponse::success($booking, 'Booking cancelled successfully');
                } else {
                    ApiResponse::notFound('Booking not found');
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

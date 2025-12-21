<?php
/**
 * Bookings API
 * Handles external service booking operations
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
require_once '../backend/Booking.php';

$bookingManager = new Booking();

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
                // Get specific booking
                $booking = $bookingManager->getBookingById($id);
                if ($booking) {
                    echo json_encode([
                        'success' => true,
                        'data' => $booking
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Booking not found'
                    ]);
                }
            } else {
                // Get bookings by customer, service type, or status
                $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
                $serviceType = isset($_GET['service_type']) ? $_GET['service_type'] : null;
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                
                if ($customerId) {
                    $bookings = $bookingManager->getBookingsByCustomer($customerId);
                } else if ($serviceType) {
                    $bookings = $bookingManager->getBookingsByServiceType($serviceType);
                } else if ($status) {
                    $bookings = $bookingManager->getBookingsByStatus($status);
                } else {
                    // Return empty array if no filters
                    $bookings = [];
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $bookings
                ]);
            }
            break;
            
        case 'POST':
            // Create new booking
            if (!isset($input['service_type']) || !isset($input['service_id']) || !isset($input['customer_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Service type, service ID, and customer ID are required'
                ]);
                exit();
            }
            
            $result = $bookingManager->createBooking(
                $input['service_type'],
                $input['service_id'],
                $input['customer_id'],
                isset($input['date']) ? $input['date'] : null,
                isset($input['time']) ? $input['time'] : null,
                isset($input['guests']) ? $input['guests'] : null,
                isset($input['special_requests']) ? $input['special_requests'] : null
            );
            
            echo json_encode($result);
            break;
            
        case 'PUT':
            // Update booking
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking ID is required'
                ]);
                exit();
            }
            
            // Check if we're updating status or details
            if (isset($input['status'])) {
                // Update status
                $result = $bookingManager->updateBookingStatus($id, $input['status']);
            } else if (isset($input['date']) && isset($input['time']) && isset($input['guests'])) {
                // Update details
                $result = $bookingManager->updateBooking(
                    $id,
                    $input['date'],
                    $input['time'],
                    $input['guests'],
                    isset($input['special_requests']) ? $input['special_requests'] : null
                );
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Either status or date/time/guests must be provided'
                ]);
                exit();
            }
            
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete booking
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking ID is required'
                ]);
                exit();
            }
            
            $result = $bookingManager->deleteBooking($id);
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

$bookingManager->close();
?>
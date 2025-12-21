<?php
/**
 * Reservations API
 * Handles restaurant reservation operations
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
require_once '../backend/Reservation.php';

$reservationManager = new Reservation();

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
            
        case 'POST':
            // Create new reservation
            if (!isset($input['restaurant_id']) || !isset($input['customer_name']) || 
                !isset($input['customer_email']) || !isset($input['customer_phone']) ||
                !isset($input['date']) || !isset($input['time']) || !isset($input['guests'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Restaurant ID, customer name, email, phone, date, time, and guests are required'
                ]);
                exit();
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
            
        case 'PUT':
            // Update reservation
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Reservation ID is required'
                ]);
                exit();
            }
            
            // Check if we're updating status or details
            if (isset($input['status'])) {
                // Update status
                $result = $reservationManager->updateReservationStatus($id, $input['status']);
            } else if (isset($input['date']) && isset($input['time']) && isset($input['guests'])) {
                // Update details
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
                    'message' => 'Either status or date/time/guests must be provided'
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

$reservationManager->close();
?>
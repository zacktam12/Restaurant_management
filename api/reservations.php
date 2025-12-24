<?php
/**
 * Reservation API Endpoint
 * Handles REST API requests for reservations
 */

require_once '../backend/config.php';
require_once '../backend/Reservation.php';
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
$reservationManager = new Reservation();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $reservation = $reservationManager->getReservationById($id);
            if ($reservation) {
                ApiResponse::sendSuccess($reservation, 'Reservation retrieved successfully');
            } else {
                ApiResponse::sendError('Reservation not found', 404);
            }
        } else if (isset($_GET['restaurant_id'])) {
            $reservations = $reservationManager->getReservationsByRestaurant($_GET['restaurant_id']);
            ApiResponse::sendSuccess($reservations, 'Restaurant reservations retrieved');
        } else if (isset($_GET['customer_email'])) {
            $reservations = $reservationManager->getReservationsByCustomer($_GET['customer_email']);
            ApiResponse::sendSuccess($reservations, 'Customer reservations retrieved');
        } else if (isset($_GET['date'])) {
            $reservations = $reservationManager->getReservationsByDate($_GET['date']);
            ApiResponse::sendSuccess($reservations, 'Date reservations retrieved');
        } else if (isset($_GET['status'])) {
            $reservations = $reservationManager->getReservationsByStatus($_GET['status']);
            ApiResponse::sendSuccess($reservations, 'Status reservations retrieved');
        } else {
            $reservations = $reservationManager->getAllReservations();
            ApiResponse::sendSuccess($reservations, 'All reservations retrieved');
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            ApiResponse::sendError('Invalid JSON input', 400);
        }
        
        $required = ['restaurant_id', 'customer_name', 'customer_email', 'customer_phone', 'date', 'time', 'guests'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                ApiResponse::sendError("Missing required field: {$field}", 400);
            }
        }
        
        $result = $reservationManager->createReservation(
            $input['restaurant_id'],
            $input['customer_name'],
            $input['customer_email'],
            $input['customer_phone'],
            $input['date'],
            $input['time'],
            $input['guests'],
            $input['special_requests'] ?? null
        );
        
        if ($result['success']) {
            ApiResponse::sendSuccess(['reservation_id' => $result['reservation_id']], $result['message'], 201);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            ApiResponse::sendError('Reservation ID is required', 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            ApiResponse::sendError('Invalid JSON input', 400);
        }
        
        if (isset($input['status'])) {
            $result = $reservationManager->updateStatus($id, $input['status']);
        } else {
            $result = $reservationManager->updateReservation(
                $id,
                $input['date'],
                $input['time'],
                $input['guests'],
                $input['special_requests'] ?? null
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
            ApiResponse::sendError('Reservation ID is required', 400);
        }
        
        $result = $reservationManager->deleteReservation($id);
        
        if ($result['success']) {
            ApiResponse::sendSuccess(null, $result['message']);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    default:
        ApiResponse::sendError('Method not allowed', 405);
}

$reservationManager->close();
?>

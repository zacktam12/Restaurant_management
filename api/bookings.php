<?php
/**
 * Bookings API Endpoint
 * REST API for booking operations
 */

require_once '../backend/config.php';
require_once '../backend/Booking.php';
require_once '../backend/ApiResponse.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$bookingManager = new Booking();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $booking = $bookingManager->getBookingById($_GET['id']);
            if ($booking) {
                ApiResponse::send(ApiResponse::success($booking));
            } else {
                ApiResponse::send(ApiResponse::error('Booking not found', 404), 404);
            }
        } elseif (isset($_GET['customer_id'])) {
            $bookings = $bookingManager->getBookingsByCustomer($_GET['customer_id']);
            ApiResponse::send(ApiResponse::success($bookings));
        } elseif (isset($_GET['service_type'])) {
            $bookings = $bookingManager->getBookingsByServiceType($_GET['service_type']);
            ApiResponse::send(ApiResponse::success($bookings));
        } else {
            $bookings = $bookingManager->getAllBookings();
            ApiResponse::send(ApiResponse::success($bookings));
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['service_type']) || !isset($input['service_id']) || !isset($input['customer_id'])) {
            ApiResponse::send(ApiResponse::error('Missing required fields', 400), 400);
        }
        
        $result = $bookingManager->createBooking(
            $input['service_type'],
            $input['service_id'],
            $input['customer_id'],
            $input['date'] ?? null,
            $input['time'] ?? null,
            $input['guests'] ?? null,
            $input['special_requests'] ?? null
        );
        
        if ($result['success']) {
            ApiResponse::send(ApiResponse::success(['booking_id' => $result['booking_id']], $result['message']), 201);
        } else {
            ApiResponse::send(ApiResponse::error($result['message'], 400), 400);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($_GET['id']) || !isset($input['status'])) {
            ApiResponse::send(ApiResponse::error('Missing booking ID or status', 400), 400);
        }
        
        $result = $bookingManager->updateStatus($_GET['id'], $input['status']);
        
        if ($result['success']) {
            ApiResponse::send(ApiResponse::success(null, $result['message']));
        } else {
            ApiResponse::send(ApiResponse::error($result['message'], 400), 400);
        }
        break;
        
    case 'DELETE':
        if (!isset($_GET['id'])) {
            ApiResponse::send(ApiResponse::error('Missing booking ID', 400), 400);
        }
        
        $result = $bookingManager->deleteBooking($_GET['id']);
        
        if ($result['success']) {
            ApiResponse::send(ApiResponse::success(null, $result['message']));
        } else {
            ApiResponse::send(ApiResponse::error($result['message'], 400), 400);
        }
        break;
        
    default:
        ApiResponse::send(ApiResponse::error('Method not allowed', 405), 405);
}

$bookingManager->close();
?>

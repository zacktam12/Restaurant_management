<?php
/**
 * Tour Participants API
 * API endpoint for viewing tour participants (Group 7 functionality)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Booking.php';

try {
    $bookingManager = new Booking();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    
    // Parse path info to get booking ID if present
    $pathParts = explode('/', trim($pathInfo, '/'));
    $bookingId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? (int)$pathParts[0] : null;
    
    switch ($method) {
        case 'GET':
            if ($bookingId) {
                // Get specific tour participants
                $participants = $bookingManager->getTourParticipants($bookingId);
                if ($participants) {
                    echo json_encode([
                        'success' => true,
                        'data' => $participants
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Tour booking not found or invalid booking type'
                    ]);
                }
            } else {
                // Get all tour bookings with participants
                $tourBookings = $bookingManager->getAllTourBookingsWithParticipants();
                echo json_encode([
                    'success' => true,
                    'data' => $tourBookings
                ]);
            }
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

if (isset($bookingManager)) {
    $bookingManager->close();
}
?>
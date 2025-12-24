<?php
/**
 * Rating API Endpoints
 * Handle restaurant ratings and reviews
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../backend/config.php';
require_once '../backend/Rating.php';
require_once '../backend/ApiResponse.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    ApiResponse::error('Unauthorized', 401);
    exit();
}

$ratingManager = new Rating();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 0);
            $review = $_POST['review'] ?? null;
            $customer_id = $_SESSION['user']['id'];
            
            if (!$restaurant_id || !$rating) {
                ApiResponse::error('Invalid input');
                exit();
            }
            
            $result = $ratingManager->addRating($restaurant_id, $customer_id, $rating, $review);
            
            if ($result['success']) {
                ApiResponse::success('Rating saved', $result);
            } else {
                ApiResponse::error($result['message']);
            }
        }
        break;
        
    case 'get':
        $restaurant_id = intval($_GET['restaurant_id'] ?? 0);
        $limit = intval($_GET['limit'] ?? 5);
        
        if (!$restaurant_id) {
            ApiResponse::error('Invalid restaurant');
            exit();
        }
        
        $ratings = $ratingManager->getRatings($restaurant_id, $limit);
        ApiResponse::success('Ratings retrieved', ['ratings' => $ratings]);
        break;
        
    case 'stats':
        $restaurant_id = intval($_GET['restaurant_id'] ?? 0);
        
        if (!$restaurant_id) {
            ApiResponse::error('Invalid restaurant');
            exit();
        }
        
        $stats = $ratingManager->getRatingStats($restaurant_id);
        ApiResponse::success('Stats retrieved', $stats);
        break;
        
    case 'my-rating':
        $restaurant_id = intval($_GET['restaurant_id'] ?? 0);
        $customer_id = $_SESSION['user']['id'];
        
        if (!$restaurant_id) {
            ApiResponse::error('Invalid restaurant');
            exit();
        }
        
        $rating = $ratingManager->getCustomerRating($restaurant_id, $customer_id);
        ApiResponse::success('Rating retrieved', ['rating' => $rating]);
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rating_id = intval($_POST['rating_id'] ?? 0);
            $customer_id = $_SESSION['user']['id'];
            
            if (!$rating_id) {
                ApiResponse::error('Invalid rating');
                exit();
            }
            
            $result = $ratingManager->deleteRating($rating_id, $customer_id);
            
            if ($result['success']) {
                ApiResponse::success('Rating deleted', $result);
            } else {
                ApiResponse::error($result['message']);
            }
        }
        break;
        
    default:
        ApiResponse::error('Invalid action');
}

$ratingManager->close();
?>

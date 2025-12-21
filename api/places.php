<?php
/**
 * Places API
 * Handles tourist places operations
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
require_once '../backend/Place.php';

$placeManager = new Place();

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
                // Get specific place
                $place = $placeManager->getPlaceById($id);
                if ($place) {
                    echo json_encode([
                        'success' => true,
                        'data' => $place
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Place not found'
                    ]);
                }
            } else {
                // Get all places or search
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                
                if ($search) {
                    $places = $placeManager->searchPlaces($search);
                } else if ($category) {
                    $places = $placeManager->filterByCategory($category);
                } else {
                    $places = $placeManager->getAllPlaces();
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $places
                ]);
            }
            break;
            
        case 'POST':
            // Create new place
            if (!isset($input['name']) || !isset($input['description']) || !isset($input['country']) || 
                !isset($input['city']) || !isset($input['category'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Name, description, country, city, and category are required'
                ]);
                exit();
            }
            
            $result = $placeManager->createPlace(
                $input['name'],
                $input['description'],
                $input['country'],
                $input['city'],
                isset($input['image']) ? $input['image'] : null,
                isset($input['rating']) ? $input['rating'] : 0.0,
                $input['category']
            );
            
            echo json_encode($result);
            break;
            
        case 'PUT':
            // Update place
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Place ID is required'
                ]);
                exit();
            }
            
            if (!isset($input['name']) || !isset($input['description']) || !isset($input['country']) || 
                !isset($input['city']) || !isset($input['category'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Name, description, country, city, and category are required'
                ]);
                exit();
            }
            
            $result = $placeManager->updatePlace(
                $id,
                $input['name'],
                $input['description'],
                $input['country'],
                $input['city'],
                isset($input['image']) ? $input['image'] : null,
                isset($input['rating']) ? $input['rating'] : 0.0,
                $input['category']
            );
            
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete place
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Place ID is required'
                ]);
                exit();
            }
            
            $result = $placeManager->deletePlace($id);
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

$placeManager->close();
?>
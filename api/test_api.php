<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../backend/ApiResponse.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // TestApiResponse functionality
            ApiResponse::success(['test' => 'API is working', 'timestamp' => date('Y-m-d H:i:s')], 'Test successful');
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            // Test validation
            if (!isset($input['test_param'])) {
                ApiResponse::validationError(['test_param' => 'Test parameter is required']);
            }
            
            ApiResponse::created($input, 'Test created successfully');
            break;
            
        default:
            ApiResponse::error('Method not allowed', [], 405);
            break;
    }
    
} catch (Exception $e) {
    ApiResponse::serverError('Server error: ' . $e->getMessage());
}
?>

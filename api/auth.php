<?php
/**
 * Authentication API
 * Handles user login and registration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../backend/config.php';
require_once '../backend/User.php';
require_once '../backend/ApiResponse.php';

$userManager = new User();

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    switch ($action) {
        case 'login':
            // Validate required fields
            if (!isset($input['email']) || !isset($input['password'])) {
                ApiResponse::validationError([
                    'email' => 'Email is required',
                    'password' => 'Password is required'
                ]);
            }
            
            $result = $userManager->login($input['email'], $input['password']);
            
            if ($result['success']) {
                // Start session
                session_start();
                $_SESSION['user'] = $result['user'];
                $_SESSION['logged_in'] = true;
                
                ApiResponse::success($result['user'], 'Login successful');
            } else {
                ApiResponse::unauthorized($result['message']);
            }
            break;
            
        case 'register':
            // Validate required fields
            $required = ['email', 'password', 'name'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $errors[$field] = ucfirst($field) . ' is required';
                }
            }
            
            // Validate email format
            if (isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
            
            // Validate password length
            if (isset($input['password']) && strlen($input['password']) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }
            
            if (!empty($errors)) {
                ApiResponse::validationError($errors);
            }
            
            $role = isset($input['role']) ? $input['role'] : 'customer';
            $phone = isset($input['phone']) ? $input['phone'] : null;
            $professionalDetails = isset($input['professional_details']) ? $input['professional_details'] : null;
            
            $result = $userManager->register(
                $input['email'],
                $input['password'],
                $input['name'],
                $role,
                $phone,
                $professionalDetails
            );
            
            if ($result['success']) {
                ApiResponse::created(null, $result['message']);
            } else {
                ApiResponse::error($result['message'], [], 400);
            }
            break;
            
        case 'logout':
            session_start();
            session_destroy();
            ApiResponse::success(null, 'Logged out successfully');
            break;
            
        case 'me':
            session_start();
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                ApiResponse::success($_SESSION['user'], 'User data retrieved');
            } else {
                ApiResponse::unauthorized('Not logged in');
            }
            break;
            
        default:
            ApiResponse::notFound('Invalid action');
            break;
    }
    
} catch (Exception $e) {
    ApiResponse::serverError('Server error: ' . $e->getMessage());
}

$userManager->close();
?>
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
            if (!isset($input['email']) || !isset($input['password']) || !isset($input['role'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email, password, and role are required'
                ]);
                exit();
            }
            
            $result = $userManager->login($input['email'], $input['password']);
            
            if ($result['success']) {
                // Start session
                session_start();
                $_SESSION['user'] = $result['user'];
                $_SESSION['logged_in'] = true;
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $result['user']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            break;
            
        case 'register':
            if (!isset($input['email']) || !isset($input['password']) || !isset($input['name'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email, password, and name are required'
                ]);
                exit();
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
            
            echo json_encode($result);
            break;
            
        case 'logout':
            session_start();
            session_destroy();
            
            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

$userManager->close();
?>
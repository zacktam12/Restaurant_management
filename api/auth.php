<?php
/**
 * Authentication API Endpoint
 * Handles login, register, and logout
 */

require_once '../backend/config.php';
require_once '../backend/User.php';
require_once '../backend/ApiResponse.php';

// Session is already started in config.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$userManager = new User();

switch ($action) {
    case 'login':
        if ($method !== 'POST') {
            ApiResponse::sendError('Method not allowed', 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['email']) || !isset($input['password'])) {
            ApiResponse::sendError('Email and password are required', 400);
        }
        
        $result = $userManager->login($input['email'], $input['password']);
        
        if ($result['success']) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = $result['user'];
            ApiResponse::sendSuccess($result['user'], 'Login successful');
        } else {
            ApiResponse::sendError($result['message'], 401);
        }
        break;
        
    case 'register':
        if ($method !== 'POST') {
            ApiResponse::sendError('Method not allowed', 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            ApiResponse::sendError('Invalid JSON input', 400);
        }
        
        $required = ['email', 'password', 'name'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                ApiResponse::sendError("Missing required field: {$field}", 400);
            }
        }
        
        $result = $userManager->register(
            $input['email'],
            $input['password'],
            $input['name'],
            $input['role'] ?? 'customer',
            $input['phone'] ?? null,
            $input['professional_details'] ?? null
        );
        
        if ($result['success']) {
            ApiResponse::sendSuccess(['user_id' => $result['user_id']], $result['message'], 201);
        } else {
            ApiResponse::sendError($result['message'], 400);
        }
        break;
        
    case 'logout':
        session_destroy();
        ApiResponse::sendSuccess(null, 'Logout successful');
        break;
        
    case 'profile':
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            ApiResponse::sendError('Not authenticated', 401);
        }
        
        if ($method === 'GET') {
            $user = $userManager->getUserById($_SESSION['user']['id']);
            if ($user) {
                ApiResponse::sendSuccess($user, 'Profile retrieved');
            } else {
                ApiResponse::sendError('User not found', 404);
            }
        } else if ($method === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $result = $userManager->updateProfile(
                $_SESSION['user']['id'],
                $input['name'],
                $input['phone'] ?? null,
                $input['professional_details'] ?? null
            );
            
            if ($result['success']) {
                $_SESSION['user']['name'] = $input['name'];
                $_SESSION['user']['phone'] = $input['phone'] ?? null;
                ApiResponse::sendSuccess(null, $result['message']);
            } else {
                ApiResponse::sendError($result['message'], 400);
            }
        }
        break;
        
    default:
        ApiResponse::sendError('Invalid action', 400);
}

$userManager->close();
?>

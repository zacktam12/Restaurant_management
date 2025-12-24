<?php
/**
 * Services API Endpoint
 * REST API for external services operations
 */

require_once '../backend/config.php';
require_once '../backend/Service.php';
require_once '../backend/ApiResponse.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$serviceManager = new Service();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $service = $serviceManager->getServiceById($_GET['id']);
            if ($service) {
                ApiResponse::send(ApiResponse::success($service));
            } else {
                ApiResponse::send(ApiResponse::error('Service not found', 404), 404);
            }
        } elseif (isset($_GET['type'])) {
            $services = $serviceManager->getServicesByType($_GET['type']);
            ApiResponse::send(ApiResponse::success($services));
        } else {
            $services = $serviceManager->getAllServices();
            ApiResponse::send(ApiResponse::success($services));
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['type']) || !isset($input['name']) || !isset($input['description']) || !isset($input['price'])) {
            ApiResponse::send(ApiResponse::error('Missing required fields', 400), 400);
        }
        
        $result = $serviceManager->addService(
            $input['type'],
            $input['name'],
            $input['description'],
            $input['price'],
            $input['image'] ?? null,
            $input['rating'] ?? 0.0
        );
        
        if ($result['success']) {
            ApiResponse::send(ApiResponse::success(['service_id' => $result['service_id']], $result['message']), 201);
        } else {
            ApiResponse::send(ApiResponse::error($result['message'], 400), 400);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($_GET['id'])) {
            ApiResponse::send(ApiResponse::error('Missing service ID', 400), 400);
        }
        
        $result = $serviceManager->updateService(
            $_GET['id'],
            $input['name'],
            $input['description'],
            $input['price'],
            $input['available'] ?? 1,
            $input['image'] ?? null
        );
        
        if ($result['success']) {
            ApiResponse::send(ApiResponse::success(null, $result['message']));
        } else {
            ApiResponse::send(ApiResponse::error($result['message'], 400), 400);
        }
        break;
        
    case 'DELETE':
        if (!isset($_GET['id'])) {
            ApiResponse::send(ApiResponse::error('Missing service ID', 400), 400);
        }
        
        $result = $serviceManager->deleteService($_GET['id']);
        
        if ($result['success']) {
            ApiResponse::send(ApiResponse::success(null, $result['message']));
        } else {
            ApiResponse::send(ApiResponse::error($result['message'], 400), 400);
        }
        break;
        
    default:
        ApiResponse::send(ApiResponse::error('Method not allowed', 405), 405);
}

$serviceManager->close();
?>

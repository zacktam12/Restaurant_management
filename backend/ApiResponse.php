<?php
/**
 * Standardized API Response Helper
 * Ensures consistent response format across all API endpoints
 */

class ApiResponse {
    /**
     * Send a success response
     * 
     * @param mixed $data The data to return
     * @param string $message Success message
     * @param array $meta Additional metadata (pagination, etc.)
     * @param int $httpCode HTTP status code
     */
    public static function success($data = null, $message = 'Success', $meta = [], $httpCode = 200) {
        http_response_code($httpCode);
        
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    /**
     * Send an error response
     * 
     * @param string $message Error message
     * @param array $errors Detailed validation errors
     * @param int $httpCode HTTP status code
     * @param mixed $data Additional error data
     */
    public static function error($message = 'Error', $errors = [], $httpCode = 400, $data = null) {
        http_response_code($httpCode);
        
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    /**
     * Send a validation error response
     * 
     * @param array $errors Validation errors
     * @param string $message Error message
     */
    public static function validationError($errors, $message = 'Validation failed') {
        self::error($message, $errors, 422);
    }
    
    /**
     * Send a not found error response
     * 
     * @param string $message Error message
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, [], 404);
    }
    
    /**
     * Send an unauthorized error response
     * 
     * @param string $message Error message
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, [], 401);
    }
    
    /**
     * Send a forbidden error response
     * 
     * @param string $message Error message
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, [], 403);
    }
    
    /**
     * Send a server error response
     * 
     * @param string $message Error message
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, [], 500);
    }
    
    /**
     * Send a paginated response
     * 
     * @param array $data The data array
     * @param int $total Total number of items
     * @param int $page Current page
     * @param int $perPage Items per page
     * @param string $message Success message
     */
    public static function paginated($data, $total, $page, $perPage, $message = 'Success') {
        $meta = [
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => ceil($total / $perPage)
            ]
        ];
        
        self::success($data, $message, $meta);
    }
    
    /**
     * Send a created response
     * 
     * @param mixed $data The created resource data
     * @param string $message Success message
     */
    public static function created($data = null, $message = 'Resource created successfully') {
        self::success($data, $message, [], 201);
    }
    
    /**
     * Send a no content response
     */
    public static function noContent() {
        http_response_code(204);
        exit();
    }
}
?>

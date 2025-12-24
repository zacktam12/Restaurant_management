<?php
/**
 * API Response Class
 * Handles standardized API responses
 */
class ApiResponse {
    
    /**
     * Success response
     */
    public static function success($data = null, $message = 'Success') {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message
        ];
    }
    
    /**
     * Error response
     */
    public static function error($message = 'Error', $code = 400) {
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
    }
    
    /**
     * Send JSON response
     */
    public static function send($response, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    /**
     * Send success response
     */
    public static function sendSuccess($data = null, $message = 'Success', $statusCode = 200) {
        self::send(self::success($data, $message), $statusCode);
    }
    
    /**
     * Send error response
     */
    public static function sendError($message = 'Error', $code = 400) {
        self::send(self::error($message, $code), $code);
    }
}
?>

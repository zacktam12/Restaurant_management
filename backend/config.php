<?php
/**
 * Configuration File
 * Contains database and application settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'restaurant_management');

// Application Settings
define('APP_NAME', 'Restaurant Management System');
define('APP_VERSION', '1.0');

// Security Settings
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_TIMEOUT', 3600); // 1 hour

// API Settings
define('API_BASE_URL', 'http://localhost/restaurant-management-system/api');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
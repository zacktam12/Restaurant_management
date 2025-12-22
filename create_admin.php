<?php
/**
 * Create Admin User Script
 * Script to create a new admin user with a known password
 */

require_once 'backend/config.php';
require_once 'backend/User.php';

$userManager = new User();

// Create a new admin user with a known password
$email = 'admin@test.com';
$password = 'admin123';
$name = 'Test Admin';
$role = 'admin';
$phone = '+1-555-0199';

$result = $userManager->register($email, $password, $name, $role, $phone);

echo "<h2>Create Admin User Result:</h2>\n";
echo "<pre>" . print_r($result, true) . "</pre>\n";

if ($result['success']) {
    echo "<p>Admin user created successfully!</p>\n";
    echo "<p>Email: " . htmlspecialchars($email) . "</p>\n";
    echo "<p>Password: " . htmlspecialchars($password) . "</p>\n";
    echo "<p>Role: " . htmlspecialchars($role) . "</p>\n";
} else {
    echo "<p>Failed to create admin user: " . htmlspecialchars($result['message']) . "</p>\n";
}

$userManager->close();
?>
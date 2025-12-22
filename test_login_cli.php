<?php
/**
 * Test Login Script (CLI)
 * Simple script to test user login functionality from command line
 */

require_once 'backend/config.php';
require_once 'backend/User.php';

// Test login with the newly created admin user
$email = 'admin@test.com';
$password = 'admin123';
$role = 'admin';

echo "Testing login for user: $email with role: $role\n";

$userManager = new User();
$result = $userManager->login($email, $password, $role);

echo "Login Result:\n";
print_r($result);

if ($result['success']) {
    echo "\nLogin successful!\n";
    echo "User data:\n";
    print_r($result['user']);
} else {
    echo "\nLogin failed: " . $result['message'] . "\n";
}

$userManager->close();
?>
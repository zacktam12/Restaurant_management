<?php
/**
 * Test Login Without Role Check
 * Script to test login without role verification
 */

require_once 'backend/config.php';
require_once 'backend/User.php';

// Test with admin@restaurant.com (from sample data)
$email = 'admin@restaurant.com';
$password = 'zack@1235'; // This is what's stored in the DB for this user
$role = ''; // Empty role to bypass role check

echo "Testing login for user: $email\n";

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

// Also test with our newly created user
echo "\n--- Testing with newly created user ---\n";
$email2 = 'admin@test.com';
$password2 = 'admin123';

echo "Testing login for user: $email2\n";

$result2 = $userManager->login($email2, $password2, '');

echo "Login Result:\n";
print_r($result2);

if ($result2['success']) {
    echo "\nLogin successful!\n";
    echo "User data:\n";
    print_r($result2['user']);
} else {
    echo "\nLogin failed: " . $result2['message'] . "\n";
}

$userManager->close();
?>
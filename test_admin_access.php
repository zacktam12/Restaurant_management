<?php
/**
 * Test Admin Access Script
 * Script to test direct access to admin dashboard
 */

session_start();

// Simulate a logged in admin user
$_SESSION['logged_in'] = true;
$_SESSION['user'] = [
    'id' => 14,
    'email' => 'admin@test.com',
    'name' => 'Test Admin',
    'role' => 'admin',
    'phone' => '+1-555-0199'
];

echo "Session set. Redirecting to admin dashboard...\n";

// Redirect to admin dashboard
header('Location: admin/index.php');
exit();
?>
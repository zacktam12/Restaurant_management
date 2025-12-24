<?php
// Fix for missing profile_image column
require_once __DIR__ . '/../backend/config.php';

// Manual connection to ensure isolation
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Checking 'users' table schema...\n";

// Check if users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck->num_rows == 0) {
    die("Error: 'users' table does not exist.\n");
}

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
if ($result->num_rows == 0) {
    echo "Column 'profile_image' missing. Attempting to add...\n";
    $sql = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER professional_details";
    if ($conn->query($sql) === TRUE) {
        echo "SUCCESS: Column 'profile_image' added to 'users' table.\n";
    } else {
        echo "ERROR adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'profile_image' already exists.\n";
}

$conn->close();
?>

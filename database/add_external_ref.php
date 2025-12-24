<?php
require_once __DIR__ . '/../backend/config.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "Checking schema...\n";
    
    // Check if column exists
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'external_reference'");
    if ($result && $result->num_rows == 0) {
        echo "Adding external_reference column...\n";
        $sql = "ALTER TABLE bookings ADD COLUMN external_reference VARCHAR(255) NULL AFTER status";
        if ($conn->query($sql)) {
            echo "Success: Column added.\n";
        } else {
            echo "Error: " . $conn->error . "\n";
        }
    } else {
        echo "Column already exists.\n";
    }
} else {
    echo "Database connection failed.\n";
}
?>

<?php
require_once 'backend/config.php';
require_once 'backend/Database.php';

$db = new Database();
$connection = $db->getConnection();

// SQL commands to insert role users
// Updated for the new 3-role system: admin, manager, customer
$commands = [
    "DELETE FROM users WHERE email IN ('admin@restaurant.com', 'manager@restaurant.com', 'customer@restaurant.com');",
    
    "INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
        'admin@restaurant.com',
        '$2y$10$FNA7YWU204BvCdBBgLgwm.RdP75i2IMIvyQknOxQUkWg0653tWzjq',
        'System Administrator',
        'admin',
        '+1-555-0100',
        'System administrator with full access rights'
    );",
    
    "INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
        'manager@restaurant.com',
        '$2y$10$wKYe1Cc5XDFol0gIEYgyR.9apAHAbK./oHizGLedaOz6oO0BVYOxa',
        'Restaurant Manager',
        'manager',
        '+1-555-0101',
        'Restaurant manager with business management access'
    );",
    
    "INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
        'customer@restaurant.com',
        '$2y$10$iR/YSVyk0CZuGd7wSYzdau13K64ZhJ617Rw9B78q.96.jNIX7BRHi',
        'Regular Customer',
        'customer',
        '+1-555-0102',
        NULL
    );"
];

// Execute each command
foreach ($commands as $sql) {
    $sql = trim($sql);
    if (!empty($sql)) {
        if ($connection->multi_query($sql)) {
            echo "Successfully executed: " . substr($sql, 0, 50) . "...\n";
            
            // Clear any remaining results
            do {
                if ($result = $connection->store_result()) {
                    $result->free();
                }
            } while ($connection->next_result());
        } else {
            echo "Error executing: " . $sql . "\n";
            echo "Error: " . $connection->error . "\n";
        }
    }
}

// Verify the inserted users
echo "\nVerifying inserted users:\n";
$verifySql = "SELECT id, email, name, role FROM users WHERE email IN ('admin@restaurant.com', 'manager@restaurant.com', 'customer@restaurant.com');";
$result = $connection->query($verifySql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Email: " . $row['email'] . ", Name: " . $row['name'] . ", Role: " . $row['role'] . "\n";
    }
} else {
    echo "Error verifying users: " . $connection->error . "\n";
}

$db->close();
?>
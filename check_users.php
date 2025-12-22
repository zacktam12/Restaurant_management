<?php
/**
 * Check Users Script
 * Simple script to check users in the database
 */

require_once 'backend/config.php';
require_once 'backend/Database.php';

try {
    $db = new Database();
    
    // Get all users
    $query = "SELECT id, email, name, role, phone FROM users ORDER BY created_at DESC";
    $users = $db->select($query);
    
    echo "<h2>Users in Database:</h2>\n";
    echo "<table border='1'>\n";
    echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Role</th><th>Phone</th></tr>\n";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . htmlspecialchars($user['phone'] ?? 'N/A') . "</td>";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
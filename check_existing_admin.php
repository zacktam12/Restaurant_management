<?php
/**
 * Check Existing Admin Users
 * Script to check if existing admin users can authenticate
 */

require_once 'backend/config.php';
require_once 'backend/Database.php';

try {
    $db = new Database();
    
    // Get all users with admin role
    $query = "SELECT id, email, name, role, password FROM users WHERE role = 'admin'";
    $users = $db->select($query);
    
    echo "<h2>Existing Admin Users:</h2>\n";
    echo "<table border='1'>\n";
    echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Role</th><th>Password Hash</th></tr>\n";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . htmlspecialchars($user['password']) . "</td>";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
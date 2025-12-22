<?php
/**
 * Test Login Script
 * Simple script to test user login functionality
 */

session_start();

require_once 'backend/config.php';
require_once 'backend/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    $userManager = new User();
    $result = $userManager->login($email, $password, $role);
    
    echo "<h2>Login Result:</h2>\n";
    echo "<pre>" . print_r($result, true) . "</pre>\n";
    
    if ($result['success']) {
        echo "<p>Login successful!</p>\n";
        echo "<p>User data: " . print_r($result['user'], true) . "</p>\n";
    } else {
        echo "<p>Login failed: " . htmlspecialchars($result['message']) . "</p>\n";
    }
} else {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test Login</title>
    </head>
    <body>
        <h2>Test Login</h2>
        <form method="POST">
            <div>
                <label>Email:</label>
                <input type="email" name="email" required><br><br>
            </div>
            <div>
                <label>Password:</label>
                <input type="password" name="password" required><br><br>
            </div>
            <div>
                <label>Role:</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="customer">Customer</option>
                    <option value="tourist">Tourist</option>
                    <option value="tour_guide">Tour Guide</option>
                </select><br><br>
            </div>
            <button type="submit">Test Login</button>
        </form>
    </body>
    </html>
    <?php
}
?>
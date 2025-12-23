<?php
// Script to generate SQL for all role users with proper password hashes
// Updated for the new 3-role system: admin, manager, customer

$passwords = ['admin123', 'manager123', 'customer123'];
$roles = ['admin', 'manager', 'customer'];
$emails = ['admin@restaurant.com', 'manager@restaurant.com', 'customer@restaurant.com'];
$names = ['System Administrator', 'Restaurant Manager', 'Regular Customer'];
$phones = ['+1-555-0100', '+1-555-0101', '+1-555-0102'];
$details = ['System administrator with full access rights', 'Restaurant manager with business management access', null];

echo "DELETE FROM users WHERE email IN ('" . implode("', '", $emails) . "');\n\n";

foreach ($passwords as $i => $pwd) {
    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
    $detailValue = $details[$i] ? "'" . addslashes($details[$i]) . "'" : 'NULL';
    
    echo "INSERT INTO users (email, password, name, role, phone, professional_details) VALUES (\n";
    echo "    '" . $emails[$i] . "',\n";
    echo "    '" . $hashedPwd . "',\n";
    echo "    '" . addslashes($names[$i]) . "',\n";
    echo "    '" . $roles[$i] . "',\n";
    echo "    '" . $phones[$i] . "',\n";
    echo "    " . $detailValue . "\n";
    echo ");\n\n";
}
?>
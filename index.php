<?php
/**
 * Main Entry Point
 * Redirects to landing page or appropriate dashboard
 */

// Session is handled by config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $role = $_SESSION['user']['role'];
    switch ($role) {
        case 'admin':
            header('Location: admin/index.php');
            break;
        case 'manager':
            header('Location: manager/index.php');
            break;
        default:
            header('Location: customer/index.php');
    }
} else {
    header('Location: landing.php');
}
exit();
?>

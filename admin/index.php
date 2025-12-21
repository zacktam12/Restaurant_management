<?php
/**
 * Admin Dashboard
 * Main admin interface for Restaurant Management System
 */

session_start();

// Check if user is logged in and is admin/manager
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'manager')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Booking.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Reservation.php';

$bookingManager = new Booking();
$restaurantManager = new Restaurant();
$reservationManager = new Reservation();

// Get statistics
$totalRestaurants = count($restaurantManager->getAllRestaurants());
$totalReservations = count($reservationManager->getAllReservations());
$totalBookings = count($bookingManager->getAllBookings());
$totalTourBookings = count($bookingManager->getBookingsByServiceType('tour'));

// Get recent tour bookings
$recentTourBookings = array_slice($bookingManager->getBookingsByServiceType('tour'), 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Restaurant Management System</title>
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/enhanced-styles.css" rel="stylesheet">
    <style>
        /* Custom icon replacement */
        .custom-icon {
            display: inline-block;
            width: 1em;
            height: 1em;
            background-color: currentColor;
            mask-size: cover;
            -webkit-mask-size: cover;
            vertical-align: -0.125em;
        }
        
        .icon-restaurant {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M1 2.5A1.5 1.5 0 0 1 2.5 1h2A1.5 1.5 0 0 1 6 2.5v1A1.5 1.5 0 0 1 4.5 5h-2A1.5 1.5 0 0 1 1 3.5v-1zM2.5 2a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2zm7 7.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5zm0-3v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5zm0-3v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5z'/%3E%3Cpath d='M15 15a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v6zM3 9v5h10V9H3z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M1 2.5A1.5 1.5 0 0 1 2.5 1h2A1.5 1.5 0 0 1 6 2.5v1A1.5 1.5 0 0 1 4.5 5h-2A1.5 1.5 0 0 1 1 3.5v-1zM2.5 2a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2zm7 7.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5zm0-3v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5zm0-3v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5z'/%3E%3Cpath d='M15 15a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v6zM3 9v5h10V9H3z'/%3E%3C/svg%3E");
        }
        
        .icon-speedometer2 {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 9a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 9zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 8.309a.5.5 0 0 1-.708-.708L9.77 4.67a.389.389 0 0 0 .02-.527zM8 13a.5.5 0 0 1 .5.5v.5a.5.5 0 0 1-1 0v-.5A.5.5 0 0 1 8 13zm7-6a.5.5 0 0 1-.5.5h-.5a.5.5 0 0 1 0-1h.5a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5H2a.5.5 0 0 1 0-1h.5A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-.915.914a.5.5 0 0 1-.708-.708l.915-.915a.5.5 0 0 1 .707 0zM9 8a.5.5 0 0 1-.5.5H7a.5.5 0 0 1 0-1h1.5A.5.5 0 0 1 9 8zm-5.5 4.5a.5.5 0 0 1 0 .707l-.915.914a.5.5 0 0 1-.708-.708l.915-.915a.5.5 0 0 1 .707 0zM4.5 12.5a.5.5 0 0 1 .707 0l.915.914a.5.5 0 0 1-.708.708l-.915-.915a.5.5 0 0 1 0-.707zM10 8a.5.5 0 0 1-.5.5H8a.5.5 0 0 1 0-1h1.5a.5.5 0 0 1 .5.5z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 9a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 9zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 8.309a.5.5 0 0 1-.708-.708L9.77 4.67a.389.389 0 0 0 .02-.527zM8 13a.5.5 0 0 1 .5.5v.5a.5.5 0 0 1-1 0v-.5A.5.5 0 0 1 8 13zm7-6a.5.5 0 0 1-.5.5h-.5a.5.5 0 0 1 0-1h.5a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5H2a.5.5 0 0 1 0-1h.5A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-.915.914a.5.5 0 0 1-.708-.708l.915-.915a.5.5 0 0 1 .707 0zM9 8a.5.5 0 0 1-.5.5H7a.5.5 0 0 1 0-1h1.5A.5.5 0 0 1 9 8zm-5.5 4.5a.5.5 0 0 1 0 .707l-.915.914a.5.5 0 0 1-.708-.708l.915-.915a.5.5 0 0 1 .707 0zM4.5 12.5a.5.5 0 0 1 .707 0l.915.914a.5.5 0 0 1-.708.708l-.915-.915a.5.5 0 0 1 0-.707zM10 8a.5.5 0 0 1-.5.5H8a.5.5 0 0 1 0-1h1.5a.5.5 0 0 1 .5.5z'/%3E%3C/svg%3E");
        }
        
        .icon-people {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022a.274.274 0 0 1-.014-.002L7.002 13z'/%3E%3Cpath fill-rule='evenodd' d='M8 4.41c.33-.15.68-.25 1.05-.25.37 0 .72.1 1.05.25.33.15.62.37.86.65.24.28.41.62.51 1.01.1.39.15.83.15 1.31 0 .48-.05.92-.15 1.31-.1.39-.27.73-.51 1.01-.24.28-.53.5-.86.65-.33.15-.68.25-1.05.25-.37 0-.72-.1-1.05-.25-.33-.15-.62-.37-.86-.65-.24-.28-.41-.62-.51-1.01-.1-.39-.15-.83-.15-1.31 0-.48.05-.92.15-1.31.1-.39.27-.73.51-1.01.24-.28.53-.5.86-.65zM6.5 7c0-.77.17-1.47.51-2.09.34-.62.82-1.1 1.45-1.44.63-.34 1.33-.51 2.09-.51.76 0 1.46.17 2.09.51.63.34 1.11.82 1.45 1.44.34.62.51 1.32.51 2.09 0 .77-.17 1.47-.51 2.09-.34.62-.82 1.1-1.45 1.44-.63.34-1.33.51-2.09.51-.76 0-1.46-.17-2.09-.51-.63-.34-1.11-.82-1.45-1.44-.34-.62-.51-1.32-.51-2.09zm3.5 3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022a.274.274 0 0 1-.014-.002L7.002 13z'/%3E%3Cpath fill-rule='evenodd' d='M8 4.41c.33-.15.68-.25 1.05-.25.37 0 .72.1 1.05.25.33.15.62.37.86.65.24.28.41.62.51 1.01.1.39.15.83.15 1.31 0 .48-.05.92-.15 1.31-.1.39-.27.73-.51 1.01-.24.28-.53.5-.86.65-.33.15-.68.25-1.05.25-.37 0-.72-.1-1.05-.25-.33-.15-.62-.37-.86-.65-.24-.28-.41-.62-.51-1.01-.1-.39-.15-.83-.15-1.31 0-.48.05-.92.15-1.31.1-.39.27-.73.51-1.01.24-.28.53-.5.86-.65zM6.5 7c0-.77.17-1.47.51-2.09.34-.62.82-1.1 1.45-1.44.63-.34 1.33-.51 2.09-.51.76 0 1.46.17 2.09.51.63.34 1.11.82 1.45 1.44.34.62.51 1.32.51 2.09 0 .77-.17 1.47-.51 2.09-.34.62-.82 1.1-1.45 1.44-.63.34-1.33.51-2.09.51-.76 0-1.46-.17-2.09-.51-.63-.34-1.11-.82-1.45-1.44-.34-.62-.51-1.32-.51-2.09zm3.5 3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5z'/%3E%3C/svg%3E");
        }
        
        .icon-shop {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.25 2.75a.5.5 0 0 1 .12.32v7.75a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4.42a.5.5 0 0 1 .12-.32L2.97 1.35zM1.5 5v7a.5.5 0 0 0 .5.5h12a.5.5 0 0 0 .5-.5V5L8.5 1 1.5 5zM3 7.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.25 2.75a.5.5 0 0 1 .12.32v7.75a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4.42a.5.5 0 0 1 .12-.32L2.97 1.35zM1.5 5v7a.5.5 0 0 0 .5.5h12a.5.5 0 0 0 .5-.5V5L8.5 1 1.5 5zM3 7.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z'/%3E%3C/svg%3E");
        }
        
        .icon-calendar-check {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z'/%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 1 0V1H3V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z'/%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 1 0V1H3V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z'/%3E%3C/svg%3E");
        }
        
        .icon-gear {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M8 4.754a3.32 3.32 0 0 0-3.32 3.32 3.32 3.32 0 0 0 3.32 3.32 3.32 3.32 0 0 0 3.32-3.32 3.32 3.32 0 0 0-3.32-3.32zm0 5.94a2.62 2.62 0 1 1 0-5.24 2.62 2.62 0 0 1 0 5.24zM.86 6.79a.5.5 0 0 1 .31-.71l2.1-.63a.5.5 0 0 1 .32.05l1.31.8a.5.5 0 0 1-.09.89l-1.3.82a.5.5 0 0 1-.55-.04L1.17 7.5a.5.5 0 0 1-.31-.71zm9.49-2.15a.5.5 0 0 1 .67.22l.63 1.09a.5.5 0 0 1-.22.67l-1.3.75a.5.5 0 0 1-.67-.22l-.63-1.09a.5.5 0 0 1 .22-.67l1.3-.75zm-6.3 7.54a.5.5 0 0 1 .71.31l.63 2.1a.5.5 0 0 1-.05.32l-.8 1.31a.5.5 0 0 1-.89-.09l-.82-1.3a.5.5 0 0 1 .04-.55l.83-1.3a.5.5 0 0 1 .31-.31zm5.43 1.31a.5.5 0 0 1 .22.67l-.75 1.3a.5.5 0 0 1-.67.22l-1.09-.63a.5.5 0 0 1-.22-.67l.75-1.3a.5.5 0 0 1 .67-.22l1.09.63zM8 .5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-1 0V1a.5.5 0 0 1 .5-.5z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M8 4.754a3.32 3.32 0 0 0-3.32 3.32 3.32 3.32 0 0 0 3.32 3.32 3.32 3.32 0 0 0 3.32-3.32 3.32 3.32 0 0 0-3.32-3.32zm0 5.94a2.62 2.62 0 1 1 0-5.24 2.62 2.62 0 0 1 0 5.24zM.86 6.79a.5.5 0 0 1 .31-.71l2.1-.63a.5.5 0 0 1 .32.05l1.31.8a.5.5 0 0 1-.09.89l-1.3.82a.5.5 0 0 1-.55-.04L1.17 7.5a.5.5 0 0 1-.31-.71zm9.49-2.15a.5.5 0 0 1 .67.22l.63 1.09a.5.5 0 0 1-.22.67l-1.3.75a.5.5 0 0 1-.67-.22l-.63-1.09a.5.5 0 0 1 .22-.67l1.3-.75zm-6.3 7.54a.5.5 0 0 1 .71.31l.63 2.1a.5.5 0 0 1-.05.32l-.8 1.31a.5.5 0 0 1-.89-.09l-.82-1.3a.5.5 0 0 1 .04-.55l.83-1.3a.5.5 0 0 1 .31-.31zm5.43 1.31a.5.5 0 0 1 .22.67l-.75 1.3a.5.5 0 0 1-.67.22l-1.09-.63a.5.5 0 0 1-.22-.67l.75-1.3a.5.5 0 0 1 .67-.22l1.09.63zM8 .5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-1 0V1a.5.5 0 0 1 .5-.5z'/%3E%3C/svg%3E");
        }
        
        .icon-graph-up {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M0 0h1v15h15v1H0V0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M0 0h1v15h15v1H0V0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07z'/%3E%3C/svg%3E");
        }
        
        .icon-key {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2zM2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2zM2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z'/%3E%3C/svg%3E");
        }
        
        .icon-diagram-3 {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M6 3.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1zM11 4a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h6zM2 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm12-9a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M6 3.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1zM11 4a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h6zM2 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm12-9a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z'/%3E%3C/svg%3E");
        }
        
        .fs-1 {
            font-size: 1.5rem !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="custom-icon icon-restaurant"></span> Restaurant Manager
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                    <span class="badge bg-secondary"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
                </span>
                <a class="btn btn-outline-light" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <span class="custom-icon icon-speedometer2"></span> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <span class="custom-icon icon-people"></span> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="restaurants.php">
                                <span class="custom-icon icon-shop"></span> Restaurants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservations.php">
                                <span class="custom-icon icon-calendar-check"></span> Reservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services.php">
                                <span class="custom-icon icon-gear"></span> External Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <span class="custom-icon icon-graph-up"></span> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="api_keys.php">
                                <span class="custom-icon icon-key"></span> API Keys
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="service_registry.php">
                                <span class="custom-icon icon-diagram-3"></span> Service Registry
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Restaurants</h5>
                                <h2><?php echo $totalRestaurants; ?></h2>
                                <span class="custom-icon icon-shop float-end fs-1"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Reservations</h5>
                                <h2><?php echo $totalReservations; ?></h2>
                                <span class="custom-icon icon-calendar-check float-end fs-1"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Total Bookings</h5>
                                <h2><?php echo $totalBookings; ?></h2>
                                <span class="custom-icon icon-calendar-check float-end fs-1"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Tour Bookings</h5>
                                <h2><?php echo $totalTourBookings; ?></h2>
                                <span class="custom-icon icon-diagram-3 float-end fs-1"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tour Bookings -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Tour Bookings</h5>
                                <a href="tour_participants.php" class="btn btn-sm btn-primary">View All Tour Participants</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentTourBookings)): ?>
                                <p class="text-muted text-center">No tour bookings yet</p>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Customer</th>
                                                <th>Tour ID</th>
                                                <th>Participants</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentTourBookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['id']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['customer_id']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['service_id']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['guests'] ?? 'N/A'); ?></td>
                                                <td><?php echo $booking['date'] ? date('M j, Y', strtotime($booking['date'])) : 'N/A'; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $booking['status'] == 'confirmed' ? 'success' : 
                                                             ($booking['status'] == 'pending' ? 'warning' : 
                                                             ($booking['status'] == 'cancelled' ? 'danger' : 'secondary')); ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Links</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="restaurants.php" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-shop me-2"></i>Manage Restaurants
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="reservations.php" class="btn btn-outline-success w-100">
                                            <i class="bi bi-calendar-check me-2"></i>View Reservations
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="services.php" class="btn btn-outline-warning w-100">
                                            <i class="bi bi-gear me-2"></i>External Services
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="reports.php" class="btn btn-outline-info w-100">
                                            <i class="bi bi-graph-up me-2"></i>Detailed Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$bookingManager->close();
$restaurantManager->close();
$reservationManager->close();
?>
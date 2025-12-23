<?php
/**
 * Admin Dashboard
 * Main admin interface for Restaurant Management System
 */

session_start();

// Check if user is logged in and has admin/manager access
require_once '../backend/Permission.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Require admin role only (managers have their own dashboard)
Permission::requireAdmin($_SESSION['user']['role']);

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
    <link href="../css/admin-dashboard-polish.css" rel="stylesheet">
    <link href="../css/admin-layout.css" rel="stylesheet">
    <link href="../css/admin-icons.css" rel="stylesheet">
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

    <!-- Sidebar -->
    <nav class="sidebar">
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
    <main class="main-content">
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
<?php
/**
 * Tourist Bookings Page
 * Tourist interface for viewing and managing their reservations
 */

session_start();

// Check if user is logged in and is tourist/customer
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'tourist' && $_SESSION['user']['role'] != 'customer')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Reservation.php';
require_once '../backend/Restaurant.php';

$reservationManager = new Reservation();
$restaurantManager = new Restaurant();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancel_reservation') {
    $result = $reservationManager->updateReservationStatus($_POST['id'], 'cancelled');
    
    if ($result['success']) {
        $message = "Reservation cancelled successfully!";
        $messageType = "success";
    } else {
        $message = "Error cancelling reservation: " . $result['message'];
        $messageType = "danger";
    }
}

// Get reservations for current user
$reservations = [];
$allReservations = $reservationManager->getAllReservations();

// Filter reservations by current user's email
foreach ($allReservations as $reservation) {
    if ($reservation['customer_email'] == $_SESSION['user']['email']) {
        $reservations[] = $reservation;
    }
}

// Separate upcoming and past reservations
$upcomingReservations = [];
$pastReservations = [];

foreach ($reservations as $reservation) {
    $reservationDate = strtotime($reservation['date']);
    if ($reservationDate >= strtotime('today') && $reservation['status'] != 'cancelled') {
        $upcomingReservations[] = $reservation;
    } else {
        $pastReservations[] = $reservation;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-compass"></i> Tourist Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-shop"></i> Restaurants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">
                            <i class="bi bi-calendar-check"></i> My Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="places.php">
                            <i class="bi bi-map"></i> Places to Visit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">
                            <i class="bi bi-gear"></i> Other Services
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                        <span class="badge bg-light text-dark"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
                    </span>
                    <a class="btn btn-outline-light" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Bookings</h1>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Upcoming Reservations -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Upcoming Reservations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingReservations)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <h4 class="mt-3">No upcoming reservations</h4>
                    <p class="text-muted">You don't have any upcoming restaurant reservations.</p>
                    <a href="index.php" class="btn btn-primary">Browse Restaurants</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Restaurant</th>
                                <th>Date & Time</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingReservations as $reservation): 
                                $restaurant = $restaurantManager->getRestaurantById($reservation['restaurant_id']);
                                $restaurantName = $restaurant ? $restaurant['name'] : 'Unknown';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($restaurantName); ?></strong><br>
                                    <small><?php echo htmlspecialchars($reservation['customer_email']); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($reservation['date'])); ?><br>
                                    <small><?php echo date('g:i A', strtotime($reservation['time'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($reservation['guests']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $reservation['status'] == 'confirmed' ? 'success' : 
                                             ($reservation['status'] == 'pending' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reservation['status'] != 'cancelled'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?')">
                                        <input type="hidden" name="action" value="cancel_reservation">
                                        <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Past Reservations -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Past Reservations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pastReservations)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-clock fs-1 text-muted"></i>
                    <h4 class="mt-3">No past reservations</h4>
                    <p class="text-muted">Your past restaurant reservations will appear here.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Restaurant</th>
                                <th>Date & Time</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastReservations as $reservation): 
                                $restaurant = $restaurantManager->getRestaurantById($reservation['restaurant_id']);
                                $restaurantName = $restaurant ? $restaurant['name'] : 'Unknown';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($restaurantName); ?></strong><br>
                                    <small><?php echo htmlspecialchars($reservation['customer_email']); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($reservation['date'])); ?><br>
                                    <small><?php echo date('g:i A', strtotime($reservation['time'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($reservation['guests']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $reservation['status'] == 'completed' ? 'success' : 
                                             ($reservation['status'] == 'cancelled' ? 'danger' : 'secondary'); ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$reservationManager->close();
$restaurantManager->close();
?>
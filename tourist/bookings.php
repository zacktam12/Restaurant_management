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

// Handle cancel confirmation
if (isset($_GET['confirm_cancel'])) {
    $reservationId = $_GET['confirm_cancel'];
    
    // We'll handle the actual cancellation through a GET request
}

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
} else if (isset($_GET['confirm_cancel'])) {
    // Handle cancel confirmation through GET parameters
    $reservationId = $_GET['confirm_cancel'];
    
    // Show confirmation message
    $reservation = $reservationManager->getReservationById($reservationId);
    if ($reservation) {
        $message = 'Are you sure you want to cancel your reservation for "' . htmlspecialchars($reservation['customer_name']) . '"?';
        $messageType = 'warning';
        $showCancelConfirmation = true;
    }
} else if (isset($_GET['cancel_confirmed']) && isset($_GET['id'])) {
    // Handle confirmed cancellation
    $result = $reservationManager->updateReservationStatus($_GET['id'], 'cancelled');
    
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
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/enhanced-styles.css" rel="stylesheet">
    <link href="../css/tourist-dashboard-polish.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Tourist Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            Restaurants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">
                            My Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="places.php">
                            Places to Visit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">
                            Other Services
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav ms-auto align-items-center d-none d-lg-flex">
                    <span class="navbar-text me-3">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                        <span class="badge"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
                    </span>
                    <a class="btn btn-outline-light" href="../logout.php">Logout</a>
                </div>
                <div class="navbar-nav d-lg-none mt-3 pt-3 border-top">
                    <span class="navbar-text mb-2">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                        <span class="badge"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
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
            <button type="button" class="btn-close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($showCancelConfirmation) && $showCancelConfirmation): ?>
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Confirm Cancellation</h4>
            <p><?php echo htmlspecialchars($message); ?></p>
            <hr>
            <div class="d-flex">
                <a href="bookings.php" class="btn btn-secondary">Cancel</a>
                <form method="GET" class="d-inline">
                    <input type="hidden" name="cancel_confirmed" value="1">
                    <input type="hidden" name="id" value="<?php echo $_GET['confirm_cancel']; ?>">
                    <button type="submit" class="btn btn-danger">Yes, Cancel Reservation</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upcoming Reservations -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Upcoming Reservations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingReservations)): ?>
                <div class="text-center py-4">
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
                                    <form method="GET">
                                        <input type="hidden" name="confirm_cancel" value="<?php echo $reservation['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Cancel
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
                <h5 class="mb-0">Past Reservations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pastReservations)): ?>
                <div class="text-center py-4">
                    <h4 class="mt-3">No past reservations</h4>
                    <p class="text-muted">Your past restaurant reservations will appear here.</p>
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

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$reservationManager->close();
$restaurantManager->close();
?>
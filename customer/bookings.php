<?php
/**
 * Customer Bookings Page
 * Customer interface for viewing and managing their reservations
 */

session_start();

// Check if user is logged in and is customer
require_once '../backend/Permission.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || !Permission::isCustomer($_SESSION['user']['role'])) {
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
    <link href="../css/admin-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="custom-icon icon-restaurant"></span> Customer Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse show" id="navbarNav">
                <div class="customer-navbar-content">
                    <ul class="navbar-nav customer-navbar-links">
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
                    <div class="customer-navbar-user">
                        <span class="navbar-text">
                            Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                            <span class="badge"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
                        </span>
                        <a class="btn btn-outline-light text-dark border-dark" href="../logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-white border-bottom py-5 mb-4 shadow-sm">
        <div class="container">
            <h1 class="fw-bold mb-2">My Reservations</h1>
            <p class="text-muted mb-0">Manage your upcoming and past dining experiences</p>
        </div>
    </div>

    <div class="container pb-5">

        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($showCancelConfirmation) && $showCancelConfirmation): ?>
        <div class="alert alert-warning shadow-sm" role="alert">
            <h4 class="alert-heading">Confirm Cancellation</h4>
            <p><?php echo htmlspecialchars($message); ?></p>
            <hr>
            <div class="d-flex gap-2">
                <a href="bookings.php" class="btn btn-light border">Keep Reservation</a>
                <form method="GET" class="d-inline">
                    <input type="hidden" name="cancel_confirmed" value="1">
                    <input type="hidden" name="id" value="<?php echo $_GET['confirm_cancel']; ?>">
                    <button type="submit" class="btn btn-danger">Yes, Cancel Reservation</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upcoming Reservations -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold text-primary">
                    <span class="custom-icon icon-calendar-check me-2" style="background-color: var(--bs-primary);"></span>
                    Upcoming Reservations
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingReservations)): ?>
                <div class="text-center py-5">
                    <div class="mb-3 text-muted opacity-50">
                        <span class="custom-icon icon-calendar-check" style="width: 48px; height: 48px; background-color: currentColor;"></span>
                    </div>
                    <h5 class="text-muted">No upcoming reservations</h5>
                    <p class="text-secondary small mb-4">You don't have any upcoming restaurant reservations.</p>
                    <a href="index.php" class="btn btn-primary px-4">Browse Restaurants</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Restaurant</th>
                                <th class="py-3">Date & Time</th>
                                <th class="py-3">Guests</th>
                                <th class="py-3">Status</th>
                                <th class="pe-4 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingReservations as $reservation): 
                                $restaurant = $restaurantManager->getRestaurantById($reservation['restaurant_id']);
                                $restaurantName = $restaurant ? $restaurant['name'] : 'Unknown';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo htmlspecialchars($restaurantName); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($reservation['customer_email']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo date('M j, Y', strtotime($reservation['date'])); ?></div>
                                    <div class="small text-muted"><?php echo date('g:i A', strtotime($reservation['time'])); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <span class="custom-icon icon-people me-1" style="width: 12px; height: 12px; vertical-align: -2px; background-color: currentColor;"></span>
                                        <?php echo htmlspecialchars($reservation['guests']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-<?php 
                                        echo $reservation['status'] == 'confirmed' ? 'success' : 
                                             ($reservation['status'] == 'pending' ? 'warning' : 'secondary'); ?> bg-opacity-25 text-<?php 
                                        echo $reservation['status'] == 'confirmed' ? 'success' : 
                                             ($reservation['status'] == 'pending' ? 'dark' : 'secondary'); ?> border border-<?php 
                                        echo $reservation['status'] == 'confirmed' ? 'success' : 
                                             ($reservation['status'] == 'pending' ? 'warning' : 'secondary'); ?> border-opacity-25">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php if ($reservation['status'] != 'cancelled'): ?>
                                    <form method="GET">
                                        <input type="hidden" name="confirm_cancel" value="<?php echo $reservation['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger hover-shadow">
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
        <div class="card border-0 shadow-sm opacity-75">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold text-secondary">
                    <span class="custom-icon icon-clock me-2" style="background-color: var(--bs-secondary);"></span>
                    Past Reservations
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pastReservations)): ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-0">No past reservations found.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Restaurant</th>
                                <th class="py-3">Date & Time</th>
                                <th class="py-3">Guests</th>
                                <th class="pe-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastReservations as $reservation): 
                                $restaurant = $restaurantManager->getRestaurantById($reservation['restaurant_id']);
                                $restaurantName = $restaurant ? $restaurant['name'] : 'Unknown';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo htmlspecialchars($restaurantName); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($reservation['customer_email']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo date('M j, Y', strtotime($reservation['date'])); ?></div>
                                    <div class="small text-muted"><?php echo date('g:i A', strtotime($reservation['time'])); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo htmlspecialchars($reservation['guests']); ?> guests
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <span class="badge rounded-pill bg-<?php 
                                        echo $reservation['status'] == 'completed' ? 'success' : 
                                             ($reservation['status'] == 'cancelled' ? 'danger' : 'secondary'); ?> bg-opacity-10 text-<?php 
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
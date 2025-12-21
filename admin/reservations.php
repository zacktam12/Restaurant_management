<?php
/**
 * Reservation Management Page
 * Admin interface for managing reservations
 */

session_start();

// Check if user is logged in and is admin/manager
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'manager')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Reservation.php';
require_once '../backend/Restaurant.php';

$reservationManager = new Reservation();
$restaurantManager = new Restaurant();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $result = $reservationManager->updateReservationStatus($_POST['id'], $_POST['status']);
                $message = $result['message'];
                break;
                
            case 'delete_reservation':
                $result = $reservationManager->deleteReservation($_POST['id']);
                $message = $result['message'];
                break;
        }
    }
}

// Get all reservations
$reservations = $reservationManager->getAllReservations();

// Get all restaurants for filter dropdown
$restaurants = $restaurantManager->getAllRestaurants();

// Filter by restaurant if specified
if (isset($_GET['restaurant_id']) && !empty($_GET['restaurant_id'])) {
    $reservations = $reservationManager->getReservationsByRestaurant($_GET['restaurant_id']);
}

// Filter by status if specified
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filteredReservations = [];
    foreach ($reservations as $reservation) {
        if ($reservation['status'] == $_GET['status']) {
            $filteredReservations[] = $reservation;
        }
    }
    $reservations = $filteredReservations;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../admin/index.php">
                <i class="bi bi-restaurant"></i> Restaurant Manager
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
                            <a class="nav-link" href="../admin/index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/users.php">
                                <i class="bi bi-people"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/restaurants.php">
                                <i class="bi bi-shop"></i> Restaurants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="../admin/reservations.php">
                                <i class="bi bi-calendar-check"></i> Reservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Reservation Management</h1>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="restaurant_filter" class="form-label">Filter by Restaurant</label>
                                <select class="form-select" id="restaurant_filter" name="restaurant_id">
                                    <option value="">All Restaurants</option>
                                    <?php foreach ($restaurants as $restaurant): ?>
                                    <option value="<?php echo $restaurant['id']; ?>" 
                                        <?php echo (isset($_GET['restaurant_id']) && $_GET['restaurant_id'] == $restaurant['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($restaurant['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="status_filter" class="form-label">Filter by Status</label>
                                <select class="form-select" id="status_filter" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                <a href="reservations.php" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reservations Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Restaurant</th>
                                        <th>Date & Time</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                        <th>Special Requests</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reservations)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No reservations found</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($reservations as $reservation): 
                                        // Get restaurant name
                                        $restaurant = $restaurantManager->getRestaurantById($reservation['restaurant_id']);
                                        $restaurantName = $restaurant ? $restaurant['name'] : 'Unknown';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($reservation['customer_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($reservation['customer_email']); ?></small><br>
                                            <small><?php echo htmlspecialchars($reservation['customer_phone']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($restaurantName); ?></td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($reservation['date'])); ?><br>
                                            <small><?php echo date('g:i A', strtotime($reservation['time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($reservation['guests']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $reservation['status'] == 'confirmed' ? 'success' : 
                                                     ($reservation['status'] == 'pending' ? 'warning' : 
                                                     ($reservation['status'] == 'cancelled' ? 'danger' : 'secondary')); ?>">
                                                <?php echo ucfirst($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($reservation['special_requests'] ?? 'None'); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                                <select name="status" class="form-select form-select-sm mb-1" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $reservation['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $reservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="cancelled" <?php echo $reservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    <option value="completed" <?php echo $reservation['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this reservation?')">
                                                <input type="hidden" name="action" value="delete_reservation">
                                                <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$reservationManager->close();
$restaurantManager->close();
?>
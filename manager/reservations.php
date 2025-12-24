<?php
/**
 * Reservation Management Page
 * Admin interface for managing reservations
 */

session_start();

// Check if user is logged in and is admin/manager
require_once '../backend/Permission.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Require manager/admin access
Permission::requireBusinessUser($_SESSION['user']['role']);

require_once '../backend/config.php';
require_once '../backend/Reservation.php';
require_once '../backend/Restaurant.php';

$reservationManager = new Reservation();
$restaurantManager = new Restaurant();

$currentUserRole = $_SESSION['user']['role'] ?? '';
$currentUserId = (int)($_SESSION['user']['id'] ?? 0);
$isManager = Permission::isManager($currentUserRole);

$restaurants = $isManager ? $restaurantManager->getRestaurantsForManager($currentUserId) : $restaurantManager->getAllRestaurants();
$allowedRestaurantIds = array_map(function($r) {
    return (int)$r['id'];
}, $restaurants);

// Handle delete confirmation
if (isset($_GET['confirm_delete'])) {
    $reservationId = $_GET['confirm_delete'];
    
    // We'll handle the actual deletion through a GET request
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                if ($isManager) {
                    $reservationToUpdate = $reservationManager->getReservationById((int)$_POST['id']);
                    if (!$reservationToUpdate || !in_array((int)$reservationToUpdate['restaurant_id'], $allowedRestaurantIds, true)) {
                        $message = 'You do not have permission to manage this reservation.';
                        $messageType = 'danger';
                        break;
                    }
                }
                $result = $reservationManager->updateReservationStatus($_POST['id'], $_POST['status']);
                $message = $result['message'];
                break;
                
            case 'update_reservation':
                $id = $_POST['id'] ?? '';
                $date = $_POST['date'] ?? '';
                $time = $_POST['time'] ?? '';
                $guests = $_POST['guests'] ?? '';
                $status = $_POST['status'] ?? '';
                $special_requests = $_POST['special_requests'] ?? '';

                if ($isManager) {
                    $reservationToUpdate = $reservationManager->getReservationById((int)$id);
                    if (!$reservationToUpdate || !in_array((int)$reservationToUpdate['restaurant_id'], $allowedRestaurantIds, true)) {
                        $message = 'You do not have permission to manage this reservation.';
                        $messageType = 'danger';
                        break;
                    }
                }
                
                if (empty($id) || empty($date) || empty($time) || empty($guests) || empty($status)) {
                    $message = 'Please fill in all required fields.';
                    $messageType = 'danger';
                } else {
                    $result = $reservationManager->updateReservation($id, $date, $time, $guests, $status, $special_requests);
                    
                    if ($result['success']) {
                        $message = 'Reservation updated successfully!';
                        $messageType = 'success';
                        // Clear edit mode
                        if (isset($_GET['edit_reservation'])) {
                            unset($_GET['edit_reservation']);
                        }
                    } else {
                        $message = $result['message'];
                        $messageType = 'danger';
                    }
                }
                break;

            case 'delete_reservation':
                if ($isManager) {
                    $reservationToDelete = $reservationManager->getReservationById((int)$_POST['id']);
                    if (!$reservationToDelete || !in_array((int)$reservationToDelete['restaurant_id'], $allowedRestaurantIds, true)) {
                        $message = 'You do not have permission to manage this reservation.';
                        $messageType = 'danger';
                        break;
                    }
                }
                $result = $reservationManager->deleteReservation($_POST['id']);
                $message = $result['message'];
                break;
        }
    }
}

// Handle edit request
$editReservation = null;
if (isset($_GET['edit_reservation'])) {
    $editReservationId = $_GET['edit_reservation'];
    $editReservation = $reservationManager->getReservationById($editReservationId);
    if (!$editReservation) {
        $message = 'Reservation not found.';
        $messageType = 'danger';
    } elseif ($isManager && !in_array((int)$editReservation['restaurant_id'], $allowedRestaurantIds, true)) {
        $message = 'You do not have permission to manage this reservation.';
        $messageType = 'danger';
        $editReservation = null;
    }
} else if (isset($_GET['confirm_delete'])) {
    // Handle delete confirmation through GET parameters
    $reservationId = $_GET['confirm_delete'];
    
    // Show confirmation message
    $reservation = $reservationManager->getReservationById($reservationId);
    if ($reservation) {
        if ($isManager && !in_array((int)$reservation['restaurant_id'], $allowedRestaurantIds, true)) {
            $message = 'You do not have permission to manage this reservation.';
            $messageType = 'danger';
        } else {
            $message = 'Are you sure you want to delete reservation for "' . htmlspecialchars($reservation['customer_name']) . '"?';
            $messageType = 'warning';
            $showDeleteConfirmation = true;
        }
    }
} else if (isset($_GET['delete_confirmed']) && isset($_GET['id'])) {
    // Handle confirmed delete
    if ($isManager) {
        $reservationToDelete = $reservationManager->getReservationById((int)$_GET['id']);
        if (!$reservationToDelete || !in_array((int)$reservationToDelete['restaurant_id'], $allowedRestaurantIds, true)) {
            $message = 'You do not have permission to manage this reservation.';
            $messageType = 'danger';
        } else {
            $result = $reservationManager->deleteReservation($_GET['id']);
            $message = $result['message'];
            $messageType = 'info';
        }
    } else {
        $result = $reservationManager->deleteReservation($_GET['id']);
        $message = $result['message'];
        $messageType = 'info';
    }
}

// Get all reservations
$reservations = $isManager ? $reservationManager->getReservationsForManager($currentUserId) : $reservationManager->getAllReservations();

// Filter by restaurant if specified
if (isset($_GET['restaurant_id']) && !empty($_GET['restaurant_id'])) {
    $restaurantFilterId = (int)$_GET['restaurant_id'];
    if (!$isManager || in_array($restaurantFilterId, $allowedRestaurantIds, true)) {
        $reservations = $isManager ? $reservationManager->getReservationsForManagerByRestaurant($currentUserId, $restaurantFilterId) : $reservationManager->getReservationsByRestaurant($restaurantFilterId);
    }
}

// Filter by status if specified
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $statusFilter = $_GET['status'];
    if ($isManager) {
        $reservations = $reservationManager->getReservationsForManagerByStatus($currentUserId, $statusFilter);
        if (isset($_GET['restaurant_id']) && !empty($_GET['restaurant_id'])) {
            $restaurantFilterId = (int)$_GET['restaurant_id'];
            if (in_array($restaurantFilterId, $allowedRestaurantIds, true)) {
                $reservations = array_values(array_filter($reservations, function($reservation) use ($restaurantFilterId) {
                    return (int)$reservation['restaurant_id'] === $restaurantFilterId;
                }));
            }
        }
    } else {
        $filteredReservations = [];
        foreach ($reservations as $reservation) {
            if ($reservation['status'] == $statusFilter) {
                $filteredReservations[] = $reservation;
            }
        }
        $reservations = $filteredReservations;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management - Restaurant Management System</title>
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
                    <span class="badge bg-info"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
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
                    <a class="nav-link" href="index.php">
                        <span class="custom-icon icon-speedometer2"></span> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <span class="custom-icon icon-people"></span> Customer Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="restaurants.php">
                        <span class="custom-icon icon-shop"></span> Restaurants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="reservations.php">
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
                        <span class="custom-icon icon-graph-up"></span> Business Reports
                    </a>
                </li>
                <!-- Note: API Keys and Service Registry are admin-only -->
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Reservation Management</h1>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo isset($messageType) ? $messageType : 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($showDeleteConfirmation) && $showDeleteConfirmation): ?>
                <div class="page-overlay" role="dialog" aria-modal="true">
                    <a class="page-overlay__backdrop" href="reservations.php" aria-label="Close"></a>
                    <div class="page-overlay__panel">
                        <div class="page-overlay__panel-header">
                            <h4 class="page-overlay__panel-title">Confirm Deletion</h4>
                            <a href="reservations.php" class="btn btn-secondary">Close</a>
                        </div>
                        <div class="page-overlay__panel-body">
                            <p><?php echo htmlspecialchars($message); ?></p>
                            <div class="page-overlay__panel-actions">
                                <a href="reservations.php" class="btn btn-secondary">Cancel</a>
                                <form method="GET" class="d-inline">
                                    <input type="hidden" name="delete_confirmed" value="1">
                                    <input type="hidden" name="id" value="<?php echo $_GET['confirm_delete']; ?>">
                                    <button type="submit" class="btn btn-danger">Yes, Delete Reservation</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Edit Reservation Form -->
                <?php if ($editReservation): ?>
                <div class="page-overlay" role="dialog" aria-modal="true">
                    <a class="page-overlay__backdrop" href="reservations.php" aria-label="Close"></a>
                    <div class="page-overlay__panel page-overlay__panel--drawer">
                        <div class="page-overlay__panel-header">
                            <h5 class="page-overlay__panel-title">Edit Reservation</h5>
                            <a href="reservations.php" class="btn btn-secondary">Close</a>
                        </div>
                        <div class="page-overlay__panel-body">
                        <form method="POST" action="reservations.php">
                            <input type="hidden" name="action" value="update_reservation">
                            <input type="hidden" name="id" value="<?php echo $editReservation['id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editReservation['customer_name']); ?>" disabled>
                                    <div class="form-text">Customer details cannot be changed here.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo $editReservation['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $editReservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $editReservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo $editReservation['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?php echo $editReservation['date']; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="time" class="form-label">Time</label>
                                    <input type="time" class="form-control" id="time" name="time" value="<?php echo $editReservation['time']; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="guests" class="form-label">Guests</label>
                                    <input type="number" class="form-control" id="guests" name="guests" value="<?php echo $editReservation['guests']; ?>" min="1" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="special_requests" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="2"><?php echo htmlspecialchars($editReservation['special_requests'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="page-overlay__panel-actions">
                                <a href="reservations.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Reservation</button>
                            </div>
                        </form>
                        </div>
                    </div>
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
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="reservations.php?edit_reservation=<?php echo $reservation['id']; ?>">
                                                            <i class="bi bi-pencil me-2"></i>Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="reservations.php?confirm_delete=<?php echo $reservation['id']; ?>">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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

    <script>
        // Add confirmation dialog before deleting reservations
        document.addEventListener('DOMContentLoaded', function() {
            var deleteButtons = document.querySelectorAll('button[type="submit"][class*="btn-outline-danger"]');
            
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    // Get customer and restaurant names from the row
                    var row = this.closest('tr');
                    var customerName = row.querySelector('td strong').textContent;
                    var restaurantName = row.querySelector('td:nth-child(2)').textContent;
                    var reservationDate = row.querySelector('td:nth-child(3)').textContent.split('\n')[0];
                    
                    if (!confirm('Are you sure you want to delete the reservation for "' + customerName + '" at "' + restaurantName + '" for ' + reservationDate + '?')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Auto-submit when filters change
            document.getElementById('restaurant_filter').addEventListener('change', function() {
                this.form.submit();
            });
            
            document.getElementById('status_filter').addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
    <script src="../js/app.js"></script>
</body>
</html>

<?php
$reservationManager->close();
$restaurantManager->close();
?>
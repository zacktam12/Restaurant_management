<?php
/**
 * Tour Participants Management
 * Admin interface for viewing tour participants
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Booking.php';

$bookingManager = new Booking();

// Handle view details request
$participantDetails = null;
$viewDetailsId = null;
if (isset($_GET['view_details'])) {
    $viewDetailsId = $_GET['view_details'];
    // In a real implementation, we would fetch the participant details from the API
    // For now, we'll just show a message
    $participantDetails = [
        'booking_id' => $viewDetailsId,
        'tour_id' => 'Sample Tour ID',
        'customer_name' => 'Sample Customer',
        'booking_date' => date('Y-m-d'),
        'participants' => 4,
        'participant_names' => 'John Doe, Jane Smith, Bob Johnson, Alice Brown'
    ];
}

// Get all tour bookings with participants
$tourBookings = $bookingManager->getAllTourBookingsWithParticipants();

// Filter by status if specified
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filteredBookings = [];
    foreach ($tourBookings as $booking) {
        if ($booking['status'] == $_GET['status']) {
            $filteredBookings[] = $booking;
        }
    }
    $tourBookings = $filteredBookings;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Participants - Restaurant Management System</title>
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
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
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="restaurants.php">
                                <i class="bi bi-shop"></i> Restaurants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservations.php">
                                <i class="bi bi-calendar-check"></i> Reservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services.php">
                                <i class="bi bi-gear"></i> External Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="tour_participants.php">
                                <i class="bi bi-people-fill"></i> Tour Participants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tour Participants</h1>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
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
                                <a href="tour_participants.php" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tour Participants Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tour Bookings & Participants</h5>
                        <span class="badge bg-primary"><?php echo count($tourBookings); ?> Total Bookings</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tourBookings)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people-fill fs-1 text-muted"></i>
                            <h3 class="mt-3">No tour bookings found</h3>
                            <p class="text-muted">There are no tour bookings in the system yet.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Tour ID</th>
                                        <th>Participants</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tourBookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['tour_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['participants']); ?></td>
                                        <td><?php echo $booking['booking_date'] ? date('M j, Y', strtotime($booking['booking_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $booking['status'] == 'confirmed' ? 'success' : 
                                                     ($booking['status'] == 'pending' ? 'warning' : 
                                                     ($booking['status'] == 'cancelled' ? 'danger' : 'secondary')); ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="GET">
                                                <input type="hidden" name="view_details" value="<?php echo $booking['booking_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Details
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Participant Details Display -->
    <?php if ($participantDetails): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tour Participant Details</h5>
            <a href="tour_participants.php" class="btn btn-sm btn-outline-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Booking Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Booking ID:</strong></td>
                            <td><?php echo htmlspecialchars($participantDetails['booking_id']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tour ID:</strong></td>
                            <td><?php echo htmlspecialchars($participantDetails['tour_id']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Customer:</strong></td>
                            <td><?php echo htmlspecialchars($participantDetails['customer_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Booking Date:</strong></td>
                            <td><?php echo htmlspecialchars($participantDetails['booking_date'] ?? 'N/A'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Participant Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Number of Participants:</strong></td>
                            <td><?php echo htmlspecialchars($participantDetails['participants'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Participant Names:</strong></td>
                            <td><?php echo htmlspecialchars($participantDetails['participant_names'] ?? 'Not specified'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$bookingManager->close();
?>
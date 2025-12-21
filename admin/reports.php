<?php
/**
 * Reports Page
 * Admin interface for viewing visitor statistics and booking history
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
require_once '../backend/Booking.php';
require_once '../backend/User.php';

$reservationManager = new Reservation();
$restaurantManager = new Restaurant();
$bookingManager = new Booking();
$userManager = new User();

// Get statistics data
$totalRestaurants = count($restaurantManager->getAllRestaurants());
$totalReservations = count($reservationManager->getAllReservations());
$totalBookings = count($bookingManager->getAllBookings());
$totalUsers = count($userManager->getAllUsers());

// Get recent reservations
$recentReservations = array_slice($reservationManager->getAllReservations(), 0, 10);

// Get recent bookings
$recentBookings = array_slice($bookingManager->getAllBookings(), 0, 10);

// Get reservations by status
$statusCounts = [
    'pending' => count($reservationManager->getReservationsByStatus('pending')),
    'confirmed' => count($reservationManager->getReservationsByStatus('confirmed')),
    'cancelled' => count($reservationManager->getReservationsByStatus('cancelled')),
    'completed' => count($reservationManager->getReservationsByStatus('completed'))
];

// Get bookings by service type
$serviceTypeCounts = [
    'tour' => count($bookingManager->getBookingsByServiceType('tour')),
    'hotel' => count($bookingManager->getBookingsByServiceType('hotel')),
    'taxi' => count($bookingManager->getBookingsByServiceType('taxi')),
    'restaurant' => count($bookingManager->getBookingsByServiceType('restaurant'))
];

// Get reservations by restaurant
$reservationsByRestaurant = [];
$restaurants = $restaurantManager->getAllRestaurants();
foreach ($restaurants as $restaurant) {
    $count = count($reservationManager->getReservationsByRestaurant($restaurant['id']));
    if ($count > 0) {
        $reservationsByRestaurant[$restaurant['name']] = $count;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <a class="nav-link" href="../admin/reservations.php">
                                <i class="bi bi-calendar-check"></i> Reservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="../admin/reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Reports & Analytics</h1>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Restaurants</h5>
                                <h2><?php echo $totalRestaurants; ?></h2>
                                <i class="bi bi-shop float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Reservations</h5>
                                <h2><?php echo $totalReservations; ?></h2>
                                <i class="bi bi-calendar-check float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Total Bookings</h5>
                                <h2><?php echo $totalBookings; ?></h2>
                                <i class="bi bi-bookmark-check float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h2><?php echo $totalUsers; ?></h2>
                                <i class="bi bi-people float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Reservations by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="reservationsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Bookings by Service Type</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="bookingsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Reservations by Restaurant</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="restaurantChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Tables -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Reservations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Restaurant</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recentReservations)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No reservations found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($recentReservations as $reservation): 
                                                $restaurant = $restaurantManager->getRestaurantById($reservation['restaurant_id']);
                                                $restaurantName = $restaurant ? $restaurant['name'] : 'Unknown';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($restaurantName); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($reservation['date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $reservation['status'] == 'confirmed' ? 'success' : 
                                                             ($reservation['status'] == 'pending' ? 'warning' : 
                                                             ($reservation['status'] == 'cancelled' ? 'danger' : 'secondary')); ?>">
                                                        <?php echo ucfirst($reservation['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Bookings</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Service Type</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recentBookings)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No bookings found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($recentBookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['customer_id']); ?></td>
                                                <td><?php echo ucfirst($booking['service_type']); ?></td>
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
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Reservations by Status Chart
        const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
        const reservationsChart = new Chart(reservationsCtx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
                datasets: [{
                    data: [<?php echo $statusCounts['pending']; ?>, <?php echo $statusCounts['confirmed']; ?>, <?php echo $statusCounts['cancelled']; ?>, <?php echo $statusCounts['completed']; ?>],
                    backgroundColor: [
                        '#ffc107',
                        '#28a745',
                        '#dc3545',
                        '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Bookings by Service Type Chart
        const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
        const bookingsChart = new Chart(bookingsCtx, {
            type: 'bar',
            data: {
                labels: ['Tours', 'Hotels', 'Taxis', 'Restaurants'],
                datasets: [{
                    label: 'Number of Bookings',
                    data: [<?php echo $serviceTypeCounts['tour']; ?>, <?php echo $serviceTypeCounts['hotel']; ?>, <?php echo $serviceTypeCounts['taxi']; ?>, <?php echo $serviceTypeCounts['restaurant']; ?>],
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Reservations by Restaurant Chart
        const restaurantCtx = document.getElementById('restaurantChart').getContext('2d');
        const restaurantChart = new Chart(restaurantCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($reservationsByRestaurant)); ?>,
                datasets: [{
                    label: 'Number of Reservations',
                    data: <?php echo json_encode(array_values($reservationsByRestaurant)); ?>,
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
$reservationManager->close();
$restaurantManager->close();
$bookingManager->close();
$userManager->close();
?>
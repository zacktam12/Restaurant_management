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

// Enhanced statistics
// Get monthly reservation trends (last 6 months)
$monthlyReservations = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $startDate = date('Y-m-01', strtotime("-$i months"));
    $endDate = date('Y-m-t', strtotime("-$i months"));
    
    $query = "SELECT COUNT(*) as count FROM reservations WHERE date BETWEEN ? AND ?";
    $params = [$startDate, $endDate];
    $paramTypes = "ss";
    
    try {
        $result = $reservationManager->db->select($query, $params, $paramTypes);
        $monthlyReservations[$month] = $result[0]['count'] ?? 0;
    } catch (Exception $e) {
        $monthlyReservations[$month] = 0;
    }
}

// Get top restaurants by reservations
$topRestaurants = [];
$query = "SELECT r.name, COUNT(res.id) as reservation_count 
          FROM restaurants r 
          LEFT JOIN reservations res ON r.id = res.restaurant_id 
          GROUP BY r.id, r.name 
          ORDER BY reservation_count DESC 
          LIMIT 5";
try {
    $topRestaurants = $restaurantManager->db->select($query);
} catch (Exception $e) {
    $topRestaurants = [];
}

// Get user registration trends
$userRegistrationTrends = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $startDate = date('Y-m-01', strtotime("-$i months"));
    $endDate = date('Y-m-t', strtotime("-$i months"));
    
    $query = "SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN ? AND ?";
    $params = [$startDate, $endDate];
    $paramTypes = "ss";
    
    try {
        $result = $userManager->db->select($query, $params, $paramTypes);
        $userRegistrationTrends[$month] = $result[0]['count'] ?? 0;
    } catch (Exception $e) {
        $userRegistrationTrends[$month] = 0;
    }
}

// Get user roles distribution
$userRoles = [
    'admin' => count($userManager->db->select("SELECT * FROM users WHERE role = 'admin'")),
    'manager' => count($userManager->db->select("SELECT * FROM users WHERE role = 'manager'")),
    'customer' => count($userManager->db->select("SELECT * FROM users WHERE role = 'customer'")),
    'tourist' => count($userManager->db->select("SELECT * FROM users WHERE role = 'tourist'"))
];

// Get average restaurant ratings
$avgRatings = [];
$query = "SELECT cuisine, AVG(rating) as avg_rating, COUNT(*) as restaurant_count 
          FROM restaurants 
          GROUP BY cuisine 
          ORDER BY avg_rating DESC";
try {
    $avgRatings = $restaurantManager->db->select($query);
} catch (Exception $e) {
    $avgRatings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Restaurant Management System</title>
    <link href="../css/style.css" rel="stylesheet">

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
                            <a class="nav-link" href="../admin/reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/api_keys.php">
                                <i class="bi bi-key"></i> API Keys
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/service_registry.php">
                                <i class="bi bi-diagram-3"></i> Service Registry
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
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $totalCount = array_sum($statusCounts);
                                            $statuses = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];
                                            foreach ($statuses as $key => $label): 
                                                $count = $statusCounts[$key];
                                                $percentage = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo $label; ?></td>
                                                <td><?php echo $count; ?></td>
                                                <td><?php echo $percentage; ?>%</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Bookings by Service Type</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Service Type</th>
                                                <th>Bookings</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Tours</td>
                                                <td><?php echo $serviceTypeCounts['tour']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Hotels</td>
                                                <td><?php echo $serviceTypeCounts['hotel']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Taxis</td>
                                                <td><?php echo $serviceTypeCounts['taxi']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Restaurants</td>
                                                <td><?php echo $serviceTypeCounts['restaurant']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Restaurant</th>
                                                <th>Reservations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservationsByRestaurant as $name => $count): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($name); ?></td>
                                                <td><?php echo $count; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Analytics Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Monthly Reservation Trends</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Reservations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($monthlyReservations as $month => $count): ?>
                                            <tr>
                                                <td><?php echo $month; ?></td>
                                                <td><?php echo $count; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">User Registration Trends</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>New Users</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userRegistrationTrends as $month => $count): ?>
                                            <tr>
                                                <td><?php echo $month; ?></td>
                                                <td><?php echo $count; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Restaurants by Reservations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Restaurant</th>
                                                <th>Reservations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topRestaurants as $restaurant): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($restaurant['name']); ?></td>
                                                <td><?php echo $restaurant['reservation_count']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">User Roles Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>User Role</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Admin</td>
                                                <td><?php echo $userRoles['admin']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Manager</td>
                                                <td><?php echo $userRoles['manager']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Customer</td>
                                                <td><?php echo $userRoles['customer']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Tourist</td>
                                                <td><?php echo $userRoles['tourist']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Average Restaurant Ratings by Cuisine</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Cuisine</th>
                                                <th>Average Rating</th>
                                                <th>Number of Restaurants</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($avgRatings as $rating): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rating['cuisine']); ?></td>
                                                <td><?php echo number_format($rating['avg_rating'], 2); ?>/5.0</td>
                                                <td><?php echo htmlspecialchars($rating['restaurant_count']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$reservationManager->close();
$restaurantManager->close();
$bookingManager->close();
$userManager->close();
?>
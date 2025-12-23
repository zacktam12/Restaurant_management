<?php
/**
 * Reports Page
 * Admin interface for viewing visitor statistics and booking history
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
require_once '../backend/Booking.php';
require_once '../backend/User.php';

$reservationManager = new Reservation();
$restaurantManager = new Restaurant();
$bookingManager = new Booking();
$userManager = new User();

$currentUserRole = $_SESSION['user']['role'] ?? '';
$currentUserId = (int)($_SESSION['user']['id'] ?? 0);
$isManager = Permission::isManager($currentUserRole);

// Get statistics data
$totalRestaurants = $isManager ? count($restaurantManager->getRestaurantsForManager($currentUserId)) : count($restaurantManager->getAllRestaurants());
$totalReservations = $isManager ? count($reservationManager->getReservationsForManager($currentUserId)) : count($reservationManager->getAllReservations());
$totalBookings = count($bookingManager->getAllBookings());

// Managers don't need total users
$totalUsers = null; 
if ($_SESSION['user']['role'] == 'admin') {
    $totalUsers = count($userManager->getAllUsers());
}

// Get recent reservations
$recentReservations = array_slice($isManager ? $reservationManager->getReservationsForManager($currentUserId) : $reservationManager->getAllReservations(), 0, 10);

// Get recent bookings
$recentBookings = array_slice($bookingManager->getAllBookings(), 0, 10);

// Get reservations by status
$statusCounts = $isManager ? [
    'pending' => count($reservationManager->getReservationsForManagerByStatus($currentUserId, 'pending')),
    'confirmed' => count($reservationManager->getReservationsForManagerByStatus($currentUserId, 'confirmed')),
    'cancelled' => count($reservationManager->getReservationsForManagerByStatus($currentUserId, 'cancelled')),
    'completed' => count($reservationManager->getReservationsForManagerByStatus($currentUserId, 'completed'))
] : [
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
$restaurants = $isManager ? $restaurantManager->getRestaurantsForManager($currentUserId) : $restaurantManager->getAllRestaurants();
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
    
    try {
        $monthlyReservations[$month] = $isManager ? $reservationManager->getReservationCountByDateRangeForManager($currentUserId, $startDate, $endDate) : $reservationManager->getReservationCountByDateRange($startDate, $endDate);
    } catch (Exception $e) {
        $monthlyReservations[$month] = 0;
    }
}

// Get top restaurants by reservations
$topRestaurants = [];
try {
    $topRestaurants = $isManager ? $restaurantManager->getTopRestaurantsByReservationsForManager($currentUserId, 5) : $restaurantManager->getTopRestaurantsByReservations(5);
} catch (Exception $e) {
    $topRestaurants = [];
}

// Get user registration trends - Restricted to Admin
$userRegistrationTrends = [];
if ($_SESSION['user']['role'] == 'admin') {
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $startDate = date('Y-m-01', strtotime("-$i months"));
        $endDate = date('Y-m-t', strtotime("-$i months"));
        
        try {
            $userRegistrationTrends[$month] = $userManager->getUserCountByDateRange($startDate, $endDate);
        } catch (Exception $e) {
            $userRegistrationTrends[$month] = 0;
        }
    }
}

// Get user roles distribution - Restricted to Admin
$userRoles = [];
if ($_SESSION['user']['role'] == 'admin') {
    $userRoles = [
        'admin' => $userManager->getUserCountByRole('admin'),
        'manager' => $userManager->getUserCountByRole('manager'),
        'customer' => $userManager->getUserCountByRole('customer')
    ];
}

// Get average restaurant ratings
$avgRatings = [];
try {
    $avgRatings = $isManager ? $restaurantManager->getRestaurantRatingsByCuisineForManager($currentUserId) : $restaurantManager->getRestaurantRatingsByCuisine();
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
                    <a class="nav-link active" href="reports.php">
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
                    <h1 class="h2">Reports & Analytics</h1>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Restaurants</h5>
                                <h2><?php echo $totalRestaurants; ?></h2>
                                <i class="bi bi-shop float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Reservations</h5>
                                <h2><?php echo $totalReservations; ?></h2>
                                <i class="bi bi-calendar-check float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Total Bookings</h5>
                                <h2><?php echo $totalBookings; ?></h2>
                                <i class="bi bi-bookmark-check float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h2><?php echo $totalUsers; ?></h2>
                                <i class="bi bi-people float-end fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="reportsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                            <span class="custom-icon icon-speedometer2 me-2"></span>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="restaurants-tab" data-bs-toggle="tab" data-bs-target="#restaurants" type="button" role="tab" aria-controls="restaurants" aria-selected="false">
                            <span class="custom-icon icon-shop me-2"></span>Restaurant Visuals
                        </button>
                    </li>
                    <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="false">
                            <span class="custom-icon icon-people me-2"></span>User Stats
                        </button>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">
                            <span class="custom-icon icon-calendar-check me-2"></span>Recent Activity
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="reportsTabContent">
                    
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
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
                                <div class="card h-100">
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
                        </div>
                    </div>

                    <!-- Restaurants Tab -->
                    <div class="tab-pane fade" id="restaurants" role="tabpanel" aria-labelledby="restaurants-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
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
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Average Ratings by Cuisine</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Cuisine</th>
                                                        <th>Average Rating</th>
                                                        <th>Restaurants</th>
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

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Total Reservations by Restaurant</h5>
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
                    </div>

                    <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                    <!-- Users Tab -->
                    <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
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
                            <div class="col-md-6">
                                <div class="card h-100">
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
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Activity Tab -->
                    <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
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
                                <div class="card h-100">
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
                    </div>

                </div>
            </main>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$reservationManager->close();
$restaurantManager->close();
$bookingManager->close();
$userManager->close();
?>
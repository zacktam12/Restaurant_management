<?php
/**
 * Tourist Dashboard
 * Tourist interface for browsing restaurants and making reservations
 */

session_start();

// Check if user is logged in and is tourist/customer
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'tourist' && $_SESSION['user']['role'] != 'customer')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Menu.php';
require_once '../backend/Reservation.php';

$restaurantManager = new Restaurant();
$menuManager = new Menu();
$reservationManager = new Reservation();

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'make_reservation') {
    $result = $reservationManager->createReservation(
        $_POST['restaurant_id'],
        $_SESSION['user']['name'],
        $_SESSION['user']['email'],
        $_POST['phone'],
        $_POST['date'],
        $_POST['time'],
        $_POST['guests'],
        $_POST['special_requests'] ?? null
    );
    
    if ($result['success']) {
        $message = "Reservation created successfully!";
        $messageType = "success";
    } else {
        $message = "Error creating reservation: " . $result['message'];
        $messageType = "danger";
    }
}

// Get all restaurants
$search = isset($_GET['search']) ? $_GET['search'] : '';
$cuisine = isset($_GET['cuisine']) ? $_GET['cuisine'] : '';

if ($search) {
    $restaurants = $restaurantManager->searchRestaurants($search);
} else if ($cuisine) {
    $restaurants = $restaurantManager->filterByCuisine($cuisine);
} else {
    $restaurants = $restaurantManager->getAllRestaurants();
}

// Get unique cuisines for filter dropdown
$allRestaurants = $restaurantManager->getAllRestaurants();
$cuisines = [];
foreach ($allRestaurants as $restaurant) {
    if (!in_array($restaurant['cuisine'], $cuisines)) {
        $cuisines[] = $restaurant['cuisine'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Dashboard - Restaurant Management System</title>
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
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-shop"></i> Restaurants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">
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
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Restaurants</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by name, description, or cuisine">
                    </div>
                    <div class="col-md-4">
                        <label for="cuisine" class="form-label">Filter by Cuisine</label>
                        <select class="form-select" id="cuisine" name="cuisine">
                            <option value="">All Cuisines</option>
                            <?php foreach ($cuisines as $cuisineOption): ?>
                            <option value="<?php echo htmlspecialchars($cuisineOption); ?>" 
                                <?php echo (isset($_GET['cuisine']) && $_GET['cuisine'] == $cuisineOption) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cuisineOption); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Restaurants Grid -->
        <div class="row">
            <?php if (empty($restaurants)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-search fs-1 text-muted"></i>
                    <h3 class="mt-3">No restaurants found</h3>
                    <p class="text-muted">Try adjusting your search criteria</p>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($restaurants as $restaurant): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($restaurant['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($restaurant['description']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($restaurant['cuisine']); ?><br>
                                <i class="bi bi-currency-dollar"></i> <?php echo htmlspecialchars($restaurant['price_range']); ?><br>
                                <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($restaurant['rating']); ?>/5.0<br>
                                <i class="bi bi-people"></i> Capacity: <?php echo htmlspecialchars($restaurant['seating_capacity']); ?> seats
                            </small>
                        </p>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                data-bs-target="#reserveModal<?php echo $restaurant['id']; ?>">
                            <i class="bi bi-calendar-plus"></i> Reserve Table
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reservation Modal for each restaurant -->
            <div class="modal fade" id="reserveModal<?php echo $restaurant['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Reserve Table at <?php echo htmlspecialchars($restaurant['name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="make_reservation">
                                <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                                <div class="mb-3">
                                    <label for="date<?php echo $restaurant['id']; ?>" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date<?php echo $restaurant['id']; ?>" 
                                           name="date" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="time<?php echo $restaurant['id']; ?>" class="form-label">Time</label>
                                    <input type="time" class="form-control" id="time<?php echo $restaurant['id']; ?>" 
                                           name="time" required>
                                </div>
                                <div class="mb-3">
                                    <label for="guests<?php echo $restaurant['id']; ?>" class="form-label">Number of Guests</label>
                                    <input type="number" class="form-control" id="guests<?php echo $restaurant['id']; ?>" 
                                           name="guests" min="1" max="<?php echo $restaurant['seating_capacity']; ?>" 
                                           value="2" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone<?php echo $restaurant['id']; ?>" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone<?php echo $restaurant['id']; ?>" 
                                           name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="special_requests<?php echo $restaurant['id']; ?>" class="form-label">Special Requests</label>
                                    <textarea class="form-control" id="special_requests<?php echo $restaurant['id']; ?>" 
                                              name="special_requests" rows="2" 
                                              placeholder="Any dietary restrictions or special requirements"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Make Reservation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$restaurantManager->close();
$menuManager->close();
$reservationManager->close();
?>
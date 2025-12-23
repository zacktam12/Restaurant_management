<?php
/**
 * Customer Dashboard
 * Customer interface for browsing restaurants and making reservations
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
require_once '../backend/Restaurant.php';
require_once '../backend/Menu.php';
require_once '../backend/Reservation.php';
require_once '../backend/Alert.php';

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
        Alert::setSuccess("Reservation created successfully!");
    } else {
        Alert::setError("Error creating reservation: " . $result['message']);
    }
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
    <title>Customer Dashboard - Restaurant Management System</title>
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
                            <a class="nav-link active" href="index.php">
                                Restaurants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="bookings.php">
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

    <!-- Hero Section -->
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="hero-title">Discover Culinary Excellence</h1>
            <p class="hero-subtitle mx-auto">Explore the finest restaurants, book tables instantly, and enjoy unforgettable dining experiences.</p>
        </div>
    </div>

    <div class="container" style="margin-top: -60px;">
        <?php Alert::display(); ?>
        
        <!-- Search and Filter -->
        <div class="search-card mb-5">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Restaurants</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <span class="custom-icon icon-search" style="mask-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z\'/%3E%3C/svg%3E'); -webkit-mask-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z\'/%3E%3C/svg%3E'); background-color: #6c757d;"></span>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Name, description, or cuisine...">
                    </div>
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
            </form>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Restaurants Grid -->
        <div class="row g-4 pb-5">
            <?php if (empty($restaurants)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <span class="custom-icon icon-shop d-block mx-auto mb-3" style="width: 3rem; height: 3rem; background-color: var(--bs-gray-400);"></span>
                    <h3 class="text-muted">No restaurants found</h3>
                    <p class="text-secondary">Try adjusting your search criteria</p>
                    <a href="index.php" class="btn btn-outline-primary mt-2">View All Restaurants</a>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($restaurants as $restaurant): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card restaurant-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($restaurant['name']); ?></h5>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($restaurant['rating']); ?> ★
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                <?php echo htmlspecialchars($restaurant['cuisine']); ?>
                            </span>
                        </div>
                        
                        <p class="card-text flex-grow-1">
                            <?php echo htmlspecialchars($restaurant['description']); ?>
                        </p>
                        
                        <div class="restaurant-stats mt-3">
                            <div class="restaurant-stat-item" title="Price Range">
                                <span class="fw-bold-primary"><?php echo htmlspecialchars($restaurant['price_range']); ?></span>
                            </div>
                            <div class="restaurant-stat-item ms-auto" title="Capacity">
                                <span class="custom-icon icon-people" style="background-color: var(--bs-gray-500);"></span>
                                <span><?php echo htmlspecialchars($restaurant['seating_capacity']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 p-3 pt-0">
                        <form method="POST">
                            <input type="hidden" name="action" value="make_reservation">
                            <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                            <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                            <input type="hidden" name="time" value="19:00">
                            <input type="hidden" name="guests" value="2">
                            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>">
                            <button type="submit" class="btn btn-primary w-100">
                                Reserve Table
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$restaurantManager->close();
$menuManager->close();
$reservationManager->close();
?>
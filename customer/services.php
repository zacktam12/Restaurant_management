<?php
/**
 * Tourist Services Page
 * Tourist interface for browsing and booking external services (tours, hotels, taxis)
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
require_once '../backend/Booking.php';
require_once '../backend/Alert.php';
require_once '../api/service_consumer.php';

$bookingManager = new Booking();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'book_service') {
    // Process booking through ServiceConsumer
    $bookingResult = null;
    
    switch ($_POST['service_type']) {
        case 'tour':
            $bookingResult = ServiceConsumer::bookTour(
                $_POST['service_id'],
                $_SESSION['user']['name'],
                $_SESSION['user']['email'],
                $_POST['date'],
                $_POST['guests']
            );
            break;
            
        case 'hotel':
            $bookingResult = ServiceConsumer::bookHotel(
                $_POST['service_id'],
                $_SESSION['user']['name'],
                $_SESSION['user']['email'],
                $_POST['date'],
                $_POST['checkout_date'] ?? '',
                $_POST['guests']
            );
            break;
            
        case 'taxi':
            $bookingResult = ServiceConsumer::bookTaxi(
                $_POST['service_id'],
                $_SESSION['user']['name'],
                $_SESSION['user']['email'],
                $_POST['pickup_location'] ?? '',
                $_POST['dropoff_location'] ?? '',
                $_POST['date'] . ' ' . $_POST['time']
            );
            break;
    }
    
    // Save booking to our local database regardless of external service result
    $result = $bookingManager->createBooking(
        $_POST['service_type'],
        $_POST['service_id'],
        $_SESSION['user']['id'],
        $_POST['date'] ?? null,
        $_POST['time'] ?? null,
        $_POST['guests'] ?? null,
        $_POST['special_requests'] ?? null
    );
    
    if ($result['success']) {
        Alert::setSuccess("Service booked successfully!");
    } else {
        Alert::setError("Error booking service: " . $result['message']);
    }
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch real services from other groups
$tours = ServiceConsumer::getTours();
$hotels = ServiceConsumer::getHotels();
$taxis = ServiceConsumer::getTaxiServices();

// Ensure we have arrays of items
if (!is_array($tours)) {
    $tours = [];
}
if (!is_array($hotels)) {
    $hotels = [];
}
if (!is_array($taxis)) {
    $taxis = [];
}

$tours = array_values(array_filter($tours, function ($item) {
    return is_array($item) && isset($item['name']) && isset($item['description']) && isset($item['price']);
}));

$hotels = array_values(array_filter($hotels, function ($item) {
    return is_array($item) && isset($item['name']) && isset($item['description']) && isset($item['price']);
}));

$taxis = array_values(array_filter($taxis, function ($item) {
    return is_array($item) && isset($item['name']) && isset($item['description']) && isset($item['price']);
}));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>External Services - Customer Portal</title>
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
                            <a class="nav-link active" href="services.php">
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
            <h1 class="fw-bold mb-2">Additional Services</h1>
            <p class="text-muted mb-0">Book tours, hotels, and transportation with ease</p>
        </div>
    </div>

    <div class="container pb-5">
        <?php Alert::display(); ?>

        <!-- Services Tabs -->
        <div class="mb-4">
            <div class="tabs-header border-bottom mb-4">
                <button class="tab-button active px-4 py-2 fw-semibold" data-tab="tours">
                    <span class="custom-icon icon-compass me-2" style="width: 1rem; height: 1rem; vertical-align: text-bottom; background-color: currentColor;"></span>Tours
                </button>
                <button class="tab-button px-4 py-2 fw-semibold" data-tab="hotels">
                    <span class="custom-icon icon-shop me-2" style="width: 1rem; height: 1rem; vertical-align: text-bottom; background-color: currentColor;"></span>Hotels
                </button>
                <button class="tab-button px-4 py-2 fw-semibold" data-tab="taxis">
                    <span class="custom-icon icon-truck me-2" style="width: 1rem; height: 1rem; vertical-align: text-bottom; background-color: currentColor;"></span>Taxis
                </button>
            </div>

            <div class="tab-content">
                <!-- Tours Tab -->
                <div class="tab-pane active" id="tours">
                    <div class="row g-4">
                        <?php if (empty($tours)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <h3 class="mt-3 text-muted">No tours available</h3>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($tours as $tour): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card restaurant-card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($tour['name']); ?></h5>
                                    
                                    <div class="mb-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">
                                            $<?php echo number_format($tour['price'], 2); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="card-text flex-grow-1 text-secondary">
                                        <?php echo htmlspecialchars($tour['description']); ?>
                                    </p>
                                    
                                    <div class="restaurant-stats mt-3 pt-3 border-top">
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="fw-bold text-warning me-1"><?php echo htmlspecialchars($tour['rating']); ?></span>
                                            <span class="text-secondary opacity-75">/ 5.0 Rating</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 p-3 pt-0">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="tour">
                                        <input type="hidden" name="service_id" value="<?php echo $tour['id']; ?>">
                                        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                                        <input type="hidden" name="time" value="10:00">
                                        <input type="hidden" name="guests" value="2">
                                        <button type="submit" class="btn btn-primary w-100 hover-shadow">
                                            Book Tour
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hotels Tab -->
                <div class="tab-pane" id="hotels">
                    <div class="row g-4">
                        <?php if (empty($hotels)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <h3 class="mt-3 text-muted">No hotels available</h3>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($hotels as $hotel): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card restaurant-card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                    
                                    <div class="mb-3">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">
                                            $<?php echo number_format($hotel['price'], 2); ?>/night
                                        </span>
                                    </div>
                                    
                                    <p class="card-text flex-grow-1 text-secondary">
                                        <?php echo htmlspecialchars($hotel['description']); ?>
                                    </p>
                                    
                                    <div class="restaurant-stats mt-3 pt-3 border-top">
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="fw-bold text-warning me-1"><?php echo htmlspecialchars($hotel['rating']); ?></span>
                                            <span class="text-secondary opacity-75">/ 5.0 Rating</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 p-3 pt-0">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="hotel">
                                        <input type="hidden" name="service_id" value="<?php echo $hotel['id']; ?>">
                                        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                                        <input type="hidden" name="checkout_date" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                        <input type="hidden" name="guests" value="2">
                                        <button type="submit" class="btn btn-primary w-100 hover-shadow">
                                            Book Hotel
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Taxis Tab -->
                <div class="tab-pane" id="taxis">
                    <div class="row g-4">
                        <?php if (empty($taxis)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <h3 class="mt-3 text-muted">No taxi services available</h3>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($taxis as $taxi): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card restaurant-card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($taxi['name']); ?></h5>
                                     
                                    <div class="mb-3">
                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 rounded-pill px-3">
                                            $<?php echo number_format($taxi['price'], 2); ?>
                                        </span>
                                    </div>

                                    <p class="card-text flex-grow-1 text-secondary">
                                        <?php echo htmlspecialchars($taxi['description']); ?>
                                    </p>
                                    
                                    <div class="restaurant-stats mt-3 pt-3 border-top">
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="fw-bold text-warning me-1"><?php echo htmlspecialchars($taxi['rating']); ?></span>
                                            <span class="text-secondary opacity-75">/ 5.0 Rating</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 p-3 pt-0">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="taxi">
                                        <input type="hidden" name="service_id" value="<?php echo $taxi['id']; ?>">
                                        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                                        <input type="hidden" name="time" value="10:00">
                                        <input type="hidden" name="pickup_location" value="Airport">
                                        <input type="hidden" name="dropoff_location" value="City Center">
                                        <button type="submit" class="btn btn-primary w-100 hover-shadow">
                                            Book Taxi
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div> <!-- Closing the mb-4 div -->
    </div>

    <script src="../js/app.js"></script>
</body>
</html>
<?php
$bookingManager->close();
?>
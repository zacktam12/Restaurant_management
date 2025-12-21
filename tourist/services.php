<?php
/**
 * Tourist Services Page
 * Tourist interface for browsing and booking external services (tours, hotels, taxis)
 */

session_start();

// Check if user is logged in and is tourist/customer
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'tourist' && $_SESSION['user']['role'] != 'customer')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Booking.php';
require_once '../api/service_consumer.php';

$bookingManager = new Booking();

// Initialize message variables
$message = '';
$messageType = 'info';

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
        $message = "Service booked successfully!";
        $messageType = "success";
    } else {
        $message = "Error booking service: " . $result['message'];
        $messageType = "danger";
    }
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
    <title>Other Services - Restaurant Management System</title>
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Tourist Portal
            </a>
            <div class="navbar-collapse">
                <ul class="navbar-nav">
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
                <div class="navbar-nav">
                    <span class="navbar-text">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                        <span class="badge"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
                    </span>
                    <a class="btn btn-outline-light" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Additional Services</h1>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close"></button>
        </div>
        <?php endif; ?>

        <!-- Services Tabs -->
        <div class="tabs-container mb-4">
            <div class="tabs-header">
                <button class="tab-button active" data-tab="tours">
                    Tours
                </button>
                <button class="tab-button" data-tab="hotels">
                    Hotels
                </button>
                <button class="tab-button" data-tab="taxis">
                    Taxis
                </button>
            </div>

            <div class="tab-content">
                <!-- Tours Tab -->
                <div class="tab-pane active" id="tours">
                    <div class="row">
                        <?php if (empty($tours)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <h3 class="mt-3">No tours available</h3>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($tours as $tour): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($tour['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($tour['description']); ?></p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            $<?php echo number_format($tour['price'], 2); ?><br>
                                            <?php echo htmlspecialchars($tour['rating']); ?>/5.0
                                        </small>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="tour">
                                        <input type="hidden" name="service_id" value="<?php echo $tour['id']; ?>">
                                        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                                        <input type="hidden" name="time" value="10:00">
                                        <input type="hidden" name="guests" value="2">
                                        <button type="submit" class="btn btn-primary w-100">
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
                    <div class="row">
                        <?php if (empty($hotels)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <h3 class="mt-3">No hotels available</h3>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($hotels as $hotel): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($hotel['description']); ?></p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            $<?php echo number_format($hotel['price'], 2); ?>/night<br>
                                            <?php echo htmlspecialchars($hotel['rating']); ?>/5.0
                                        </small>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="hotel">
                                        <input type="hidden" name="service_id" value="<?php echo $hotel['id']; ?>">
                                        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                                        <input type="hidden" name="checkout_date" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                        <input type="hidden" name="guests" value="2">
                                        <button type="submit" class="btn btn-primary w-100">
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
                    <div class="row">
                        <?php if (empty($taxis)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <h3 class="mt-3">No taxi services available</h3>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($taxis as $taxi): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($taxi['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($taxi['description']); ?></p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            $<?php echo number_format($taxi['price'], 2); ?><br>
                                            <?php echo htmlspecialchars($taxi['rating']); ?>/5.0
                                        </small>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="taxi">
                                        <input type="hidden" name="service_id" value="<?php echo $taxi['id']; ?>">
                                        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                                        <input type="hidden" name="time" value="10:00">
                                        <input type="hidden" name="pickup_location" value="Airport">
                                        <input type="hidden" name="dropoff_location" value="City Center">
                                        <button type="submit" class="btn btn-primary w-100">
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
        </div>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$bookingManager->close();
?>
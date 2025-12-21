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

$bookingManager = new Booking();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'book_service') {
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

// Sample external services data (in a real app, this would come from a database)
$externalServices = [
    [
        'id' => 1,
        'type' => 'tour',
        'name' => 'City Historical Tour',
        'description' => '3-hour guided tour of historical landmarks',
        'price' => 45.00,
        'image' => '/city-tour-bus.jpg',
        'rating' => 4.7,
        'available' => true
    ],
    [
        'id' => 2,
        'type' => 'hotel',
        'name' => 'Grand Palace Hotel',
        'description' => '5-star luxury accommodation',
        'price' => 250.00,
        'image' => '/luxury-hotel-exterior.png',
        'rating' => 4.9,
        'available' => true
    ],
    [
        'id' => 3,
        'type' => 'taxi',
        'name' => 'Premium Taxi Service',
        'description' => '24/7 reliable transportation',
        'price' => 25.00,
        'image' => '/taxi-cab-service.jpg',
        'rating' => 4.5,
        'available' => true
    ]
];

// Group services by type
$tours = array_filter($externalServices, function($service) { return $service['type'] == 'tour'; });
$hotels = array_filter($externalServices, function($service) { return $service['type'] == 'hotel'; });
$taxis = array_filter($externalServices, function($service) { return $service['type'] == 'taxi'; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Services - Restaurant Management System</title>
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
                        <a class="nav-link" href="index.php">
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
                        <a class="nav-link active" href="services.php">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Additional Services</h1>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Services Tabs -->
        <ul class="nav nav-tabs mb-4" id="serviceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tours-tab" data-bs-toggle="tab" data-bs-target="#tours" type="button" role="tab">
                    <i class="bi bi-compass"></i> Tours
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="hotels-tab" data-bs-toggle="tab" data-bs-target="#hotels" type="button" role="tab">
                    <i class="bi bi-building"></i> Hotels
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="taxis-tab" data-bs-toggle="tab" data-bs-target="#taxis" type="button" role="tab">
                    <i class="bi bi-car-front"></i> Taxis
                </button>
            </li>
        </ul>

        <div class="tab-content" id="serviceTabContent">
            <!-- Tours Tab -->
            <div class="tab-pane fade show active" id="tours" role="tabpanel">
                <div class="row">
                    <?php if (empty($tours)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-compass fs-1 text-muted"></i>
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
                                        <i class="bi bi-currency-dollar"></i> $<?php echo number_format($tour['price'], 2); ?><br>
                                        <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($tour['rating']); ?>/5.0
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                        data-bs-target="#bookTourModal<?php echo $tour['id']; ?>">
                                    <i class="bi bi-calendar-plus"></i> Book Tour
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Book Tour Modal -->
                    <div class="modal fade" id="bookTourModal<?php echo $tour['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Book <?php echo htmlspecialchars($tour['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="tour">
                                        <input type="hidden" name="service_id" value="<?php echo $tour['id']; ?>">
                                        <div class="mb-3">
                                            <label for="tour_date<?php echo $tour['id']; ?>" class="form-label">Date</label>
                                            <input type="date" class="form-control" id="tour_date<?php echo $tour['id']; ?>" 
                                                   name="date" min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tour_time<?php echo $tour['id']; ?>" class="form-label">Time</label>
                                            <input type="time" class="form-control" id="tour_time<?php echo $tour['id']; ?>" 
                                                   name="time" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tour_guests<?php echo $tour['id']; ?>" class="form-label">Number of Guests</label>
                                            <input type="number" class="form-control" id="tour_guests<?php echo $tour['id']; ?>" 
                                                   name="guests" min="1" value="2" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tour_special_requests<?php echo $tour['id']; ?>" class="form-label">Special Requests</label>
                                            <textarea class="form-control" id="tour_special_requests<?php echo $tour['id']; ?>" 
                                                      name="special_requests" rows="2" 
                                                      placeholder="Any special requirements"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Book Tour</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hotels Tab -->
            <div class="tab-pane fade" id="hotels" role="tabpanel">
                <div class="row">
                    <?php if (empty($hotels)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-building fs-1 text-muted"></i>
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
                                        <i class="bi bi-currency-dollar"></i> $<?php echo number_format($hotel['price'], 2); ?>/night<br>
                                        <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($hotel['rating']); ?>/5.0
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                        data-bs-target="#bookHotelModal<?php echo $hotel['id']; ?>">
                                    <i class="bi bi-calendar-plus"></i> Book Hotel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Book Hotel Modal -->
                    <div class="modal fade" id="bookHotelModal<?php echo $hotel['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Book <?php echo htmlspecialchars($hotel['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="hotel">
                                        <input type="hidden" name="service_id" value="<?php echo $hotel['id']; ?>">
                                        <div class="mb-3">
                                            <label for="checkin_date<?php echo $hotel['id']; ?>" class="form-label">Check-in Date</label>
                                            <input type="date" class="form-control" id="checkin_date<?php echo $hotel['id']; ?>" 
                                                   name="date" min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="checkout_date<?php echo $hotel['id']; ?>" class="form-label">Check-out Date</label>
                                            <input type="date" class="form-control" id="checkout_date<?php echo $hotel['id']; ?>" 
                                                   name="checkout_date" min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="hotel_guests<?php echo $hotel['id']; ?>" class="form-label">Number of Guests</label>
                                            <input type="number" class="form-control" id="hotel_guests<?php echo $hotel['id']; ?>" 
                                                   name="guests" min="1" value="2" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="hotel_special_requests<?php echo $hotel['id']; ?>" class="form-label">Special Requests</label>
                                            <textarea class="form-control" id="hotel_special_requests<?php echo $hotel['id']; ?>" 
                                                      name="special_requests" rows="2" 
                                                      placeholder="Room preferences, accessibility needs, etc."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Book Hotel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Taxis Tab -->
            <div class="tab-pane fade" id="taxis" role="tabpanel">
                <div class="row">
                    <?php if (empty($taxis)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-car-front fs-1 text-muted"></i>
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
                                        <i class="bi bi-currency-dollar"></i> $<?php echo number_format($taxi['price'], 2); ?><br>
                                        <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($taxi['rating']); ?>/5.0
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                        data-bs-target="#bookTaxiModal<?php echo $taxi['id']; ?>">
                                    <i class="bi bi-calendar-plus"></i> Book Taxi
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Book Taxi Modal -->
                    <div class="modal fade" id="bookTaxiModal<?php echo $taxi['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Book <?php echo htmlspecialchars($taxi['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="book_service">
                                        <input type="hidden" name="service_type" value="taxi">
                                        <input type="hidden" name="service_id" value="<?php echo $taxi['id']; ?>">
                                        <div class="mb-3">
                                            <label for="pickup_date<?php echo $taxi['id']; ?>" class="form-label">Pickup Date</label>
                                            <input type="date" class="form-control" id="pickup_date<?php echo $taxi['id']; ?>" 
                                                   name="date" min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="pickup_time<?php echo $taxi['id']; ?>" class="form-label">Pickup Time</label>
                                            <input type="time" class="form-control" id="pickup_time<?php echo $taxi['id']; ?>" 
                                                   name="time" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="pickup_location<?php echo $taxi['id']; ?>" class="form-label">Pickup Location</label>
                                            <input type="text" class="form-control" id="pickup_location<?php echo $taxi['id']; ?>" 
                                                   name="pickup_location" placeholder="Enter pickup address" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="dropoff_location<?php echo $taxi['id']; ?>" class="form-label">Drop-off Location</label>
                                            <input type="text" class="form-control" id="dropoff_location<?php echo $taxi['id']; ?>" 
                                                   name="dropoff_location" placeholder="Enter destination address" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="taxi_special_requests<?php echo $taxi['id']; ?>" class="form-label">Special Requests</label>
                                            <textarea class="form-control" id="taxi_special_requests<?php echo $taxi['id']; ?>" 
                                                      name="special_requests" rows="2" 
                                                      placeholder="Any special requirements"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Book Taxi</button>
                                    </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$bookingManager->close();
?>
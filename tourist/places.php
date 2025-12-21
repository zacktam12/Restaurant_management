<?php
/**
 * Tourist Places Page
 * Tourist interface for browsing places to visit
 */

session_start();

// Check if user is logged in and is tourist/customer
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'tourist' && $_SESSION['user']['role'] != 'customer')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Place.php';

$placeManager = new Place();

// Handle actions
$message = '';
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'book_tour':
            if (isset($_GET['place_name'])) {
                $message = 'Booking functionality for ' . htmlspecialchars($_GET['place_name']) . ' would be implemented here.';
            }
            break;
        case 'find_restaurants':
            if (isset($_GET['city'])) {
                $message = 'Finding restaurants in ' . htmlspecialchars($_GET['city']) . ' would be implemented here.';
            }
            break;
    }
}

// Get all places
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';

if ($action == 'view_details' && isset($_GET['place_id'])) {
    $placeId = $_GET['place_id'];
    $placeDetails = $placeManager->getPlaceById($placeId);
    
    if ($placeDetails) {
        // Display place details in a simple way
        $message = '<strong>' . htmlspecialchars($placeDetails['name']) . '</strong><br>';
        $message .= htmlspecialchars($placeDetails['description']) . '<br><br>';
        $message .= '<strong>Location:</strong> ' . htmlspecialchars($placeDetails['city']) . ', ' . htmlspecialchars($placeDetails['country']) . '<br>';
        $message .= '<strong>Rating:</strong> ' . htmlspecialchars($placeDetails['rating']) . '/5.0<br>';
        $message .= '<strong>Category:</strong> ' . ucfirst(htmlspecialchars($placeDetails['category'])) . '<br><br>';
        
        // Add action buttons
        $message .= '<form method="GET" class="d-inline">
                        <input type="hidden" name="action" value="book_tour">
                        <input type="hidden" name="place_name" value="' . htmlspecialchars($placeDetails['name']) . '">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-compass"></i> Book a Tour
                        </button>
                     </form>
                     <form method="GET" class="d-inline">
                        <input type="hidden" name="action" value="find_restaurants">
                        <input type="hidden" name="city" value="' . htmlspecialchars($placeDetails['city']) . '">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-shop"></i> Nearby Restaurants
                        </button>
                     </form>';
    }
    
    // Redirect to clean URL to prevent parameter carryover
    $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $cleanUrl");
    exit();
} else if ($action == 'book_tour' && isset($_GET['place_name'])) {
    $message = 'To book a tour for "' . htmlspecialchars($_GET['place_name']) . '", please visit the Services page.';
    // Redirect to clean URL to prevent parameter carryover
    $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $cleanUrl");
    exit();
} else if ($action == 'find_restaurants' && isset($_GET['city'])) {
    $message = 'To find restaurants in "' . htmlspecialchars($_GET['city']) . '", please visit the Restaurants page.';
    // Redirect to clean URL to prevent parameter carryover
    $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $cleanUrl");
    exit();
}

$category = isset($_GET['category']) ? $_GET['category'] : '';

if ($search) {
    $places = $placeManager->searchPlaces($search);
} else if ($category) {
    $places = $placeManager->filterByCategory($category);
} else {
    $places = $placeManager->getAllPlaces();
}

// Get unique categories for filter dropdown
$allPlaces = $placeManager->getAllPlaces();
$categories = [];
foreach ($allPlaces as $place) {
    if (!in_array($place['category'], $categories)) {
        $categories[] = $place['category'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Places to Visit - Restaurant Management System</title>
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
                        <a class="nav-link active" href="places.php">
                            Places to Visit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">
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
            <h1>Places to Visit</h1>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Places</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by name, city, or country">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label">Filter by Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $categoryOption): ?>
                            <option value="<?php echo htmlspecialchars($categoryOption); ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $categoryOption) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($categoryOption)); ?>
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

        <?php if (!empty($message)): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close"></button>
        </div>
        <?php endif; ?>

        <!-- Places Grid -->
        <div class="row">
            <?php if (empty($places)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <h3 class="mt-3">No places found</h3>
                    <p class="text-muted">Try adjusting your search criteria</p>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($places as $place): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($place['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($place['description']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                <?php echo htmlspecialchars($place['city']); ?>, <?php echo htmlspecialchars($place['country']); ?><br>
                                <?php echo htmlspecialchars($place['rating']); ?>/5.0<br>
                                <?php echo ucfirst(htmlspecialchars($place['category'])); ?>
                            </small>
                        </p>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-outline-primary w-100" 
                            data-name="<?php echo htmlspecialchars($place['name']); ?>" 
                            data-description="<?php echo htmlspecialchars($place['description']); ?>" 
                            data-city="<?php echo htmlspecialchars($place['city']); ?>" 
                            data-country="<?php echo htmlspecialchars($place['country']); ?>" 
                            data-rating="<?php echo htmlspecialchars($place['rating']); ?>" 
                            data-category="<?php echo htmlspecialchars($place['category']); ?>">
                            More Info
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Place Details Modal -->
        <div class="modal" id="placeModal" tabindex="-1" aria-labelledby="placeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="placeModalLabel">Place Details</h5>
                        <button type="button" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 id="modal-place-name"></h4>
                        <p id="modal-place-description"></p>
                        <p>
                            <strong>Location:</strong> <span id="modal-place-location"></span><br>
                            <strong>Rating:</strong> <span id="modal-place-rating"></span>/5.0<br>
                            <strong>Category:</strong> <span id="modal-place-category"></span>
                        </p>
                        <div class="mt-3">
                            <form method="GET" class="d-inline">
                                <input type="hidden" name="action" value="book_tour">
                                <input type="hidden" name="place_name" id="modal-place-name-input">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Book a Tour
                                </button>
                            </form>
                            <form method="GET" class="d-inline ms-2">
                                <input type="hidden" name="action" value="find_restaurants">
                                <input type="hidden" name="city" id="modal-place-city-input">
                                <button type="submit" class="btn btn-success btn-sm">
                                    Nearby Restaurants
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$placeManager->close();
?>
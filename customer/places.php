<?php
/**
 * Customer Places Page
 * Customer interface for browsing places to visit
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
require_once '../backend/Place.php';
require_once '../backend/Alert.php';

$placeManager = new Place();

// Handle actions
// Note: We use Alert class for messages which persist across redirects

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // View Details Logic
    if ($action == 'view_details' && isset($_GET['place_id'])) {
        $placeId = $_GET['place_id'];
        $placeDetails = $placeManager->getPlaceById($placeId);
        
        if ($placeDetails) {
            // We'll show this as a large info alert or maybe a modal logic (which is already client side usually)
            // But since this was implemented as a server-side message:
            
            $msg = '<strong>' . htmlspecialchars($placeDetails['name']) . '</strong><br>';
            $msg .= htmlspecialchars($placeDetails['description']) . '<br><br>';
            $msg .= '<strong>Location:</strong> ' . htmlspecialchars($placeDetails['city']) . ', ' . htmlspecialchars($placeDetails['country']) . '<br>';
            $msg .= '<strong>Rating:</strong> ' . htmlspecialchars($placeDetails['rating']) . '/5.0<br>';
            $msg .= '<strong>Category:</strong> ' . ucfirst(htmlspecialchars($placeDetails['category']));
            
            // For complex HTML content, we might use a different approach, but Alert supports HTML string
            // However, this "view details" as a flash message is a bit odd UX. 
            // The original code tried to show it as a message. We will replicate that behavior with Alert::setInfo
            
            // To include buttons, we need to be careful with quotes.
            // Simplified for standardized alert:
            Alert::setInfo($msg);
            
            // Note: The action buttons were hardcoded html forms in the message. 
            // Our new Alert class renders a clean structure. HTML content is allowed.
            // Let's add the buttons back carefully.
            
             $buttons = '<div class="mt-3 d-flex gap-2">
                            <form method="GET" class="d-inline m-0">
                                <input type="hidden" name="action" value="book_tour">
                                <input type="hidden" name="place_name" value="' . htmlspecialchars($placeDetails['name']) . '">
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                    Book Tour
                                </button>
                             </form>
                             <form method="GET" class="d-inline m-0">
                                <input type="hidden" name="action" value="find_restaurants">
                                <input type="hidden" name="city" value="' . htmlspecialchars($placeDetails['city']) . '">
                                <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                    Restaurants Nearby
                                </button>
                             </form>
                         </div>';
                         
             Alert::setInfo($msg . $buttons);
        }
        
        // Redirect to clean URL
        $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: $cleanUrl");
        exit();

    } else if ($action == 'book_tour' && isset($_GET['place_name'])) {
        Alert::setInfo('To book a tour for <strong>' . htmlspecialchars($_GET['place_name']) . '</strong>, please visit the <a href="services.php?tab=tours" class="alert-link">Services page</a>.');
        $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: $cleanUrl");
        exit();

    } else if ($action == 'find_restaurants' && isset($_GET['city'])) {
        Alert::setInfo('To find restaurants in <strong>' . htmlspecialchars($_GET['city']) . '</strong>, please visit the <a href="index.php?search=' . urlencode($_GET['city']) . '" class="alert-link">Restaurants page</a>.');
        $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: $cleanUrl");
        exit();
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
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
            <h1 class="fw-bold mb-2">Places to Visit</h1>
            <p class="text-muted mb-0">Discover popular attractions and hidden gems</p>
        </div>
    </div>

    <div class="container pb-5">
        <?php Alert::display(); ?>

        <!-- Search and Filter -->
        <div class="card mb-4 border-0 shadow-sm search-card">
            <div class="card-body p-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label text-uppercase small fw-bold text-muted">Search Places</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <span class="custom-icon icon-search" style="mask-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z\'/%3E%3C/svg%3E'); -webkit-mask-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z\'/%3E%3C/svg%3E'); background-color: #6c757d;"></span>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0 bg-light" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, city, or country">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label text-uppercase small fw-bold text-muted">Filter by Category</label>
                        <select class="form-select bg-light" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $categoryOption): ?>
                            <option value="<?php echo htmlspecialchars($categoryOption); ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $categoryOption) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($categoryOption)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close"></button>
        </div>
        <?php endif; ?>            

        <!-- Places Grid -->
        <div class="row g-4">
            <?php if (empty($places)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <span class="custom-icon icon-search d-block mx-auto mb-3" style="width: 3rem; height: 3rem; background-color: var(--bs-gray-400);"></span>
                    <h3 class="text-muted">No places found</h3>
                    <p class="text-secondary">Try adjusting your search criteria</p>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($places as $place): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card restaurant-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($place['name']); ?></h5>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($place['rating']); ?> ★
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">
                                <?php echo ucfirst(htmlspecialchars($place['category'])); ?>
                            </span>
                        </div>

                        <p class="card-text flex-grow-1">
                            <?php echo htmlspecialchars($place['description']); ?>
                        </p>
                        
                        <div class="restaurant-stats mt-3 pt-3 border-top">
                            <div class="d-flex align-items-center text-muted small">
                                <span class="custom-icon icon-shop me-2" style="background-color: var(--bs-gray-500);"></span>
                                <?php echo htmlspecialchars($place['city']); ?>, <?php echo htmlspecialchars($place['country']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 p-3 pt-0">
                        <button type="button" class="btn btn-outline-primary w-100 hover-shadow" 
                            data-name="<?php echo htmlspecialchars($place['name']); ?>" 
                            data-description="<?php echo htmlspecialchars($place['description']); ?>" 
                            data-city="<?php echo htmlspecialchars($place['city']); ?>" 
                            data-country="<?php echo htmlspecialchars($place['country']); ?>" 
                            data-rating="<?php echo htmlspecialchars($place['rating']); ?>" 
                            data-category="<?php echo htmlspecialchars($place['category']); ?>">
                            View Details
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
                        <div class="mt-3 place-modal-actions">
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
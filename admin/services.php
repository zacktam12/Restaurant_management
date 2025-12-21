<?php
/**
 * External Services Management Page
 * Admin interface for managing external services (tours, hotels, taxis)
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Service.php';

$serviceManager = new Service();

// Handle delete confirmation
if (isset($_GET['confirm_delete']) && isset($_GET['type'])) {
    $serviceId = $_GET['confirm_delete'];
    $serviceType = $_GET['type'];
    
    // Display confirmation page
    $service = $serviceManager->getServiceById($serviceId);
    if ($service) {
        // We'll handle the actual deletion through a POST request
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_service':
                $result = $serviceManager->createService(
                    $_POST['service_type'],
                    $_POST['service_name'],
                    $_POST['service_description'],
                    $_POST['service_price'],
                    $_POST['service_image'] ?? null,
                    $_POST['service_rating'] ?? 0.0,
                    isset($_POST['service_available']) ? 1 : 0
                );
                $message = $result['message'];
                break;
                
            case 'update_service':
                $result = $serviceManager->updateService(
                    $_POST['id'],
                    $_POST['service_type'],
                    $_POST['service_name'],
                    $_POST['service_description'],
                    $_POST['service_price'],
                    $_POST['service_image'] ?? null,
                    $_POST['service_rating'] ?? 0.0,
                    isset($_POST['service_available']) ? 1 : 0
                );
                $message = $result['message'];
                break;
                
            case 'delete_service':
                $result = $serviceManager->deleteService($_POST['id']);
                $message = $result['message'];
                break;
                
            case 'toggle_availability':
                $result = $serviceManager->toggleAvailability($_POST['id']);
                $message = $result['message'];
                break;
                
            case 'edit_service':
                // For now, we'll just show a message that edit functionality would be implemented
                $message = 'Edit functionality would be implemented here for service ID: ' . $_POST['id'];
                break;
        }
    }
} else if (isset($_GET['confirm_delete']) && isset($_GET['type'])) {
    // Handle delete confirmation through GET parameters
    $serviceId = $_GET['confirm_delete'];
    $serviceType = $_GET['type'];
    
    // Show confirmation message
    $service = $serviceManager->getServiceById($serviceId);
    if ($service) {
        $message = 'Are you sure you want to delete "' . htmlspecialchars($service['name']) . '"?';
        $messageType = 'warning';
        $showDeleteConfirmation = true;
    }
} else if (isset($_GET['delete_confirmed']) && isset($_GET['id'])) {
    // Handle confirmed delete
    $result = $serviceManager->deleteService($_GET['id']);
    $message = $result['message'];
    $messageType = 'info';
}

// Get all services
$allServices = $serviceManager->getAllServices();

// Group services by type
$tours = array_filter($allServices, function($service) { return $service['type'] == 'tour'; });
$hotels = array_filter($allServices, function($service) { return $service['type'] == 'hotel'; });
$taxis = array_filter($allServices, function($service) { return $service['type'] == 'taxi'; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>External Services Management - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
                            <a class="nav-link active" href="../admin/services.php">
                                <i class="bi bi-gear"></i> External Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">External Services Management</h1>
                    <form method="GET">
                        <input type="hidden" name="show_add_form" value="1">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add New Service
                        </button>
                    </form>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo isset($messageType) ? $messageType : 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($showDeleteConfirmation) && $showDeleteConfirmation): ?>
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Confirm Deletion</h4>
                    <p>Are you sure you want to delete this service? This action cannot be undone.</p>
                    <hr>
                    <div class="d-flex">
                        <a href="services.php" class="btn btn-secondary me-2">Cancel</a>
                        <form method="GET" class="d-inline">
                            <input type="hidden" name="delete_confirmed" value="1">
                            <input type="hidden" name="id" value="<?php echo $_GET['confirm_delete']; ?>">
                            <button type="submit" class="btn btn-danger">Yes, Delete Service</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Services Tabs -->
                <div class="mb-4">
                    <form method="GET" class="d-inline">
                        <input type="hidden" name="tab" value="tours">
                        <button type="submit" class="btn btn-<?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'tours') ? 'primary' : 'outline-secondary'; ?> me-2">
                            <i class="bi bi-compass"></i> Tours
                        </button>
                    </form>
                    <form method="GET" class="d-inline">
                        <input type="hidden" name="tab" value="hotels">
                        <button type="submit" class="btn btn-<?php echo (isset($_GET['tab']) && $_GET['tab'] == 'hotels') ? 'primary' : 'outline-secondary'; ?> me-2">
                            <i class="bi bi-building"></i> Hotels
                        </button>
                    </form>
                    <form method="GET" class="d-inline">
                        <input type="hidden" name="tab" value="taxis">
                        <button type="submit" class="btn btn-<?php echo (isset($_GET['tab']) && $_GET['tab'] == 'taxis') ? 'primary' : 'outline-secondary'; ?>">
                            <i class="bi bi-car-front"></i> Taxis
                        </button>
                    </form>
                </div>

                <div>
                    <!-- Tours Tab -->
                    <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'tours'): ?>
                    <div>
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
                                        <div class="btn-group w-100" role="group">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="edit_service">
                                                <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                                <button type="submit" class="btn btn-outline-<?php echo $tour['available'] ? 'success' : 'secondary'; ?> btn-sm">
                                                    <i class="bi bi-<?php echo $tour['available'] ? 'check-circle' : 'x-circle'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="GET" class="d-inline">
                                                <input type="hidden" name="confirm_delete" value="<?php echo $tour['id']; ?>">
                                                <input type="hidden" name="type" value="tour">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Hotels Tab -->
                    <?php if (isset($_GET['tab']) && $_GET['tab'] == 'hotels'): ?>
                    <div>
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
                                        <div class="btn-group w-100" role="group">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="edit_service">
                                                <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">
                                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">
                                                <button type="submit" class="btn btn-outline-<?php echo $hotel['available'] ? 'success' : 'secondary'; ?> btn-sm">
                                                    <i class="bi bi-<?php echo $hotel['available'] ? 'check-circle' : 'x-circle'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="GET" class="d-inline">
                                                <input type="hidden" name="confirm_delete" value="<?php echo $hotel['id']; ?>">
                                                <input type="hidden" name="type" value="hotel">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Taxis Tab -->
                    <?php if (isset($_GET['tab']) && $_GET['tab'] == 'taxis'): ?>
                    <div>
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
                                        <div class="btn-group w-100" role="group">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="edit_service">
                                                <input type="hidden" name="id" value="<?php echo $taxi['id']; ?>">
                                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="id" value="<?php echo $taxi['id']; ?>">
                                                <button type="submit" class="btn btn-outline-<?php echo $taxi['available'] ? 'success' : 'secondary'; ?> btn-sm">
                                                    <i class="bi bi-<?php echo $taxi['available'] ? 'check-circle' : 'x-circle'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="GET" class="d-inline">
                                                <input type="hidden" name="confirm_delete" value="<?php echo $taxi['id']; ?>">
                                                <input type="hidden" name="type" value="taxi">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Service Form -->
    <?php if (isset($_GET['show_add_form']) && $_GET['show_add_form'] == '1'): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Add New Service</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="add_service">
                <div class="mb-3">
                    <label for="service_type" class="form-label">Service Type</label>
                    <select class="form-select" id="service_type" name="service_type" required>
                        <option value="">Select Service Type</option>
                        <option value="tour">Tour</option>
                        <option value="hotel">Hotel</option>
                        <option value="taxi">Taxi</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="service_name" class="form-label">Service Name</label>
                    <input type="text" class="form-control" id="service_name" name="service_name" required>
                </div>
                <div class="mb-3">
                    <label for="service_description" class="form-label">Description</label>
                    <textarea class="form-control" id="service_description" name="service_description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="service_price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="service_price" name="service_price" step="0.01" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="service_rating" class="form-label">Rating</label>
                    <input type="number" class="form-control" id="service_rating" name="service_rating" step="0.1" min="0" max="5" value="0">
                </div>
                <div class="mb-3">
                    <label for="service_image" class="form-label">Image URL</label>
                    <input type="text" class="form-control" id="service_image" name="service_image">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="service_available" name="service_available" value="1" checked>
                        <label class="form-check-label" for="service_available">Available</label>
                    </div>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="services.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>

<?php
$serviceManager->close();
?>
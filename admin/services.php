<?php
/**
 * External Services Management Page
 * Admin interface for managing external services (tours, hotels, taxis)
 */

session_start();

// Check if user is logged in and is admin
require_once '../backend/Permission.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Require admin access
Permission::requireAdmin($_SESSION['user']['role']);

require_once '../backend/config.php';
require_once '../backend/Service.php';
require_once '../backend/Alert.php';

$serviceManager = new Service();

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
                if ($result['success']) {
                    Alert::setSuccess($result['message']);
                } else {
                    Alert::setError($result['message']);
                }
                header("Location: services.php");
                exit();
                
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
                if ($result['success']) {
                    Alert::setSuccess($result['message']);
                } else {
                    Alert::setError($result['message']);
                }
                header("Location: services.php");
                exit();
                
            case 'delete_service':
                $result = $serviceManager->deleteService($_POST['id']);
                if ($result['success']) {
                    Alert::setSuccess($result['message']);
                } else {
                    Alert::setError($result['message']);
                }
                header("Location: services.php");
                exit();
                
            case 'toggle_availability':
                $result = $serviceManager->toggleAvailability($_POST['id']);
                if ($result['success']) {
                    Alert::setSuccess($result['message']);
                } else {
                    Alert::setError($result['message']);
                }
                header("Location: services.php");
                exit();
                
            case 'edit_service':
                Alert::setInfo('Edit functionality would be implemented here for service ID: ' . $_POST['id']);
                header("Location: services.php");
                exit();
        }
    }
} else if (isset($_GET['confirm_delete']) && isset($_GET['type'])) {
    $serviceId = $_GET['confirm_delete'];
    $service = $serviceManager->getServiceById($serviceId);
    if ($service) {
        $showDeleteConfirmation = true;
        $confirmService = $service;
    }
} else if (isset($_GET['delete_confirmed']) && isset($_GET['id'])) {
    $result = $serviceManager->deleteService($_GET['id']);
    if ($result['success']) {
        Alert::setSuccess($result['message']);
    } else {
        Alert::setError($result['message']);
    }
    header("Location: services.php");
    exit();
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
            <a class="navbar-brand" href="../admin/index.php">
                <span class="custom-icon icon-restaurant"></span> Restaurant Manager
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

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="position-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../admin/index.php">
                        <span class="custom-icon icon-speedometer2"></span> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/users.php">
                        <span class="custom-icon icon-people"></span> User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/restaurants.php">
                        <span class="custom-icon icon-shop"></span> Restaurants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/reservations.php">
                        <span class="custom-icon icon-calendar-check"></span> Reservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="../admin/services.php">
                        <span class="custom-icon icon-gear"></span> External Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/reports.php">
                        <span class="custom-icon icon-graph-up"></span> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/api_keys.php">
                        <span class="custom-icon icon-key"></span> API Keys
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/service_registry.php">
                        <span class="custom-icon icon-diagram-3"></span> Service Registry
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
            <h1 class="h2 fw-bold">External Services Management</h1>
            <form method="GET">
                <input type="hidden" name="show_add_form" value="1">
                <button type="submit" class="btn btn-primary shadow-sm">
                    <span class="custom-icon icon-plus me-2" style="background-color: white;"></span>Add New Service
                </button>
            </form>
        </div>

        <?php Alert::display(); ?>
        
        <?php if (isset($showDeleteConfirmation) && $showDeleteConfirmation): ?>
        <div class="page-overlay" role="dialog" aria-modal="true">
            <a class="page-overlay__backdrop" href="services.php" aria-label="Close"></a>
            <div class="page-overlay__panel">
                <div class="page-overlay__panel-header">
                    <h4 class="page-overlay__panel-title">Confirm Deletion</h4>
                    <a href="services.php" class="btn btn-secondary">Close</a>
                </div>
                <div class="page-overlay__panel-body">
                    <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($confirmService['name']); ?></strong>? This action cannot be undone.</p>
                    <div class="page-overlay__panel-actions">
                        <a href="services.php" class="btn btn-secondary">Cancel</a>
                        <form method="GET" class="d-inline">
                            <input type="hidden" name="delete_confirmed" value="1">
                            <input type="hidden" name="id" value="<?php echo $_GET['confirm_delete']; ?>">
                            <button type="submit" class="btn btn-danger">Yes, Delete Service</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Services Tabs -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-2 bg-light rounded">
                <div class="d-flex gap-2">
                    <form method="GET" class="flex-grow-1">
                        <input type="hidden" name="tab" value="tours">
                        <button type="submit" class="btn w-100 <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'tours') ? 'btn-white shadow-sm fw-bold text-primary' : 'btn-light text-muted'; ?> border-0">
                            <span class="custom-icon icon-compass me-2" style="background-color: currentColor;"></span>Tours
                        </button>
                    </form>
                    <form method="GET" class="flex-grow-1">
                        <input type="hidden" name="tab" value="hotels">
                        <button type="submit" class="btn w-100 <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'hotels') ? 'btn-white shadow-sm fw-bold text-primary' : 'btn-light text-muted'; ?> border-0">
                            <span class="custom-icon icon-shop me-2" style="background-color: currentColor;"></span>Hotels
                        </button>
                    </form>
                    <form method="GET" class="flex-grow-1">
                        <input type="hidden" name="tab" value="taxis">
                        <button type="submit" class="btn w-100 <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'taxis') ? 'btn-white shadow-sm fw-bold text-primary' : 'btn-light text-muted'; ?> border-0">
                            <span class="custom-icon icon-truck me-2" style="background-color: currentColor;"></span>Taxis
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <!-- Tours Tab -->
            <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'tours'): ?>
            <div>
                <div class="row g-4">
                    <?php if (empty($tours)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <span class="custom-icon icon-compass d-block mx-auto mb-3" style="width: 3rem; height: 3rem; background-color: var(--bs-gray-400);"></span>
                            <h3 class="text-muted">No tours available</h3>
                            <p class="text-secondary">Add a new tour service to get started</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($tours as $tour): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm service-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title fw-bold mb-0"><?php echo htmlspecialchars($tour['name']); ?></h5>
                                    <span class="badge bg-<?php echo $tour['available'] ? 'success' : 'secondary'; ?> bg-opacity-10 text-<?php echo $tour['available'] ? 'success' : 'secondary'; ?> border border-<?php echo $tour['available'] ? 'success' : 'secondary'; ?> border-opacity-25 rounded-pill">
                                        <?php echo $tour['available'] ? 'Available' : 'Unavailable'; ?>
                                    </span>
                                </div>
                                <p class="card-text text-muted small mb-4"><?php echo htmlspecialchars($tour['description']); ?></p>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="h5 mb-0 text-primary fw-bold">$<?php echo number_format($tour['price'], 2); ?></span>
                                    <span class="mx-2 text-muted">|</span>
                                    <div class="d-flex align-items-center text-warning small">
                                        <span class="fw-bold me-1"><?php echo htmlspecialchars($tour['rating']); ?></span> ★
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_availability">
                                        <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $tour['available'] ? 'warning' : 'success'; ?> rounded-pill px-3">
                                            <?php echo $tour['available'] ? 'Mark Unavailable' : 'Mark Available'; ?>
                                        </button>
                                    </form>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <span class="custom-icon icon-three-dots-vertical" style="background-color: var(--bs-gray-600);"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="edit_service">
                                                    <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <span class="custom-icon icon-pencil me-2" style="background-color: var(--bs-gray-600); width: 0.8em; height: 0.8em;"></span>Edit
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="GET" class="d-inline">
                                                    <input type="hidden" name="confirm_delete" value="<?php echo $tour['id']; ?>">
                                                    <input type="hidden" name="type" value="tour">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <span class="custom-icon icon-trash me-2" style="background-color: var(--bs-danger); width: 0.8em; height: 0.8em;"></span>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
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
                <div class="row g-4">
                    <?php if (empty($hotels)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <span class="custom-icon icon-shop d-block mx-auto mb-3" style="width: 3rem; height: 3rem; background-color: var(--bs-gray-400);"></span>
                            <h3 class="text-muted">No hotels available</h3>
                            <p class="text-secondary">Add a new hotel service to get started</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($hotels as $hotel): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm service-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title fw-bold mb-0"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                    <span class="badge bg-<?php echo $hotel['available'] ? 'success' : 'secondary'; ?> bg-opacity-10 text-<?php echo $hotel['available'] ? 'success' : 'secondary'; ?> border border-<?php echo $hotel['available'] ? 'success' : 'secondary'; ?> border-opacity-25 rounded-pill">
                                        <?php echo $hotel['available'] ? 'Available' : 'Unavailable'; ?>
                                    </span>
                                </div>
                                <p class="card-text text-muted small mb-4"><?php echo htmlspecialchars($hotel['description']); ?></p>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="h5 mb-0 text-primary fw-bold">$<?php echo number_format($hotel['price'], 2); ?><span class="fs-6 text-muted fw-normal">/night</span></span>
                                    <span class="mx-2 text-muted">|</span>
                                    <div class="d-flex align-items-center text-warning small">
                                        <span class="fw-bold me-1"><?php echo htmlspecialchars($hotel['rating']); ?></span> ★
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_availability">
                                        <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $hotel['available'] ? 'warning' : 'success'; ?> rounded-pill px-3">
                                            <?php echo $hotel['available'] ? 'Mark Unavailable' : 'Mark Available'; ?>
                                        </button>
                                    </form>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <span class="custom-icon icon-three-dots-vertical" style="background-color: var(--bs-gray-600);"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="edit_service">
                                                    <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <span class="custom-icon icon-pencil me-2" style="background-color: var(--bs-gray-600); width: 0.8em; height: 0.8em;"></span>Edit
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="GET" class="d-inline">
                                                    <input type="hidden" name="confirm_delete" value="<?php echo $hotel['id']; ?>">
                                                    <input type="hidden" name="type" value="hotel">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <span class="custom-icon icon-trash me-2" style="background-color: var(--bs-danger); width: 0.8em; height: 0.8em;"></span>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
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
                <div class="row g-4">
                    <?php if (empty($taxis)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <span class="custom-icon icon-truck d-block mx-auto mb-3" style="width: 3rem; height: 3rem; background-color: var(--bs-gray-400);"></span>
                            <h3 class="text-muted">No taxi services available</h3>
                            <p class="text-secondary">Add a new taxi service to get started</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($taxis as $taxi): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm service-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title fw-bold mb-0"><?php echo htmlspecialchars($taxi['name']); ?></h5>
                                    <span class="badge bg-<?php echo $taxi['available'] ? 'success' : 'secondary'; ?> bg-opacity-10 text-<?php echo $taxi['available'] ? 'success' : 'secondary'; ?> border border-<?php echo $taxi['available'] ? 'success' : 'secondary'; ?> border-opacity-25 rounded-pill">
                                        <?php echo $taxi['available'] ? 'Available' : 'Unavailable'; ?>
                                    </span>
                                </div>
                                <p class="card-text text-muted small mb-4"><?php echo htmlspecialchars($taxi['description']); ?></p>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="h5 mb-0 text-primary fw-bold">$<?php echo number_format($taxi['price'], 2); ?></span>
                                    <span class="mx-2 text-muted">|</span>
                                    <div class="d-flex align-items-center text-warning small">
                                        <span class="fw-bold me-1"><?php echo htmlspecialchars($taxi['rating']); ?></span> ★
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_availability">
                                        <input type="hidden" name="id" value="<?php echo $taxi['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $taxi['available'] ? 'warning' : 'success'; ?> rounded-pill px-3">
                                            <?php echo $taxi['available'] ? 'Mark Unavailable' : 'Mark Available'; ?>
                                        </button>
                                    </form>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <span class="custom-icon icon-three-dots-vertical" style="background-color: var(--bs-gray-600);"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="edit_service">
                                                    <input type="hidden" name="id" value="<?php echo $taxi['id']; ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <span class="custom-icon icon-pencil me-2" style="background-color: var(--bs-gray-600); width: 0.8em; height: 0.8em;"></span>Edit
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="GET" class="d-inline">
                                                    <input type="hidden" name="confirm_delete" value="<?php echo $taxi['id']; ?>">
                                                    <input type="hidden" name="type" value="taxi">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <span class="custom-icon icon-trash me-2" style="background-color: var(--bs-danger); width: 0.8em; height: 0.8em;"></span>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
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

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$serviceManager->close();
?>
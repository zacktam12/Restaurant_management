<?php
/**
 * Restaurant Management Page
 * Admin interface for managing restaurants
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
require_once '../backend/Restaurant.php';
require_once '../backend/Menu.php';

$restaurantManager = new Restaurant();
$menuManager = new Menu();

$currentUserRole = $_SESSION['user']['role'] ?? '';
$currentUserId = (int)($_SESSION['user']['id'] ?? 0);
$isManager = Permission::isManager($currentUserRole);

// Handle delete confirmation
if (isset($_GET['confirm_delete']) && isset($_GET['type'])) {
    $itemId = $_GET['confirm_delete'];
    $itemType = $_GET['type'];
    
    // We'll handle the actual deletion through a GET request
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_restaurant':
                if ($isManager) {
                    $message = 'You do not have permission to create restaurants.';
                    $messageType = 'danger';
                    break;
                }
                $result = $restaurantManager->createRestaurant(
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['cuisine'],
                    $_POST['address'],
                    $_POST['phone'],
                    $_POST['price_range'],
                    $_POST['image'] ?? null,
                    $_POST['seating_capacity'] ?? 0
                );
                $message = $result['message'];
                break;
                
            case 'update_restaurant':
                if ($isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$_POST['id'])) {
                    $message = 'You do not have permission to update this restaurant.';
                    $messageType = 'danger';
                    break;
                }
                $result = $restaurantManager->updateRestaurant(
                    $_POST['id'],
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['cuisine'],
                    $_POST['address'],
                    $_POST['phone'],
                    $_POST['price_range'],
                    $_POST['image'] ?? null,
                    $_POST['seating_capacity'] ?? 0
                );
                $message = $result['message'];
                break;
                
            case 'delete_restaurant':
                if ($isManager) {
                    $message = 'You do not have permission to delete restaurants.';
                    $messageType = 'danger';
                    break;
                }
                $result = $restaurantManager->deleteRestaurant($_POST['id']);
                $message = $result['message'];
                break;
                
            case 'add_menu_item':
                if ($isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$_POST['restaurant_id'])) {
                    $message = 'You do not have permission to manage this restaurant.';
                    $messageType = 'danger';
                    break;
                }
                $result = $menuManager->createMenuItem(
                    $_POST['restaurant_id'],
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['category'],
                    $_POST['image'] ?? null,
                    $_POST['available'] ?? 1
                );
                $message = $result['message'];
                break;
                
            case 'update_menu_item':
                $existingMenuItem = $menuManager->getMenuItemById((int)$_POST['id']);
                if (!$existingMenuItem) {
                    $message = 'Menu item not found.';
                    $messageType = 'danger';
                    break;
                }
                if ($isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$existingMenuItem['restaurant_id'])) {
                    $message = 'You do not have permission to manage this restaurant.';
                    $messageType = 'danger';
                    break;
                }
                $result = $menuManager->updateMenuItem(
                    $_POST['id'],
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['category'],
                    $_POST['image'] ?? null,
                    $_POST['available'] ?? 1
                );
                $message = $result['message'];
                break;
                
            case 'delete_menu_item':
                $existingMenuItem = $menuManager->getMenuItemById((int)$_POST['id']);
                if (!$existingMenuItem) {
                    $message = 'Menu item not found.';
                    $messageType = 'danger';
                    break;
                }
                if ($isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$existingMenuItem['restaurant_id'])) {
                    $message = 'You do not have permission to manage this restaurant.';
                    $messageType = 'danger';
                    break;
                }
                $result = $menuManager->deleteMenuItem($_POST['id']);
                $message = $result['message'];
                break;
                
            case 'edit_restaurant':
                // Show edit form for restaurant
                $restaurant = $restaurantManager->getRestaurantById($_POST['id']);
                if ($restaurant && $isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$restaurant['id'])) {
                    $message = 'You do not have permission to edit this restaurant.';
                    $messageType = 'danger';
                    break;
                }
                if ($restaurant) {
                    // We'll set a flag to show the edit form
                    $showEditForm = true;
                    $editRestaurant = $restaurant;
                } else {
                    $message = 'Restaurant not found.';
                }
                break;
                
            case 'edit_menu_item':
                // Show edit form for menu item
                $menuItem = $menuManager->getMenuItemById($_POST['id']);
                if ($menuItem && $isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$menuItem['restaurant_id'])) {
                    $message = 'You do not have permission to manage this restaurant.';
                    $messageType = 'danger';
                    break;
                }
                if ($menuItem) {
                    // We'll set a flag to show the edit form
                    $showEditMenuItemForm = true;
                    $editMenuItem = $menuItem;
                } else {
                    $message = 'Menu item not found.';
                }
                break;
        }
    }
} else if (isset($_GET['confirm_delete']) && isset($_GET['type'])) {
    // Handle delete confirmation through GET parameters
    $itemId = $_GET['confirm_delete'];
    $itemType = $_GET['type'];
    
    // Show confirmation message
    if ($itemType == 'restaurant') {
        $restaurant = $restaurantManager->getRestaurantById($itemId);
        if ($restaurant) {
            if ($isManager) {
                $message = 'You do not have permission to delete restaurants.';
                $messageType = 'danger';
            } else {
                $message = 'Are you sure you want to delete restaurant "' . htmlspecialchars($restaurant['name']) . '"? All menu items and reservations will also be deleted.';
                $messageType = 'warning';
                $showDeleteConfirmation = true;
                $deleteItemType = 'restaurant';
            }
        }
    } else if ($itemType == 'menu_item') {
        $menuItem = $menuManager->getMenuItemById($itemId);
        if ($menuItem) {
            if ($isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$menuItem['restaurant_id'])) {
                $message = 'You do not have permission to manage this restaurant.';
                $messageType = 'danger';
            } else {
                $message = 'Are you sure you want to delete menu item "' . htmlspecialchars($menuItem['name']) . '"?';
                $messageType = 'warning';
                $showDeleteConfirmation = true;
                $deleteItemType = 'menu_item';
            }
        }
    }
} else if (isset($_GET['delete_confirmed']) && isset($_GET['id']) && isset($_GET['type'])) {
    // Handle confirmed delete
    if ($_GET['type'] == 'restaurant') {
        if ($isManager) {
            $message = 'You do not have permission to delete restaurants.';
            $messageType = 'danger';
        } else {
            $result = $restaurantManager->deleteRestaurant($_GET['id']);
            $message = $result['message'];
        }
    } else if ($_GET['type'] == 'menu_item') {
        $menuItem = $menuManager->getMenuItemById((int)$_GET['id']);
        if ($menuItem && $isManager && !$restaurantManager->isManagerAssignedToRestaurant($currentUserId, (int)$menuItem['restaurant_id'])) {
            $message = 'You do not have permission to manage this restaurant.';
            $messageType = 'danger';
        } else {
            $result = $menuManager->deleteMenuItem($_GET['id']);
            $message = $result['message'];
        }
    }
    if (!isset($messageType)) {
        $messageType = 'info';
    }
}

// Get all restaurants
$restaurants = $isManager ? $restaurantManager->getRestaurantsForManager($currentUserId) : $restaurantManager->getAllRestaurants();

$allowedRestaurantIds = array_map(function($r) {
    return (int)$r['id'];
}, $restaurants);

// Get menu items for the first restaurant (or selected restaurant)
$selectedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 
                       (!empty($restaurants) ? $restaurants[0]['id'] : null);
$selectedRestaurantId = ($selectedRestaurantId && in_array($selectedRestaurantId, $allowedRestaurantIds, true)) ? $selectedRestaurantId : (!empty($allowedRestaurantIds) ? $allowedRestaurantIds[0] : null);
$menuItems = $selectedRestaurantId ? $menuManager->getMenuItemsByRestaurant($selectedRestaurantId) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management - Restaurant Management System</title>
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
                    <a class="nav-link active" href="restaurants.php">
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
                    <a class="nav-link" href="reports.php">
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
                    <h1 class="h2">Restaurant Management</h1>
                    <?php if (!$isManager): ?>
                    <form method="GET">
                        <input type="hidden" name="show_add_form" value="1">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add New Restaurant
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo isset($messageType) ? $messageType : 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($showDeleteConfirmation) && $showDeleteConfirmation): ?>
                <div class="page-overlay" role="dialog" aria-modal="true">
                    <a class="page-overlay__backdrop" href="restaurants.php" aria-label="Close"></a>
                    <div class="page-overlay__panel">
                        <div class="page-overlay__panel-header">
                            <h4 class="page-overlay__panel-title">Confirm Deletion</h4>
                            <a href="restaurants.php" class="btn btn-secondary">Close</a>
                        </div>
                        <div class="page-overlay__panel-body">
                            <p><?php echo htmlspecialchars($message); ?></p>
                            <div class="page-overlay__panel-actions">
                                <a href="restaurants.php" class="btn btn-secondary">Cancel</a>
                                <form method="GET" class="d-inline">
                                    <input type="hidden" name="delete_confirmed" value="1">
                                    <input type="hidden" name="id" value="<?php echo $_GET['confirm_delete']; ?>">
                                    <input type="hidden" name="type" value="<?php echo $deleteItemType; ?>">
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Add Restaurant Form -->
                <?php if (!$isManager && isset($_GET['show_add_form']) && $_GET['show_add_form'] == '1'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Add New Restaurant</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_restaurant">
                            <div class="mb-3">
                                <label for="name" class="form-label">Restaurant Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="cuisine" class="form-label">Cuisine</label>
                                <input type="text" class="form-control" id="cuisine" name="cuisine" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="price_range" class="form-label">Price Range</label>
                                <select class="form-select" id="price_range" name="price_range" required>
                                    <option value="$">$</option>
                                    <option value="$$">$$</option>
                                    <option value="$$$">$$$</option>
                                    <option value="$$$$">$$$$</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="seating_capacity" class="form-label">Seating Capacity</label>
                                <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" min="0">
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="image" name="image">
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="restaurants.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Restaurant</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($showEditForm) && $showEditForm): ?>
                <div class="page-overlay" role="dialog" aria-modal="true">
                    <a class="page-overlay__backdrop" href="restaurants.php" aria-label="Close"></a>
                    <div class="page-overlay__panel page-overlay__panel--drawer">
                        <div class="page-overlay__panel-header">
                            <h5 class="page-overlay__panel-title">Edit Restaurant</h5>
                            <a href="restaurants.php" class="btn btn-secondary">Close</a>
                        </div>
                        <div class="page-overlay__panel-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_restaurant">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editRestaurant['id']); ?>">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Restaurant Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($editRestaurant['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3" required><?php echo htmlspecialchars($editRestaurant['description']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_cuisine" class="form-label">Cuisine</label>
                                <input type="text" class="form-control" id="edit_cuisine" name="cuisine" value="<?php echo htmlspecialchars($editRestaurant['cuisine']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_address" class="form-label">Address</label>
                                <textarea class="form-control" id="edit_address" name="address" rows="2" required><?php echo htmlspecialchars($editRestaurant['address']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($editRestaurant['phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_price_range" class="form-label">Price Range</label>
                                <select class="form-select" id="edit_price_range" name="price_range" required>
                                    <option value="$" <?php echo $editRestaurant['price_range'] == '$' ? 'selected' : ''; ?>>$</option>
                                    <option value="$$" <?php echo $editRestaurant['price_range'] == '$$' ? 'selected' : ''; ?>>$$</option>
                                    <option value="$$$" <?php echo $editRestaurant['price_range'] == '$$$' ? 'selected' : ''; ?>>$$$</option>
                                    <option value="$$$$" <?php echo $editRestaurant['price_range'] == '$$$$' ? 'selected' : ''; ?>>$$$$</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_seating_capacity" class="form-label">Seating Capacity</label>
                                <input type="number" class="form-control" id="edit_seating_capacity" name="seating_capacity" min="0" value="<?php echo htmlspecialchars($editRestaurant['seating_capacity']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="edit_image" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="edit_image" name="image" value="<?php echo htmlspecialchars($editRestaurant['image'] ?? ''); ?>">
                            </div>
                            <div class="page-overlay__panel-actions">
                                <a href="restaurants.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Restaurant</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Restaurants List -->
                <div class="row">
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
                                        <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($restaurant['rating']); ?>/5.0
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="?restaurant_id=<?php echo $restaurant['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list"></i> Menu
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="edit_restaurant">
                                                    <input type="hidden" name="id" value="<?php echo $restaurant['id']; ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-pencil me-2"></i>Edit
                                                    </button>
                                                </form>
                                            </li>
                                            <?php if (!$isManager): ?>
                                            <li>
                                                <form method="GET" class="d-inline">
                                                    <input type="hidden" name="confirm_delete" value="<?php echo $restaurant['id']; ?>">
                                                    <input type="hidden" name="type" value="restaurant">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add Menu Item Form -->
                <?php if (isset($_GET['show_add_menu_form']) && $_GET['show_add_menu_form'] == '1' && isset($_GET['restaurant_id'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Add Menu Item</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_menu_item">
                            <input type="hidden" name="restaurant_id" value="<?php echo $_GET['restaurant_id']; ?>">
                            <div class="mb-3">
                                <label for="menu_name" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="menu_name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="menu_description" class="form-label">Description</label>
                                <textarea class="form-control" id="menu_description" name="description" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="appetizer">Appetizer</option>
                                    <option value="main">Main Course</option>
                                    <option value="dessert">Dessert</option>
                                    <option value="beverage">Beverage</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="menu_image" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="menu_image" name="image">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="available" name="available" value="1" checked>
                                    <label class="form-check-label" for="available">Available</label>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="restaurants.php?restaurant_id=<?php echo $_GET['restaurant_id']; ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Menu Item</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($showEditMenuItemForm) && $showEditMenuItemForm): ?>
                <div class="page-overlay" role="dialog" aria-modal="true">
                    <a class="page-overlay__backdrop" href="restaurants.php" aria-label="Close"></a>
                    <div class="page-overlay__panel page-overlay__panel--drawer">
                        <div class="page-overlay__panel-header">
                            <h5 class="page-overlay__panel-title">Edit Menu Item</h5>
                            <a href="restaurants.php" class="btn btn-secondary">Close</a>
                        </div>
                        <div class="page-overlay__panel-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_menu_item">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editMenuItem['id']); ?>">
                            <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($editMenuItem['restaurant_id']); ?>">
                            <div class="mb-3">
                                <label for="edit_menu_name" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="edit_menu_name" name="name" value="<?php echo htmlspecialchars($editMenuItem['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_menu_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_menu_description" name="description" rows="2" required><?php echo htmlspecialchars($editMenuItem['description']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($editMenuItem['price']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_category" class="form-label">Category</label>
                                <select class="form-select" id="edit_category" name="category" required>
                                    <option value="appetizer" <?php echo $editMenuItem['category'] == 'appetizer' ? 'selected' : ''; ?>>Appetizer</option>
                                    <option value="main" <?php echo $editMenuItem['category'] == 'main' ? 'selected' : ''; ?>>Main Course</option>
                                    <option value="dessert" <?php echo $editMenuItem['category'] == 'dessert' ? 'selected' : ''; ?>>Dessert</option>
                                    <option value="beverage" <?php echo $editMenuItem['category'] == 'beverage' ? 'selected' : ''; ?>>Beverage</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_menu_image" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="edit_menu_image" name="image" value="<?php echo htmlspecialchars($editMenuItem['image'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_available" name="available" value="1" <?php echo $editMenuItem['available'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="edit_available">Available</label>
                                </div>
                            </div>
                            <div class="page-overlay__panel-actions">
                                <a href="restaurants.php?restaurant_id=<?php echo htmlspecialchars($editMenuItem['restaurant_id']); ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Menu Item</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($selectedRestaurantId): ?>
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Menu Items</h5>
                        <form method="GET">
                            <input type="hidden" name="show_add_menu_form" value="1">
                            <input type="hidden" name="restaurant_id" value="<?php echo $selectedRestaurantId; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg"></i> Add Menu Item
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th>Available</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menuItems as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo ucfirst($item['category']); ?></td>
                                        <td>
                                            <?php if ($item['available']): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="edit_menu_item">
                                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-pencil me-2"></i>Edit
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="GET" class="d-inline">
                                                            <input type="hidden" name="confirm_delete" value="<?php echo $item['id']; ?>">
                                                            <input type="hidden" name="type" value="menu_item">
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i>Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$restaurantManager->close();
$menuManager->close();
?>
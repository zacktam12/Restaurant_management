<?php
/**
 * Restaurant Management Page
 * Admin interface for managing restaurants
 */

session_start();

// Check if user is logged in and is admin/manager
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'manager')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Menu.php';

$restaurantManager = new Restaurant();
$menuManager = new Menu();

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
                $result = $restaurantManager->deleteRestaurant($_POST['id']);
                $message = $result['message'];
                break;
                
            case 'add_menu_item':
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
                $result = $menuManager->deleteMenuItem($_POST['id']);
                $message = $result['message'];
                break;
                
            case 'edit_restaurant':
                // For now, we'll just show a message that edit functionality would be implemented
                $message = 'Edit functionality would be implemented here for restaurant ID: ' . $_POST['id'];
                break;
                
            case 'edit_menu_item':
                // For now, we'll just show a message that edit functionality would be implemented
                $message = 'Edit functionality would be implemented here for menu item ID: ' . $_POST['id'];
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
            $message = 'Are you sure you want to delete restaurant "' . htmlspecialchars($restaurant['name']) . '"? All menu items and reservations will also be deleted.';
            $messageType = 'warning';
            $showDeleteConfirmation = true;
            $deleteItemType = 'restaurant';
        }
    } else if ($itemType == 'menu_item') {
        $menuItem = $menuManager->getMenuItemById($itemId);
        if ($menuItem) {
            $message = 'Are you sure you want to delete menu item "' . htmlspecialchars($menuItem['name']) . '"?';
            $messageType = 'warning';
            $showDeleteConfirmation = true;
            $deleteItemType = 'menu_item';
        }
    }
} else if (isset($_GET['delete_confirmed']) && isset($_GET['id']) && isset($_GET['type'])) {
    // Handle confirmed delete
    if ($_GET['type'] == 'restaurant') {
        $result = $restaurantManager->deleteRestaurant($_GET['id']);
        $message = $result['message'];
    } else if ($_GET['type'] == 'menu_item') {
        $result = $menuManager->deleteMenuItem($_GET['id']);
        $message = $result['message'];
    }
    $messageType = 'info';
}

// Get all restaurants
$restaurants = $restaurantManager->getAllRestaurants();

// Get menu items for the first restaurant (or selected restaurant)
$selectedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 
                       (!empty($restaurants) ? $restaurants[0]['id'] : null);
$menuItems = $selectedRestaurantId ? $menuManager->getMenuItemsByRestaurant($selectedRestaurantId) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management - Restaurant Management System</title>
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
                            <a class="nav-link active" href="../admin/restaurants.php">
                                <i class="bi bi-shop"></i> Restaurants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/reservations.php">
                                <i class="bi bi-calendar-check"></i> Reservations
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
                    <h1 class="h2">Restaurant Management</h1>
                    <form method="GET">
                        <input type="hidden" name="show_add_form" value="1">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add New Restaurant
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
                    <p><?php echo htmlspecialchars($message); ?></p>
                    <hr>
                    <div class="d-flex">
                        <a href="restaurants.php" class="btn btn-secondary me-2">Cancel</a>
                        <form method="GET" class="d-inline">
                            <input type="hidden" name="delete_confirmed" value="1">
                            <input type="hidden" name="id" value="<?php echo $_GET['confirm_delete']; ?>">
                            <input type="hidden" name="type" value="<?php echo $deleteItemType; ?>">
                            <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Add Restaurant Form -->
                <?php if (isset($_GET['show_add_form']) && $_GET['show_add_form'] == '1'): ?>
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
                                <div class="btn-group w-100" role="group">
                                    <a href="?restaurant_id=<?php echo $restaurant['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list"></i> Menu
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="edit_restaurant">
                                        <input type="hidden" name="id" value="<?php echo $restaurant['id']; ?>">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                    </form>
                                    <form method="GET" class="d-inline">
                                        <input type="hidden" name="confirm_delete" value="<?php echo $restaurant['id']; ?>">
                                        <input type="hidden" name="type" value="restaurant">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
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
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="edit_menu_item">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </form>
                                            <form method="GET" class="d-inline">
                                                <input type="hidden" name="confirm_delete" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="type" value="menu_item">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
        </div>
    </div>

</body>
</html>

<?php
$restaurantManager->close();
$menuManager->close();
?>
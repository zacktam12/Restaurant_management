<?php
/**
 * Manager Menu Management
 * CRUD operations for menu items
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'manager') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Menu.php';
require_once '../backend/Alert.php';

$restaurantManager = new Restaurant();
$menuManager = new Menu();

// Get manager's restaurants
$myRestaurants = $restaurantManager->getRestaurantsByManager($_SESSION['user']['id']);
$restaurantIds = array_column($myRestaurants, 'id');

// Selected restaurant
$selectedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : ($myRestaurants[0]['id'] ?? 0);

// Verify ownership
if ($selectedRestaurantId && !in_array($selectedRestaurantId, $restaurantIds)) {
    Alert::setError('You can only manage menu for your own restaurants.');
    header('Location: menu.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $result = $menuManager->addMenuItem(
            $_POST['restaurant_id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['category'],
            $_POST['image'] ?? null
        );
        
        if ($result['success']) {
            Alert::setSuccess('Menu item added successfully!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'edit') {
        $result = $menuManager->updateMenuItem(
            $_POST['id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['category'],
            $_POST['available'],
            $_POST['image'] ?? null
        );
        
        if ($result['success']) {
            Alert::setSuccess('Menu item updated successfully!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'delete') {
        $result = $menuManager->deleteMenuItem($_POST['id']);
        if ($result['success']) {
            Alert::setSuccess('Menu item deleted successfully!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'toggle') {
        $result = $menuManager->toggleAvailability($_POST['id']);
        if ($result['success']) {
            Alert::setSuccess('Availability toggled!');
        } else {
            Alert::setError($result['message']);
        }
    }
    
    header('Location: menu.php?restaurant_id=' . ($_POST['restaurant_id'] ?? $selectedRestaurantId));
    exit();
}

// Check if editing
$editItem = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editItem = $menuManager->getMenuItemById($_GET['id']);
}

$showForm = isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit');
$menuItems = $selectedRestaurantId ? $menuManager->getMenuByRestaurant($selectedRestaurantId) : [];

// Group by category
$menuByCategory = [];
foreach ($menuItems as $item) {
    $menuByCategory[$item['category']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Manager</title>
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .action-dropdown {
            position: relative;
            display: inline-block;
        }
        .action-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            z-index: 1050; /* Higher than sticky headers */
            min-width: 180px;
            padding: 0.5rem 0;
            margin-top: 0.125rem;
            font-size: 0.875rem;
            color: #212529;
            text-align: left;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }
        .action-dropdown-menu.show {
            display: block;
        }
        .action-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.5rem 1rem;
            clear: both;
            font-weight: 500;
            color: #4b5563;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            cursor: pointer;
            text-decoration: none;
        }
        .action-dropdown-item:hover {
            background-color: #f3f4f6;
            color: #111827;
        }
        .action-dropdown-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.25rem;
            color: #9ca3af;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: color 0.2s, background-color 0.2s;
        }
        .action-dropdown-toggle:hover, .action-dropdown-toggle[aria-expanded="true"] {
            color: #111827;
            background-color: #f3f4f6;
        }
        .action-dropdown-divider {
            height: 0;
            margin: 0.5rem 0;
            overflow: hidden;
            border-top: 1px solid #e5e7eb;
        }
        .action-dropdown-item.danger {
            color: #dc2626;
        }
        .action-dropdown-item.danger:hover {
            background-color: #fef2f2;
        }
        
        /* Ensure table doesn't clip dropdowns */
        .table-responsive {
            overflow: visible !important; 
        }
        td {
            position: relative; /* Anchor for dropdown */
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ) Manager
            </a>
            <ul class="navbar-nav">
                <li><a class="nav-link" href="index.php">Dashboard</a></li>
                <li><a class="nav-link" href="analytics.php">Analytics</a></li>
                <li><a class="nav-link" href="restaurants.php">My Restaurants</a></li>
                <li><a class="nav-link" href="reservations.php">Reservations</a></li>
                <li><a class="nav-link active" href="menu.php">Menu</a></li>
                <li>
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle" onclick="toggleUserDropdown(this)" type="button">
                            <div class="user-avatar">
                                <?php if (!empty($_SESSION['user']['profile_image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($_SESSION['user']['profile_image']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['user']['name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
                            </div>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        <div class="user-dropdown-menu">
                            <div class="user-dropdown-header">
                                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></div>
                            </div>
                            <div class="user-dropdown-divider"></div>
                            <a href="profile.php" class="user-dropdown-item">
                                <span class="icon">👤</span>
                                Profile
                            </a>
                            <a href="../logout.php" class="user-dropdown-item logout">
                                <span class="icon">🚪</span>
                                Logout
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container py-5">
        <?php Alert::display(); ?>
        
        <?php if (empty($myRestaurants)): ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <h3 class="text-muted">No restaurants assigned</h3>
                <p>Contact the administrator to assign restaurants to your account.</p>
            </div>
        </div>
        <?php else: ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Menu Management</h1>
            <?php if (!$showForm): ?>
            <a href="menu.php?action=add&restaurant_id=<?php echo $selectedRestaurantId; ?>" class="btn btn-primary">+ Add Menu Item</a>
            <?php else: ?>
            <a href="menu.php?restaurant_id=<?php echo $selectedRestaurantId; ?>" class="btn btn-secondary">Back to Menu</a>
            <?php endif; ?>
        </div>
        
        <!-- Restaurant Selector -->
        <?php if (!$showForm): ?>
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex gap-3 align-items-end">
                    <div class="form-group mb-0" style="flex: 1;">
                        <label for="restaurant_id" class="form-label">Select Restaurant</label>
                        <select class="form-control form-select" id="restaurant_id" name="restaurant_id" onchange="this.form.submit()">
                            <?php foreach ($myRestaurants as $restaurant): ?>
                            <option value="<?php echo $restaurant['id']; ?>" <?php echo $selectedRestaurantId == $restaurant['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($restaurant['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($showForm): ?>
        <!-- Add/Edit Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $editItem ? 'Edit Menu Item' : 'Add New Menu Item'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editItem ? 'edit' : 'add'; ?>">
                    <input type="hidden" name="restaurant_id" value="<?php echo $selectedRestaurantId; ?>">
                    <?php if ($editItem): ?>
                    <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($editItem['name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label for="price" class="form-label">Price ($) *</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                       value="<?php echo htmlspecialchars($editItem['price'] ?? ''); ?>" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-control form-select" id="category" name="category" required>
                                    <option value="appetizer" <?php echo ($editItem['category'] ?? '') === 'appetizer' ? 'selected' : ''; ?>>Appetizer</option>
                                    <option value="main" <?php echo ($editItem['category'] ?? '') === 'main' ? 'selected' : ''; ?>>Main Course</option>
                                    <option value="dessert" <?php echo ($editItem['category'] ?? '') === 'dessert' ? 'selected' : ''; ?>>Dessert</option>
                                    <option value="beverage" <?php echo ($editItem['category'] ?? '') === 'beverage' ? 'selected' : ''; ?>>Beverage</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="image" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image" name="image" 
                                       value="<?php echo htmlspecialchars($editItem['image'] ?? ''); ?>">
                            </div>
                        </div>
                        <?php if ($editItem): ?>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="available" class="form-label">Availability</label>
                                <select class="form-control form-select" id="available" name="available">
                                    <option value="1" <?php echo ($editItem['available'] ?? 1) == 1 ? 'selected' : ''; ?>>Available</option>
                                    <option value="0" <?php echo ($editItem['available'] ?? 1) == 0 ? 'selected' : ''; ?>>Unavailable</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update Item' : 'Add Item'; ?></button>
                        <a href="menu.php?restaurant_id=<?php echo $selectedRestaurantId; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Menu Items -->
        <?php if (empty($menuItems)): ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <h3 class="text-muted">No menu items yet</h3>
                <p>Add your first menu item!</p>
                <a href="menu.php?action=add&restaurant_id=<?php echo $selectedRestaurantId; ?>" class="btn btn-primary mt-2">+ Add Menu Item</a>
            </div>
        </div>
        <?php else: ?>
        <?php 
        $categories = ['appetizer' => 'Appetizers', 'main' => 'Main Courses', 'dessert' => 'Desserts', 'beverage' => 'Beverages'];
        foreach ($categories as $catKey => $catName): 
            if (!isset($menuByCategory[$catKey])) continue;
        ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $catName; ?></h5>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menuByCategory[$catKey] as $item): ?>
                            <tr>
                                <td class="ps-4"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $item['available'] ? 'success' : 'danger'; ?>">
                                        <?php echo $item['available'] ? 'Available' : 'Unavailable'; ?>
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <div class="action-dropdown">
                                        <button class="action-dropdown-toggle" onclick="toggleDropdown(this)" type="button">
                                            ⋯
                                        </button>
                                        <div class="action-dropdown-menu">
                                            <a href="menu.php?action=edit&id=<?php echo $item['id']; ?>&restaurant_id=<?php echo $selectedRestaurantId; ?>" class="action-dropdown-item">
                                                <span class="icon">✏️</span>
                                                Edit
                                            </a>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="restaurant_id" value="<?php echo $selectedRestaurantId; ?>">
                                                <button type="submit" class="action-dropdown-item">
                                                    <span class="icon">🔄</span>
                                                    Toggle Status
                                                </button>
                                            </form>
                                            <div class="action-dropdown-divider"></div>
                                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete this item?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="restaurant_id" value="<?php echo $selectedRestaurantId; ?>">
                                                <button type="submit" class="action-dropdown-item danger">
                                                    <span class="icon">🗑️</span>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
    <script>
    // Dropdown functionality with Fixed Positioning to escape table clipping
    function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;
        const allDropdowns = document.querySelectorAll('.action-dropdown-menu');
        
        // Close all other dropdowns
        allDropdowns.forEach(menu => {
            if (menu !== dropdown) {
                menu.classList.remove('show');
                menu.style.position = '';
                menu.style.top = '';
                menu.style.left = '';
            }
        });
        
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            dropdown.style.position = '';
        } else {
            dropdown.classList.add('show');
            
            // Get button coordinates
            const rect = button.getBoundingClientRect();
            
            // Apply fixed positioning relative to viewport
            dropdown.style.position = 'fixed';
            dropdown.style.zIndex = '9999';
            dropdown.style.minWidth = '160px';
            
            // Align: Top of menu to Bottom of button
            dropdown.style.top = (rect.bottom + 2) + 'px';
            
            // Align: Right of menu to Right of button (since it's on the right edge)
            // We need to verify if it fits. 
            // Default: Align right edges
            // rect.right is the right edge of button. 
            // We want menu.right = rect.right -> menu.left = rect.right - menuWidth.
            // Since we don't know exact width yet (auto), let's guess standard (~160px) or use right property.
            // Using 'right' with fixed positioning is tricky if not setting left/width.
            // Safest: set left based on calculation. 
            // Let's set it approx:
            
            const menuWidth = 160; 
            dropdown.style.left = (rect.right - menuWidth) + 'px';
            
            // Optional: Check if bottom clips screen
            if (rect.bottom + 200 > window.innerHeight) {
               // Open upwards
               dropdown.style.top = 'auto';
               dropdown.style.bottom = (window.innerHeight - rect.top + 2) + 'px';
            }
        }
    }
    
    // Close dropdowns on click outside or Scroll
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-dropdown') && !event.target.closest('.action-dropdown-menu')) {
            document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
                menu.style.position = '';
            });
        }
    });
    
    // Close on scroll to prevent floating menu
    window.addEventListener('scroll', function() {
        document.querySelectorAll('.action-dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
            menu.style.position = '';
        });
    }, true);

    function toggleUserDropdown(button) {
        const dropdown = button.closest('.user-dropdown');
        const menu = dropdown.querySelector('.user-dropdown-menu');
        const allUserDropdowns = document.querySelectorAll('.user-dropdown');
        
        allUserDropdowns.forEach(d => {
            if (d !== dropdown) {
                d.classList.remove('show');
                d.querySelector('.user-dropdown-menu').classList.remove('show');
            }
        });
        
        dropdown.classList.toggle('show');
        menu.classList.toggle('show');
    }
    
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.user-dropdown')) {
            document.querySelectorAll('.user-dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
                dropdown.querySelector('.user-dropdown-menu').classList.remove('show');
            });
        }
    });
    </script>
</body>
</html>
<?php
$restaurantManager->close();
$menuManager->close();
?>

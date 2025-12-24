<?php
/**
 * Admin Restaurant Management
 * CRUD operations for restaurants
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/User.php';
require_once '../backend/Alert.php';

$restaurantManager = new Restaurant();
$userManager = new User();

// Get managers for dropdown
$managers = $userManager->getUsersByRole('manager');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $result = $restaurantManager->createRestaurant(
            $_POST['name'],
            $_POST['description'],
            $_POST['cuisine'],
            $_POST['address'],
            $_POST['phone'],
            $_POST['price_range'],
            $_POST['seating_capacity'],
            $_POST['manager_id'],
            $_POST['image'] ?? null
        );
        
        if ($result['success']) {
            Alert::setSuccess('Restaurant created successfully!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'edit') {
        $result = $restaurantManager->updateRestaurant(
            $_POST['id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['cuisine'],
            $_POST['address'],
            $_POST['phone'],
            $_POST['price_range'],
            $_POST['seating_capacity'],
            $_POST['image'] ?? null,
            $_POST['manager_id'] ?? null
        );
        
        if ($result['success']) {
            Alert::setSuccess('Restaurant updated successfully!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'delete') {
        $result = $restaurantManager->deleteRestaurant($_POST['id']);
        if ($result['success']) {
            Alert::setSuccess('Restaurant deleted successfully!');
        } else {
            Alert::setError($result['message']);
        }
    }
    
    header('Location: restaurants.php');
    exit();
}

// Check if editing
$editRestaurant = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editRestaurant = $restaurantManager->getRestaurantById($_GET['id']);
}

$showForm = isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit');
$restaurants = $restaurantManager->getAllRestaurants();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Restaurants - Admin</title>
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ) Admin
            </a>
            <ul class="navbar-nav" id="navbarNav">
                <li><a class="nav-link" href="index.php">Dashboard</a></li>
                <li><a class="nav-link" href="analytics.php">Analytics</a></li>
                <li><a class="nav-link active" href="restaurants.php">Restaurants</a></li>
                <li><a class="nav-link" href="reservations.php">Reservations</a></li>
                <li><a class="nav-link" href="users.php">Users</a></li>
            </ul>
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-left: auto;">
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
                <div class="menu-toggle" onclick="toggleMenu()">☰</div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php Alert::display(); ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Restaurants</h1>
            <?php if (!$showForm): ?>
            <a href="restaurants.php?action=add" class="btn btn-primary">+ Add Restaurant</a>
            <?php else: ?>
            <a href="restaurants.php" class="btn btn-secondary">Back to List</a>
            <?php endif; ?>
        </div>
        
        <?php if ($showForm): ?>
        <!-- Add/Edit Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $editRestaurant ? 'Edit Restaurant' : 'Add New Restaurant'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editRestaurant ? 'edit' : 'add'; ?>">
                    <?php if ($editRestaurant): ?>
                    <input type="hidden" name="id" value="<?php echo $editRestaurant['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="name" class="form-label">Restaurant Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($editRestaurant['name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="cuisine" class="form-label">Cuisine Type *</label>
                                <input type="text" class="form-control" id="cuisine" name="cuisine" 
                                       value="<?php echo htmlspecialchars($editRestaurant['cuisine'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($editRestaurant['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address *</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?php echo htmlspecialchars($editRestaurant['address'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($editRestaurant['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="price_range" class="form-label">Price Range *</label>
                                <select class="form-control form-select" id="price_range" name="price_range" required>
                                    <option value="$" <?php echo ($editRestaurant['price_range'] ?? '') === '$' ? 'selected' : ''; ?>>$ - Budget</option>
                                    <option value="$$" <?php echo ($editRestaurant['price_range'] ?? '') === '$$' ? 'selected' : ''; ?>>$$ - Moderate</option>
                                    <option value="$$$" <?php echo ($editRestaurant['price_range'] ?? '') === '$$$' ? 'selected' : ''; ?>>$$$ - Upscale</option>
                                    <option value="$$$$" <?php echo ($editRestaurant['price_range'] ?? '') === '$$$$' ? 'selected' : ''; ?>>$$$$ - Premium</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="seating_capacity" class="form-label">Seating Capacity *</label>
                                <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" 
                                       value="<?php echo htmlspecialchars($editRestaurant['seating_capacity'] ?? '50'); ?>" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="manager_id" class="form-label">Assign Manager *</label>
                                <select class="form-control form-select" id="manager_id" name="manager_id" required>
                                    <option value="">Select Manager</option>
                                    <?php foreach ($managers as $manager): ?>
                                    <option value="<?php echo $manager['id']; ?>" 
                                        <?php echo ($editRestaurant['manager_id'] ?? '') == $manager['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($manager['name']); ?> (<?php echo htmlspecialchars($manager['email']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="image" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image" name="image" 
                                       value="<?php echo htmlspecialchars($editRestaurant['image'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo $editRestaurant ? 'Update Restaurant' : 'Create Restaurant'; ?></button>
                        <a href="restaurants.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Restaurant List -->
        <div class="card">
            <div class="card-body px-0">
                <?php if (empty($restaurants)): ?>
                <p class="text-center text-muted py-4">No restaurants found. Add your first restaurant!</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Cuisine</th>
                                <th>Manager</th>
                                <th>Price</th>
                                <th>Rating</th>
                                <th>Capacity</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($restaurants as $restaurant): ?>
                            <tr>
                                <td class="ps-4"><?php echo htmlspecialchars($restaurant['name']); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($restaurant['cuisine']); ?></span></td>
                                <td><?php echo htmlspecialchars($restaurant['manager_name'] ?? 'Unassigned'); ?></td>
                                <td><?php echo htmlspecialchars($restaurant['price_range']); ?></td>
                                <td><?php echo $restaurant['rating']; ?> / 5</td>
                                <td><?php echo $restaurant['seating_capacity']; ?></td>
                                <td class="pe-4">
                                    <div class="action-dropdown">
                                        <button class="action-dropdown-toggle" onclick="toggleDropdown(this)" type="button">
                                            ⋯
                                        </button>
                                        <div class="action-dropdown-menu">
                                            <a href="restaurants.php?action=edit&id=<?php echo $restaurant['id']; ?>" class="action-dropdown-item">
                                                <span class="icon">✏️</span>
                                                Edit
                                            </a>
                                            <div class="action-dropdown-divider"></div>
                                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this restaurant?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $restaurant['id']; ?>">
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
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    function toggleMenu() {
        document.getElementById('navbarNav').classList.toggle('active');
    }
    // Dropdown toggle functionality
    function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;
        const allDropdowns = document.querySelectorAll('.action-dropdown-menu');
        
        // Close all other dropdowns
        allDropdowns.forEach(menu => {
            if (menu !== dropdown) {
                menu.classList.remove('show');
            }
        });
        
        // Toggle current dropdown
        dropdown.classList.toggle('show');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
    </script>
    <script>
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
$userManager->close();
?>

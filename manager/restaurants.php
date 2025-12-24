<?php
/**
 * Manager Restaurant Management
 * View and manage assigned restaurants
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
require_once '../backend/Alert.php';

$restaurantManager = new Restaurant();

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $result = $restaurantManager->updateRestaurant(
        $_POST['id'],
        $_POST['name'],
        $_POST['description'],
        $_POST['cuisine'],
        $_POST['address'],
        $_POST['phone'],
        $_POST['price_range'],
        $_POST['seating_capacity'],
        $_POST['image'] ?? null
    );
    
    if ($result['success']) {
        Alert::setSuccess('Restaurant updated successfully!');
    } else {
        Alert::setError($result['message']);
    }
    
    header('Location: restaurants.php');
    exit();
}

// Get manager's restaurants
$myRestaurants = $restaurantManager->getRestaurantsByManager($_SESSION['user']['id']);

// Check if editing
$editRestaurant = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editRestaurant = $restaurantManager->getRestaurantById($_GET['id']);
    // Verify ownership
    if ($editRestaurant && $editRestaurant['manager_id'] != $_SESSION['user']['id']) {
        Alert::setError('You can only edit your own restaurants.');
        header('Location: restaurants.php');
        exit();
    }
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'edit' && $editRestaurant;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Restaurants - Manager</title>
    <link href="../css/style.css" rel="stylesheet">
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
                <li><a class="nav-link active" href="restaurants.php">My Restaurants</a></li>
                <li><a class="nav-link" href="reservations.php">Reservations</a></li>
                <li><a class="nav-link" href="menu.php">Menu</a></li>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Restaurants</h1>
            <?php if ($showForm): ?>
            <a href="restaurants.php" class="btn btn-secondary">Back to List</a>
            <?php endif; ?>
        </div>
        
        <?php if ($showForm): ?>
        <!-- Edit Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Restaurant</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editRestaurant['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="name" class="form-label">Restaurant Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($editRestaurant['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="cuisine" class="form-label">Cuisine Type *</label>
                                <input type="text" class="form-control" id="cuisine" name="cuisine" 
                                       value="<?php echo htmlspecialchars($editRestaurant['cuisine']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($editRestaurant['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address *</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?php echo htmlspecialchars($editRestaurant['address']); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($editRestaurant['phone']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="price_range" class="form-label">Price Range *</label>
                                <select class="form-control form-select" id="price_range" name="price_range" required>
                                    <option value="$" <?php echo $editRestaurant['price_range'] === '$' ? 'selected' : ''; ?>>$ - Budget</option>
                                    <option value="$$" <?php echo $editRestaurant['price_range'] === '$$' ? 'selected' : ''; ?>>$$ - Moderate</option>
                                    <option value="$$$" <?php echo $editRestaurant['price_range'] === '$$$' ? 'selected' : ''; ?>>$$$ - Upscale</option>
                                    <option value="$$$$" <?php echo $editRestaurant['price_range'] === '$$$$' ? 'selected' : ''; ?>>$$$$ - Premium</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="seating_capacity" class="form-label">Seating Capacity *</label>
                                <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" 
                                       value="<?php echo $editRestaurant['seating_capacity']; ?>" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="image" name="image" 
                               value="<?php echo htmlspecialchars($editRestaurant['image'] ?? ''); ?>">
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Restaurant</button>
                        <a href="restaurants.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Restaurant List -->
        <?php if (empty($myRestaurants)): ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <h3 class="text-muted">No restaurants assigned</h3>
                <p>Contact the administrator to assign restaurants to your account.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($myRestaurants as $restaurant): ?>
            <div class="col-md-6 col-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title"><?php echo htmlspecialchars($restaurant['name']); ?></h5>
                            <span class="badge"><?php echo $restaurant['rating']; ?> / 5</span>
                        </div>
                        <span class="badge badge-info mb-3"><?php echo htmlspecialchars($restaurant['cuisine']); ?></span>
                        <p class="card-text"><?php echo htmlspecialchars($restaurant['description']); ?></p>
                        <div class="restaurant-stats">
                            <span><?php echo htmlspecialchars($restaurant['price_range']); ?></span>
                            <span>Capacity: <?php echo $restaurant['seating_capacity']; ?></span>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <a href="restaurants.php?action=edit&id=<?php echo $restaurant['id']; ?>" class="btn btn-outline-primary">Edit Details</a>
                        <a href="menu.php?restaurant_id=<?php echo $restaurant['id']; ?>" class="btn btn-outline-primary">Manage Menu</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
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
?>

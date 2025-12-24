<?php
/**
 * Admin Dashboard
 * Full system access for administrators
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin access
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Reservation.php';
require_once '../backend/User.php';
require_once '../backend/Alert.php';

$restaurantManager = new Restaurant();
$reservationManager = new Reservation();
$userManager = new User();

// Get statistics
$restaurants = $restaurantManager->getAllRestaurants();
$reservationCounts = $reservationManager->getReservationCounts();
$users = $userManager->getAllUsers();

$totalRestaurants = count($restaurants);
$totalUsers = count($users);
$pendingReservations = $reservationCounts['pending'];
$confirmedReservations = $reservationCounts['confirmed'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gebeta (ገበታ)</title>
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ) Admin
            </a>
            <ul class="navbar-nav" id="navbarNav">
                <li><a class="nav-link active" href="index.php">Dashboard</a></li>
                <li><a class="nav-link" href="analytics.php">Analytics</a></li>
                <li><a class="nav-link" href="restaurants.php">Restaurants</a></li>
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
        
        <h1 class="mb-4">Admin Dashboard</h1>
        
        <!-- Statistics Cards -->
        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <div class="stat-number"><?php echo $totalRestaurants; ?></div>
                    <div class="stat-label">Total Restaurants</div>
                </div>
            </div>
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <div class="stat-number" style="color: var(--warning-color);"><?php echo $pendingReservations; ?></div>
                    <div class="stat-label">Pending Reservations</div>
                </div>
            </div>
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <div class="stat-number" style="color: var(--success-color);"><?php echo $confirmedReservations; ?></div>
                    <div class="stat-label">Confirmed Reservations</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 flex-wrap">
                    <a href="restaurants.php?action=add" class="btn btn-primary flex-grow-1">+ Add Restaurant</a>
                    <a href="users.php?action=add" class="btn btn-secondary flex-grow-1">+ Add User</a>
                    <a href="reservations.php" class="btn btn-outline-primary flex-grow-1">View All Reservations</a>
                </div>
            </div>
        </div>
        
        <!-- Recent Restaurants -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Restaurants</h5>
                <a href="restaurants.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Cuisine</th>
                                <th>Rating</th>
                                <th>Capacity</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recentRestaurants = array_slice($restaurants, 0, 5);
                            foreach ($recentRestaurants as $restaurant): 
                            ?>
                            <tr>
                                <td class="ps-4"><?php echo htmlspecialchars($restaurant['name']); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($restaurant['cuisine']); ?></span></td>
                                <td><?php echo $restaurant['rating']; ?> ⭐</td>
                                <td><?php echo $restaurant['seating_capacity']; ?></td>
                                <td class="pe-4">
                                    <a href="restaurants.php?action=edit&id=<?php echo $restaurant['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recent Users -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Users</h5>
                <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="pe-4">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recentUsers = array_slice($users, 0, 5);
                            foreach ($recentUsers as $user): 
                            ?>
                            <tr>
                                <td class="ps-4"><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $user['role'] === 'admin' ? 'danger' : 
                                            ($user['role'] === 'manager' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="pe-4"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function toggleMenu() {
        document.getElementById('navbarNav').classList.toggle('active');
    }

    // User dropdown toggle functionality
    function toggleUserDropdown(button) {
        const dropdown = button.closest('.user-dropdown');
        const menu = dropdown.querySelector('.user-dropdown-menu');
        const allUserDropdowns = document.querySelectorAll('.user-dropdown');
        
        // Close all other user dropdowns
        allUserDropdowns.forEach(d => {
            if (d !== dropdown) {
                d.classList.remove('show');
                d.querySelector('.user-dropdown-menu').classList.remove('show');
            }
        });
        
        // Toggle current dropdown
        dropdown.classList.toggle('show');
        menu.classList.toggle('show');
    }
    
    // Close user dropdown when clicking outside
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
$reservationManager->close();
$userManager->close();
?>

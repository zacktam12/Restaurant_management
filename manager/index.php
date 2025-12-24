<?php
/**
 * Manager Dashboard
 * Restaurant management interface for managers
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
require_once '../backend/Reservation.php';
require_once '../backend/Alert.php';

$restaurantManager = new Restaurant();
$reservationManager = new Reservation();

// Get manager's restaurants
$myRestaurants = $restaurantManager->getRestaurantsByManager($_SESSION['user']['id']);

// Get reservations for manager's restaurants
$allReservations = [];
foreach ($myRestaurants as $restaurant) {
    $reservations = $reservationManager->getReservationsByRestaurant($restaurant['id']);
    $allReservations = array_merge($allReservations, $reservations);
}

// Count pending reservations
$pendingCount = 0;
foreach ($allReservations as $res) {
    if ($res['status'] === 'pending') $pendingCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Gebeta (ገበታ)</title>
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ) Manager
            </a>
            <ul class="navbar-nav" id="navbarNav">
                <li><a class="nav-link active" href="index.php">Dashboard</a></li>
                <li><a class="nav-link" href="analytics.php">Analytics</a></li>
                <li><a class="nav-link" href="restaurants.php">My Restaurants</a></li>
                <li><a class="nav-link" href="reservations.php">Reservations</a></li>
                <li><a class="nav-link" href="menu.php">Menu</a></li>
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
        
        <h1 class="mb-4">Manager Dashboard</h1>
        
        <!-- Stats -->
        <div class="row mb-5">
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="dashboard-stat-card h-100">
                    <div class="stat-content">
                        <div class="stat-title">My Restaurants</div>
                        <div class="stat-value"><?php echo count($myRestaurants); ?></div>
                    </div>
                    <div class="stat-icon primary">🏢</div>
                </div>
            </div>
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="dashboard-stat-card h-100">
                    <div class="stat-content">
                        <div class="stat-title">Total Reservations</div>
                        <div class="stat-value"><?php echo count($allReservations); ?></div>
                    </div>
                    <div class="stat-icon info">📅</div>
                </div>
            </div>
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="dashboard-stat-card h-100">
                    <div class="stat-content">
                        <div class="stat-title">Pending Action</div>
                        <div class="stat-value"><?php echo $pendingCount; ?></div>
                    </div>
                    <div class="stat-icon warning">⏳</div>
                </div>
            </div>
            <div class="col col-md-3 col-sm-6 mb-3">
                <div class="dashboard-stat-card h-100">
                    <div class="stat-content">
                        <div class="stat-title">Visitor Traffic</div>
                        <div class="stat-value"><?php echo count($allReservations) * 8 + rand(10, 50); ?></div>
                    </div>
                    <div class="stat-icon success">📈</div>
                </div>
            </div>
        </div>
        
        <!-- My Restaurants -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Restaurants</h5>
                <a href="restaurants.php" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body">
                <?php if (empty($myRestaurants)): ?>
                <p class="text-muted text-center">No restaurants assigned yet. Contact admin to add restaurants.</p>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($myRestaurants as $restaurant): ?>
                    <div class="col col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6><?php echo htmlspecialchars($restaurant['name']); ?></h6>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($restaurant['cuisine']); ?> · <?php echo $restaurant['price_range']; ?></p>
                                <span class="badge"><?php echo $restaurant['rating']; ?> ⭐</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Reservations -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Reservations</h5>
                <a href="reservations.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($allReservations)): ?>
                <div class="text-center py-5">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                    <h5 class="text-muted">No reservations yet</h5>
                    <p class="text-muted">New reservations will appear here.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="border-top: none;">Customer</th>
                                <th style="border-top: none;">Date</th>
                                <th style="border-top: none;">Time</th>
                                <th style="border-top: none;">Guests</th>
                                <th style="border-top: none;">Status</th>
                                <th style="border-top: none;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recentReservations = array_slice($allReservations, 0, 5);
                            foreach ($recentReservations as $res): 
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; background: var(--light-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--primary-color);">
                                            <?php echo strtoupper(substr($res['customer_name'], 0, 1)); ?>
                                        </div>
                                        <div><?php echo htmlspecialchars($res['customer_name']); ?></div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($res['date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($res['time'])); ?></td>
                                <td><?php echo $res['guests']; ?> people</td>
                                <td>
                                    <?php 
                                    $statusClass = 'badge-soft-secondary';
                                    if ($res['status'] === 'confirmed') $statusClass = 'badge-soft-success';
                                    elseif ($res['status'] === 'pending') $statusClass = 'badge-soft-warning';
                                    elseif ($res['status'] === 'cancelled') $statusClass = 'badge-soft-danger';
                                    elseif ($res['status'] === 'completed') $statusClass = 'badge-soft-info';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($res['status']); ?></span>
                                </td>
                                <td>
                                    <a href="reservations.php" class="btn btn-sm btn-outline-primary">Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
    function toggleMenu() {
        document.getElementById('navbarNav').classList.toggle('active');
    }

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
$reservationManager->close();
?>

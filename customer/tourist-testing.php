<?php
/**
 * Tourist Testing Page (Comprehensive Customer Dashboard)
 * Consolidates all testing functionalities: search, browse, book, review.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check customer access
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Service.php';
require_once '../backend/Alert.php';

$restaurantManager = new Restaurant();
$serviceManager = new Service();

$allRestaurants = $restaurantManager->getAllRestaurants();
$allServices = $serviceManager->getAllServices();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Testing Hub - Restaurant Management System</title>
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .test-hub-header {
            background: linear-gradient(135deg, var(--sidebar-bg), var(--dark-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .search-box {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            margin-top: -2.5rem;
            display: flex;
            gap: 1rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .quick-action-card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            text-align: center;
            background: white;
            transition: all 0.2s;
        }
        
        .quick-action-card:hover {
            transform: scale(1.03);
            border-color: var(--primary-color);
        }
        
        .quick-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ)
            </a>
            <ul class="navbar-nav">
                <li><a class="nav-link" href="index.php">Dashboard</a></li>
                <li><a class="nav-link active" href="tourist-testing.php">Testing Hub</a></li>
                <li><a class="nav-link" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="test-hub-header text-center">
        <div class="container">
            <h1>Tourist Testing Hub</h1>
            <p>Comprehensive testing for browsing, searching, and booking services.</p>
            <div class="mt-3">
                <span class="badge badge-success">Login Status: Active</span>
                <span class="badge badge-info">Role: Customer</span>
                <span class="badge badge-secondary">User: <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
            </div>
        </div>
    </section>

    <div class="container pb-5">
        <div class="search-box mb-5">
            <input type="text" id="globalSearch" class="form-control" placeholder="Search for restaurants, cuisines, tours, or hotels...">
            <button class="btn btn-primary px-5">Search</button>
        </div>

        <?php Alert::display(); ?>

        <!-- Quick Actions -->
        <h2 class="section-header">Quick Testing Actions</h2>
        <div class="row mb-5">
            <div class="col col-md-3">
                <a href="index.php" style="text-decoration: none; color: inherit;">
                    <div class="quick-action-card">
                        <span class="quick-icon">🍽️</span>
                        <h4>Browse Places</h4>
                        <p class="text-muted small">Explore all available restaurants</p>
                    </div>
                </a>
            </div>
            <div class="col col-md-3">
                <a href="services.php?type=tour" style="text-decoration: none; color: inherit;">
                    <div class="quick-action-card">
                        <span class="quick-icon">🗺️</span>
                        <h4>Book Tour</h4>
                        <p class="text-muted small">Search and book local tours</p>
                    </div>
                </a>
            </div>
            <div class="col col-md-3">
                <a href="services.php?type=hotel" style="text-decoration: none; color: inherit;">
                    <div class="quick-action-card">
                        <span class="quick-icon">🏨</span>
                        <h4>Room Booking</h4>
                        <p class="text-muted small">Find and reserve hotel rooms</p>
                    </div>
                </a>
            </div>
            <div class="col col-md-3">
                <a href="services.php?type=taxi" style="text-decoration: none; color: inherit;">
                    <div class="quick-action-card">
                        <span class="quick-icon">🚕</span>
                        <h4>Order Taxi</h4>
                        <p class="text-muted small">Quick transportation booking</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Left Side: Restaurants -->
            <div class="col col-md-6">
                <h3 class="section-header">Popular Places</h3>
                <?php foreach (array_slice($allRestaurants, 0, 4) as $rest): ?>
                    <div class="card mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($rest['name']); ?></h4>
                                <span class="text-muted small"><?php echo htmlspecialchars($rest['cuisine']); ?> · ⭐ <?php echo $rest['rating'] ?? '4.5'; ?></span>
                            </div>
                            <a href="restaurant-details.php?id=<?php echo $rest['id']; ?>" class="btn btn-sm btn-outline-primary">Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="index.php" class="btn btn-outline-light btn-sm w-100 mt-2">View All Restaurants</a>
            </div>

            <!-- Right Side: Recent Bookings -->
            <div class="col col-md-6">
                <h3 class="section-header">All My Bookings</h3>
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                require_once '../backend/Booking.php';
                                $bookings = (new Booking())->getBookingsByCustomer($_SESSION['user']['id']);
                                if (empty($bookings)): 
                                ?>
                                    <tr><td colspan="3" class="text-center py-4">No bookings found</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($bookings, 0, 5) as $b): ?>
                                        <tr>
                                            <td><span class="badge"><?php echo ucfirst($b['service_type']); ?></span></td>
                                            <td><?php echo $b['date'] ?: 'N/A'; ?></td>
                                            <td><span class="badge badge-warning"><?php echo ucfirst($b['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <a href="services.php" class="btn btn-outline-light btn-sm w-100 mt-2">Manage All Bookings</a>
            </div>
        </div>
    </div>

    <footer style="background-color: var(--primary-color); color: white; text-align: center; padding: 2rem; margin-top: 3rem;">
        <p>&copy; 2025 Gebeta (ገበታ). All rights reserved.</p>
    </footer>
</body>
</html>

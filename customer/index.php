<?php
/**
 * Customer Dashboard
 * Browse restaurants, make reservations, and rate experiences
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
require_once '../backend/Reservation.php';
require_once '../backend/Alert.php';

$restaurantManager = new Restaurant();
$reservationManager = new Reservation();

// Get all restaurants
$restaurants = $restaurantManager->getAllRestaurants();

// Get customer's recent reservations
$customerId = $_SESSION['user']['id'];
$recentReservations = $reservationManager->getReservationsByCustomerId($customerId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Gebeta (ገበታ)</title>
    <link href="../css/style.css" rel="stylesheet">
    <style>
         /* Additional Dashboard-specific overrides if needed */
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ)
            </a>
            <ul class="navbar-nav" id="navbarNav">
                <li><a class="nav-link active" href="index.php">Dashboard</a></li>
                <li><a class="nav-link" href="reservations.php">My Reservations</a></li>
                <li><a class="nav-link" href="services.php">Services</a></li>
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
        
        <h1 class="mb-4">Welcome Back, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
        <p class="text-muted mb-4">Discover amazing restaurants and book your next culinary experience</p>
        
        <!-- Quick Stats -->
        <div class="row mb-5">
            <div class="col col-md-3">
                <div class="dashboard-stat-card">
                    <div class="stat-content">
                        <div class="stat-title">Restaurants</div>
                        <div class="stat-value"><?php echo count($restaurants); ?></div>
                    </div>
                    <div class="stat-icon primary">
                        🍽️
                    </div>
                </div>
            </div>
            <div class="col col-md-3">
                <div class="dashboard-stat-card">
                    <div class="stat-content">
                        <div class="stat-title">Reservations</div>
                        <div class="stat-value"><?php echo count($recentReservations); ?></div>
                    </div>
                    <div class="stat-icon info">
                        📅
                    </div>
                </div>
            </div>
            <div class="col col-md-3">
                <div class="dashboard-stat-card">
                    <div class="stat-content">
                        <div class="stat-title">Avg Rating</div>
                        <div class="stat-value">4.8</div>
                    </div>
                    <div class="stat-icon warning">
                        ⭐
                    </div>
                </div>
            </div>
            <div class="col col-md-3">
                <div class="dashboard-stat-card">
                    <div class="stat-content">
                        <div class="stat-title">Cuisines</div>
                        <div class="stat-value">12</div>
                    </div>
                    <div class="stat-icon success">
                        🌍
                    </div>
                </div>
            </div>
        </div>

        <!-- Browse Restaurants -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Popular Restaurants</h2>
                <a href="reservations.php" class="btn btn-outline-primary">Make a Reservation</a>
            </div>
            
            <div class="row">
                <?php foreach ($restaurants as $restaurant): ?>
                <div class="col col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.2s;">
                        <div style="height: 200px; background: #000; overflow: hidden; border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                            <?php if ($restaurant['image']): ?>
                                <img src="<?php echo htmlspecialchars($restaurant['image']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="card-img-top" style="height: 100%; width: 100%; object-fit: cover; object-position: center;">
                            <?php else: ?>
                                <span style="font-size: 3rem;">🍽️</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0" style="font-weight: 600;"><?php echo htmlspecialchars($restaurant['name']); ?></h5>
                                <span class="badge badge-soft-warning">⭐ <?php echo $restaurant['rating'] ?? '4.5'; ?></span>
                            </div>
                            <p class="card-text text-muted mb-3 small"><?php echo htmlspecialchars($restaurant['cuisine']); ?> · <?php echo $restaurant['price_range'] ?? '$$'; ?></p>
                            
                            <div class="mt-auto">
                                <p class="card-text small text-muted mb-3"><span class="icon">📍</span> <?php echo htmlspecialchars($restaurant['address']); ?></p>
                                <div class="d-flex gap-2">
                                    <a href="restaurant-details.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-primary btn-sm flex-1">View Details</a>
                                    <button onclick="openBookingModal(<?php echo $restaurant['id']; ?>, '<?php echo htmlspecialchars($restaurant['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($restaurant['cuisine'], ENT_QUOTES); ?>', '<?php echo $restaurant['price_range'] ?? '$$$'; ?>', '<?php echo htmlspecialchars($restaurant['address'], ENT_QUOTES); ?>')" class="btn btn-outline-primary btn-sm flex-1">Book Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Reservations -->
        <?php if (!empty($recentReservations)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Recent Reservations</h5>
                <a href="reservations.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Restaurant</th>
                                <th>Date</th>
                                <th>Party Size</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReservations as $reservation): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo htmlspecialchars($reservation['restaurant_name']); ?></div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span style="background: var(--light-color); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                            👥 <?php echo $reservation['party_size']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = 'badge-soft-secondary';
                                    if ($reservation['status'] === 'confirmed') $statusClass = 'badge-soft-success';
                                    elseif ($reservation['status'] === 'pending') $statusClass = 'badge-soft-warning';
                                    elseif ($reservation['status'] === 'cancelled') $statusClass = 'badge-soft-danger';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($reservation['status']); ?></span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="reservations.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-light text-primary">Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <div class="card" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-hover)); color: white;">
            <div class="card-body text-center py-5">
                <h3 class="mb-3" style="color: white;">Ready to Book Your Next Meal?</h3>
                <p class="mb-4" style="color: rgba(255,255,255,0.9);">Browse our exclusive collection of restaurants and make a reservation today!</p>
                <a href="reservations.php" class="btn" style="background-color: white; color: var(--primary-color); border: none; font-weight: 600;">Make a Reservation</a>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb;">
                <div>
                    <h3 class="modal-title" id="modalRestaurantName" style="margin-bottom: 0.25rem;">Reserve a Table</h3>
                    <p id="modalRestaurantInfo" style="font-size: 0.875rem; color: #6b7280; margin: 0;"></p>
                </div>
                <button type="button" class="btn-close" onclick="closeBookingModal()" style="font-size: 1.5rem; color: #9ca3af; background: none; border: none; cursor: pointer; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">×</button>
            </div>
            <div class="modal-body">
                <form id="bookingForm" method="POST" action="reservations.php">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="restaurant_id" id="modalRestaurantId" value="">
                    <input type="hidden" name="customer_id" value="<?php echo $_SESSION['user']['id']; ?>">
                    <input type="hidden" name="redirect_url" value="index.php">
                    
                    <div class="row" style="margin-bottom: 1rem;">
                        <div class="col col-md-6" style="margin-bottom: 1rem;">
                            <label class="form-label" style="font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <span style="font-size: 1rem;">📅</span> Date
                            </label>
                            <input type="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>" style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                        </div>
                        <div class="col col-md-6" style="margin-bottom: 1rem;">
                            <label class="form-label" style="font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <span style="font-size: 1rem;">🕐</span> Time
                            </label>
                            <select name="time" class="form-control form-select" required style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; background-color: white;">
                                <option value="">Select time</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="13:30">1:30 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="18:00">6:00 PM</option>
                                <option value="18:30">6:30 PM</option>
                                <option value="19:00">7:00 PM</option>
                                <option value="19:30">7:30 PM</option>
                                <option value="20:00">8:00 PM</option>
                                <option value="20:30">8:30 PM</option>
                                <option value="21:00">9:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span style="font-size: 1rem;">👥</span> Number of Guests
                        </label>
                        <select name="guests" class="form-control form-select" required style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; background-color: white;">
                            <option value="1">1 Guest</option>
                            <option value="2" selected>2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5 Guests</option>
                            <option value="6">6 Guests</option>
                            <option value="7">7 Guests</option>
                            <option value="8">8 Guests</option>
                            <option value="9">9 Guests</option>
                            <option value="10">10 Guests</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Name</label>
                        <input type="text" name="customer_name" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user']['name']); ?>" style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Phone</label>
                        <input type="tel" name="customer_phone" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>" placeholder="+1 555-0102" style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Email</label>
                        <input type="email" name="customer_email" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Special Requests (optional)</label>
                        <textarea name="special_requests" class="form-control" rows="3" placeholder="Any dietary requirements, seating preferences, or special occasions?" style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; resize: vertical;"></textarea>

                    <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="closeBookingModal()" style="padding: 0.625rem 1.25rem; background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; font-weight: 500;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="padding: 0.625rem 1.25rem; background-color: #c2410c; color: white; border: none; border-radius: 6px; font-weight: 500;">Confirm Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer style="background-color: var(--primary-color); color: white; text-align: center; padding: 2rem; margin-top: 3rem;">
        <p>&copy; 2025 Gebeta (ገበታ). All rights reserved.</p>
    </footer>
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
    
    // Booking Modal Functions
    function openBookingModal(restaurantId, restaurantName, cuisine, priceRange, address) {
        // Set modal title and info
        document.getElementById('modalRestaurantName').textContent = 'Reserve at ' + restaurantName;
        document.getElementById('modalRestaurantInfo').textContent = cuisine + ' • ' + priceRange + ' • ' + address;
        document.getElementById('modalRestaurantId').value = restaurantId;
        
        // Show modal
        document.getElementById('bookingModal').classList.add('show');
    }
    
    function closeBookingModal() {
        document.getElementById('bookingModal').classList.remove('show');
    }
    
    document.addEventListener('click', function(event) {
        // Close user dropdowns
        if (!event.target.closest('.user-dropdown')) {
            document.querySelectorAll('.user-dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
                dropdown.querySelector('.user-dropdown-menu').classList.remove('show');
            });
        }
        
        // Close modal on outside click
        const bookingModal = document.getElementById('bookingModal');
        if (event.target === bookingModal) {
            closeBookingModal();
        }
    });
    </script>
</body>
</html>
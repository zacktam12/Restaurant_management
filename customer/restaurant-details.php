<?php
/**
 * Restaurant Details Page
 * View menu, reviews, and reserve a table
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
require_once '../backend/Menu.php';
require_once '../backend/Rating.php';
require_once '../backend/Alert.php';

$restaurantId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$restaurantId) {
    header('Location: index.php');
    exit();
}

$restaurantManager = new Restaurant();
$menuManager = new Menu();
$ratingManager = new Rating();

$restaurant = $restaurantManager->getRestaurantById($restaurantId);

if (!$restaurant) {
    header('Location: index.php');
    exit();
}

$menuItems = $menuManager->getMenuByRestaurant($restaurantId);
$reviews = $ratingManager->getRatings($restaurantId);
$userRating = $ratingManager->getCustomerRating($restaurantId, $_SESSION['user']['id']);

// Group menu items by category
$groupedMenu = [];
foreach ($menuItems as $item) {
    $category = $item['category'] ?: 'Other';
    $groupedMenu[$category][] = $item;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['name']); ?> - Gebeta (ገበታ)</title>
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .hero-banner {
            position: relative;
            height: 400px;
            background-color: var(--dark-color);
            /* Image handled in HTML for better control */
            display: flex;
            align-items: flex-end;
            color: white;
            border-bottom: 2px solid var(--primary-color);
            overflow: hidden;
        }
        
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);
        }
        
        .hero-content {
            position: relative;
            padding-bottom: 3rem;
            width: 100%;
        }
        
        .restaurant-badge {
            background: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
            gap: 2rem;
        }
        
        .tab {
            padding: 1rem 0.5rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            color: var(--text-muted);
            transition: all 0.2s;
        }
        
        .tab:hover {
            color: var(--primary-color);
        }
        
        .tab.active {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        @media (min-width: 768px) {
            .menu-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .menu-item-card {
            background: white;
            padding: 1.25rem;
            border-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border: 1px solid var(--border-color);
            transition: transform 0.2s;
        }
        
        .menu-item-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .menu-item-price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .review-card {
            background: var(--secondary-color);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .stars {
            color: var(--warning-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }
        
        .sidebar-card {
            position: sticky;
            top: 100px;
        }
        
        .service-list {
            list-style: none;
            padding: 0;
        }
        
        .service-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .service-item:last-child {
            border-bottom: none;
        }
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
            <ul class="navbar-nav">
                <li><a class="nav-link" href="index.php">Dashboard</a></li>
                <li><a class="nav-link" href="reservations.php">My Reservations</a></li>
                <li><a class="nav-link" href="services.php">Services</a></li>
                <li>
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle" onclick="toggleUserDropdown(this)" type="button">
                            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user']['name'], 0, 1)); ?></div>
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
                            </div>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="profile.php" class="user-dropdown-item">👤 Profile</a>
                            <a href="../logout.php" class="user-dropdown-item logout">🚪 Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <!-- Blurred Background Layer -->
        <div style="position: absolute; inset: -20px; background-image: url('<?php echo htmlspecialchars($restaurant['image'] ?: '../public/placeholder-restaurant.png'); ?>'); background-size: cover; background-position: center; filter: blur(25px) brightness(0.5); z-index: 0;"></div>
        
        <!-- Main Image Layer (Contained) -->
        <img src="<?php echo htmlspecialchars($restaurant['image'] ?: '../public/placeholder-restaurant.png'); ?>" 
             style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; z-index: 1; margin: auto;">

        <!-- Gradient Overlay for Text Readability -->
        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.9) 10%, transparent 80%); z-index: 2;"></div>

        <div class="hero-content" style="z-index: 3; position: relative;">
            <div class="container">
                <a href="index.php" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; opacity: 0.8;">
                    <span>←</span> Back to Dashboard
                </a>
                <span class="restaurant-badge"><?php echo htmlspecialchars($restaurant['cuisine']); ?></span>
                <h1 style="font-size: 3rem; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
                <div style="display: flex; align-items: center; gap: 1.5rem; opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">
                    <span>⭐ <?php echo number_format($restaurant['rating'], 1); ?> (<?php echo count($reviews); ?> Reviews)</span>
                    <span>📍 <?php echo htmlspecialchars($restaurant['address']); ?></span>
                    <span>💰 <?php echo $restaurant['price_range'] ?: '$$'; ?></span>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <?php Alert::display(); ?>
        
        <div class="row">
            <div class="col col-md-8">
                <!-- Description -->
                <div class="mb-5">
                    <h2 class="mb-3">About</h2>
                    <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($restaurant['description'])); ?>
                    </p>
                </div>

                <!-- Tabs -->
                <div class="tabs">
                    <div class="tab active" data-tab="menu">Menu</div>
                    <div class="tab" data-tab="reviews">Reviews</div>
                </div>

                <!-- Menu Tab Content -->
                <div id="menu-content" class="tab-content active">
                    <?php if (empty($groupedMenu)): ?>
                        <div class="empty-state">No menu items available at the moment.</div>
                    <?php else: ?>
                        <?php foreach ($groupedMenu as $category => $items): ?>
                            <h3 class="mb-4 mt-4"><?php echo htmlspecialchars($category); ?></h3>
                            <div class="menu-grid mb-5">
                                <?php foreach ($items as $item): ?>
                                    <?php $isAvailable = isset($item['available']) ? (bool)$item['available'] : true; ?>
                                    <div class="menu-item-card" style="<?php echo !$isAvailable ? 'opacity: 0.6; pointer-events: none; background-color: #f9fafb;' : ''; ?>">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <h4 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <?php if (!$isAvailable): ?>
                                                <span class="badge badge-secondary" style="font-size: 0.7em; padding: 0.2em 0.5em;">Unavailable</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['description']); ?></p>
                                        </div>
                                        <div class="menu-item-price" style="<?php echo !$isAvailable ? 'color: #9ca3af;' : ''; ?>">$<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Reviews Tab Content -->
                <div id="reviews-content" class="tab-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Customer Reviews</h3>
                        <button class="btn btn-primary" onclick="openRatingModal()">Write a Review</button>
                    </div>

                    <?php if (empty($reviews)): ?>
                        <div class="empty-state">No reviews yet. Be the first to share your experience!</div>
                    <?php else: ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="user-avatar" style="width: 40px; height: 40px;">
                                                <?php echo strtoupper(substr($review['customer_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars($review['customer_name']); ?></div>
                                                <div class="text-muted small"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="stars">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col col-md-4">
                <div class="sidebar-card">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="mb-4">Details</h3>
                            <ul class="service-list mb-4">
                                <li class="service-item">
                                    <span class="icon">📍</span>
                                    <div>
                                        <strong>Address</strong><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($restaurant['address']); ?></span>
                                    </div>
                                </li>
                                <li class="service-item">
                                    <span class="icon">📞</span>
                                    <div>
                                        <strong>Phone</strong><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($restaurant['phone']); ?></span>
                                    </div>
                                </li>
                                <li class="service-item">
                                    <span class="icon">👥</span>
                                    <div>
                                        <strong>Capacity</strong><br>
                                        <span class="text-muted"><?php echo $restaurant['seating_capacity']; ?> seats available</span>
                                    </div>
                                </li>
                                <li class="service-item">
                                    <span class="icon">🕤</span>
                                    <div>
                                        <strong>Hours</strong><br>
                                        <span class="text-muted">11:00 AM - 10:00 PM</span>
                                    </div>
                                </li>
                            </ul>
                            <button onclick="openBookingModal()" class="btn btn-primary w-100 btn-lg shadow-sm">Reserve a Table</button>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">Integrated Services</h4>
                            <p class="text-muted small mb-4">Book related services for your visit</p>
                            <div class="d-grid gap-2">
                                <a href="services.php?type=taxi" class="btn btn-outline-primary btn-sm">Order a Taxi</a>
                                <a href="services.php?type=hotel" class="btn btn-outline-primary btn-sm">Near Hotels</a>
                                <a href="services.php?type=tour" class="btn btn-outline-primary btn-sm">Local Tours</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Modal -->
    <div id="ratingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Rate Your Experience</h3>
                <button type="button" class="btn-close" onclick="closeRatingModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="ratingForm">
                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurantId; ?>">
                    <div class="form-group mb-4 text-center">
                        <label class="form-label">How was your visit?</label>
                        <div class="rating-input" style="font-size: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
                            <span class="star-btn" data-value="1" style="cursor: pointer;">☆</span>
                            <span class="star-btn" data-value="2" style="cursor: pointer;">☆</span>
                            <span class="star-btn" data-value="3" style="cursor: pointer;">☆</span>
                            <span class="star-btn" data-value="4" style="cursor: pointer;">☆</span>
                            <span class="star-btn" data-value="5" style="cursor: pointer;">☆</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reviewText">Your Review (Optional)</label>
                        <textarea class="form-control" name="review" id="reviewText" rows="4" placeholder="Share details of your experience..."></textarea>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-secondary" onclick="closeRatingModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Booking Modal (Next.js Style) -->
    <div id="bookingModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb;">
                <div>
                    <h3 class="modal-title" style="margin-bottom: 0.25rem;">Reserve at <?php echo htmlspecialchars($restaurant['name']); ?></h3>
                    <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">
                        <?php echo htmlspecialchars($restaurant['cuisine']); ?> • <?php echo $restaurant['price_range'] ?: '$$$'; ?> • <?php echo htmlspecialchars($restaurant['address']); ?>
                    </p>
                </div>
                <button type="button" class="btn-close" onclick="closeBookingModal()" style="font-size: 1.5rem; color: #9ca3af; background: none; border: none; cursor: pointer; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">×</button>
            </div>
            <div class="modal-body">
                <form id="bookingForm" method="POST" action="reservations.php">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurantId; ?>">
                    <input type="hidden" name="customer_id" value="<?php echo $_SESSION['user']['id']; ?>">
                    <input type="hidden" name="redirect_url" value="restaurant-details.php?id=<?php echo $restaurantId; ?>">
                    
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
                    </div>

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
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tab');
                
                // Update tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Update content
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                document.getElementById(`${target}-content`).classList.add('active');
            });
        });

        // Rating Modal functionality
        function openRatingModal() {
            document.getElementById('ratingModal').classList.add('show');
        }

        function closeRatingModal() {
            document.getElementById('ratingModal').classList.remove('show');
        }

        // Star Rating selection
        const stars = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('ratingValue');

        stars.forEach(star => {
            star.addEventListener('mouseover', () => {
                const val = parseInt(star.getAttribute('data-value'));
                highlightStars(val);
            });

            star.addEventListener('mouseout', () => {
                highlightStars(parseInt(ratingInput.value));
            });

            star.addEventListener('click', () => {
                const val = parseInt(star.getAttribute('data-value'));
                ratingInput.value = val;
                highlightStars(val);
            });
        });

        function highlightStars(count) {
            stars.forEach(s => {
                const v = parseInt(s.getAttribute('data-value'));
                s.textContent = v <= count ? '★' : '☆';
                s.style.color = v <= count ? 'var(--warning-color)' : '';
            });
        }

        // Form Submission
        document.getElementById('ratingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (ratingInput.value == '0') {
                alert('Please select a rating');
                return;
            }

            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('../api/ratings.php?action=add', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Error submitting review');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred. Please try again.');
            }
        });


        function toggleUserDropdown(button) {
            const dropdown = button.closest('.user-dropdown');
            dropdown.classList.toggle('show');
        }

        // Booking Modal functionality
        function openBookingModal() {
            document.getElementById('bookingModal').classList.add('show');
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.remove('show');
        }

        window.onclick = function(event) {
            // Close user dropdown
            if (!event.target.matches('.user-dropdown-toggle') && !event.target.closest('.user-dropdown-toggle')) {
                document.querySelectorAll('.user-dropdown').forEach(d => d.classList.remove('show'));
            }
            
            // Close modals on outside click
            const ratingModal = document.getElementById('ratingModal');
            const bookingModal = document.getElementById('bookingModal');
            if (event.target === ratingModal) {
                closeRatingModal();
            }
            if (event.target === bookingModal) {
                closeBookingModal();
            }
        }
    </script>
</body>
</html>

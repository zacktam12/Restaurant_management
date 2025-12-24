<?php
/**
 * Customer Reservations Page
 * View and manage personal reservations
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Reservation.php';
require_once '../backend/Restaurant.php';
require_once '../backend/Alert.php';

$reservationManager = new Reservation();
$restaurantManager = new Restaurant();

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'cancel') {
        $result = $reservationManager->cancelReservation($_POST['reservation_id']);
        if ($result['success']) {
            Alert::setSuccess('Reservation cancelled successfully.');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($_POST['action'] === 'create') {
        $restaurant_id = intval($_POST['restaurant_id']);
        $date = $_POST['date'];
        $time = $_POST['time'];
        $guests = intval($_POST['guests']);
        $special_requests = $_POST['special_requests'] ?? null;
        
        $customer_name = $_SESSION['user']['name'];
        $customer_email = $_SESSION['user']['email'];
        $customer_phone = $_SESSION['user']['phone'] ?? '';
        $customer_id = $_SESSION['user']['id'];

        $result = $reservationManager->createReservation(
            $restaurant_id, 
            $customer_name, 
            $customer_email, 
            $customer_phone, 
            $date, 
            $time, 
            $guests, 
            $special_requests, 
            $customer_id
        );

        if ($result['success']) {
            Alert::setSuccess('Reservation created successfully!');
        } else {
            Alert::setError($result['message']);
        }
    }
    
    // Check if a custom redirect URL is provided
    $redirectUrl = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'reservations.php';
    header('Location: ' . $redirectUrl);
    exit();
}

// Get customer reservations - using customer_id for consistency with dashboard
$customerId = $_SESSION['user']['id'];
$reservations = $reservationManager->getAllReservationsByCustomerId($customerId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Gebeta (ገበታ)</title>
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ)
            </a>
            <ul class="navbar-nav" id="navbarNav">
                <li><a class="nav-link" href="index.php">Dashboard</a></li>
                <li><a class="nav-link active" href="reservations.php">My Reservations</a></li>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Reservations</h1>
            <button onclick="toggleReservationForm()" class="btn btn-primary" id="newReservationBtn">+ New Reservation</button>
        </div>

        <!-- New Reservation Form -->
        <div id="reservationFormContainer" class="card mb-5" style="<?php echo isset($_GET['restaurant_id']) ? '' : 'display: none;'; ?>">
            <div class="card-header">
                <h3 class="mb-0">Make a New Reservation</h3>
            </div>
            <div class="card-body">
                <form action="reservations.php" method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col col-md-4">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Restaurant</label>
                                <select name="restaurant_id" class="form-control form-select" required style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; background-color: white;">
                                    <option value="">Select a restaurant</option>
                                    <?php 
                                    $restaurants = $restaurantManager->getAllRestaurants();
                                    foreach ($restaurants as $rest): 
                                    ?>
                                        <option value="<?php echo $rest['id']; ?>" <?php echo (isset($_GET['restaurant_id']) && $_GET['restaurant_id'] == $rest['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rest['name']); ?> (<?php echo htmlspecialchars($rest['cuisine']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col col-md-4">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Date</label>
                                <input type="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>" style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col col-md-4">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Time</label>
                                <select name="time" class="form-control form-select" required style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; background-color: white;">
                                    <option value="">Select time</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="11:30">11:30 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="12:30">12:30 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="13:30">1:30 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="18:00">6:00 AM</option>
                                    <option value="18:30">6:30 PM</option>
                                    <option value="19:00">7:00 PM</option>
                                    <option value="19:30">7:30 PM</option>
                                    <option value="20:00">8:00 PM</option>
                                    <option value="20:30">8:30 PM</option>
                                    <option value="21:00">9:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col col-md-4">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Guests</label>
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
                        </div>
                        <div class="col col-md-8">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Special Requests (Optional)</label>
                                <input type="text" name="special_requests" class="form-control" placeholder="Any dietary requirements, seating preferences, or special occasions?" style="padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col col-md-12">
                            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                                <button type="button" class="btn btn-secondary" onclick="toggleReservationForm()" style="padding: 0.625rem 1.25rem; background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; font-weight: 500;">Cancel</button>
                                <button type="submit" class="btn btn-primary" style="padding: 0.625rem 1.25rem; background-color: #c2410c; color: white; border: none; border-radius: 6px; font-weight: 500;">Confirm Reservation</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (empty($reservations)): ?>
        <div class="card text-center py-5" id="emptyState">
            <div class="card-body">
                <h3 class="text-muted">No reservations yet</h3>
                <p>Browse restaurants and make your first reservation!</p>
                <button onclick="toggleReservationForm()" class="btn btn-primary mt-3">Make a Reservation</button>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Restaurant</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['restaurant_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($reservation['date'])); ?></td>
                            <td><?php echo date('g:i A', strtotime($reservation['time'])); ?></td>
                            <td><?php echo $reservation['guests']; ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $reservation['status'] === 'confirmed' ? 'success' : 
                                        ($reservation['status'] === 'pending' ? 'warning' : 
                                        ($reservation['status'] === 'cancelled' ? 'danger' : 'secondary')); 
                                ?>">
                                    <?php echo ucfirst($reservation['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($reservation['status'] === 'pending' || $reservation['status'] === 'confirmed'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script>
    function toggleMenu() {
        document.getElementById('navbarNav').classList.toggle('active');
    }

    function toggleReservationForm() {
        const container = document.getElementById('reservationFormContainer');
        const emptyState = document.getElementById('emptyState');
        const btn = document.getElementById('newReservationBtn');
        
        if (container.style.display === 'none') {
            container.style.display = 'block';
            btn.textContent = 'Cancel';
            btn.classList.replace('btn-primary', 'btn-secondary');
            if (emptyState) emptyState.style.display = 'none';
        } else {
            container.style.display = 'none';
            btn.textContent = '+ New Reservation';
            btn.classList.replace('btn-secondary', 'btn-primary');
            if (emptyState) emptyState.style.display = 'block';
        }
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
$reservationManager->close();
?>

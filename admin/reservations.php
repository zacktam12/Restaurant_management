<?php
/**
 * Admin Reservation Management
 * View and manage all reservations
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Reservation.php';
require_once '../backend/Alert.php';

$reservationManager = new Reservation();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $result = $reservationManager->updateStatus($_POST['id'], $_POST['status']);
        if ($result['success']) {
            Alert::setSuccess('Reservation status updated!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'delete') {
        $result = $reservationManager->deleteReservation($_POST['id']);
        if ($result['success']) {
            Alert::setSuccess('Reservation deleted!');
        } else {
            Alert::setError($result['message']);
        }
    }
    
    header('Location: reservations.php');
    exit();
}

// Filter by status
$statusFilter = $_GET['status'] ?? '';
if ($statusFilter) {
    $reservations = $reservationManager->getReservationsByStatus($statusFilter);
} else {
    $reservations = $reservationManager->getAllReservations();
}

$counts = $reservationManager->getReservationCounts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations - Admin</title>
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
                <li><a class="nav-link" href="restaurants.php">Restaurants</a></li>
                <li><a class="nav-link active" href="reservations.php">Reservations</a></li>
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
        
        <h1 class="mb-4">Manage Reservations</h1>
        
        <!-- Stats -->
        <style>
            .status-card-grid {
                display: grid;
                grid-template-columns: repeat(1, 1fr);
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .status-card-grid a { text-decoration: none !important; }
            @media (min-width: 640px) { .status-card-grid { grid-template-columns: repeat(2, 1fr); } }
            @media (min-width: 1024px) { .status-card-grid { grid-template-columns: repeat(4, 1fr); } }

            .stat-card-modern {
                background: white;
                border-radius: 12px;
                padding: 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
                border: 1px solid rgba(0,0,0,0.05);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .stat-card-modern:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 12px -3px rgba(0, 0, 0, 0.08);
            }
            .stat-info h3 {
                font-size: 1.5rem;
                font-weight: 700;
                margin: 0;
                line-height: 1;
                color: #111827;
            }
            .stat-info p {
                color: #6b7280;
                margin: 0.25rem 0 0 0;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .stat-icon-wrapper {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
            }
            .stat-info p {
                color: #6b7280;
                margin: 0.5rem 0 0 0;
                font-size: 0.875rem;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .stat-icon-wrapper {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
            }
            .stat-total .stat-icon-wrapper { background: #eff6ff; color: #3b82f6; }
            .stat-pending .stat-icon-wrapper { background: #fffbeb; color: #d97706; }
            .stat-confirmed .stat-icon-wrapper { background: #ecfdf5; color: #10b981; }
            .stat-cancelled .stat-icon-wrapper { background: #fef2f2; color: #ef4444; }
        </style>

        <!-- Stats -->
        <div class="status-card-grid">
            <!-- Total -->
            <a href="reservations.php" class="text-decoration-none">
                <div class="stat-card-modern stat-total">
                    <div class="stat-info">
                        <h3><?php echo $counts['total']; ?></h3>
                        <p>Total</p>
                    </div>
                    <div class="stat-icon-wrapper">
                        📊
                    </div>
                </div>
            </a>

            <!-- Pending -->
            <a href="reservations.php?status=pending" class="text-decoration-none">
                <div class="stat-card-modern stat-pending">
                    <div class="stat-info">
                        <h3><?php echo $counts['pending']; ?></h3>
                        <p>Pending</p>
                    </div>
                    <div class="stat-icon-wrapper">
                        ⏳
                    </div>
                </div>
            </a>

            <!-- Confirmed -->
            <a href="reservations.php?status=confirmed" class="text-decoration-none">
                <div class="stat-card-modern stat-confirmed">
                    <div class="stat-info">
                        <h3><?php echo $counts['confirmed']; ?></h3>
                        <p>Confirmed</p>
                    </div>
                    <div class="stat-icon-wrapper">
                        ✅
                    </div>
                </div>
            </a>

            <!-- Cancelled -->
            <a href="reservations.php?status=cancelled" class="text-decoration-none">
                <div class="stat-card-modern stat-cancelled">
                    <div class="stat-info">
                        <h3><?php echo $counts['cancelled']; ?></h3>
                        <p>Cancelled</p>
                    </div>
                    <div class="stat-icon-wrapper">
                        ✖️
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Reservations Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <?php echo $statusFilter ? ucfirst($statusFilter) . ' Reservations' : 'All Reservations'; ?>
                </h5>
                <?php if ($statusFilter): ?>
                <a href="reservations.php" class="btn btn-sm btn-outline-primary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body px-0">
                <?php if (empty($reservations)): ?>
                <p class="text-center text-muted py-4">No reservations found.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Restaurant</th>
                                <th>Customer</th>
                                <th>Date & Time</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td class="ps-4">#<?php echo $res['id']; ?></td>
                                <td><?php echo htmlspecialchars($res['restaurant_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($res['customer_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($res['customer_email']); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($res['date'])); ?><br>
                                    <small><?php echo date('g:i A', strtotime($res['time'])); ?></small>
                                </td>
                                <td><?php echo $res['guests']; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $res['status'] === 'confirmed' ? 'success' : 
                                            ($res['status'] === 'pending' ? 'warning' : 
                                            ($res['status'] === 'cancelled' ? 'danger' : 'secondary')); 
                                    ?>">
                                        <?php echo ucfirst($res['status']); ?>
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <div class="action-dropdown">
                                        <button class="action-dropdown-toggle" onclick="toggleDropdown(this)" type="button">
                                            ⋯
                                        </button>
                                        <div class="action-dropdown-menu">
                                            <?php if ($res['status'] === 'pending'): ?>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="action-dropdown-item">
                                                    <span class="icon">✓</span>
                                                    Confirm
                                                </button>
                                            </form>
                                            <div class="action-dropdown-divider"></div>
                                            <?php endif; ?>
                                            
                                            <?php if ($res['status'] === 'confirmed'): ?>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="action-dropdown-item">
                                                    <span class="icon">✔️</span>
                                                    Complete
                                                </button>
                                            </form>
                                            <div class="action-dropdown-divider"></div>
                                            <?php endif; ?>
                                            
                                            <?php if ($res['status'] !== 'cancelled' && $res['status'] !== 'completed'): ?>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="action-dropdown-item danger">
                                                    <span class="icon">✖</span>
                                                    Cancel
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($res['status'] === 'cancelled' || $res['status'] === 'completed'): ?>
                                            <div class="action-dropdown-item" style="color: var(--text-muted); cursor: default;">
                                                <span class="icon">ℹ️</span>
                                                No actions available
                                            </div>
                                            <?php endif; ?>
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
$reservationManager->close();
?>

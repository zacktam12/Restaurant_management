<?php
/**
 * Customer Services Page
 * Browse and book external services (tours, hotels, taxis)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/Service.php';
require_once '../backend/Booking.php';
require_once '../backend/Alert.php';

$serviceManager = new Service();
$bookingManager = new Booking();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $special_requests = $_POST['special_requests'] ?? '';
    
    // Append Taxi specific fields to special requests for backend parsing
    if (isset($_POST['service_type']) && $_POST['service_type'] === 'taxi') {
        $pickup = $_POST['pickup_location'] ?? 'Addis Ababa';
        $dropoff = $_POST['dropoff_location'] ?? 'Bole Airport';
        $special_requests .= " Pickup: {$pickup}; Dropoff: {$dropoff}";
    }
    
    $result = $bookingManager->createBooking(
        $_POST['service_type'],
        $_POST['service_id'],
        $_SESSION['user']['id'],
        $_POST['date'] ?? null,
        $_POST['time'] ?? null,
        $_POST['guests'] ?? null,
        $special_requests
    );
    
    if ($result['success']) {
        Alert::setSuccess('Booking created successfully!');
    } else {
        Alert::setError($result['message']);
    }
    
    header('Location: services.php');
    exit();
}

// Get services by type
$typeFilter = $_GET['type'] ?? '';
if ($typeFilter) {
    $services = $serviceManager->getServicesByType($typeFilter);
} else {
    $services = $serviceManager->getAllServices();
}

// Get user's bookings
$myBookings = $bookingManager->getBookingsByCustomer($_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Gebeta (ገበታ)</title>
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
                <li><a class="nav-link" href="reservations.php">My Reservations</a></li>
                <li><a class="nav-link active" href="services.php">Services</a></li>
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
        
        <h1 class="mb-4">External Services</h1>
        <p class="text-muted mb-4">Browse and book tours, hotels, and transportation through integrated service providers.</p>
        
        <!-- Filter Tabs -->
        <div class="mb-4 d-flex gap-2">
            <a href="services.php" class="btn <?php echo !$typeFilter ? 'btn-primary' : 'btn-outline-primary'; ?>">All Services</a>
            <a href="services.php?type=tour" class="btn <?php echo $typeFilter === 'tour' ? 'btn-primary' : 'btn-outline-primary'; ?>">Tours</a>
            <a href="services.php?type=hotel" class="btn <?php echo $typeFilter === 'hotel' ? 'btn-primary' : 'btn-outline-primary'; ?>">Hotels</a>
            <a href="services.php?type=taxi" class="btn <?php echo $typeFilter === 'taxi' ? 'btn-primary' : 'btn-outline-primary'; ?>">Transportation</a>
        </div>
        
        <style>
            .services-grid {
                display: grid;
                grid-template-columns: repeat(1, 1fr);
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            
            @media (min-width: 640px) {
                .services-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            
            @media (min-width: 1024px) {
                .services-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }
            
            @media (min-width: 1280px) {
                .services-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
            }
            
            .service-card {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid rgba(0,0,0,0.05);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                flex-direction: column;
                height: 100%;
            }
            
            .service-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                border-color: rgba(0,0,0,0.1);
            }
            
            .service-image {
                height: 180px; /* Slightly taller */
                width: 100%;
                background-color: #222; /* Dark background for letterboxing */
                position: relative;
                overflow: hidden;
                display: flex;
                align-items: center; 
                justify-content: center;
            }
            
            
            .service-placeholder-img {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 3rem;
                background: linear-gradient(45deg, #f3f4f6, #e5e7eb);
            }
            
            .service-content {
                padding: 1.25rem;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }
            
            .service-type-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.75rem;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                margin-bottom: 0.5rem;
            }
            
            .badge-tour { background-color: #dbeafe; color: #1e40af; }
            .badge-hotel { background-color: #fae8ff; color: #86198f; }
            .badge-taxi { background-color: #fef3c7; color: #92400e; }
            .badge-ticket { background-color: #dcfce7; color: #166534; }
            
            .service-title {
                font-size: 1.125rem;
                font-weight: 600;
                color: #111827;
                margin-bottom: 0.5rem;
            }
            
            .service-description {
                font-size: 0.875rem;
                color: #6b7280;
                margin-bottom: 1rem;
                flex-grow: 1;
            }
            
            .service-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: auto;
                padding-top: 1rem;
                border-top: 1px solid #f3f4f6;
            }
            
            .service-price {
                font-weight: 700;
                color: #111827;
                font-size: 1.125rem;
            }
            
            .btn-book {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .img-fluid {
                width: 100%;
                height: 100%;
                object-fit: contain; /* Show WHOLE image */
            }
        </style>

        <!-- Services Grid -->
        <?php if (empty($services)): ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <h3 class="text-muted">No services available</h3>
                <p>Check back later for new services.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
            <article class="service-card">
                <div class="service-image">
                    <?php if (isset($service['image']) && $service['image']): ?>
                        <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>" class="img-fluid">
                    <?php else: ?>
                        <div class="service-placeholder-img">
                            <?php 
                                $icon = '🏷️';
                                if ($service['type'] === 'tour') $icon = '🗺️';
                                if ($service['type'] === 'hotel') $icon = '🏨';
                                if ($service['type'] === 'taxi') $icon = '🚕';
                                echo $icon;
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="service-content">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="service-type-badge badge-<?php echo $service['type']; ?>">
                            <?php echo ucfirst($service['type']); ?>
                        </span>
                        <div class="d-flex align-items-center gap-1">
                            <span class="rating-star">★</span>
                            <span style="font-size: 0.875rem; font-weight: 600; color: #4b5563;">
                                <?php echo $service['rating']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <h3 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                    
                    <div class="service-meta">
                        <div class="service-price">
                            $<?php echo number_format($service['price'], 2); ?>
                        </div>
                        <button type="button" 
                                class="btn btn-primary btn-book"
                                onclick="openBookingModal('<?php echo $service['type']; ?>', <?php echo $service['id']; ?>, '<?php echo addslashes($service['name']); ?>', <?php echo $service['price']; ?>)">
                            Book Now
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- My Bookings Section -->
        <?php if (!empty($myBookings)): ?>
        <div class="mt-5">
            <h2 class="mb-4">My Bookings</h2>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myBookings as $booking): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            // Fallback if service_details is missing
                                            echo htmlspecialchars($booking['service_name'] ?? 'Booking #' . $booking['id']); 
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $booking['service_type']; ?>">
                                            <?php echo ucfirst($booking['service_type']); ?>
                                        </span>
                                    </td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($booking['external_reference'] ?? '-'); ?></small></td>
                                    <td><?php echo $booking['date'] ? date('M d, Y', strtotime($booking['date'])) : '-'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $booking['status'] === 'confirmed' ? 'success' : 
                                                ($booking['status'] === 'pending' ? 'warning' : 
                                                ($booking['status'] === 'cancelled' ? 'danger' : 'secondary')); 
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Booking Modal -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Book Service</h5>
                <button type="button" class="btn-close" onclick="closeModal()">x</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="book">
                    <input type="hidden" name="service_id" id="modal_service_id">
                    <input type="hidden" name="service_type" id="modal_service_type">
                    
                    <p class="mb-3">Booking: <strong id="modal_service_name"></strong></p>
                    
                    <div id="taxi-fields" style="display: none; background: #fffbeb; padding: 10px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fcd34d;">
                        <h6 class="text-warning-darker mb-2" style="color: #92400e;">🚖 Taxi Details</h6>
                        <div class="form-group mb-2">
                            <label for="pickup_location" class="form-label" style="font-size: 0.9em;">Pickup Location</label>
                            <input type="text" class="form-control" id="pickup_location" name="pickup_location" placeholder="e.g. Hotel Lobby">
                        </div>
                        <div class="form-group">
                            <label for="dropoff_location" class="form-label" style="font-size: 0.9em;">Dropoff Location</label>
                            <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" placeholder="e.g. Airport Terminal 1">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="time" class="form-label">Preferred Time</label>
                        <select class="form-control form-select" id="time" name="time">
                            <option value="">Any time</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="guests" class="form-label">Number of People</label>
                        <select class="form-control form-select" id="guests" name="guests">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests" class="form-label">Special Requests</label>
                        <textarea class="form-control" id="special_requests" name="special_requests" 
                                  placeholder="Any special requirements?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openBookingModal(serviceType, serviceId, serviceName, servicePrice) {
            document.getElementById('modal_service_id').value = serviceId;
            document.getElementById('modal_service_type').value = serviceType;
            document.getElementById('modal_service_name').textContent = serviceName;
            
            // Show/Hide Taxi Fields
            const taxiFields = document.getElementById('taxi-fields');
            if (serviceType === 'taxi') {
                taxiFields.style.display = 'block';
                document.getElementById('pickup_location').required = true;
                document.getElementById('dropoff_location').required = true;
            } else {
                taxiFields.style.display = 'none';
                document.getElementById('pickup_location').required = false;
                document.getElementById('dropoff_location').required = false;
            }
            
            document.getElementById('bookingModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('bookingModal').classList.remove('show');
        }
        
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
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
$serviceManager->close();
$bookingManager->close();
?>

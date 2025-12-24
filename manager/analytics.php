<?php
/**
 * Manager Analytics Dashboard
 * Restaurant-specific analytics and reporting
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

// Get selected restaurant (default to first)
$selectedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : (count($myRestaurants) > 0 ? $myRestaurants[0]['id'] : null);

// Get date range
$endDate = date('Y-m-d');
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDateFilter = isset($_GET['end_date']) ? $_GET['end_date'] : $endDate;

// Get reservations for selected restaurant
$restaurantReservations = [];
if ($selectedRestaurantId) {
    $allReservations = $reservationManager->getReservationsByRestaurant($selectedRestaurantId);
    $restaurantReservations = array_filter($allReservations, function($res) use ($startDate, $endDateFilter) {
        return $res['date'] >= $startDate && $res['date'] <= $endDateFilter;
    });
}

// Calculate metrics
$totalReservations = count($restaurantReservations);
$totalGuests = 0;
$totalRevenue = 0;
$averagePartySize = 0;

$statusCounts = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'completed' => 0];

foreach ($restaurantReservations as $res) {
    $totalGuests += $res['guests'];
    $totalRevenue += $res['guests'] * 45;
    
    if (isset($statusCounts[$res['status']])) {
        $statusCounts[$res['status']]++;
    }
}

$averagePartySize = $totalReservations > 0 ? round($totalGuests / $totalReservations, 1) : 0;
$occupancyRate = 0;
if ($selectedRestaurantId && isset($myRestaurants[0])) {
    foreach ($myRestaurants as $rest) {
        if ($rest['id'] == $selectedRestaurantId) {
            $occupancyRate = $rest['seating_capacity'] > 0 ? round(($totalGuests / ($rest['seating_capacity'] * 30)) * 100, 1) : 0;
            break;
        }
    }
}

// Daily reservations for chart
$dailyReservations = [];
$currentDate = new DateTime($startDate);
$endDateTime = new DateTime($endDateFilter);

while ($currentDate <= $endDateTime) {
    $dateStr = $currentDate->format('Y-m-d');
    $count = 0;
    foreach ($restaurantReservations as $res) {
        if ($res['date'] === $dateStr) {
            $count++;
        }
    }
    $dailyReservations[] = [
        'date' => $currentDate->format('M d'),
        'count' => $count
    ];
    $currentDate->modify('+1 day');
}

// Get selected restaurant info
$selectedRestaurant = null;
if ($selectedRestaurantId) {
    foreach ($myRestaurants as $rest) {
        if ($rest['id'] == $selectedRestaurantId) {
            $selectedRestaurant = $rest;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Analytics - Gebeta (ገበታ)</title>
    <link href="../css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-filters {
            background: var(--background-secondary);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        .analytics-filters select,
        .analytics-filters input[type="date"],
        .analytics-filters button {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
        }
        .analytics-filters select,
        .analytics-filters input[type="date"] {
            background: white;
            color: var(--text-primary);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .metric-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .metric-card.revenue {
            border-left-color: var(--success-color);
        }
        .metric-card.guests {
            border-left-color: var(--warning-color);
        }
        .metric-card.occupancy {
            border-left-color: var(--info-color);
        }
        .metric-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }
        .metric-card.revenue .metric-value {
            color: var(--success-color);
        }
        .metric-card.guests .metric-value {
            color: var(--warning-color);
        }
        .metric-card.occupancy .metric-value {
            color: var(--info-color);
        }
        .metric-label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        .status-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .status-badge {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 15px;
            border-radius: 6px;
            background: var(--background-secondary);
        }
        .status-badge-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: capitalize;
        }
        .status-badge-value {
            font-size: 24px;
            font-weight: bold;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <img src="../assets/logo.jpg" alt="Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover;">
                Gebeta (ገበታ) Manager
            </a>
            <ul class="navbar-nav">
                <li><a class="nav-link" href="index.php">Dashboard</a></li>
                <li><a class="nav-link active" href="analytics.php">Analytics</a></li>
                <li><a class="nav-link" href="restaurants.php">My Restaurants</a></li>
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
        
        <div style="margin-bottom: 30px;">
            <h1>Restaurant Analytics & Reports</h1>
            <p class="text-muted">Detailed analytics for your restaurant operations</p>
        </div>

        <!-- Restaurant Selection & Date Filter -->
        <div class="analytics-filters">
            <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                <?php if (count($myRestaurants) > 1): ?>
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 5px;">Select Restaurant</label>
                    <select name="restaurant_id" onchange="this.form.submit()">
                        <?php foreach ($myRestaurants as $rest): ?>
                        <option value="<?php echo $rest['id']; ?>" <?php echo $rest['id'] == $selectedRestaurantId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($rest['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 5px;">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 5px;">End Date</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDateFilter); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 22px;">Filter</button>
                <a href="analytics.php" class="btn btn-outline-primary" style="margin-top: 22px;">Reset</a>
            </form>
        </div>

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Reservations</div>
                <div class="metric-value"><?php echo $totalReservations; ?></div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                    Period: <?php echo date('M d', strtotime($startDate)); ?> - <?php echo date('M d', strtotime($endDateFilter)); ?>
                </div>
            </div>
            
            <div class="metric-card revenue">
                <div class="metric-label">Est. Revenue</div>
                <div class="metric-value">$<?php echo number_format($totalRevenue, 0); ?></div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                    Based on avg $45/guest
                </div>
            </div>
            
            <div class="metric-card guests">
                <div class="metric-label">Total Guests</div>
                <div class="metric-value"><?php echo number_format($totalGuests, 0); ?></div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                    Avg party: <?php echo $averagePartySize; ?> guests
                </div>
            </div>
            
            <div class="metric-card occupancy">
                <div class="metric-label">Occupancy Rate</div>
                <div class="metric-value"><?php echo $occupancyRate; ?>%</div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                    Based on capacity utilization
                </div>
            </div>
        </div>

        <!-- Reservation Status -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="mb-0">Reservation Status Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="status-badges">
                    <div class="status-badge" style="background-color: rgba(184, 102, 40, 0.1);">
                        <div class="status-badge-label">Pending</div>
                        <div class="status-badge-value" style="color: oklch(0.75 0.15 70);"><?php echo $statusCounts['pending']; ?></div>
                    </div>
                    <div class="status-badge" style="background-color: rgba(34, 197, 94, 0.1);">
                        <div class="status-badge-label">Confirmed</div>
                        <div class="status-badge-value" style="color: oklch(0.6 0.15 145);"><?php echo $statusCounts['confirmed']; ?></div>
                    </div>
                    <div class="status-badge" style="background-color: rgba(239, 68, 68, 0.1);">
                        <div class="status-badge-label">Cancelled</div>
                        <div class="status-badge-value" style="color: oklch(0.55 0.2 25);"><?php echo $statusCounts['cancelled']; ?></div>
                    </div>
                    <div class="status-badge" style="background-color: rgba(94, 165, 255, 0.1);">
                        <div class="status-badge-label">Completed</div>
                        <div class="status-badge-value" style="color: oklch(0.55 0.15 45);"><?php echo $statusCounts['completed']; ?></div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 col-12">
                        <div style="margin-top: 30px;">
                            <h6 style="margin-bottom: 20px;">Status Distribution</h6>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div style="margin-top: 30px;">
                            <h6 style="margin-bottom: 20px;">Party Size Distribution</h6>
                            <div class="chart-container">
                                <canvas id="partySizeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Reservations Trend -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="mb-0">Daily Reservations Trend</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Reservations -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Reservations</h5>
                <a href="reservations.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body px-0">
                <?php if (empty($restaurantReservations)): ?>
                <p class="text-muted text-center py-4">No reservations for the selected period.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Customer</th>
                                <th>Date & Time</th>
                                <th>Guests</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $sorted = array_slice(array_reverse($restaurantReservations), 0, 10);
                        foreach ($sorted as $res): 
                        ?>
                        <tr>
                            <td class="ps-4"><?php echo htmlspecialchars($res['customer_name']); ?></td>
                            <td><?php echo date('M d, Y g:i A', strtotime($res['date'] . ' ' . $res['time'])); ?></td>
                            <td><?php echo $res['guests']; ?> guests</td>
                            <td class="pe-4">
                                <span class="badge badge-<?php 
                                    echo $res['status'] === 'confirmed' ? 'success' : 
                                        ($res['status'] === 'pending' ? 'warning' : 
                                        ($res['status'] === 'completed' ? 'info' : 'danger')); 
                                ?>">
                                    <?php echo ucfirst($res['status']); ?>
                                </span>
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
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
                datasets: [{
                    data: [
                        <?php echo $statusCounts['pending']; ?>,
                        <?php echo $statusCounts['confirmed']; ?>,
                        <?php echo $statusCounts['cancelled']; ?>,
                        <?php echo $statusCounts['completed']; ?>
                    ],
                    backgroundColor: [
                        '#fbbf24', // Warning/Pending (Yellow/Orange)
                        '#10b981', // Success/Confirmed (Green)
                        '#ef4444', // Danger/Cancelled (Red)
                        '#3b82f6'  // Info/Completed (Blue)
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Party Size Distribution
        const partySizeCtx = document.getElementById('partySizeChart').getContext('2d');
        const partySizeData = {1: 0, 2: 0, 3: 0, 4: 0, '5+': 0};
        <?php foreach ($restaurantReservations as $res): ?>
            var guests = <?php echo $res['guests']; ?>; // var to avoid scoping issues if any
            if (guests === 1) partySizeData[1]++;
            else if (guests === 2) partySizeData[2]++;
            else if (guests === 3) partySizeData[3]++;
            else if (guests === 4) partySizeData[4]++;
            else partySizeData['5+']++;
        <?php endforeach; ?>

        new Chart(partySizeCtx, {
            type: 'bar',
            data: {
                labels: ['1 Guest', '2 Guests', '3 Guests', '4 Guests', '5+ Guests'],
                datasets: [{
                    label: 'Reservations',
                    data: [partySizeData[1], partySizeData[2], partySizeData[3], partySizeData[4], partySizeData['5+']],
                    backgroundColor: '#8b5cf6' // Purple
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Daily Reservations Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("','", array_map(function($d) { return $d['date']; }, $dailyReservations)) . "'"; ?>],
                datasets: [{
                    label: 'Reservations',
                    data: [<?php echo implode(',', array_map(function($d) { return $d['count']; }, $dailyReservations)); ?>],
                    borderColor: '#f59e0b', // Amber/Orange
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
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
$reservationManager->close();
?>

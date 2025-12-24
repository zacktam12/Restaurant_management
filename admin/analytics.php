<?php
/**
 * Admin Analytics Dashboard
 * System-wide analytics and reporting
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Get date range for filtering (default: last 30 days)
$endDate = date('Y-m-d');
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDateFilter = isset($_GET['end_date']) ? $_GET['end_date'] : $endDate;

// Get all data for analytics
$restaurants = $restaurantManager->getAllRestaurants();
$allReservations = $reservationManager->getAllReservations();
$users = $userManager->getAllUsers();

// Filter reservations by date range
$filteredReservations = array_filter($allReservations, function($res) use ($startDate, $endDateFilter) {
    return $res['date'] >= $startDate && $res['date'] <= $endDateFilter;
});

// Calculate system-wide metrics
$totalRevenue = 0;
$totalGuests = 0;
$averagePartySize = 0;

foreach ($filteredReservations as $res) {
    $totalGuests += $res['guests'];
    // Estimate revenue (can be enhanced with actual transaction data)
    $totalRevenue += $res['guests'] * 45; // Average per guest
}

$totalReservations = count($filteredReservations);
$averagePartySize = $totalReservations > 0 ? round($totalGuests / $totalReservations, 1) : 0;

// Reservation status breakdown
$statusCounts = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'completed' => 0];
foreach ($filteredReservations as $res) {
    if (isset($statusCounts[$res['status']])) {
        $statusCounts[$res['status']]++;
    }
}

// Top restaurants by reservations
$restaurantReservations = [];
foreach ($restaurants as $restaurant) {
    $count = 0;
    $revenue = 0;
    foreach ($filteredReservations as $res) {
        if ($res['restaurant_id'] == $restaurant['id']) {
            $count++;
            $revenue += $res['guests'] * 45;
        }
    }
    if ($count > 0) {
        $restaurantReservations[] = [
            'name' => $restaurant['name'],
            'reservations' => $count,
            'revenue' => $revenue,
            'rating' => $restaurant['rating']
        ];
    }
}

// Sort by reservations
usort($restaurantReservations, function($a, $b) {
    return $b['reservations'] - $a['reservations'];
});

// User breakdown
$customerCount = 0;
$managerCount = 0;
$adminCount = 0;

foreach ($users as $user) {
    if ($user['role'] === 'customer') $customerCount++;
    elseif ($user['role'] === 'manager') $managerCount++;
    elseif ($user['role'] === 'admin') $adminCount++;
}

// Daily reservations for chart
$dailyReservations = [];
$currentDate = new DateTime($startDate);
$endDateTime = new DateTime($endDateFilter);

while ($currentDate <= $endDateTime) {
    $dateStr = $currentDate->format('Y-m-d');
    $count = 0;
    foreach ($filteredReservations as $res) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics - Gebeta (ገበታ)</title>
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
        .analytics-filters input[type="date"],
        .analytics-filters button {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
        }
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
        .metric-card.average {
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
        .metric-card.average .metric-value {
            color: var(--info-color);
        }
        .metric-label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        .top-restaurants-table {
            width: 100%;
            border-collapse: collapse;
        }
        .top-restaurants-table thead {
            background: var(--background-secondary);
        }
        .top-restaurants-table th {
            padding: 12px;
            text-align: left;
            color: var(--text-primary);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }
        .top-restaurants-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        .top-restaurants-table tr:hover {
            background: var(--background-hover);
        }
    </style>
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
                <li><a class="nav-link" href="index.php">Dashboard</a></li>
                <li><a class="nav-link active" href="analytics.php">Analytics</a></li>
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
        
        <div style="margin-bottom: 30px;">
            <h1>System Analytics & Reports</h1>
            <p class="text-muted">Comprehensive analytics dashboard for system-wide monitoring</p>
        </div>

        <!-- Date Range Filter -->
        <div class="analytics-filters mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDateFilter); ?>" required>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="analytics.php" class="btn btn-outline-primary w-100">Reset</a>
                </div>
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
                <div class="metric-label">Estimated Revenue</div>
                <div class="metric-value">$<?php echo number_format($totalRevenue, 0); ?></div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                    Based on avg $45/guest
                </div>
            </div>
            
            <div class="metric-card guests">
                <div class="metric-label">Total Guests Served</div>
                <div class="metric-value"><?php echo number_format($totalGuests, 0); ?></div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                    Avg party: <?php echo $averagePartySize; ?> guests
                </div>
            </div>
            
            <div class="metric-card average">
                <div class="metric-label">Avg Party Size</div>
                <div class="metric-value"><?php echo $averagePartySize; ?></div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                    People per reservation
                </div>
            </div>
        </div>

        <!-- Reservation Status Breakdown -->
        <div class="row mb-5">
            <div class="col-md-6 col-12 mb-4 mb-md-0">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Reservation Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">User Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="userChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Reservations Chart -->
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

        <!-- Top Restaurants -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="mb-0">Top Restaurants by Reservations</h5>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="top-restaurants-table table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Restaurant</th>
                                <th>Reservations</th>
                                <th>Est. Revenue</th>
                                <th class="pe-4">Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($restaurantReservations, 0, 10) as $restaurant): ?>
                            <tr>
                                <td class="ps-4"><strong><?php echo htmlspecialchars($restaurant['name']); ?></strong></td>
                                <td><?php echo $restaurant['reservations']; ?></td>
                                <td>$<?php echo number_format($restaurant['revenue'], 0); ?></td>
                                <td class="pe-4">
                                    <span class="badge" style="background: var(--primary-color); color: white;">
                                        <?php echo $restaurant['rating']; ?> ⭐
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <div style="text-align: center; padding: 20px; background: var(--light-color); border-radius: 8px; margin-bottom: 15px;">
                            <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px; font-weight: 500;">Total Restaurants</div>
                            <div style="font-size: 32px; font-weight: bold; color: var(--primary-color);"><?php echo count($restaurants); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <div style="text-align: center; padding: 20px; background: var(--light-color); border-radius: 8px; margin-bottom: 15px;">
                            <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px; font-weight: 500;">Total Users</div>
                            <div style="font-size: 32px; font-weight: bold; color: var(--primary-color);"><?php echo count($users); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div style="text-align: center; padding: 20px; background: var(--light-color); border-radius: 8px; margin-bottom: 15px;">
                            <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px; font-weight: 500;">Total Reservations</div>
                            <div style="font-size: 32px; font-weight: bold; color: var(--primary-color);"><?php echo count($allReservations); ?></div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 25px; padding-top: 25px; border-top: 1px solid var(--border-color);">
                    <h6 style="color: var(--text-primary); margin-bottom: 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">User Breakdown</h6>
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3 mb-md-0">
                            <div style="text-align: center; padding: 20px;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--success-color), oklch(0.55 0.15 155)); margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 24px; font-weight: bold; color: white;"><?php echo $customerCount; ?></span>
                                </div>
                                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Customers</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3 mb-md-0">
                            <div style="text-align: center; padding: 20px;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--warning-color), oklch(0.70 0.15 75)); margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 24px; font-weight: bold; color: white;"><?php echo $managerCount; ?></span>
                                </div>
                                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Managers</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div style="text-align: center; padding: 20px;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--destructive-color), oklch(0.50 0.20 30)); margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 24px; font-weight: bold; color: white;"><?php echo $adminCount; ?></span>
                                </div>
                                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Admins</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById('navbarNav').classList.toggle('active');
        }

        // Reservation Status Chart
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
                        'oklch(0.75 0.15 70)',
                        'oklch(0.6 0.15 145)',
                        'oklch(0.55 0.2 25)',
                        'oklch(0.55 0.15 45)'
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

        // User Distribution Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        new Chart(userCtx, {
            type: 'pie',
            data: {
                labels: ['Customers', 'Managers', 'Admins'],
                datasets: [{
                    data: [
                        <?php echo $customerCount; ?>,
                        <?php echo $managerCount; ?>,
                        <?php echo $adminCount; ?>
                    ],
                    backgroundColor: [
                        'oklch(0.6 0.15 145)',
                        'oklch(0.75 0.15 70)',
                        'oklch(0.55 0.2 25)'
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

        // Daily Reservations Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("','", array_map(function($d) { return $d['date']; }, $dailyReservations)) . "'"; ?>],
                datasets: [{
                    label: 'Reservations',
                    data: [<?php echo implode(',', array_map(function($d) { return $d['count']; }, $dailyReservations)); ?>],
                    borderColor: 'oklch(0.55 0.15 45)',
                    backgroundColor: 'rgba(184, 102, 40, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'oklch(0.55 0.15 45)',
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
$userManager->close();
?>

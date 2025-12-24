<?php
/**
 * Distributed Services Testing Hub
 * Test both service provider and service consumer functionality
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/ExternalServiceClient.php';

$client = new ExternalServiceClient();
$testResults = [];

// Test each external service
if (isset($_GET['test'])) {
    $testType = $_GET['test'];
    
    switch ($testType) {
        case 'tours':
            $testResults['tours'] = $client->getTours();
            break;
        case 'hotels':
            $testResults['hotels'] = $client->getHotels();
            break;
        case 'taxis':
            $testResults['taxis'] = $client->getTaxiAvailability('Downtown');
            break;
        case 'tickets':
            $testResults['tickets'] = $client->getTicketRoutes();
            break;
        case 'health':
            $testResults['health'] = $client->checkServiceHealth();
            break;
        case 'all':
            $testResults['tours'] = $client->getTours();
            $testResults['hotels'] = $client->getHotels();
            $testResults['taxis'] = $client->getTaxiAvailability('Downtown');
            $testResults['tickets'] = $client->getTicketRoutes();
            $testResults['health'] = $client->checkServiceHealth();
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributed Services Testing - Restaurant Management</title>
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .test-panel {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .test-result {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-online {
            background: #d4edda;
            color: #155724;
        }
        
        .status-offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
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
                <li><a class="nav-link" href="reservations.php">My Reservations</a></li>
                <li><a class="nav-link" href="services.php">Services</a></li>
                <li><a class="nav-link active" href="distributed-testing.php">SOA Testing</a></li>
                <li>
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle" onclick="toggleUserDropdown(this)" type="button">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['user']['name'], 0, 1)); ?>
                            </div>
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

    <div class="container py-5">
        <h1 class="mb-4">🌐 Distributed Service Architecture Testing</h1>
        <p class="text-muted mb-5">Test the service provider and service consumer functionality of our distributed system</p>

        <!-- Service Provider Section -->
        <div class="test-panel">
            <h2 class="mb-3">📤 Our Service Provider API</h2>
            <p class="text-muted">We expose restaurant booking services to external systems</p>
            
            <div class="mt-4">
                <h4>Available Endpoints:</h4>
                <div class="code-block">
GET  /api/service-provider.php?action=restaurants
GET  /api/service-provider.php?action=restaurant_details&id={id}
POST /api/service-provider.php?action=create_reservation
GET  /api/service-provider.php?action=check_availability
GET  /api/service-provider.php?action=menu&restaurant_id={id}
GET  /api/service-provider.php?action=service_info
                </div>
                
                <div class="mt-3">
                    <a href="../api/service-provider.php?action=service_info" target="_blank" class="btn btn-primary">
                        Test Service Info Endpoint
                    </a>
                    <a href="../api/service-provider.php?action=restaurants" target="_blank" class="btn btn-outline-primary">
                        Test Restaurant List
                    </a>
                    <a href="../api/openapi.yaml" target="_blank" class="btn btn-outline-success">
                        View OpenAPI Spec
                    </a>
                </div>
            </div>
        </div>

        <!-- Service Consumer Section -->
        <div class="test-panel">
            <h2 class="mb-3">📥 External Service Consumer</h2>
            <p class="text-muted">We consume services from other groups (Tours, Hotels, Taxis, Tickets)</p>
            
            <div class="d-flex gap-2 mb-4">
                <a href="?test=tours" class="btn btn-info">Test Tours</a>
                <a href="?test=hotels" class="btn btn-success">Test Hotels</a>
                <a href="?test=taxis" class="btn btn-warning">Test Taxis</a>
                <a href="?test=tickets" class="btn btn-secondary">Test Tickets</a>
                <a href="?test=health" class="btn btn-outline-primary">Health Check</a>
                <a href="?test=all" class="btn btn-primary">Test All Services</a>
            </div>

            <?php if (!empty($testResults)): ?>
                <?php foreach ($testResults as $serviceName => $result): ?>
                <div class="test-result">
                    <h4><?php echo ucfirst($serviceName); ?> Service Response</h4>
                    
                    <?php if ($serviceName === 'health'): ?>
                        <table class="table mt-3">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Response Time</th>
                                    <th>Endpoint</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result as $service => $status): ?>
                                <tr>
                                    <td><?php echo ucfirst($service); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $status['status']; ?>">
                                            <?php echo $status['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $status['response_time_ms']; ?>ms</td>
                                    <td><small><?php echo $status['endpoint']; ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <pre style="background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto;"><?php echo json_encode($result, JSON_PRETTY_PRINT); ?></pre>
                        
                        <?php if (isset($result['success'])): ?>
                        <div class="mt-3">
                            <?php if ($result['success']): ?>
                                <div class="alert alert-success">
                                    ✅ Service call successful! 
                                    <?php if (isset($result['message'])): ?>
                                        <?php echo $result['message']; ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    ⚠️ Service not available or returned error
                                    <br><small>Note: This is expected if the external service is not running</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Service Information -->
        <div class="test-panel">
            <h2 class="mb-3">📋 Service Registry</h2>
            <p class="text-muted">Configured external service endpoints</p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Endpoint</th>
                        <th>Capabilities</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>🗺️ Tours</td>
                        <td>Tour Operator</td>
                        <td><small>http://localhost/tour-service/api</small></td>
                        <td>Book tours, List tours, Check availability</td>
                    </tr>
                    <tr>
                        <td>🏨 Hotels</td>
                        <td>Accommodation</td>
                        <td><small>http://localhost/hotel-service/api</small></td>
                        <td>Book rooms, List hotels, Check availability</td>
                    </tr>
                    <tr>
                        <td>🚕 Taxis</td>
                        <td>Transportation</td>
                        <td><small>http://localhost/taxi-service/api</small></td>
                        <td>Book taxi, Track ride, Fare estimate</td>
                    </tr>
                    <tr>
                        <td>🎫 Tickets</td>
                        <td>Travel Tickets</td>
                        <td><small>http://localhost/ticket-service/api</small></td>
                        <td>Book tickets, List routes, Check schedule</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Documentation -->
        <div class="test-panel">
            <h2 class="mb-3">📚 Documentation</h2>
            <div class="row">
                <div class="col col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Distributed Architecture</h5>
                            <p class="text-muted small">Complete SOA documentation</p>
                            <a href="../DISTRIBUTED_ARCHITECTURE.md" target="_blank" class="btn btn-sm btn-primary">
                                View Documentation
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>OpenAPI Specification</h5>
                            <p class="text-muted small">REST API contract</p>
                            <a href="../api/openapi.yaml" target="_blank" class="btn btn-sm btn-success">
                                Download YAML
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Example Usage -->
        <div class="test-panel">
            <h2 class="mb-3">💡 Example: External System Using Our API</h2>
            <p class="text-muted">How a tour operator would book a restaurant for their tour group:</p>
            
            <div class="code-block">
curl -X POST http://localhost/rest/api/service-provider.php?action=create_reservation \
  -H "Content-Type: application/json" \
  -H "X-API-Key: TOUR_SERVICE_KEY_2025" \
  -d '{
    "restaurant_id": 1,
    "customer_name": "Tour Group Alpha",
    "customer_email": "alpha@tourcompany.com",
    "customer_phone": "+1-555-TOUR",
    "date": "2025-12-25",
    "time": "19:00",
    "guests": 20,
    "special_requests": "Group dinner for tour package",
    "external_booking_id": "TOUR-2025-XYZ789"
  }'
            </div>
        </div>
    </div>

    <footer style="background-color: var(--primary-color); color: white; text-align: center; padding: 2rem; margin-top: 3rem;">
        <p>&copy; 2025 Gebeta (ገበታ). All rights reserved.</p>
    </footer>

    <script>
    function toggleUserDropdown(button) {
        const dropdown = button.closest('.user-dropdown');
        dropdown.classList.toggle('show');
    }
    
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.user-dropdown')) {
            document.querySelectorAll('.user-dropdown').forEach(d => d.classList.remove('show'));
        }
    });
    </script>
</body>
</html>

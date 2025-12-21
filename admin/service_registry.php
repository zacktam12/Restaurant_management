<?php
/**
 * Service Registry
 * Centralized documentation of all service endpoints and integration points
 */

session_start();

// Check if user is logged in and is admin/manager
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'manager')) {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Registry - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-restaurant"></i> Restaurant Manager
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                    <span class="badge bg-secondary"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
                </span>
                <a class="btn btn-outline-light" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="restaurants.php">
                                <i class="bi bi-shop"></i> Restaurants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservations.php">
                                <i class="bi bi-calendar-check"></i> Reservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services.php">
                                <i class="bi bi-gear"></i> External Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="service_registry.php">
                                <i class="bi bi-diagram-3"></i> Service Registry
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Service Registry</h1>
                </div>

                <!-- Overview -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Service Registry Overview</h5>
                    </div>
                    <div class="card-body">
                        <p>This registry documents all available services and endpoints for the Restaurant Management System. It serves as a central reference for service consumers (other groups) to integrate with our system.</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Note:</strong> All API requests require authentication except for GET requests. See <a href="api_keys.php">API Keys Management</a> for details.
                        </div>
                    </div>
                </div>

                <!-- Service Provider Endpoints -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Service Provider Endpoints (Group 7)</h5>
                    </div>
                    <div class="card-body">
                        <p>These endpoints allow other groups to consume our restaurant services.</p>
                        
                        <h6>Base URL</h6>
                        <pre class="bg-light p-3">http://your-domain.com/api/service_provider.php</pre>
                        
                        <h6>Endpoints</h6>
                        <div class="accordion" id="providerEndpoints">
                            <!-- Restaurants -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingRestaurants">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRestaurants">
                                        <i class="bi bi-shop me-2"></i> Restaurants
                                    </button>
                                </h2>
                                <div id="collapseRestaurants" class="accordion-collapse collapse show" data-bs-parent="#providerEndpoints">
                                    <div class="accordion-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Endpoint</th>
                                                    <th>Description</th>
                                                    <th>Authentication</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/restaurants</code></td>
                                                    <td>Get all restaurants</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/restaurants/{id}</code></td>
                                                    <td>Get specific restaurant</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/restaurants?search={keyword}</code></td>
                                                    <td>Search restaurants</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/restaurants?cuisine={type}</code></td>
                                                    <td>Filter by cuisine</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-primary">POST</span></td>
                                                    <td><code>/restaurants</code></td>
                                                    <td>Create new restaurant</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-warning">PUT</span></td>
                                                    <td><code>/restaurants/{id}</code></td>
                                                    <td>Update restaurant</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-danger">DELETE</span></td>
                                                    <td><code>/restaurants/{id}</code></td>
                                                    <td>Delete restaurant</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Menu -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingMenu">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenu">
                                        <i class="bi bi-list me-2"></i> Menu
                                    </button>
                                </h2>
                                <div id="collapseMenu" class="accordion-collapse collapse" data-bs-parent="#providerEndpoints">
                                    <div class="accordion-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Endpoint</th>
                                                    <th>Description</th>
                                                    <th>Authentication</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/menu?restaurant_id={id}</code></td>
                                                    <td>Get menu items by restaurant</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/menu?restaurant_id={id}&category={type}</code></td>
                                                    <td>Get menu items by restaurant and category</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/menu/{id}</code></td>
                                                    <td>Get specific menu item</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-primary">POST</span></td>
                                                    <td><code>/menu</code></td>
                                                    <td>Create new menu item</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-warning">PUT</span></td>
                                                    <td><code>/menu/{id}</code></td>
                                                    <td>Update menu item</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-danger">DELETE</span></td>
                                                    <td><code>/menu/{id}</code></td>
                                                    <td>Delete menu item</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reservations -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingReservations">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReservations">
                                        <i class="bi bi-calendar-check me-2"></i> Reservations
                                    </button>
                                </h2>
                                <div id="collapseReservations" class="accordion-collapse collapse" data-bs-parent="#providerEndpoints">
                                    <div class="accordion-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Endpoint</th>
                                                    <th>Description</th>
                                                    <th>Authentication</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/reservations</code></td>
                                                    <td>Get all reservations</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/reservations/{id}</code></td>
                                                    <td>Get specific reservation</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/reservations?restaurant_id={id}</code></td>
                                                    <td>Get reservations by restaurant</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/reservations?status={status}</code></td>
                                                    <td>Get reservations by status</td>
                                                    <td>None</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-primary">POST</span></td>
                                                    <td><code>/reservations</code></td>
                                                    <td>Create new reservation</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-warning">PUT</span></td>
                                                    <td><code>/reservations/{id}</code></td>
                                                    <td>Update reservation details/status</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-danger">DELETE</span></td>
                                                    <td><code>/reservations/{id}</code></td>
                                                    <td>Delete reservation</td>
                                                    <td>Required (Write)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Availability -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingAvailability">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAvailability">
                                        <i class="bi bi-check-circle me-2"></i> Availability
                                    </button>
                                </h2>
                                <div id="collapseAvailability" class="accordion-collapse collapse" data-bs-parent="#providerEndpoints">
                                    <div class="accordion-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Endpoint</th>
                                                    <th>Description</th>
                                                    <th>Authentication</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/availability/{restaurant_id}?date={YYYY-MM-DD}</code></td>
                                                    <td>Check restaurant availability</td>
                                                    <td>None</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Consumer Endpoints -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Service Consumer Endpoints</h5>
                    </div>
                    <div class="card-body">
                        <p>These endpoints allow us to consume services from other groups.</p>
                        
                        <h6>Base URL</h6>
                        <pre class="bg-light p-3">http://your-domain.com/api/service_consumer.php</pre>
                        
                        <h6>Endpoints</h6>
                        <div class="accordion" id="consumerEndpoints">
                            <!-- Tours -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTours">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTours">
                                        <i class="bi bi-compass me-2"></i> Tours (Groups 1, 5, 9)
                                    </button>
                                </h2>
                                <div id="collapseTours" class="accordion-collapse collapse show" data-bs-parent="#consumerEndpoints">
                                    <div class="accordion-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Endpoint</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/tours</code></td>
                                                    <td>Get all tours</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/tours?search={keyword}</code></td>
                                                    <td>Search tours</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-primary">POST</span></td>
                                                    <td><code>/bookings/tour</code></td>
                                                    <td>Book a tour</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hotels -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingHotels">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHotels">
                                        <i class="bi bi-building me-2"></i> Hotels (Groups 2, 6)
                                    </button>
                                </h2>
                                <div id="collapseHotels" class="accordion-collapse collapse" data-bs-parent="#consumerEndpoints">
                                    <div class="accordion-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Endpoint</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/hotels</code></td>
                                                    <td>Get all hotels</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/hotels?location={location}</code></td>
                                                    <td>Filter hotels by location</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-primary">POST</span></td>
                                                    <td><code>/bookings/hotel</code></td>
                                                    <td>Book a hotel</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Taxis -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTaxis">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTaxis">
                                        <i class="bi bi-taxi-front me-2"></i> Taxis (Groups 4, 8)
                                    </button>
                                </h2>
                                <div id="collapseTaxis" class="accordion-collapse collapse" data-bs-parent="#consumerEndpoints">
                                    <div class="accordion-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Endpoint</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge bg-success">GET</span></td>
                                                    <td><code>/taxis</code></td>
                                                    <td>Get all taxi services</td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-primary">POST</span></td>
                                                    <td><code>/bookings/taxi</code></td>
                                                    <td>Book a taxi</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Integration Points -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Integration Points</h5>
                    </div>
                    <div class="card-body">
                        <h6>Contact Information</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-1-circle me-2"></i> Tour Groups</h6>
                                        <p class="card-text">Groups 1, 5, 9</p>
                                        <small class="text-muted">Contact for tour services integration</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-2-circle me-2"></i> Hotel Groups</h6>
                                        <p class="card-text">Groups 2, 6</p>
                                        <small class="text-muted">Contact for hotel services integration</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-3-circle me-2"></i> Taxi Groups</h6>
                                        <p class="card-text">Groups 4, 8</p>
                                        <small class="text-muted">Contact for taxi services integration</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mt-4">Technical Specifications</h6>
                        <ul>
                            <li><strong>Protocol:</strong> REST over HTTP/HTTPS</li>
                            <li><strong>Data Format:</strong> JSON</li>
                            <li><strong>Authentication:</strong> API Key in Authorization header</li>
                            <li><strong>Rate Limiting:</strong> 1000 requests/hour per API key</li>
                            <li><strong>Error Handling:</strong> Standard HTTP status codes with JSON error messages</li>
                        </ul>
                    </div>
                </div>

                <!-- Documentation Links -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Additional Documentation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-file-earmark-code me-2"></i> API Documentation</h6>
                                        <p class="card-text">Complete API documentation with examples and data models.</p>
                                        <a href="../api/API_DOCUMENTATION.md" class="btn btn-primary">View Documentation</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-key me-2"></i> API Keys Management</h6>
                                        <p class="card-text">Generate and manage API keys for service consumers.</p>
                                        <a href="api_keys.php" class="btn btn-primary">Manage API Keys</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
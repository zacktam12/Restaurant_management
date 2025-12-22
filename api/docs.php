<?php
/**
 * API Documentation
 * Documentation for the Restaurant Management System Web Services API
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Restaurant Management System</title>
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/enhanced-styles.css" rel="stylesheet">
    <style>
        .endpoint-card {
            margin-bottom: 1rem;
        }
        .endpoint-method {
            font-weight: bold;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            color: white;
        }
        .method-get { background-color: var(--bs-success); }
        .method-post { background-color: var(--bs-primary); }
        .method-put { background-color: var(--bs-warning); color: black; }
        .method-delete { background-color: var(--bs-danger); }
        pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            overflow-x: auto;
        }
        /* Custom icon replacement */
        .custom-icon {
            display: inline-block;
            width: 1em;
            height: 1em;
            background-color: currentColor;
            mask-size: cover;
            -webkit-mask-size: cover;
            vertical-align: -0.125em;
        }
        
        .icon-code-square {
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z'/%3E%3Cpath d='M6.854 4.646a.5.5 0 0 1 0 .708L4.207 8l2.647 2.646a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 0 1 .708 0zm2.292 0a.5.5 0 0 0 0 .708L11.793 8l-2.647 2.646a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708 0z'/%3E%3C/svg%3E");
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z'/%3E%3Cpath d='M6.854 4.646a.5.5 0 0 1 0 .708L4.207 8l2.647 2.646a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 0 1 .708 0zm2.292 0a.5.5 0 0 0 0 .708L11.793 8l-2.647 2.646a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708 0z'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <span class="custom-icon icon-code-square"></span> API Documentation
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="#introduction" class="list-group-item list-group-item-action">Introduction</a>
                    <a href="#authentication" class="list-group-item list-group-item-action">Authentication</a>
                    <a href="#restaurants" class="list-group-item list-group-item-action">Restaurants</a>
                    <a href="#menu" class="list-group-item list-group-item-action">Menu Items</a>
                    <a href="#reservations" class="list-group-item list-group-item-action">Reservations</a>
                    <a href="#users" class="list-group-item list-group-item-action">Users</a>
                    <a href="#bookings" class="list-group-item list-group-item-action">Bookings</a>
                    <a href="#places" class="list-group-item list-group-item-action">Places</a>
                </div>
            </div>
            <div class="col-md-9">
                <div id="introduction">
                    <h1>Restaurant Management System API</h1>
                    <p class="lead">This documentation provides details about the RESTful API endpoints available for the Restaurant Management System.</p>
                    
                    <div class="alert alert-info">
                        <h5>Base URL</h5>
                        <code>http://localhost/restaurant-management-system/api</code>
                    </div>
                    
                    <h3>Response Format</h3>
                    <p>All API responses are returned in JSON format:</p>
                    <pre>{
    "success": true,
    "message": "Operation completed successfully",
    "data": {}
}</pre>
                </div>

                <div id="authentication" class="mt-5">
                    <h2>Authentication</h2>
                    <p>Most endpoints require authentication. Authentication is handled through session cookies.</p>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-post">POST</span>
                            <code>/auth.php?action=login</code>
                        </div>
                        <div class="card-body">
                            <h5>Login</h5>
                            <p>Authenticate a user and start a session.</p>
                            
                            <h6>Request Body</h6>
                            <pre>{
    "email": "user@example.com",
    "password": "password123",
    "role": "customer"
}</pre>
                            
                            <h6>Response</h6>
                            <pre>{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "email": "user@example.com",
        "name": "John Doe",
        "role": "customer",
        "phone": "+1234567890",
        "professional_details": null
    }
}</pre>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-post">POST</span>
                            <code>/auth.php?action=register</code>
                        </div>
                        <div class="card-body">
                            <h5>Register</h5>
                            <p>Create a new user account.</p>
                            
                            <h6>Request Body</h6>
                            <pre>{
    "email": "newuser@example.com",
    "password": "password123",
    "name": "Jane Smith",
    "role": "tourist",
    "phone": "+1234567890",
    "professional_details": "Tour guide with 5 years experience"
}</pre>
                        </div>
                    </div>
                </div>

                <div id="restaurants" class="mt-5">
                    <h2>Restaurants</h2>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/restaurants.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Get All Restaurants</h5>
                            <p>Retrieve a list of all restaurants.</p>
                            
                            <h6>Response</h6>
                            <pre>{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "La Bella Vista",
            "description": "Authentic Italian cuisine",
            "cuisine": "Italian",
            "address": "123 Main St",
            "phone": "+1234567890",
            "price_range": "$$$",
            "rating": "4.5",
            "image": "/images/restaurant1.jpg",
            "seating_capacity": "100"
        }
    ]
}</pre>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/restaurants.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Get Restaurant by ID</h5>
                            <p>Retrieve details of a specific restaurant.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-post">POST</span>
                            <code>/restaurants.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Create Restaurant</h5>
                            <p>Create a new restaurant.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-put">PUT</span>
                            <code>/restaurants.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Update Restaurant</h5>
                            <p>Update an existing restaurant.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-delete">DELETE</span>
                            <code>/restaurants.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Delete Restaurant</h5>
                            <p>Delete a restaurant.</p>
                        </div>
                    </div>
                </div>

                <div id="menu" class="mt-5">
                    <h2>Menu Items</h2>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/menu.php?restaurant_id={id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Get Menu Items by Restaurant</h5>
                            <p>Retrieve menu items for a specific restaurant.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/menu.php?restaurant_id={id}&category={category}</code>
                        </div>
                        <div class="card-body">
                            <h5>Get Menu Items by Category</h5>
                            <p>Retrieve menu items for a specific restaurant and category.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-post">POST</span>
                            <code>/menu.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Create Menu Item</h5>
                            <p>Create a new menu item.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-put">PUT</span>
                            <code>/menu.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Update Menu Item</h5>
                            <p>Update an existing menu item.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-delete">DELETE</span>
                            <code>/menu.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Delete Menu Item</h5>
                            <p>Delete a menu item.</p>
                        </div>
                    </div>
                </div>

                <div id="reservations" class="mt-5">
                    <h2>Reservations</h2>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/reservations.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Get All Reservations</h5>
                            <p>Retrieve all reservations.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/reservations.php?restaurant_id={id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Get Reservations by Restaurant</h5>
                            <p>Retrieve reservations for a specific restaurant.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-post">POST</span>
                            <code>/reservations.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Create Reservation</h5>
                            <p>Create a new reservation.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-put">PUT</span>
                            <code>/reservations.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Update Reservation</h5>
                            <p>Update an existing reservation (status or details).</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-delete">DELETE</span>
                            <code>/reservations.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Delete Reservation</h5>
                            <p>Delete a reservation.</p>
                        </div>
                    </div>
                </div>

                <div id="users" class="mt-5">
                    <h2>Users</h2>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/users.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Get All Users</h5>
                            <p>Retrieve all users (admin only).</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/users.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Get User by ID</h5>
                            <p>Retrieve details of a specific user.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-put">PUT</span>
                            <code>/users.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Update User</h5>
                            <p>Update user profile information.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-delete">DELETE</span>
                            <code>/users.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Delete User</h5>
                            <p>Delete a user account.</p>
                        </div>
                    </div>
                </div>

                <div id="bookings" class="mt-5">
                    <h2>Bookings</h2>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/bookings.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Get All Bookings</h5>
                            <p>Retrieve all external service bookings.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/bookings.php?customer_id={id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Get Bookings by Customer</h5>
                            <p>Retrieve bookings for a specific customer.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-post">POST</span>
                            <code>/bookings.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Create Booking</h5>
                            <p>Create a new external service booking.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-put">PUT</span>
                            <code>/bookings.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Update Booking</h5>
                            <p>Update an existing booking (status or details).</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-delete">DELETE</span>
                            <code>/bookings.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Delete Booking</h5>
                            <p>Delete a booking.</p>
                        </div>
                    </div>
                </div>

                <div id="places" class="mt-5">
                    <h2>Places</h2>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/places.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Get All Places</h5>
                            <p>Retrieve a list of all tourist places.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-get">GET</span>
                            <code>/places.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Get Place by ID</h5>
                            <p>Retrieve details of a specific place.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-post">POST</span>
                            <code>/places.php</code>
                        </div>
                        <div class="card-body">
                            <h5>Create Place</h5>
                            <p>Create a new tourist place.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-put">PUT</span>
                            <code>/places.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Update Place</h5>
                            <p>Update an existing tourist place.</p>
                        </div>
                    </div>
                    
                    <div class="card endpoint-card">
                        <div class="card-header">
                            <span class="endpoint-method method-delete">DELETE</span>
                            <code>/places.php/{id}</code>
                        </div>
                        <div class="card-body">
                            <h5>Delete Place</h5>
                            <p>Delete a tourist place.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>
<?php
/**
 * User Management Page
 * Admin interface for managing users
 */

session_start();

// Check if user is logged in and is admin
require_once '../backend/Permission.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Require admin access
Permission::requireAdmin($_SESSION['user']['role']);

require_once '../backend/config.php';
require_once '../backend/User.php';

$userManager = new User();

// Handle delete confirmation
if (isset($_GET['confirm_delete'])) {
    $userId = $_GET['confirm_delete'];
    
    // We'll handle the actual deletion through a GET request
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $professional_details = $_POST['professional_details'] ?? '';
                
                // Validate input
                if (empty($name) || empty($email) || empty($password) || empty($role)) {
                    $message = 'Please fill in all required fields.';
                    $messageType = 'danger';
                } else {
                    // Attempt registration
                    $result = $userManager->register($email, $password, $name, $role, $phone, $professional_details);
                    
                    if ($result['success']) {
                        $message = 'User added successfully!';
                        $messageType = 'success';
                    } else {
                        $message = $result['message'];
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'update_user':
                $id = $_POST['id'] ?? '';
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $role = $_POST['role'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $professional_details = $_POST['professional_details'] ?? '';
                
                if (empty($id) || empty($name) || empty($email) || empty($role)) {
                    $message = 'Please fill in all required fields.';
                    $messageType = 'danger';
                } else {
                    $result = $userManager->updateUser($id, $name, $email, $role, $phone, $professional_details);
                    
                    if ($result['success']) {
                        $message = 'User updated successfully!';
                        $messageType = 'success';
                        // Clear edit mode
                        if (isset($_GET['edit_user'])) {
                            unset($_GET['edit_user']);
                            // Optional: redirect to remove query param
                            // header("Location: users.php?msg=updated");
                        }
                    } else {
                        $message = $result['message'];
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'delete_user':
                $message = 'Delete functionality would be implemented here for user ID: ' . $_POST['id'];
                $messageType = 'info';
                break;
        }
    }
} elseif (isset($_GET['confirm_delete'])) {
    // Handle delete confirmation through GET parameters
    $userId = $_GET['confirm_delete'];
    
    // Show confirmation message
    $user = $userManager->getUserById($userId);
    if ($user) {
        $message = 'Are you sure you want to delete user "' . htmlspecialchars($user['name']) . '"?';
        $messageType = 'warning';
        $showDeleteConfirmation = true;
    }
}

// Handle edit request
$editUser = null;
if (isset($_GET['edit_user'])) {
    $editUserId = $_GET['edit_user'];
    $editUser = $userManager->getUserById($editUserId);
    if (!$editUser) {
        $message = 'User not found.';
        $messageType = 'danger';
    }
} else if (isset($_GET['delete_confirmed']) && isset($_GET['id'])) {
    // Handle confirmed delete
    $result = $userManager->deleteUser($_GET['id']);
    if ($result['success']) {
        $message = 'User deleted successfully.';
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

$users = $userManager->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Restaurant Management System</title>
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/enhanced-styles.css" rel="stylesheet">
    <link href="../css/admin-dashboard-polish.css" rel="stylesheet">
    <link href="../css/admin-layout.css" rel="stylesheet">
    <link href="../css/admin-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../admin/index.php">
                <span class="custom-icon icon-restaurant"></span> Restaurant Manager
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

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="position-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <span class="custom-icon icon-speedometer2"></span> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="users.php">
                        <span class="custom-icon icon-people"></span> User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="restaurants.php">
                        <span class="custom-icon icon-shop"></span> Restaurants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php">
                        <span class="custom-icon icon-calendar-check"></span> Reservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="services.php">
                        <span class="custom-icon icon-gear"></span> External Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <span class="custom-icon icon-graph-up"></span> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="api_keys.php">
                        <span class="custom-icon icon-key"></span> API Keys
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="service_registry.php">
                        <span class="custom-icon icon-diagram-3"></span> Service Registry
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">User Management</h1>
            <form method="GET">
                <input type="hidden" name="show_add_form" value="1">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New User
                </button>
            </form>
        </div>
                
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($showDeleteConfirmation) && $showDeleteConfirmation): ?>
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Confirm Deletion</h4>
                    <p><?php echo htmlspecialchars($message); ?></p>
                    <hr>
                    <div class="d-flex">
                        <a href="users.php" class="btn btn-secondary me-2">Cancel</a>
                        <form method="GET" class="d-inline">
                            <input type="hidden" name="delete_confirmed" value="1">
                            <input type="hidden" name="id" value="<?php echo $_GET['confirm_delete']; ?>">
                            <button type="submit" class="btn btn-danger">Yes, Delete User</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Add User Form -->
                <?php if (isset($_GET['show_add_form']) && $_GET['show_add_form'] == '1'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Add New User</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_user">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="customer">Customer</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="professional_details" class="form-label">Professional Details</label>
                                <textarea class="form-control" id="professional_details" name="professional_details" rows="3"></textarea>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="users.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Edit User Form -->
                <?php if ($editUser): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit User</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="users.php">
                            <input type="hidden" name="action" value="update_user">
                            <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_role" class="form-label">Role</label>
                                <select class="form-select" id="edit_role" name="role" required>
                                    <option value="customer" <?php echo $editUser['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                    <option value="manager" <?php echo $editUser['role'] == 'manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="admin" <?php echo $editUser['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($editUser['phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="edit_professional_details" class="form-label">Professional Details</label>
                                <textarea class="form-control" id="edit_professional_details" name="professional_details" rows="3"><?php echo htmlspecialchars($editUser['professional_details'] ?? ''); ?></textarea>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="users.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update User</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Phone</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['role'] == 'admin' ? 'danger' : 
                                                     ($user['role'] == 'manager' ? 'warning' : 
                                                     ($user['role'] == 'customer' ? 'primary' : 'secondary')); ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="users.php?edit_user=<?php echo $user['id']; ?>">
                                                            <i class="bi bi-pencil me-2"></i>Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="users.php?confirm_delete=<?php echo $user['id']; ?>">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

    <script src="../js/app.js"></script>
</body>
</html>

<?php
$userManager->close();
?>
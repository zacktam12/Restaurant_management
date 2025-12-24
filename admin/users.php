<?php
/**
 * Admin User Management
 * CRUD operations for users
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/User.php';
require_once '../backend/Alert.php';

$userManager = new User();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $result = $userManager->register(
            $_POST['email'],
            $_POST['password'],
            $_POST['name'],
            $_POST['role'],
            $_POST['phone'] ?? null,
            $_POST['professional_details'] ?? null
        );
        
        if ($result['success']) {
            Alert::setSuccess('User created successfully!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'update_role') {
        $result = $userManager->updateRole($_POST['id'], $_POST['role']);
        if ($result['success']) {
            Alert::setSuccess('User role updated!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'delete') {
        if ($_POST['id'] == $_SESSION['user']['id']) {
            Alert::setError('You cannot delete your own account!');
        } else {
            $result = $userManager->deleteUser($_POST['id']);
            if ($result['success']) {
                Alert::setSuccess('User deleted successfully!');
            } else {
                Alert::setError($result['message']);
            }
        }
    }
    
    header('Location: users.php');
    exit();
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'add';

// Filter by role
$roleFilter = $_GET['role'] ?? '';
if ($roleFilter) {
    $users = $userManager->getUsersByRole($roleFilter);
} else {
    $users = $userManager->getAllUsers();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
                <li><a class="nav-link" href="reservations.php">Reservations</a></li>
                <li><a class="nav-link active" href="users.php">Users</a></li>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h1 class="mb-0">Manage Users</h1>
            <?php if (!$showForm): ?>
            <a href="users.php?action=add" class="btn btn-primary">+ Add User</a>
            <?php else: ?>
            <a href="users.php" class="btn btn-secondary">Back to List</a>
            <?php endif; ?>
        </div>
        
        <?php if ($showForm): ?>
        <!-- Add User Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New User</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-control form-select" id="role" name="role" required>
                                    <option value="customer">Customer</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="professional_details" class="form-label">Professional Details</label>
                        <textarea class="form-control" id="professional_details" name="professional_details"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="users.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Filter Buttons -->
        <div class="mb-4 d-flex gap-2 flex-wrap">
            <a href="users.php" class="btn <?php echo !$roleFilter ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
            <a href="users.php?role=admin" class="btn <?php echo $roleFilter === 'admin' ? 'btn-primary' : 'btn-outline-primary'; ?>">Admins</a>
            <a href="users.php?role=manager" class="btn <?php echo $roleFilter === 'manager' ? 'btn-primary' : 'btn-outline-primary'; ?>">Managers</a>
            <a href="users.php?role=customer" class="btn <?php echo $roleFilter === 'customer' ? 'btn-primary' : 'btn-outline-primary'; ?>">Customers</a>
        </div>
        
        <!-- Users Table -->
        <div class="card">
            <div class="card-body px-0">
                <?php if (empty($users)): ?>
                <p class="text-center text-muted py-4">No users found.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="ps-4">#<?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $user['role'] === 'admin' ? 'danger' : 
                                            ($user['role'] === 'manager' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="pe-4">
                                    <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                                    <div class="action-dropdown">
                                        <button class="action-dropdown-toggle" onclick="toggleActionDropdown(this)" type="button">
                                            ⋯
                                        </button>
                                        <div class="action-dropdown-menu">
                                            <div class="action-dropdown-header">Change Role</div>
                                            
                                            <?php foreach(['customer', 'manager', 'admin'] as $role): ?>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="action" value="update_role">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="role" value="<?php echo $role; ?>">
                                                <button type="submit" class="action-dropdown-item <?php echo $user['role'] === $role ? 'active' : ''; ?>" <?php echo $user['role'] === $role ? 'disabled' : ''; ?>>
                                                    <span class="icon"><?php echo $user['role'] === $role ? '✓' : '○'; ?></span>
                                                    <?php echo ucfirst($role); ?>
                                                </button>
                                            </form>
                                            <?php endforeach; ?>
                                            
                                            <div class="action-dropdown-divider"></div>
                                            
                                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="action-dropdown-item danger">
                                                    <span class="icon">🗑️</span>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted small">(You)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script>
    function toggleMenu() {
        document.getElementById('navbarNav').classList.toggle('active');
    }
    
    function toggleUserDropdown(button) {
        const dropdown = button.closest('.user-dropdown');
        const menu = dropdown.querySelector('.user-dropdown-menu'); // Fix: Use querySelector on the specific dropdown
        const allUserDropdowns = document.querySelectorAll('.user-dropdown'); // Close others
        
        allUserDropdowns.forEach(d => {
            if (d !== dropdown) {
                d.classList.remove('show');
                const m = d.querySelector('.user-dropdown-menu');
                if (m) m.classList.remove('show');
            }
        });

        // Close any open action dropdowns
        document.querySelectorAll('.action-dropdown-menu.show').forEach(m => m.classList.remove('show'));
        
        dropdown.classList.toggle('show');
        if (menu) menu.classList.toggle('show');
    }

    function toggleActionDropdown(button) {
        const dropdown = button.nextElementSibling;
        const allDropdowns = document.querySelectorAll('.action-dropdown-menu');
        
        // Close all other dropdowns
        allDropdowns.forEach(menu => {
            if (menu !== dropdown) {
                menu.classList.remove('show');
            }
        });
        
        // Close user dropdowns
        document.querySelectorAll('.user-dropdown, .user-dropdown-menu').forEach(el => el.classList.remove('show'));

        // Toggle current dropdown
        dropdown.classList.toggle('show');
    }
    
    document.addEventListener('click', function(event) {
        // Close user dropdown if clicked outside
        if (!event.target.closest('.user-dropdown')) {
            document.querySelectorAll('.user-dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
                const menu = dropdown.querySelector('.user-dropdown-menu');
                if (menu) menu.classList.remove('show');
            });
        }

        // Close action dropdown if clicked outside
        if (!event.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
    </script>
</body>
</html>
<?php
$userManager->close();
?>

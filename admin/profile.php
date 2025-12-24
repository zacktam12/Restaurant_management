<?php
/**
 * Admin Profile Page
 * View and update profile settings
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
    
    if ($action === 'update_profile') {
        $profile_image = null;
        
        // Handle Image Upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/users/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileInfo = pathinfo($_FILES['profile_image']['name']);
            $extension = strtolower($fileInfo['extension']);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extension, $allowedExtensions)) {
                $fileName = 'user_' . $_SESSION['user']['id'] . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                    $profile_image = 'assets/users/' . $fileName;
                } else {
                    Alert::setError('Failed to upload image.');
                }
            } else {
                Alert::setError('Invalid file type. Allowed: JPG, JPEG, PNG, GIF.');
            }
        }

        $result = $userManager->updateProfile(
            $_SESSION['user']['id'],
            $_POST['name'],
            $_POST['phone'],
            null,
            $profile_image
        );
        
        if ($result['success']) {
            $_SESSION['user']['name'] = $_POST['name'];
            $_SESSION['user']['phone'] = $_POST['phone'];
            if ($profile_image) {
                $_SESSION['user']['profile_image'] = $profile_image;
            }
            Alert::setSuccess('Profile updated successfully!');
        } else {
            Alert::setError($result['message']);
        }
    } elseif ($action === 'change_password') {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            Alert::setError('New passwords do not match.');
        } else {
            $result = $userManager->updatePassword(
                $_SESSION['user']['id'],
                $_POST['current_password'],
                $_POST['new_password']
            );
            
            if ($result['success']) {
                Alert::setSuccess('Password changed successfully!');
            } else {
                Alert::setError($result['message']);
            }
        }
    }
    
    header('Location: profile.php');
    exit();
}

$user = $userManager->getUserById($_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Gebeta (ገበታ)</title>
    <link href="../css/style.css" rel="stylesheet">
    <style>
         /* Additional profile-specific overrides if needed */
    </style>
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
        
        <div class="row">
            <div class="col-12 col-md-2 mb-4 mb-md-0">
                <div class="profile-card mb-4 h-100">
                    <div class="profile-card-header">
                        <div class="profile-card-avatar mx-auto">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                            <?php else: ?>
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        
                        <h4 class="profile-card-name mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="profile-card-role mb-2"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                        <p class="profile-card-join mb-0">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    
                    <div class="profile-card-body text-center">
                        <div class="profile-stats">
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?php echo $userManager->getUserReservationsCount($_SESSION['user']['id']); ?></div>
                                <div class="profile-stat-label">Reservations</div>
                            </div>
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?php echo $userManager->getUserRestaurantsCount($_SESSION['user']['id']); ?></div>
                                <div class="profile-stat-label">Restaurants</div>
                            </div>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="mt-3">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            <div class="mb-2">
                                <label for="profile_image" class="form-label">Upload Profile Image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif" onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-10">
                <!-- Profile Settings -->
                <div class="profile-settings-card mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">Profile Settings</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="profile-settings-card">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">Change Password</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

<?php
/**
 * Login Page
 * User authentication interface
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if ($_SESSION['user']['role'] == 'admin' || $_SESSION['user']['role'] == 'manager') {
        header('Location: admin/index.php');
    } else {
        header('Location: tourist/index.php');
    }
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'backend/config.php';
    require_once 'backend/User.php';
    
    $userManager = new User();
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all fields.';
    } else {
        // Attempt login
        $result = $userManager->login($email, $password, $role);
        
        if ($result['success']) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = $result['user'];
            
            // Redirect based on role
            if ($role == 'admin' || $role == 'manager') {
                header('Location: admin/index.php');
            } else {
                header('Location: tourist/index.php');
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-restaurant fs-1 text-primary"></i>
                            <h2 class="mt-3">Restaurant Manager</h2>
                            <p class="text-muted">Sign in to your account</p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
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
                                    <option value="">Select your role</option>
                                    <option value="customer">Customer</option>
                                    <option value="tourist">Tourist</option>
                                    <option value="tour_guide">Tour Guide</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Don't have an account? <a href="register.php">Register</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
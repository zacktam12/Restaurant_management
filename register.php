<?php
/**
 * Registration Page - Professional split-layout design
 * Matches Login Page aesthetics
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: customer/index.php');
    exit();
}

require_once 'backend/User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $professional_details = trim($_POST['professional_details'] ?? '');
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } else if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $userManager = new User();
        // register method signature: ($email, $password, $name, $role, $phone, $professional_details)
        $result = $userManager->register($email, $password, $name, $role, $phone, $professional_details);
        
        if ($result['success']) {
            $success = 'Registration successful! Redirecting to login...';
            header('refresh:2;url=login.php');
        } else {
            $error = $result['message'];
        }
        
        $userManager->close();
    }
}

// Fetch Real-time stats for the Hero section
require_once 'backend/config.php';
$db = new Database();
$conn = $db->getConnection();

// 1. Total Restaurants
$resCount = $conn->query("SELECT COUNT(*) as total FROM restaurants WHERE is_deleted = 0")->fetch_assoc()['total'];
// 2. Total Experiences (Reservations + Bookings)
$bookingCount = $conn->query("SELECT (SELECT COUNT(*) FROM reservations) + (SELECT COUNT(*) FROM bookings) as total")->fetch_assoc()['total'];
// 3. System Avg Rating
$avgRating = $conn->query("SELECT AVG(rating) as avg FROM reviews")->fetch_assoc()['avg'] ?: 4.8;
$avgRating = number_format($avgRating, 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Gebeta (ገበታ)</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            height: 100vh;
            overflow: hidden; /* Prevent body scroll */
        }
        .login-wrapper {
            height: 100vh; /* Fixed height */
            display: flex;
            background: white;
            overflow: hidden;
        }
        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px; /* Reduced padding */
            background: white;
            position: relative;
            overflow-y: auto;
        }
        .login-right {
            flex: 1;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.6)), 
                        url('https://f7e5m2b4.delivery.rocketcdn.me/wp-content/uploads/2018/10/Ethiopian-Restaurant-1.jpg.avif');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        /* Decor */
        .login-right::before, .login-right::after {
            content: ''; position: absolute; border-radius: 50%; opacity: 0.1;
        }
        .login-right::before { width: 300px; height: 300px; background: white; top: -100px; left: -100px; }
        .login-right::after { width: 200px; height: 200px; background: white; bottom: -80px; right: -80px; }

        .hero-content { text-align: center; position: relative; z-index: 1; max-width: 450px; }
        .hero-icon {
            font-size: 60px; margin-bottom: 30px; display: inline-flex;
            background: rgba(255, 255, 255, 0.2); width: 100px; height: 100px;
            border-radius: 20px; align-items: center; justify-content: center;
        }
        .hero-title { font-size: 32px; font-weight: 700; margin-bottom: 15px; color: white; }
        .hero-text { font-size: 15px; line-height: 1.6; color: rgba(255, 255, 255, 0.9); margin-bottom: 40px; }
        
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .stat-item {
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);
            border-radius: 12px; padding: 20px; border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .stat-number { font-size: 24px; font-weight: 700; color: white; margin-bottom: 5px; }
        .stat-label { font-size: 12px; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.5px; }

        .login-card {
            width: 100%; max-width: 500px;
            background: white; border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05); /* Lighter shadow */
            padding: 25px 30px; /* Compact padding */
        }
        
        .login-header { text-align: center; margin-bottom: 15px; /* Reduced margin */ }
        .login-icon {
            display: inline-flex; width: 48px; height: 48px; /* Smaller icon */
            background: linear-gradient(135deg, oklch(0.55 0.15 45) 0%, oklch(0.45 0.15 45) 100%);
            border-radius: 12px; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 10px; /* Reduced margin */
        }
        .login-header h1 { font-size: 20px; color: #1e293b; margin-bottom: 4px; font-weight: 700; }
        .login-header p { font-size: 13px; color: #64748b; }

        .form-group { margin-bottom: 10px; /* Compact spacing */ }
        .form-label {
            display: block; font-size: 12px; font-weight: 600; color: #1e293b;
            margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .form-control {
            width: 100%; padding: 8px 12px; /* Compact inputs */
            border: 2px solid #e2e8f0;
            border-radius: 6px; font-size: 13px; transition: all 0.3s;
        }
        .form-control:focus { outline: none; border-color: oklch(0.55 0.15 45); background: #faf5f0; }
        
        /* ... select base styles ... */

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: right 12px center; background-size: 16px 12px;
        }

        .btn-register {
            width: 100%; padding: 12px 24px;
            background: linear-gradient(135deg, oklch(0.55 0.15 45) 0%, oklch(0.45 0.15 45) 100%);
            color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all 0.3s; text-transform: uppercase; letter-spacing: 0.5px;
            margin-top: 10px;
        }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(140, 70, 0, 0.3); }

        .login-link { text-align: center; font-size: 13px; color: #64748b; margin-top: 20px; }
        .login-link a { color: oklch(0.55 0.15 45); text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid; }
        .alert-danger { background: #fee2e2; color: #991b1b; border-color: #dc2626; }
        .alert-success { background: #dcfce7; color: #166534; border-color: #22c55e; }

        .row-group { display: flex; gap: 15px; }
        .row-group .form-group { flex: 1; }

        @media (max-width: 900px) {
            body, .login-wrapper { height: auto; overflow: visible; }
            .login-wrapper { flex-direction: column; }
            .login-left { padding: 40px 20px; min-height: 100vh; }
            .login-right { display: none; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Side: Registration Form -->
        <div class="login-left">
            <div class="login-card">
                <div class="login-header">
                    <div style="margin-bottom: 20px;">
                        <img src="assets/logo.jpg" alt="Gebeta Logo" style="width: 64px; height: 64px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    </div>
                    <h1>Create Account</h1>
                    <p>Join our restaurant management platform</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <!-- Email & Phone Row -->
                    <div class="row-group">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div class="form-group">
                        <label for="role" class="form-label">Account Type</label>
                        <select class="form-control form-select" id="role" name="role" onchange="toggleProfessionalDetails()">
                            <option value="customer" <?php echo ($_POST['role'] ?? '') === 'customer' ? 'selected' : ''; ?>>Customer (Diner)</option>
                            <option value="manager" <?php echo ($_POST['role'] ?? '') === 'manager' ? 'selected' : ''; ?>>Restaurant Manager</option>
                        </select>
                    </div>

                    <!-- Manager Details -->
                    <div class="form-group" id="professional-details-group" style="display:none;">
                        <label for="professional_details" class="form-label">Professional Details</label>
                        <textarea class="form-control" id="professional_details" name="professional_details" 
                                  placeholder="Describe your experience..."><?php echo htmlspecialchars($_POST['professional_details'] ?? ''); ?></textarea>
                    </div>

                    <!-- Password Row -->
                    <div class="row-group">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">Register Now</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </div>
        </div>

        <!-- Right Side: Hero Section -->
        <div class="login-right">
            <div class="hero-content">
                <div class="hero-icon" style="background: white; padding: 10px; overflow: hidden; display: flex;">
                    <img src="assets/logo.jpg" alt="Gebeta Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                </div>
                <h2 class="hero-title">Gebeta (ገበታ)</h2>
                <p class="hero-text">Create your account today to unlock seamless reservations, exclusive offers, and smarter management tools.</p>

                <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $resCount; ?></div>
                <div class="stat-label">Restaurants</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo ($bookingCount > 1000) ? number_format($bookingCount/1000, 1).'k' : $bookingCount; ?></div>
                <div class="stat-label">Experiences</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $avgRating; ?></div>
                <div class="stat-label">Avg Rating</div>
            </div>
        </div>
                </div>

                <p style="font-size: 13px; color: rgba(255, 255, 255, 0.8);">
                    Experience the best restaurant management platform.
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleProfessionalDetails() {
            const role = document.getElementById('role').value;
            const detailsGroup = document.getElementById('professional-details-group');
            detailsGroup.style.display = (role === 'manager') ? 'block' : 'none';
        }
        toggleProfessionalDetails();
    </script>
</body>
</html>

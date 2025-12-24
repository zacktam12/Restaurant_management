<?php
/**
 * Login Page - Professional Design with Warm Theme
 * Handles user authentication
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $redirect = 'index.php'; // Default fallback
    if (isset($_SESSION['user']['role'])) {
        switch ($_SESSION['user']['role']) {
            case 'admin':   $redirect = 'admin/index.php'; break;
            case 'manager': $redirect = 'manager/index.php'; break;
            case 'customer':$redirect = 'customer/index.php'; break;
        }
    }
    header('Location: ' . $redirect);
    exit();
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

require_once 'backend/User.php';
require_once 'backend/Alert.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $userManager = new User();
        $result = $userManager->login($email, $password);
        
        if ($result['success']) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = $result['user'];
            
            $role = $result['user']['role'];
            switch ($role) {
                case 'admin':
                    header('Location: admin/index.php');
                    break;
                case 'manager':
                    header('Location: manager/index.php');
                    break;
                default:
                    header('Location: customer/index.php');
            }
            exit();
        } else {
            $error = $result['message'];
        }
        
        $userManager->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gebeta (ገበታ)</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            background: white;
        }

        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 60px;
            background: white;
            position: relative;
        }

        /* Updated gradient to warm amber/terracotta tones with background image */
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

        /* Decorative floating elements */
        .login-right::before,
        .login-right::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
        }

        .login-right::before {
            width: 300px;
            height: 300px;
            background: white;
            top: -100px;
            left: -100px;
        }

        .login-right::after {
            width: 200px;
            height: 200px;
            background: white;
            bottom: -80px;
            right: -80px;
        }

        .hero-content {
            text-align: center;
            position: relative;
            z-index: 1;
            max-width: 450px;
        }

        .hero-icon {
            font-size: 60px;
            margin-bottom: 30px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            width: 100px;
            height: 100px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
        }

        .hero-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            color: white;
        }

        .hero-text {
            font-size: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 50px 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        /* Updated login icon gradient to warm theme */
        .login-icon {
            display: inline-block;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, oklch(0.55 0.15 45) 0%, oklch(0.45 0.15 45) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .login-header h1 {
            font-size: 24px;
            color: #1e293b;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .login-header p {
            font-size: 14px;
            color: #64748b;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        /* Updated focus state to warm primary color */
        .form-control:focus {
            outline: none;
            border-color: oklch(0.55 0.15 45);
            background: #faf5f0;
        }

        .form-control::placeholder {
            color: #cbd5e1;
        }

        .form-input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #cbd5e1;
        }

        .form-control.with-icon {
            padding-left: 40px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            font-size: 13px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: oklch(0.55 0.15 45);
        }

        .forgot-link {
            color: oklch(0.55 0.15 45);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: oklch(0.45 0.15 45);
        }

        /* Updated button gradient to warm theme */
        .btn-login {
            width: 100%;
            padding: 12px 24px;
            background: linear-gradient(135deg, oklch(0.55 0.15 45) 0%, oklch(0.45 0.15 45) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(140, 70, 0, 0.3);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-color: #dc2626;
        }

        .signup-link {
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        .signup-link a {
            color: oklch(0.55 0.15 45);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }

            .login-left {
                flex: 1;
                padding: 40px 20px;
                min-height: 100vh;
            }

            .login-right {
                display: none;
            }

            .login-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Side - Login Form -->
        <div class="login-left">
            <div class="login-card">
                <div class="login-header">
                    <div style="margin-bottom: 20px;">
                        <img src="assets/logo.jpg" alt="Gebeta Logo" style="width: 64px; height: 64px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    </div>
                    <h1>Welcome Back</h1>
                    <p>Sign in to access your dashboard</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="form-input-group">
                            <span class="input-icon">✉️</span>
                            <input type="email" class="form-control with-icon" id="email" name="email"
                                placeholder="Enter your email address"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="form-input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" class="form-control with-icon" id="password" name="password"
                                placeholder="Enter your password"
                                required>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login">Sign In</button>
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="register.php">Create one now</a>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero Section -->
        <div class="login-right">
            <div class="hero-content">
                <div class="hero-icon" style="background: white; padding: 10px; overflow: hidden;">
                    <img src="assets/logo.jpg" alt="Gebeta Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                </div>
                <h2 class="hero-title">Gebeta (ገበታ)</h2>
                <p class="hero-text">The premium choice for restaurant management and culinary exploration in Ethiopia.</p>

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

                <p style="font-size: 13px; color: rgba(255, 255, 255, 0.8);">
                    Join thousands of restaurants making reservations easier
                </p>
            </div>
        </div>
    </div>
</body>
</html>

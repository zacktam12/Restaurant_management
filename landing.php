<?php
/**
 * Professional Landing Page for Gebeta (ገበታ)
 * Showcases featured Restaurants and Tours dynamically.
 */

require_once 'backend/config.php';
require_once 'backend/ExternalServiceClient.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $role = $_SESSION['user']['role'];
    switch ($role) {
        case 'admin': header('Location: admin/index.php'); break;
        case 'manager': header('Location: manager/index.php'); break;
        default: header('Location: customer/index.php');
    }
    exit();
}

// Fetch Real Data
$restaurants = [];
$tours = [];

try {
    // 1. Fetch Featured Restaurants
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        // Fetch top 3 restaurants
        $sql = "SELECT * FROM restaurants LIMIT 3";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $restaurants = $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // 2. Fetch Featured Tours
    $client = new ExternalServiceClient();
    $tourData = $client->getTours();
    if (isset($tourData['success']) && $tourData['success']) {
        $tours = array_slice($tourData['data'], 0, 3);
    }
} catch (Exception $e) {
    error_log("Landing Page Data Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gebeta (ገበታ) - Discover top restaurants and book exclusive tours in Ethiopia.">
    <title>Gebeta (ገበታ) - Premium Cuisine & Experiences</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary: #d97706;
            --primary-dark: #b45309;
            --secondary: #fffbeb;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-light: #f9fafb;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.5);
            --gradient-hero: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            background-color: var(--bg-light);
            line-height: 1.6;
            margin: 0;
            overflow-x: hidden;
        }

        .landing-nav {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 800;
        }
        .brand img {
            height: 40px;
            width: 40px;
            object-fit: cover;
            border-radius: 8px;
        }
        .brand span {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-links .btn-solid { display: none !important; }
        @media (max-width: 768px) {
            .nav-links .btn-solid { display: inline-block !important; }
        }
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--primary); }
        .nav-btn {
            display: inline-block;
            padding: 0.6rem 1.25rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            margin-right: 1rem;
        }
        .btn-outline:hover { background: var(--secondary); }
        .btn-solid {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.2);
        }
        .btn-solid:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .hero {
            background: var(--gradient-hero);
            padding: 8rem 1.5rem 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            background-image: url('https://pngmagic.com/webp_images/restaurant-website-hero-background-for-landing-page_T6PO.webp');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
        }
        .hero::before {
            content: '';
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 1;
        }
        .hero > div { position: relative; z-index: 2; }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
            color: #111;
        }
        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        /* Mobile Responsive adjustments */
        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 2rem;
                gap: 1.5rem;
                border-bottom: 1px solid #eee;
                transform: translateY(-150%);
                transition: transform 0.3s ease;
                z-index: 999;
            }
            .nav-links.active {
                transform: translateY(0);
            }
            .menu-toggle {
                display: block !important;
                font-size: 1.5rem;
                cursor: pointer;
            }
            .hero { padding: 6rem 1.5rem 4rem; }
            .hero h1 { font-size: 2.25rem; }
            .hero p { font-size: 1rem; }
            .section-header h2 { font-size: 2rem; }
            .grid { grid-template-columns: 1fr; }
            .modal-btns { flex-direction: column; }
            .modal-btns .nav-btn { width: 100% !important; margin: 0; }
            .nav-actions { display: none; }
        }

        .menu-toggle { display: none; }
        .hero-image-mockup {
            max-width: 900px;
            margin: 4rem auto 0;
            border-radius: 1rem;
            box-shadow: 0 20px 50px -12px rgba(0,0,0,0.15);
            background: white;
            padding: 1rem;
            border: 1px solid rgba(255,255,255,0.5);
        }

        .section-padding { padding: 6rem 1.5rem; }
        .section-header { text-align: center; margin-bottom: 4rem; }
        .section-header h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; color: #111; }
        .section-header p { color: var(--text-muted); font-size: 1.125rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem; max-width: 1280px; margin: 0 auto; }
        
        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #f3f4f6;
        }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .card-img-wrapper { height: 220px; overflow: hidden; background: #000; position: relative; }
        .card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .card:hover .card-img { transform: scale(1.05); }
        .card-body { padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; }
        .card-tag { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--primary); margin-bottom: 0.5rem; }
        .card-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #111; }
        .card-desc { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; flex-grow: 1; }
        .card-footer { margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f3f4f6; padding-top: 1rem; }
        .card-price { font-weight: 700; font-size: 1.1rem; color: #111; }
        .card-btn { background: var(--text-main); color: white; border: none; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .card-btn:hover { background: #000; }

        .cta-section { background: #111; color: white; text-align: center; padding: 5rem 1.5rem; }
        .footer { background: var(--primary); color: white; padding: 3rem 1.5rem; text-align: center; }

        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-content { background: white; width: 90%; max-width: 450px; padding: 2.5rem; border-radius: 16px; text-align: center; transform: scale(0.95); transition: transform 0.3s; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .modal-overlay.active .modal-content { transform: scale(1); }
        .modal-icon { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .modal-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; color: #111; }
        .modal-text { color: var(--text-muted); margin-bottom: 2rem; }
        .modal-btns { display: flex; gap: 1rem; justify-content: center; }
        .close-modal { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; }
    </style>
</head>
<body>
    <nav class="landing-nav">
        <div class="nav-container">
            <a href="landing.php" class="brand">
                <img src="assets/logo.jpg" alt="Gebeta Logo">
                <span>Gebeta (ገበታ)</span>
            </a>
            <ul class="nav-links" id="navLinks">
                <li><a href="#restaurants" onclick="toggleMenu()">Restaurants</a></li>
                <li><a href="#tours" onclick="toggleMenu()">Experiences</a></li>
                <li><a href="#features" onclick="toggleMenu()">Features</a></li>
                <li><a href="login.php" class="nav-btn btn-solid" style="display: inline-block; width: auto; margin:0;">Join Gebeta</a></li>
            </ul>
            <div class="menu-toggle" onclick="toggleMenu()">☰</div>
            <div class="nav-actions">
                <a href="login.php" class="nav-btn btn-outline">Log In</a>
                <a href="register.php" class="nav-btn btn-solid">Sign Up</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h1>Savor the Flavors with Gebeta (ገበታ)</h1>
            <p>Discover top-rated restaurants, book exclusive local tours, and manage all your culinary adventures in one seamless platform.</p>
            <div style="display: flex; gap: 1rem; justify-content: center; align-items: center; flex-wrap: wrap; margin-top: 2rem;">
                <a href="#restaurants" class="nav-btn btn-solid" style="padding: 1rem 2rem; font-size: 1.1rem; display: inline-block;">Explore Now</a>
                <a href="register.php" class="nav-btn btn-outline" style="padding: 1rem 2rem; font-size: 1.1rem; border-color: rgba(0,0,0,0.1); color: #444; background: white; display: inline-block;">Get Started Free</a>
            </div>
            
            <div class="hero-image-mockup">
                <div style="background: #f3f4f6; height: 12px; border-radius: 6px; margin-bottom: 20px; width: 100%;">
                    <div style="display: flex; gap: 8px;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444;"></div>
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b;"></div>
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 20px; height: 400px;">
                    <div style="background: #f9fafb; border-radius: 8px;"></div>
                    <div style="display: grid; grid-template-rows: 1fr 1fr; gap: 20px;">
                        <div style="background: #f9fafb; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d1d5db; font-weight: 700; font-size: 2rem;">Easy Bookings</div>
                        <div style="background: #f9fafb; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d1d5db; font-weight: 700; font-size: 2rem;">Real-time Stats</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="restaurants" class="section-padding">
        <div class="section-header">
            <h2>Featured Restaurants</h2>
            <p>Hand-picked culinary destinations from our premium network.</p>
        </div>
        <div class="grid">
            <?php if (!empty($restaurants)): ?>
                <?php foreach ($restaurants as $restaurant): ?>
                    <div class="card">
                        <div class="card-img-wrapper">
                            <?php if (!empty($restaurant['image'])): ?>
                                <img src="<?php echo htmlspecialchars($restaurant['image']); ?>" alt="Restaurant" class="card-img">
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#ffedd5; font-size:3rem;">🍽️</div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <span class="card-tag"><?php echo htmlspecialchars($restaurant['cuisine'] ?? 'Cuisine'); ?></span>
                            <h3 class="card-title"><?php echo htmlspecialchars($restaurant['name'] ?? 'Restaurant'); ?></h3>
                            <p class="card-desc"><?php echo htmlspecialchars($restaurant['address'] ?? 'Ethiopia'); ?></p>
                            <div class="card-footer">
                                <div class="card-price">Available Now</div>
                                <button class="card-btn" onclick="triggerGate('<?php echo addslashes($restaurant['name'] ?? 'Restaurant'); ?>')">Book Table</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <p>No restaurants found. Start by adding some in the manager panel!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Trending Experiences (Tours) -->
    <section id="tours" class="section-padding" style="background: white;">
        <div class="section-header">
            <h2>Trending Experiences</h2>
            <p>Explore the city with our curated tours and adventures.</p>
        </div>
        <div class="grid">
            <?php if (!empty($tours)): ?>
                <?php foreach ($tours as $tour): ?>
                    <div class="card">
                        <div class="card-img-wrapper">
                            <img src="<?php echo htmlspecialchars($tour['image'] ?? 'https://images.unsplash.com/photo-1533105079780-92b9be482077?w=500&q=80'); ?>" alt="Tour" class="card-img">
                        </div>
                        <div class="card-body">
                            <span class="card-tag">Adventure</span>
                            <h3 class="card-title"><?php echo htmlspecialchars($tour['name'] ?? 'Great Experience'); ?></h3>
                            <p class="card-desc"><?php echo htmlspecialchars($tour['description'] ?? 'Explore with Gebeta (ገበታ)'); ?></p>
                            <div class="card-footer">
                                <div class="card-price">$<?php echo number_format($tour['price'] ?? 0, 2); ?></div>
                                <button class="card-btn" onclick="triggerGate('<?php echo addslashes($tour['name'] ?? 'Tour'); ?>')">Book Tour</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <p>Discover more exclusive tours after signing up!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="features" class="section-padding" style="background: #f9fafb;">
        <div class="section-header">
            <h2>Why Choose Gebeta (ገበታ)?</h2>
            <p>The all-in-one solution for hospitality and management in Ethiopia.</p>
        </div>
        <div class="grid">
            <div style="text-align:center; padding: 2rem;">
                <div style="font-size:3rem; margin-bottom:1rem;">🥘</div>
                <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Authentic Flavors</h3>
                <p style="color:var(--text-muted);">Discover the finest traditional and modern restaurants in the heart of Ethiopia.</p>
            </div>
            <div style="text-align:center; padding: 2rem;">
                <div style="font-size:3rem; margin-bottom:1rem;">🚕</div>
                <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Seamless Travel</h3>
                <p style="color:var(--text-muted);">Book taxis and curated local tours directly alongside your restaurant reservations.</p>
            </div>
            <div style="text-align:center; padding: 2rem;">
                <div style="font-size:3rem; margin-bottom:1rem;">🇪🇹</div>
                <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Local Empowerment</h3>
                <p style="color:var(--text-muted);">Professional management tools designed to help Ethiopian hospitality businesses thrive.</p>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; 2025 Gebeta (ገበታ). All rights reserved.</p>
    </footer>

    <div class="modal-overlay" id="gateModal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeGate()">×</button>
            <span class="modal-icon">🔒</span>
            <h3 class="modal-title">Unlock This Experience</h3>
            <p class="modal-text">Sign up or log in to book <strong id="gateTargetName">this item</strong> and access exclusive Gebeta member pricing!</p>
            <div class="modal-btns">
                <a href="login.php" class="nav-btn btn-outline" style="width: 120px;">Log In</a>
                <a href="register.php" class="nav-btn btn-solid" style="width: 120px;">Sign Up</a>
            </div>
        </div>
    </div>

    <script>
        function triggerGate(itemName) {
            document.getElementById('gateTargetName').textContent = itemName;
            document.getElementById('gateModal').classList.add('active');
        }
        function closeGate() {
            document.getElementById('gateModal').classList.remove('active');
        }
        function toggleMenu() {
            if (window.innerWidth <= 768) {
                document.getElementById('navLinks').classList.toggle('active');
            }
        }
        document.getElementById('gateModal').addEventListener('click', function(e) {
            if (e.target === this) closeGate();
        });
    </script>
</body>
</html>

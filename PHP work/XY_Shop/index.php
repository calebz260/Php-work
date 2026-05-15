<?php
// ============================================================
// index.php — Home / Landing Page
// ============================================================
session_start();
if (isset($_SESSION['shopkeeper_id'])) {
    header('Location: dashboard.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XY Shop — Stock Management System</title>
    <meta name="description" content="XY Shop Stock Management System — manage your shoes and clothes inventory in Kigali with ease.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ---- NAV ---- */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 64px;
            background: rgba(15,23,42,0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(99,102,241,0.15);
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .nav-logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .nav-logo-text { font-size: 1.2rem; font-weight: 800; color: #f1f5f9; }
        .nav-logo-sub  { font-size: 0.7rem; color: #64748b; display: block; }

        .nav-links { display: flex; align-items: center; gap: 12px; }
        .btn-nav-outline {
            padding: 9px 22px;
            border: 1px solid rgba(99,102,241,0.4);
            border-radius: 8px;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            background: transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-nav-outline:hover { border-color: #6366f1; color: #818cf8; background: rgba(99,102,241,0.08); }

        .btn-nav-solid {
            padding: 9px 22px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(99,102,241,0.4);
        }
        .btn-nav-solid:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.55); }

        /* ---- HERO ---- */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 32px 80px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse at 30% 30%, rgba(99,102,241,0.25) 0%, transparent 60%),
                radial-gradient(ellipse at 70% 70%, rgba(16,185,129,0.15) 0%, transparent 60%),
                #0f172a;
        }
        /* floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            animation: floatOrb 8s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; background: rgba(99,102,241,0.15); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(16,185,129,0.1);  bottom: -80px; right: -60px; animation-delay: -4s; }
        .orb-3 { width: 200px; height: 200px; background: rgba(245,158,11,0.08); top: 50%; left: 60%; animation-delay: -2s; }

        .hero-content { position: relative; z-index: 2; max-width: 800px; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.3);
            border-radius: 50px;
            padding: 7px 18px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #818cf8;
            margin-bottom: 28px;
            animation: fadeSlideUp 0.6s ease both;
        }

        .hero-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            display: block;
            animation: fadeSlideUp 0.6s 0.05s ease both, float 4s ease-in-out 0.6s infinite;
        }

        .hero-title {
            font-size: clamp(2.4rem, 6vw, 4rem);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 20px;
            animation: fadeSlideUp 0.6s 0.1s ease both;
        }
        .hero-title .grad {
            background: linear-gradient(135deg, #6366f1 0%, #818cf8 40%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-sub {
            font-size: 1.1rem;
            color: #94a3b8;
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto 40px;
            animation: fadeSlideUp 0.6s 0.15s ease both;
        }

        .hero-btns {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeSlideUp 0.6s 0.2s ease both;
        }
        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 6px 24px rgba(99,102,241,0.45);
        }
        .btn-hero-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 32px rgba(99,102,241,0.6); }

        .btn-hero-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            background: transparent;
            border: 1px solid rgba(99,102,241,0.35);
            border-radius: 12px;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .btn-hero-outline:hover { border-color: #6366f1; color: #818cf8; background: rgba(99,102,241,0.08); transform: translateY(-3px); }

        /* ---- FEATURES SECTION ---- */
        .features-section {
            padding: 100px 64px;
            background: #0f172a;
        }
        .section-label {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6366f1;
            margin-bottom: 14px;
        }
        .section-title {
            text-align: center;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            margin-bottom: 60px;
            letter-spacing: -0.02em;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .feat-card {
            background: rgba(30,41,59,0.7);
            border: 1px solid rgba(99,102,241,0.15);
            border-radius: 16px;
            padding: 32px 28px;
            transition: all 0.25s ease;
        }
        .feat-card:hover {
            border-color: rgba(99,102,241,0.4);
            transform: translateY(-4px);
            background: rgba(30,41,59,1);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .feat-icon {
            font-size: 2.2rem;
            margin-bottom: 16px;
            display: block;
        }
        .feat-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
        .feat-card p  { font-size: 0.85rem; color: #64748b; line-height: 1.6; }

        /* ---- CTA SECTION ---- */
        .cta-section {
            padding: 100px 64px;
            text-align: center;
            background:
                radial-gradient(ellipse at center, rgba(99,102,241,0.18) 0%, transparent 70%),
                #0f172a;
        }
        .cta-section h2 {
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }
        .cta-section p { color: #64748b; margin-bottom: 36px; font-size: 1rem; }

        /* ---- FOOTER ---- */
        footer {
            border-top: 1px solid rgba(99,102,241,0.1);
            padding: 28px 64px;
            text-align: center;
            font-size: 0.8rem;
            color: #334155;
        }

        /* ---- Animations ---- */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }
        @keyframes floatOrb {
            0%,100% { transform: translateY(0) scale(1); }
            50%      { transform: translateY(-20px) scale(1.05); }
        }

        /* ---- Responsive ---- */
        @media (max-width: 768px) {
            nav { padding: 16px 24px; }
            .features-section, .cta-section { padding: 60px 24px; }
            footer { padding: 24px; }
        }
    </style>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- ====== NAVBAR ====== -->
    <nav>
        <a href="index.php" class="nav-brand">
            <div class="nav-logo-icon"><i class='bx bx-shopping-bag'></i></div>
            <div>
                <span class="nav-logo-text">XY Shop</span>
                <span class="nav-logo-sub">Stock Management</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="register.php" class="btn-nav-outline" id="navRegisterBtn">Register</a>
            <a href="login.php"    class="btn-nav-solid"   id="navLoginBtn"><i class='bx bx-lock-alt'></i> Login</a>
        </div>
    </nav>

    <!-- ====== HERO ====== -->
    <section class="hero">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="hero-content">
            <div class="hero-badge"><i class='bx bx-store'></i> Kicukiro District, Kigali — Rwanda</div>
            <span class="hero-icon"><i class='bx bx-shopping-bag'></i></span>
            <h1 class="hero-title">
                Smart Stock Management<br>for <span class="grad">Shoes & Clothes</span>
            </h1>
            <p class="hero-sub">
                Say goodbye to manual record books. Track every stock-in and stock-out,
                manage your products, and generate instant reports — all in one place.
            </p>
            <div class="hero-btns">
                <a href="login.php"    class="btn-hero-primary" id="heroLoginBtn"><i class='bx bx-lock-alt'></i> Login to Dashboard</a>
                <a href="register.php" class="btn-hero-outline" id="heroRegisterBtn"><i class='bx bx-sparkles'></i> Create Account</a>
            </div>
        </div>
    </section>

    <!-- ====== FEATURES ====== -->
    <section class="features-section">
        <p class="section-label">What You Can Do</p>
        <h2 class="section-title">Everything You Need to Run Your Shop</h2>
        <div class="features-grid">
            <div class="feat-card">
                <span class="feat-icon"><i class='bx bx-lock-alt'></i></span>
                <h3>Secure Login</h3>
                <p>Password-protected access ensures only authorised shopkeepers can manage your stock.</p>
            </div>
            <div class="feat-card">
                <span class="feat-icon"><i class='bx bx-box'></i></span>
                <h3>Product Management</h3>
                <p>Add, update, and remove shoes and clothes products easily with a clean interface.</p>
            </div>
            <div class="feat-card">
                <span class="feat-icon"><i class='bx bx-down-arrow-circle'></i></span>
                <h3>Stock In</h3>
                <p>Record every incoming shipment with quantity, unit price and auto-computed totals.</p>
            </div>
            <div class="feat-card">
                <span class="feat-icon"><i class='bx bx-up-arrow-circle'></i></span>
                <h3>Stock Out</h3>
                <p>Record all sales with a built-in stock availability check to prevent overselling.</p>
            </div>
            <div class="feat-card">
                <span class="feat-icon"><i class='bx bx-line-chart'></i></span>
                <h3>Reports</h3>
                <p>Generate daily, weekly, or custom-range reports showing stock status and total values.</p>
            </div>
            <div class="feat-card">
                <span class="feat-icon"><i class='bx bx-bar-chart-alt-2'></i></span>
                <h3>Dashboard</h3>
                <p>Get a live overview of your entire stock with real-time stats and recent transactions.</p>
            </div>
        </div>
    </section>



    <!-- ====== FOOTER ====== -->
    <footer>
        <strong style="color:#475569;">XY Shop</strong> &mdash;
        Shoes &amp; Clothes Stock Management System &bull;
        Kicukiro District, Kigali &bull;
        &copy; <?= date('Y') ?>
    </footer>

</body>
</html>


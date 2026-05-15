<?php
// ============================================================
// login.php — Secure Login with Rate Limiting + CSRF
// ============================================================
require 'security.php';   // Sets headers + starts hardened session
require 'csrf.php';        // CSRF helpers
require 'db.php';
require 'logger.php';

// Already logged in — go to dashboard
if (isset($_SESSION['shopkeeper_id'])) {
    header('Location: dashboard.php'); exit;
}

$error = '';

// ---- Rate Limiting (max 5 attempts per 15 minutes per IP) ----
$ip          = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$maxAttempts = 5;
$lockWindow  = 15 * 60; // 15 minutes in seconds

function isRateLimited(PDO $pdo, string $ip, int $maxAttempts, int $lockWindow): bool {
    // Clean up old attempts first
    $pdo->prepare('DELETE FROM login_attempts WHERE AttemptTime < ?')
        ->execute([date('Y-m-d H:i:s', time() - $lockWindow)]);

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE IpAddress = ? AND AttemptTime > ?'
    );
    $stmt->execute([$ip, date('Y-m-d H:i:s', time() - $lockWindow)]);
    return (int)$stmt->fetchColumn() >= $maxAttempts;
}

function recordAttempt(PDO $pdo, string $ip): void {
    $pdo->prepare('INSERT INTO login_attempts (IpAddress, AttemptTime) VALUES (?, NOW())')
        ->execute([$ip]);
}

// ---- Handle POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. CSRF check — reject if token is invalid
    csrf_verify();

    // 2. Rate limit check
    if (isRateLimited($pdo, $ip, $maxAttempts, $lockWindow)) {
        $error = 'Too many login attempts. Please wait 15 minutes and try again.';
        audit_log($pdo, 'LOGIN_BLOCKED', "IP {$ip} blocked due to rate limiting.");
    } else {
        $username = trim($_POST['UserName'] ?? '');
        $password = trim($_POST['Password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Please fill in all fields.';
        } else {
            // 3. Parameterized query — no SQL injection possible
            $stmt = $pdo->prepare('SELECT * FROM Shopkeeper WHERE UserName = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['Password'])) {
                // 4. Successful login
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['shopkeeper_id']   = $user['ShopkeeperId'];
                $_SESSION['shopkeeper_name'] = $user['UserName'];
                $_SESSION['last_activity']   = time();

                // Store session fingerprint
                $_SESSION['fingerprint'] = hash('sha256',
                    ($_SERVER['HTTP_USER_AGENT']      ?? '') .
                    ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
                );

                // Audit log: successful login
                audit_log($pdo, 'LOGIN_SUCCESS',
                    "User '{$username}' logged in successfully.",
                    $user['ShopkeeperId']
                );

                header('Location: dashboard.php');
                exit;

            } else {
                // 5. Failed login — record attempt
                recordAttempt($pdo, $ip);
                $error = 'Invalid username or password.';

                // Audit log: failed login (do not log the password)
                audit_log($pdo, 'LOGIN_FAILED',
                    "Failed login attempt for username '{$username}' from IP {$ip}."
                );
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — XY Shop</title>
    <meta name="description" content="Login to XY Shop Stock Management System.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
        }
        .home-wrap {
            display: grid;
            grid-template-columns: 1fr 480px;
            min-height: 100vh;
        }
        .home-left {
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99,102,241,0.35) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(16,185,129,0.2)  0%, transparent 55%),
                #0f172a;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 64px;
            position: relative;
            overflow: hidden;
        }
        .home-left::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            border: 1px solid rgba(99,102,241,0.12);
            border-radius: 50%;
            top: -200px; left: -200px;
            pointer-events: none;
        }
        .home-left::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border: 1px solid rgba(99,102,241,0.08);
            border-radius: 50%;
            bottom: -150px; right: -100px;
            pointer-events: none;
        }
        .home-logo {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
            animation: fadeSlideUp 0.5s 0.1s ease both;
        }
        .logo-box {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 0 40px rgba(99,102,241,0.5);
            flex-shrink: 0;
            animation: float 4s ease-in-out infinite;
            color: #fff;
        }
        .logo-name   { font-size: 2.4rem; font-weight: 900; letter-spacing: -0.02em; }
        .logo-sub    { font-size: 0.85rem; color: #64748b; font-weight: 400; margin-top: 2px; }
        .home-headline {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 20px;
            animation: fadeSlideUp 0.5s 0.15s ease both;
        }
        .home-headline span {
            background: linear-gradient(135deg, #6366f1, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .home-desc {
            font-size: 1rem;
            color: #94a3b8;
            line-height: 1.7;
            max-width: 480px;
            margin-bottom: 40px;
            animation: fadeSlideUp 0.5s 0.2s ease both;
        }
        .home-right {
            background: #1e293b;
            border-left: 1px solid rgba(99,102,241,0.2);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 48px;
            position: relative;
        }
        .login-header { margin-bottom: 32px; animation: fadeSlideUp 0.5s 0.1s ease both; }
        .login-header h1 { font-size: 1.7rem; font-weight: 800; }
        .login-header p  { color: #64748b; font-size: 0.9rem; margin-top: 6px; }
        .form-group { margin-bottom: 20px; animation: fadeSlideUp 0.5s 0.2s ease both; }
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #475569;
            pointer-events: none;
        }
        .form-group input {
            width: 100%;
            background: rgba(15,23,42,0.8);
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: 10px;
            padding: 12px 44px;
            color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.25s ease;
        }
        .form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
            background: rgba(15,23,42,1);
        }
        .form-group input::placeholder { color: #475569; }
        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: #64748b;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: #6366f1; }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: fadeSlideUp 0.3s ease;
        }
        .alert-danger  { background: rgba(239,68,68,0.1);  border: 1px solid #ef4444; color: #f87171; }
        .alert-warning { background: rgba(245,158,11,0.1); border: 1px solid #f59e0b; color: #fbbf24; }
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 20px rgba(99,102,241,0.4);
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            animation: fadeSlideUp 0.5s 0.25s ease both;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(99,102,241,0.55);
        }
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            animation: fadeSlideUp 0.5s 0.3s ease both;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: rgba(99,102,241,0.15); }
        .divider span { font-size: 0.75rem; color: #475569; white-space: nowrap; }
        .btn-register {
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 1px solid rgba(99,102,241,0.3);
            border-radius: 10px;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            animation: fadeSlideUp 0.5s 0.35s ease both;
        }
        .btn-register:hover { border-color: #6366f1; color: #818cf8; background: rgba(99,102,241,0.08); }
        .back-link {
            position: absolute;
            top: 24px;
            left: 32px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
            z-index: 10;
        }
        .back-link:hover { color: #f1f5f9; }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        @media (max-width: 900px) {
            .home-wrap { grid-template-columns: 1fr; }
            .home-left  { padding: 80px 32px 48px; }
            .home-right { padding: 40px 32px; border-left: none; border-top: 1px solid rgba(99,102,241,0.2); }
        }
        @media (max-width: 480px) {
            .home-left  { padding: 80px 20px 36px; }
            .home-right { padding: 32px 20px; }
        }
    </style>
</head>
<body>
<div class="home-wrap">

    <!-- LEFT -->
    <div class="home-left">
        <a href="index.php" class="back-link">
            <i class='bx bx-arrow-back'></i> Back to Home
        </a>
        <div class="home-logo">
            <div class="logo-box"><i class='bx bx-store-alt'></i></div>
            <div>
                <div class="logo-name">XY Shop</div>
                <div class="logo-sub">Stock Management System</div>
            </div>
        </div>
        <h2 class="home-headline">
            Welcome to the<br><span>Shopkeeper Portal</span>
        </h2>
        <p class="home-desc">
            Securely access your dashboard to manage products, record stock transactions,
            and view detailed inventory reports. Your session is protected and encrypted.
        </p>
    </div>

    <!-- RIGHT — Login Form -->
    <div class="home-right">
        <div class="login-header">
            <h1>Sign In</h1>
            <p>Access your stock dashboard</p>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'session_expired'): ?>
                <div class="alert alert-warning">
                    <i class='bx bx-time'></i> Your session expired. Please sign in again.
                </div>
            <?php elseif ($_GET['msg'] === 'security_error'): ?>
                <div class="alert alert-danger">
                    <i class='bx bx-shield-x'></i> Security check failed. Please sign in again.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class='bx bx-error-circle'></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm" novalidate>
            <!-- CSRF Token — prevents cross-site request forgery -->
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="UserName">Username</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class='bx bx-user'></i></span>
                    <input type="text"
                           id="UserName"
                           name="UserName"
                           placeholder="Enter your username"
                           value="<?= htmlspecialchars($_POST['UserName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           autocomplete="username"
                           maxlength="100"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="Password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class='bx bx-key'></i></span>
                    <input type="password"
                           id="Password"
                           name="Password"
                           placeholder="Enter your password"
                           autocomplete="current-password"
                           maxlength="255"
                           required>
                    <button type="button" class="toggle-pw" data-target="Password" title="Show/hide password">
                        <i class='bx bx-show' id="pwEyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class='bx bx-lock-open-alt'></i> Sign In
            </button>
        </form>

        <div class="divider"><span>Don't have an account?</span></div>

        <a href="register.php" class="btn-register" id="goRegisterBtn">
            <i class='bx bx-user-plus'></i> Register New Account
        </a>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-pw').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var target = document.getElementById(btn.dataset.target);
        var icon   = btn.querySelector('i');
        if (!target) return;
        if (target.type === 'password') {
            target.type = 'text';
            icon.className = 'bx bx-hide';
        } else {
            target.type = 'password';
            icon.className = 'bx bx-show';
        }
    });
});

// Auto-dismiss alerts after 5 seconds
document.querySelectorAll('.alert').forEach(function(a) {
    setTimeout(function() {
        a.style.transition = 'all 0.4s ease';
        a.style.opacity = '0';
        setTimeout(function() { a.remove(); }, 400);
    }, 5000);
});
</script>
</body>
</html>

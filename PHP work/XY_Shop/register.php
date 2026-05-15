<?php
// ============================================================
// register.php — Secure Registration with CSRF
// ============================================================
require 'security.php';
require 'csrf.php';
require 'db.php';
require 'logger.php';

// Already logged in
if (isset($_SESSION['shopkeeper_id'])) {
    header('Location: dashboard.php'); exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF check
    csrf_verify();

    $username = trim($_POST['UserName']        ?? '');
    $password = trim($_POST['Password']        ?? '');
    $confirm  = trim($_POST['ConfirmPassword'] ?? '');

    // 2. Strict server-side validation
    if ($username === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3 || strlen($username) > 100) {
        $error = 'Username must be between 3 and 100 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username may only contain letters, numbers, and underscores.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // 3. Check uniqueness
        $stmt = $pdo->prepare('SELECT ShopkeeperId FROM Shopkeeper WHERE UserName = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already exists. Please choose another.';
        } else {
            // 4. Hash password with Argon2id (most secure, fallback to bcrypt)
            $algo   = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $hashed = password_hash($password, $algo);

            $pdo->prepare('INSERT INTO Shopkeeper (UserName, Password) VALUES (?, ?)')
                ->execute([$username, $hashed]);

            // 5. Audit log
            audit_log($pdo, 'REGISTER',
                "New shopkeeper account registered: '{$username}'."
            );

            $success = 'Account created successfully! You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — XY Shop</title>
    <meta name="description" content="Register an account for XY Shop Stock Management System.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #f1f5f9; min-height: 100vh; }
        .home-wrap { display: grid; grid-template-columns: 1fr 480px; min-height: 100vh; }
        .home-left {
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99,102,241,0.35) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(16,185,129,0.2) 0%, transparent 55%),
                #0f172a;
            display: flex; flex-direction: column; justify-content: center;
            padding: 60px 64px; position: relative; overflow: hidden;
        }
        .home-left::before { content:''; position:absolute; width:600px; height:600px; border:1px solid rgba(99,102,241,0.12); border-radius:50%; top:-200px; left:-200px; pointer-events:none; }
        .home-left::after  { content:''; position:absolute; width:400px; height:400px; border:1px solid rgba(99,102,241,0.08); border-radius:50%; bottom:-150px; right:-100px; pointer-events:none; }
        .home-logo { display:flex; align-items:center; gap:16px; margin-bottom:28px; animation:fadeSlideUp 0.5s 0.1s ease both; }
        .logo-box { width:64px; height:64px; background:linear-gradient(135deg,#6366f1,#818cf8); border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:30px; box-shadow:0 0 40px rgba(99,102,241,0.5); flex-shrink:0; color:#fff; animation:float 4s ease-in-out infinite; }
        .logo-name { font-size:2.4rem; font-weight:900; letter-spacing:-0.02em; }
        .logo-sub  { font-size:0.85rem; color:#64748b; font-weight:400; margin-top:2px; }
        .home-headline { font-size:clamp(1.8rem,3vw,2.6rem); font-weight:800; line-height:1.15; letter-spacing:-0.02em; margin-bottom:20px; animation:fadeSlideUp 0.5s 0.15s ease both; }
        .home-headline span { background:linear-gradient(135deg,#6366f1,#34d399); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .home-desc { font-size:1rem; color:#94a3b8; line-height:1.7; max-width:480px; margin-bottom:20px; animation:fadeSlideUp 0.5s 0.2s ease both; }
        .home-right { background:#1e293b; border-left:1px solid rgba(99,102,241,0.2); display:flex; flex-direction:column; justify-content:center; padding:48px; position:relative; }
        .login-header { margin-bottom:24px; animation:fadeSlideUp 0.5s 0.1s ease both; }
        .login-header h1 { font-size:1.7rem; font-weight:800; }
        .login-header p  { color:#64748b; font-size:0.9rem; margin-top:6px; }
        .form-group { margin-bottom:16px; animation:fadeSlideUp 0.5s 0.2s ease both; }
        .form-group label { display:block; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.07em; color:#94a3b8; margin-bottom:8px; }
        .input-wrap { position:relative; }
        .input-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:1rem; color:#475569; pointer-events:none; }
        .form-group input { width:100%; background:rgba(15,23,42,0.8); border:1px solid rgba(99,102,241,0.2); border-radius:10px; padding:12px 44px; color:#f1f5f9; font-family:'Inter',sans-serif; font-size:0.95rem; outline:none; transition:all 0.25s ease; }
        .form-group input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,0.2); background:rgba(15,23,42,1); }
        .form-group input::placeholder { color:#475569; }
        .toggle-pw { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:1rem; color:#64748b; transition:color 0.2s; }
        .toggle-pw:hover { color:#6366f1; }
        .alert { padding:12px 16px; border-radius:10px; font-size:0.85rem; margin-bottom:16px; display:flex; align-items:center; gap:8px; animation:fadeSlideUp 0.3s ease; }
        .alert-danger  { background:rgba(239,68,68,0.1);  border:1px solid #ef4444; color:#f87171; }
        .alert-success { background:rgba(16,185,129,0.1); border:1px solid #10b981; color:#34d399; }
        .btn-login { width:100%; padding:13px; background:linear-gradient(135deg,#6366f1,#818cf8); border:none; border-radius:10px; color:#fff; font-family:'Inter',sans-serif; font-size:1rem; font-weight:700; cursor:pointer; transition:all 0.25s ease; box-shadow:0 4px 20px rgba(99,102,241,0.4); margin-top:4px; display:flex; align-items:center; justify-content:center; gap:8px; animation:fadeSlideUp 0.5s 0.25s ease both; }
        .btn-login:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(99,102,241,0.55); }
        .divider { display:flex; align-items:center; gap:12px; margin:20px 0; animation:fadeSlideUp 0.5s 0.3s ease both; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:rgba(99,102,241,0.15); }
        .divider span { font-size:0.75rem; color:#475569; white-space:nowrap; }
        .btn-register { width:100%; padding:12px; background:transparent; border:1px solid rgba(99,102,241,0.3); border-radius:10px; color:#94a3b8; font-family:'Inter',sans-serif; font-size:0.9rem; font-weight:600; cursor:pointer; transition:all 0.25s ease; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px; animation:fadeSlideUp 0.5s 0.35s ease both; }
        .btn-register:hover { border-color:#6366f1; color:#818cf8; background:rgba(99,102,241,0.08); }
        .back-link { position:absolute; top:24px; left:32px; display:inline-flex; align-items:center; gap:8px; color:#94a3b8; font-size:0.85rem; font-weight:600; text-decoration:none; transition:color 0.2s; z-index:10; }
        .back-link:hover { color:#f1f5f9; }
        @keyframes fadeSlideUp { from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)} }
        @keyframes float { 0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)} }
        @media(max-width:900px){ .home-wrap{grid-template-columns:1fr} .home-left{padding:80px 32px 48px} .home-right{padding:40px 32px;border-left:none;border-top:1px solid rgba(99,102,241,0.2)} }
        @media(max-width:480px){ .home-left{padding:80px 20px 36px} .home-right{padding:32px 20px} }
    </style>
</head>
<body>
<div class="home-wrap">
    <div class="home-left">
        <a href="index.php" class="back-link"><i class='bx bx-arrow-back'></i> Back to Home</a>
        <div class="home-logo">
            <div class="logo-box"><i class='bx bx-store-alt'></i></div>
            <div><div class="logo-name">XY Shop</div><div class="logo-sub">Stock Management System</div></div>
        </div>
        <h2 class="home-headline">Create Your<br><span>Shopkeeper Account</span></h2>
        <p class="home-desc">Register to get full access to the inventory dashboard, stock tracking, and comprehensive reports.</p>
    </div>

    <div class="home-right">
        <div class="login-header">
            <h1>Register</h1>
            <p>Setup your new account</p>
        </div>

        <?php if ($error):   ?><div class="alert alert-danger"><i class='bx bx-error-circle'></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><i class='bx bx-check-circle'></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="registerForm" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="UserName">Username</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class='bx bx-user'></i></span>
                    <input type="text" id="UserName" name="UserName"
                           placeholder="Letters, numbers, underscore (min 3)"
                           value="<?= htmlspecialchars($_POST['UserName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           autocomplete="username" maxlength="100" required>
                </div>
            </div>

            <div class="form-group">
                <label for="Password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class='bx bx-key'></i></span>
                    <input type="password" id="Password" name="Password"
                           placeholder="Minimum 8 characters"
                           autocomplete="new-password" maxlength="255" required>
                    <button type="button" class="toggle-pw" data-target="Password"><i class='bx bx-show'></i></button>
                </div>
            </div>

            <div class="form-group">
                <label for="ConfirmPassword">Confirm Password</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class='bx bx-key'></i></span>
                    <input type="password" id="ConfirmPassword" name="ConfirmPassword"
                           placeholder="Repeat your password"
                           autocomplete="new-password" maxlength="255" required>
                    <button type="button" class="toggle-pw" data-target="ConfirmPassword"><i class='bx bx-show'></i></button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="registerBtn">
                <i class='bx bx-user-plus'></i> Create Account
            </button>
        </form>
        <?php endif; ?>

        <div class="divider"><span>Already have an account?</span></div>
        <a href="login.php" class="btn-register" id="goLoginBtn">
            <i class='bx bx-lock-open-alt'></i> Sign In
        </a>
    </div>
</div>
<script>
document.querySelectorAll('.toggle-pw').forEach(function(btn){
    btn.addEventListener('click',function(){
        var t=document.getElementById(btn.dataset.target), i=btn.querySelector('i');
        if(!t)return;
        if(t.type==='password'){t.type='text';i.className='bx bx-hide';}
        else{t.type='password';i.className='bx bx-show';}
    });
});
document.querySelectorAll('.alert').forEach(function(a){
    setTimeout(function(){a.style.transition='all 0.4s ease';a.style.opacity='0';setTimeout(function(){a.remove();},400);},5000);
});
</script>
</body>
</html>

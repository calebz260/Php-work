<?php
// ============================================================
// auth.php — Central Authentication + Session Security Guard
// Include at top of every protected page (after security.php).
// ============================================================

// security.php already started the session with hardened settings.
// If it wasn't included, start the session safely here.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ---- Session Timeout (30 minutes of inactivity) ----
$timeout = 30 * 60; // 30 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > $timeout) {
        // Session expired — destroy everything and redirect home
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: index.php?msg=session_expired');
        exit;
    }
}
// Refresh last activity timestamp on every request
$_SESSION['last_activity'] = time();

// ---- Authentication Check ----
// Same browser tab: session cookie is shared automatically — page loads fine.
// Different browser / incognito: no cookie = no session = redirect to home.
if (!isset($_SESSION['shopkeeper_id'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// ---- Session Fingerprint (Detect Hijacking) ----
// Bind the session to the user's browser fingerprint.
$fingerprint = hash('sha256',
    ($_SERVER['HTTP_USER_AGENT'] ?? '') .
    ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
);

if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = $fingerprint;
} elseif (!hash_equals($_SESSION['fingerprint'], $fingerprint)) {
    // Fingerprint mismatch — possible session hijack attempt
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header('Location: index.php?msg=security_error');
    exit;
}

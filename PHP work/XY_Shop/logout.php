<?php
// ============================================================
// logout.php — Secure Session Destruction
// ============================================================
require 'security.php';
require 'db.php';
require 'logger.php';

// Log the logout before destroying the session
$uid  = $_SESSION['shopkeeper_id']   ?? null;
$name = $_SESSION['shopkeeper_name'] ?? 'Unknown';
audit_log($pdo, 'LOGOUT', "User '{$name}' logged out.", $uid);

// Destroy all session data
$_SESSION = [];

// Expire the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Redirect to home (not login directly)
header('Location: index.php');
exit;

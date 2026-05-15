<?php
// ============================================================
// security.php — Security HTTP Headers
// Include at the very top of EVERY page (before any output).
// ============================================================

// --- Prevent session fixation ---
if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookie before starting
    session_set_cookie_params([
        'lifetime' => 0,           // Session cookie (expires when browser closes)
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']), // HTTPS only when available
        'httponly' => true,        // JS cannot access the cookie
        'samesite' => 'Strict',    // No cross-site sending
    ]);
    session_start();
}

// --- Security HTTP Response Headers ---

// Prevent clickjacking
header('X-Frame-Options: DENY');

// Prevent MIME-type sniffing
header('X-Content-Type-Options: nosniff');

// Basic XSS protection for older browsers
header('X-XSS-Protection: 1; mode=block');

// Control referrer information sent to other sites
header('Referrer-Policy: strict-origin-when-cross-origin');

// Restrict powerful browser features
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Content Security Policy — restricts where resources can load from
// Allows: self, Google Fonts, Boxicons CDN, unpkg CDN
header("Content-Security-Policy: "
    . "default-src 'self'; "
    . "script-src 'self' 'unsafe-inline'; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdnjs.cloudflare.com; "
    . "font-src 'self' https://fonts.gstatic.com https://unpkg.com; "
    . "img-src 'self' data:; "
    . "connect-src 'self'; "
    . "frame-ancestors 'none';"
);

// Cache control — prevent caching of authenticated pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// HSTS (uncomment when site runs on HTTPS)
// header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

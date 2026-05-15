<?php
// ============================================================
// csrf.php — CSRF Token Helper
// Generates and validates tokens for all state-changing forms.
// ============================================================

/**
 * Generate a CSRF token and store it in the session.
 * Returns the token string.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field.
 * Use inside every HTML <form> that changes data.
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate the CSRF token submitted with a POST request.
 * Call this at the top of every POST handler.
 * Kills the request with 403 if the token is missing or invalid.
 */
function csrf_verify(): void {
    $submitted = $_POST['_csrf_token'] ?? '';
    $stored    = $_SESSION['csrf_token'] ?? '';

    if (
        empty($submitted) ||
        empty($stored) ||
        !hash_equals($stored, $submitted)
    ) {
        http_response_code(403);
        die('<h1>403 — Invalid or missing CSRF token.</h1><p><a href="index.php">Go Home</a></p>');
    }
}

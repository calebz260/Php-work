<?php
// ============================================================
// logger.php — Audit Log Service
// Records every significant action: who, what, when, from where.
// ============================================================

/**
 * Write an audit log entry to the database.
 *
 * @param PDO    $pdo     Database connection
 * @param string $action  Short action name e.g. "LOGIN", "ADD_PRODUCT"
 * @param string $details Human-readable description of what happened
 * @param int|null $userId The shopkeeper ID (null for unauthenticated events)
 */
function audit_log(PDO $pdo, string $action, string $details, ?int $userId = null): void {
    try {
        $ip        = $_SERVER['REMOTE_ADDR']     ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Truncate details to 1000 chars to prevent bloat
        $details = mb_substr($details, 0, 1000);

        $stmt = $pdo->prepare(
            'INSERT INTO audit_log (UserId, Action, Details, IpAddress, UserAgent, CreatedAt)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$userId, $action, $details, $ip, $userAgent]);
    } catch (Throwable $e) {
        // Silently fail — never let logging crash the application
        error_log('[AUDIT LOG ERROR] ' . $e->getMessage());
    }
}

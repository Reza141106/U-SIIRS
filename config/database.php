<?php
/**
 * config/database.php
 * Central configuration: PDO connection, BASE_URL, session, helpers, and constants.
 * All pages require_once this file first.
 */

// ── Database credentials (edit for your XAMPP setup) ──────────────────────────
$DB_HOST = 'localhost';
$DB_NAME = 'u_siirs';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Show a user-friendly error instead of raw credentials
    die('<p style="font-family:sans-serif;color:#c62828;padding:2rem;">Database connection failed. Please check your XAMPP MySQL service and credentials in config/database.php.</p>');
}

// ── Base URL (works for XAMPP subfolders and root installs) ───────────────────
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);
    // If inside admin/, go up one level
    if (basename($script) === 'admin') $script = dirname($script);
    $script = rtrim(str_replace('\\', '/', $script), '/');
    define('BASE_URL', $scheme . '://' . $host . $script);
}

// ── Session ───────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');

// ── Application constants — use these instead of magic strings ────────────────
// Prevents typos and makes status validation consistent across the entire codebase.
define('REPORT_STATUSES',    ['Pending', 'In Progress', 'Resolved', 'Closed', 'Rejected']);
define('REPORT_PRIORITIES',  ['Low', 'Medium', 'High', 'Critical']);
define('REPORT_CATEGORIES',  [
    'Electrical',
    'Plumbing',
    'Structural',
    'Cleanliness',
    'Safety Hazard',
    'IT / Network',
    'Landscaping',
    'Other',
]);

// ── Helper functions ──────────────────────────────────────────────────────────

/**
 * Escape output safely.
 */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a path relative to BASE_URL.
 */
function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Flash message getter/setter.
 * Set:  flash('error', 'Something went wrong.')
 * Get:  flash('error')  — returns the message and clears it
 */
function flash(string $key, ?string $msg = null): ?string {
    if ($msg === null) {
        $v = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $v;
    }
    $_SESSION['flash'][$key] = $msg;
    return null;
}

/**
 * Get (or generate) the CSRF token for the current session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Validate the CSRF token from POST.
 * Terminates with 400 if token is missing or doesn't match.
 */
function csrf_check(): void {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400);
        die('Invalid CSRF token. Please go back and try again.');
    }
}

/**
 * Friendly relative time (e.g. "2 hours ago", "3 days ago").
 */
function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return floor($diff / 60) . ' min ago';
    if ($diff < 86400)   return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800)  return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
    return date('M j, Y', strtotime($datetime));
}

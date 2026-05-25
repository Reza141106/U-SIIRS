<?php
// Database configuration — edit credentials for your XAMPP setup
$DB_HOST = 'localhost';
$DB_NAME = 'u_siirs';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// Base URL helper — works under /U-SIIRS/ on XAMPP
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);
    // if inside admin/, go up one level
    if (basename($script) === 'admin') $script = dirname($script);
    $script = rtrim(str_replace('\\','/',$script), '/');
    define('BASE_URL', $scheme.'://'.$host.$script);
}

if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Kuala_Lumpur'); 

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($p){ header('Location: '.BASE_URL.'/'.ltrim($p,'/')); exit; }
function flash($key, $msg=null){
    if ($msg === null) { $v = $_SESSION['flash'][$key] ?? null; unset($_SESSION['flash'][$key]); return $v; }
    $_SESSION['flash'][$key] = $msg;
}
function csrf_token(){
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(){
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400); die('Invalid CSRF token');
    }
}
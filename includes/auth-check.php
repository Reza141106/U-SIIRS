<?php
/**
 * includes/auth-check.php
 * BUG FIX: Added `created_at` to the SELECT column list.
 * Previously missing, which caused profile.php to render "Member Since: Jan 1970".
 */
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['user_id'])) {
    flash('error', 'Please log in to continue.');
    redirect('login.php');
}

// FIX: created_at added — profile.php needs it for "Member Since" display
$stmt = $pdo->prepare(
    'SELECT id, full_name, email, avatar, is_banned, created_at, notification_email FROM users WHERE id = ?'
);
$stmt->execute([$_SESSION['user_id']]);
$CURRENT_USER = $stmt->fetch();

if (!$CURRENT_USER) {
    session_destroy();
    redirect('login.php');
}

if (!empty($CURRENT_USER['is_banned'])) {
    session_destroy();
    session_start();
    flash('error', 'Your account has been suspended. Please contact the administrator.');
    redirect('login.php');
}

<?php
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['user_id'])) {
    flash('error', 'Please login to continue.');
    redirect('login.php');
}
$CURRENT_USER = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$CURRENT_USER->execute([$_SESSION['user_id']]);
$CURRENT_USER = $CURRENT_USER->fetch();
if (!$CURRENT_USER) { session_destroy(); redirect('login.php'); }
if (!empty($CURRENT_USER['is_banned'])) { session_destroy(); die('Your account has been suspended.'); }

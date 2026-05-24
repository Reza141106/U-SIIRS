<?php
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['admin_id'])) {
    flash('error', 'Admin login required.');
    redirect('admin/login.php');
}
$CURRENT_ADMIN = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$CURRENT_ADMIN->execute([$_SESSION['admin_id']]);
$CURRENT_ADMIN = $CURRENT_ADMIN->fetch();
if (!$CURRENT_ADMIN) { unset($_SESSION['admin_id']); redirect('admin/login.php'); }

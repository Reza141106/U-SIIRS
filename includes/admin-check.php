<?php
/**
 * includes/admin-check.php
 * UPDATED: Loads admins.role column for super_admin checks.
 */
require_once __DIR__ . '/../config/database.php';

$IS_ADMIN_PAGE = true;

if (empty($_SESSION['admin_id'])) {
    flash('error', 'Admin login required.');
    redirect('admin/login.php');
}

$stmt = $pdo->prepare(
    'SELECT id, full_name, email, role, created_at FROM admins WHERE id = ?'
);
$stmt->execute([$_SESSION['admin_id']]);
$CURRENT_ADMIN = $stmt->fetch();

if (!$CURRENT_ADMIN) {
    unset($_SESSION['admin_id']);
    redirect('admin/login.php');
}

/**
 * Helper: require super_admin role, redirect with error otherwise.
 */
function require_super_admin(): void {
    global $CURRENT_ADMIN;
    if (($CURRENT_ADMIN['role'] ?? 'admin') !== 'super_admin') {
        flash('error', 'This page requires Super Admin access.');
        redirect('admin/dashboard.php');
    }
}

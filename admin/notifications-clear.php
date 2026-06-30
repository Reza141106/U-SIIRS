<?php
/**
 * admin/notifications-clear.php
 * Clears all admin notifications.
 */
require_once __DIR__.'/../includes/admin-check.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('admin/notifications.php'); }
csrf_check();
$pdo->query('DELETE FROM admin_notifications');
flash('success', 'All notifications cleared.');
redirect('admin/notifications.php');

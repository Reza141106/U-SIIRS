<?php require_once __DIR__.'/../config/database.php';
unset($_SESSION['admin_id']);
redirect('admin/login.php');

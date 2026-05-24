<?php require_once __DIR__ . '/../config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($PAGE_TITLE) ? e($PAGE_TITLE).' — U-SIIRS' : 'U-SIIRS — UTeM Smart Infrastructure Issue Reporting System' ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="page active" style="display:flex;">
<?php if (!empty($_SESSION['flash']['error'])): ?>
  <div class="toast-container"><div class="toast danger"><?= e(flash('error')) ?></div></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash']['success'])): ?>
  <div class="toast-container"><div class="toast success"><?= e(flash('success')) ?></div></div>
<?php endif; ?>

<?php
/**
 * admin/upload-proof.php
 * Handles admin proof media uploads for a report.
 * POST only — redirects back to reports-view.
 */
require_once __DIR__.'/../includes/admin-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('admin/reports.php'); }
csrf_check();

$reportId = (int)($_POST['report_id'] ?? 0);
$caption  = trim($_POST['caption'] ?? '');
if (strlen($caption) > 300) $caption = substr($caption, 0, 300);

// Verify report exists
$check = $pdo->prepare('SELECT id FROM reports WHERE id = ?');
$check->execute([$reportId]);
if (!$check->fetch()) {
    flash('error', 'Report not found.');
    redirect('admin/reports.php');
}

// Validate file
if (empty($_FILES['proof_photo']) || $_FILES['proof_photo']['error'] === UPLOAD_ERR_NO_FILE) {
    flash('error', 'Please select a photo to upload.');
    redirect('admin/reports-view.php?id=' . $reportId);
}

if ($_FILES['proof_photo']['error'] !== UPLOAD_ERR_OK) {
    flash('error', 'Upload failed. Please try again.');
    redirect('admin/reports-view.php?id=' . $reportId);
}

if ($_FILES['proof_photo']['size'] > 5 * 1024 * 1024) {
    flash('error', 'Proof photo must be under 5 MB.');
    redirect('admin/reports-view.php?id=' . $reportId);
}

$info = getimagesize($_FILES['proof_photo']['tmp_name']);
if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png'], true)) {
    flash('error', 'Only JPG and PNG images are accepted.');
    redirect('admin/reports-view.php?id=' . $reportId);
}

$ext       = ($info['mime'] === 'image/png') ? 'png' : 'jpg';
$filename  = 'proof_' . $reportId . '_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
$uploadDir = __DIR__ . '/../assets/uploads/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!move_uploaded_file($_FILES['proof_photo']['tmp_name'], $uploadDir . $filename)) {
    flash('error', 'Could not save the file. Please try again.');
    redirect('admin/reports-view.php?id=' . $reportId);
}

// Save to DB
$pdo->prepare(
    'INSERT INTO report_progress_media (report_id, admin_id, filename, caption) VALUES (?, ?, ?, ?)'
)->execute([$reportId, $_SESSION['admin_id'], $filename, $caption ?: null]);

// Log to audit trail
$pdo->prepare(
    'INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)'
)->execute([
    $_SESSION['admin_id'], 'upload_proof', 'report', $reportId,
    'Uploaded proof photo: ' . $filename . ($caption ? ' — "' . $caption . '"' : ''),
]);

// Notify the user that progress media was added
$reportRow = $pdo->prepare('SELECT user_id, title FROM reports WHERE id = ?');
$reportRow->execute([$reportId]);
$rep = $reportRow->fetch();
if ($rep) {
    $msg = "Admin has uploaded a work progress photo for your report #$reportId: \"{$rep['title']}\".";
    $pdo->prepare(
        'INSERT INTO notifications (user_id, report_id, message) VALUES (?, ?, ?)'
    )->execute([$rep['user_id'], $reportId, $msg]);
}

flash('success', 'Proof photo uploaded successfully.');
redirect('admin/reports-view.php?id=' . $reportId);

<?php
/**
 * admin/update-status.php
 * UPDATED: Logs action to admin_activity_log after each status change.
 */
require_once __DIR__.'/../includes/admin-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/reports.php');
}
csrf_check();

$id         = (int)($_POST['id'] ?? 0);
$status     = $_POST['status']      ?? '';
$priority   = $_POST['priority']    ?? 'Medium';
$remarks    = trim($_POST['remarks']     ?? '');
$adminNotes = trim($_POST['admin_notes'] ?? '');

if (!in_array($status, REPORT_STATUSES, true)) {
    flash('error', 'Invalid status value.');
    redirect('admin/report-view.php?id=' . $id);
}

if (!in_array($priority, REPORT_PRIORITIES, true)) {
    $priority = 'Medium';
}

$s = $pdo->prepare('SELECT user_id, status, title FROM reports WHERE id = ?');
$s->execute([$id]);
$r = $s->fetch();

if (!$r) {
    flash('error', 'Report not found.');
    redirect('admin/reports.php');
}

$oldStatus = $r['status'];

$pdo->prepare(
    'UPDATE reports SET status = ?, priority = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?'
)->execute([$status, $priority, $adminNotes ?: null, $id]);

$pdo->prepare(
    'INSERT INTO status_updates (report_id, status, remarks, changed_by_admin_id) VALUES (?, ?, ?, ?)'
)->execute([$id, $status, $remarks ?: null, $_SESSION['admin_id']]);

// Notify report owner if notification_email is on (or null = default on)
if ($r['user_id']) {
    // Check user notification preference
    $prefStmt = $pdo->prepare('SELECT notification_email FROM users WHERE id = ?');
    $prefStmt->execute([$r['user_id']]);
    $pref = $prefStmt->fetchColumn();

    $msg = "Your report #$id status has been updated to \"$status\"" .
           ($remarks ? ": $remarks" : '.');

    // Always insert in-app notification
    $pdo->prepare(
         'INSERT INTO notifications (user_id, report_id, message) VALUES (?, ?, ?)'
    )->execute([$r['user_id'], $id, $msg]);

    // Send email only if user has not opted out
    if ($pref === null || $pref == 1) {
        // Email sending via mailer is handled separately — this flag gates it
        // include __DIR__ . '/../includes/mailer.php'; send_status_email(...)
    }
}

// ── AUDIT LOG ────────────────────────────────────────────────────────────────
$pdo->prepare(
    'INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details)
     VALUES (?, ?, ?, ?, ?)'
)->execute([
    $_SESSION['admin_id'],
    'update_status',
    'report',
    $id,
    "Status changed from \"{$oldStatus}\" to \"{$status}\"" . ($remarks ? " — Remarks: {$remarks}" : ''),
]);

flash('success', 'Report status updated successfully.');
redirect('admin/report-view.php?id=' . $id);

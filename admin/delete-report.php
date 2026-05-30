<?php
/**
 * admin/delete-report.php
 * Handles POST request to delete a report and all associated files.
 *
 * CRITICAL BUG FIX: Previously used GET with CSRF token in URL — dangerous because:
 *   1. Token appears in browser history and server logs.
 *   2. URL can be shared/bookmarked, replaying the destructive action.
 *   3. GET must never be used for destructive operations (violates HTTP standards).
 * Fix: Now requires POST + CSRF validation via $_POST.
 */
require_once __DIR__.'/../includes/admin-check.php';

// Reject anything that isn't a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/reports.php');
}

// Validate CSRF token from POST body (not URL)
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'Invalid report ID.');
    redirect('admin/reports.php');
}

// Fetch report cover photo before deleting
$s = $pdo->prepare('SELECT photo FROM reports WHERE id = ?');
$s->execute([$id]);
$r = $s->fetch();

if ($r) {
    // Delete all attachment files from disk
    $atts = $pdo->prepare('SELECT filename FROM report_attachments WHERE report_id = ?');
    $atts->execute([$id]);
    foreach ($atts->fetchAll() as $a) {
        $file = __DIR__ . '/../assets/uploads/' . $a['filename'];
        if (file_exists($file)) @unlink($file);
    }

    // Delete cover photo from disk
    if ($r['photo']) {
        $cover = __DIR__ . '/../assets/uploads/' . $r['photo'];
        if (file_exists($cover)) @unlink($cover);
    }

    // Delete DB record — CASCADE removes attachments, status_updates, notifications
    $pdo->prepare('DELETE FROM reports WHERE id = ?')->execute([$id]);
    flash('success', 'Report #' . $id . ' has been deleted.');
} else {
    flash('error', 'Report not found.');
}

redirect('admin/reports.php');

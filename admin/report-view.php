<?php
/**
 * admin/report-view.php
 * Detailed view of a single report with status update form.
 * IMPROVEMENT: Explicit column selection (no SELECT *).
 * IMPROVEMENT: Inline styles replaced with CSS classes.
 * IMPROVEMENT: Status/priority options use REPORT_STATUSES/REPORT_PRIORITIES constants.
 */
require_once __DIR__.'/../includes/admin-check.php';

$id = (int)($_GET['id'] ?? 0);

// Explicit columns — no SELECT *
$s = $pdo->prepare(
    'SELECT r.id, r.title, r.category, r.description, r.location, r.photo,
            r.status, r.priority, r.admin_notes, r.created_at, r.updated_at,
            u.full_name, u.email
     FROM reports r
     JOIN users u ON u.id = r.user_id
     WHERE r.id = ?'
);
$s->execute([$id]);
$r = $s->fetch();

if (!$r) {
    flash('error', 'Report not found.');
    redirect('admin/reports.php');
}

// Status history / audit trail
$hist = $pdo->prepare(
    'SELECT s.status, s.remarks, s.created_at, a.full_name AS admin_name
     FROM status_updates s
     LEFT JOIN admins a ON a.id = s.changed_by_admin_id
     WHERE s.report_id = ?
     ORDER BY s.created_at ASC'
);
$hist->execute([$id]);
$hist = $hist->fetchAll();

// Collect all photos (cover + attachments)
$allPhotos = [];
if ($r['photo']) $allPhotos[] = $r['photo'];
$attStmt = $pdo->prepare(
    'SELECT filename FROM report_attachments WHERE report_id = ? ORDER BY id ASC'
);
$attStmt->execute([$id]);
foreach ($attStmt->fetchAll() as $a) $allPhotos[] = $a['filename'];

$statusBadge = ['Pending' => 'warning', 'In Progress' => 'info',
                'Resolved' => 'success', 'Closed' => 'neutral',
                'Rejected' => 'danger'][$r['status']] ?? 'neutral';

$PAGE_TITLE = 'Report #' . $r['id'];
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>

  <div class="admin-main">

<div class="page-header flex-between">
  <div>
    <h1><?= e($r['title']) ?></h1>
    <p>
      Report #<?= (int)$r['id'] ?>
      · Submitted by <strong><?= e($r['full_name']) ?></strong>
      (<?= e($r['email']) ?>)
      · <?= e(date('M j, Y g:i a', strtotime($r['created_at']))) ?>
    </p>
  </div>
  <span class="badge badge-<?= $statusBadge ?> badge-lg"><?= e($r['status']) ?></span>
</div>

<div class="container">
  <div class="row-grid">

    <!-- Left: Report Details -->
    <div class="card">

      <!-- Photo gallery -->
      <?php if (count($allPhotos) === 1): ?>
        <img class="thumb mb-2"
             src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>"
             alt="Report photo"
             onclick="openLightbox(this.src)"
             style="cursor:pointer;">

      <?php elseif (count($allPhotos) > 1): ?>
        <div class="photo-gallery">
          <img id="mainPhoto"
               class="gallery-main"
               src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>"
               alt="Report photo"
               onclick="openLightbox(this.src)">
          <div class="gallery-thumbs">
            <?php foreach ($allPhotos as $i => $ph): ?>
              <img src="<?= BASE_URL ?>/assets/uploads/<?= e($ph) ?>"
                   onclick="document.getElementById('mainPhoto').src=this.src; setActive(this);"
                   class="thumb-mini <?= $i === 0 ? 'thumb-active' : '' ?>"
                   alt="Photo <?= $i + 1 ?>">
            <?php endforeach; ?>
          </div>
          <p class="photo-count"><?= count($allPhotos) ?> photo(s) attached</p>
        </div>
      <?php endif; ?>

      <!-- Report metadata -->
      <div class="report-meta-grid">
        <div class="report-meta-item">
          <span class="report-meta-label">Category</span>
          <span><?= e($r['category']) ?></span>
        </div>
        <div class="report-meta-item">
          <span class="report-meta-label">Priority</span>
          <span><?= e($r['priority']) ?></span>
        </div>
        <div class="report-meta-item">
          <span class="report-meta-label">Location</span>
          <span><?= e($r['location']) ?></span>
        </div>
        <div class="report-meta-item">
          <span class="report-meta-label">Last Updated</span>
          <span><?= e(date('M j, Y g:i a', strtotime($r['updated_at']))) ?></span>
        </div>
      </div>

      <h3 class="mt-3 report-desc-title">Description</h3>
      <p class="report-desc-text"><?= e($r['description']) ?></p>
    </div>

    <!-- Right: Update Form + History -->
    <div>
      <div class="card mb-2">
        <h3 class="card-section-title">Update Status</h3>
        <form method="post" action="<?= BASE_URL ?>/admin/update-status.php">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="id"   value="<?= (int)$r['id'] ?>">

          <div class="form-group">
            <label class="form-label" for="rv-status">Status</label>
            <select id="rv-status" name="status" class="form-control" required>
              <?php foreach (REPORT_STATUSES as $st): ?>
                <option <?= $r['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="rv-priority">Priority</label>
            <select id="rv-priority" name="priority" class="form-control">
              <?php foreach (REPORT_PRIORITIES as $p): ?>
                <option <?= $r['priority'] === $p ? 'selected' : '' ?>><?= $p ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="rv-remarks">Remarks <span class="form-hint-inline">(visible to user)</span></label>
            <textarea id="rv-remarks"
                      class="form-control"
                      name="remarks"
                      placeholder="Optional message to the user about this update…"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="rv-notes">
              Admin Notes
              <span class="form-hint-inline">— private, not shown to user</span>
            </label>
            <textarea id="rv-notes"
                      class="form-control"
                      name="admin_notes"
                      placeholder="Internal notes, reference numbers, escalation details…"><?= e($r['admin_notes'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Save Update</button>
        </form>
      </div>

      <!-- Status history timeline -->
      <div class="card">
        <h3 class="card-section-title">Status History</h3>
        <?php if (empty($hist)): ?>
          <p class="text-muted">No status changes recorded yet.</p>
        <?php else: ?>
          <div class="timeline">
            <?php foreach ($hist as $h): ?>
              <div class="timeline-item">
                <div class="timeline-time">
                  <?= e(date('M j, Y g:i a', strtotime($h['created_at']))) ?>
                  <?= $h['admin_name'] ? ' · ' . e($h['admin_name']) : '' ?>
                </div>
                <div class="timeline-text">
                  <strong><?= e($h['status']) ?></strong>
                  <?= $h['remarks'] ? ' — ' . e($h['remarks']) : '' ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

</div><!-- /admin-main -->

<?php include __DIR__.'/../includes/lightbox.php'; ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

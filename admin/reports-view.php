<?php
/**
 * admin/reports-view.php
 * Detailed admin view of a single report.
 * v2: Leaflet map, admin proof media upload + display.
 */
require_once __DIR__.'/../includes/admin-check.php';

$id = (int)($_GET['id'] ?? 0);

$s = $pdo->prepare(
    'SELECT r.id, r.title, r.category, r.description, r.location,
            r.latitude, r.longitude,
            r.photo, r.status, r.priority, r.admin_notes,
            r.created_at, r.updated_at, u.full_name, u.email
     FROM reports r
     JOIN users u ON u.id = r.user_id
     WHERE r.id = ?'
);
$s->execute([$id]);
$r = $s->fetch();

if (!$r) { flash('error', 'Report not found.'); redirect('admin/reports.php'); }

// Status history
$hist = $pdo->prepare(
    'SELECT s.status, s.remarks, s.created_at, a.full_name AS admin_name
     FROM status_updates s
     LEFT JOIN admins a ON a.id = s.changed_by_admin_id
     WHERE s.report_id = ? ORDER BY s.created_at ASC'
);
$hist->execute([$id]);
$hist = $hist->fetchAll();

// All photos
$allPhotos = [];
if ($r['photo']) $allPhotos[] = $r['photo'];
$attStmt = $pdo->prepare('SELECT filename FROM report_attachments WHERE report_id = ? ORDER BY id ASC');
$attStmt->execute([$id]);
foreach ($attStmt->fetchAll() as $a) $allPhotos[] = $a['filename'];

// Admin proof media
$proofStmt = $pdo->prepare(
    'SELECT m.id, m.filename, m.caption, m.created_at, a.full_name AS admin_name
     FROM report_progress_media m
     JOIN admins a ON a.id = m.admin_id
     WHERE m.report_id = ? ORDER BY m.created_at ASC'
);
$proofStmt->execute([$id]);
$proofMedia = $proofStmt->fetchAll();

$hasMap = ($r['latitude'] !== null && $r['longitude'] !== null);

$statusBadge = ['Pending' => 'warning', 'In Progress' => 'info', 'Resolved' => 'success',
                'Closed'  => 'neutral',  'Rejected'   => 'danger'][$r['status']] ?? 'neutral';

$EXTRA_HEAD = '
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
  <style>
    .report-map      { height: 280px; border-radius: 10px; border: 1px solid var(--border); margin-top:.5rem; z-index:0; }
    .proof-grid      { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:1rem; margin-top:1rem; }
    .proof-card      { border:1px solid var(--border); border-radius:10px; overflow:hidden; }
    .proof-card img  { width:100%; height:120px; object-fit:cover; cursor:pointer; display:block; }
    .proof-card-body { padding:.5rem .65rem; font-size:.78rem; }
    .proof-card-cap  { font-weight:500; margin-bottom:.15rem; }
    .proof-card-meta { color:var(--text2); }
    .proof-upload-area { border:2px dashed var(--border); border-radius:10px; padding:1.5rem;
                         text-align:center; cursor:pointer; transition:.2s; margin-top:.5rem; }
    .proof-upload-area:hover { border-color:var(--navy); background:var(--bg2); }
  </style>';

$IS_ADMIN_PAGE = true;
$PAGE_TITLE    = 'Report #' . $r['id'];
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>

<div class="page-header flex-between">
  <div>
    <h1><?= e($r['title']) ?></h1>
    <p>
      Report #<?= (int)$r['id'] ?>
      · Submitted by <strong><?= e($r['full_name']) ?></strong> (<?= e($r['email']) ?>)
      · <?= e(date('M j, Y g:i a', strtotime($r['created_at']))) ?>
    </p>
  </div>
  <span class="badge badge-<?= $statusBadge ?> badge-lg"><?= e($r['status']) ?></span>
</div>

<div class="container">
  <div class="row-grid">

    <!-- ── LEFT: Report details ── -->
    <div>

      <!-- Photo gallery -->
      <div class="card" style="margin-bottom:1.5rem;">
        <?php if (count($allPhotos) === 1): ?>
          <img class="thumb mb-2"
               src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>"
               alt="Report photo" onclick="openLightbox(this.src)" style="cursor:pointer;">

        <?php elseif (count($allPhotos) > 1): ?>
          <div class="photo-gallery">
            <img id="mainPhoto" class="gallery-main"
                 src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>"
                 alt="Report photo" onclick="openLightbox(this.src)">
            <div class="gallery-thumbs">
              <?php foreach ($allPhotos as $i => $ph): ?>
                <img src="<?= BASE_URL ?>/assets/uploads/<?= e($ph) ?>"
                     onclick="document.getElementById('mainPhoto').src=this.src; setActive(this);"
                     class="thumb-mini <?= $i === 0 ? 'thumb-active' : '' ?>" alt="Photo <?= $i + 1 ?>">
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

        <!-- Map -->
        <?php if ($hasMap): ?>
          <div class="mt-3">
            <h4 style="margin-bottom:.25rem; font-size:.9rem;">📍 Reported Location (Map)</h4>
            <div id="reportMap" class="report-map"></div>
          </div>
        <?php else: ?>
          <div class="mt-3" style="font-size:.82rem;color:var(--text2);">
            ℹ️ No map pin was attached to this report.
          </div>
        <?php endif; ?>
      </div>

      <!-- ── Proof media: upload + gallery ── -->
      <div class="card">
        <h3 class="card-section-title">🔧 Work Progress Media</h3>
        <p style="font-size:.85rem;color:var(--text2);margin-bottom:1rem;">
          Upload photos as proof of work done or in progress. These are visible to the user.
        </p>

        <!-- Upload form -->
        <form method="post" enctype="multipart/form-data"
              action="<?= BASE_URL ?>/admin/upload-proof.php">
          <input type="hidden" name="csrf"      value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">

          <div class="form-group" style="margin-bottom:.75rem;">
            <label class="form-label" for="proofCaption">Caption <span class="form-hint-inline">(optional)</span></label>
            <input id="proofCaption" class="form-control" name="caption"
                   maxlength="300" placeholder="e.g. Electrician replacing the faulty switch">
          </div>

          <input id="proofFileInput" type="file" name="proof_photo" accept="image/jpeg,image/png"
                 class="visually-hidden" onchange="showProofPreview(this)">
          <div class="proof-upload-area" onclick="document.getElementById('proofFileInput').click()" id="proofDropZone">
            <div style="font-size:1.5rem;">📸</div>
            <div id="proofUploadLabel" style="font-size:.88rem;margin-top:.3rem;">Click to select a proof photo (JPG/PNG, max 5 MB)</div>
            <img id="proofPreviewImg" src="" alt="" style="display:none;max-height:120px;margin:8px auto 0;border-radius:8px;">
          </div>

          <button type="submit" class="btn btn-primary" style="margin-top:.75rem;">
            Upload Proof Photo
          </button>
        </form>

        <!-- Existing proof photos -->
        <?php if (!empty($proofMedia)): ?>
          <div class="proof-grid">
            <?php foreach ($proofMedia as $pm): ?>
              <div class="proof-card">
                <img src="<?= BASE_URL ?>/assets/uploads/<?= e($pm['filename']) ?>"
                     alt="Proof" onclick="openLightbox(this.src)">
                <div class="proof-card-body">
                  <?php if ($pm['caption']): ?>
                    <div class="proof-card-cap"><?= e($pm['caption']) ?></div>
                  <?php endif; ?>
                  <div class="proof-card-meta">
                    <?= e($pm['admin_name']) ?><br>
                    <?= e(date('M j, Y g:i a', strtotime($pm['created_at']))) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted" style="margin-top:1rem;font-size:.85rem;">No proof media uploaded yet.</p>
        <?php endif; ?>
      </div>

    </div><!-- /left -->

    <!-- ── RIGHT: Status update + history ── -->
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
            <label class="form-label" for="rv-remarks">
              Remarks <span class="form-hint-inline">(visible to user)</span>
            </label>
            <textarea id="rv-remarks" class="form-control" name="remarks"
                      placeholder="Optional message to the user about this update…"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="rv-notes">
              Admin Notes <span class="form-hint-inline">— private</span>
            </label>
            <textarea id="rv-notes" class="form-control" name="admin_notes"
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

<?php if ($hasMap): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
(function () {
  var lat = <?= (float)$r['latitude'] ?>;
  var lng = <?= (float)$r['longitude'] ?>;
  var map = L.map('reportMap', { scrollWheelZoom: false }).setView([lat, lng], 18);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(map);
  L.marker([lat, lng]).addTo(map)
    .bindPopup('<strong><?= e(addslashes($r['location'])) ?></strong><br><?= e(addslashes($r['full_name'])) ?>').openPopup();
}());
</script>
<?php endif; ?>

<script>
function showProofPreview(input) {
  var img   = document.getElementById('proofPreviewImg');
  var label = document.getElementById('proofUploadLabel');
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
      img.src = e.target.result;
      img.style.display = 'block';
      label.textContent = input.files[0].name;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php include __DIR__.'/../includes/lightbox.php'; ?>
<?php include __DIR__.'/../includes/footer.php'; ?>

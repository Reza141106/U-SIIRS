<?php
/**
 * report-details.php
 * User view of a single report.
 * v2: Shows Leaflet map pin if coordinates saved + admin proof media section.
 */
require_once __DIR__.'/includes/auth-check.php';

$id = (int)($_GET['id'] ?? 0);
$s  = $pdo->prepare(
    'SELECT r.id, r.title, r.category, r.description, r.location,
            r.latitude, r.longitude,
            r.photo, r.status, r.priority, r.created_at, u.full_name
     FROM reports r
     JOIN users u ON u.id = r.user_id
     WHERE r.id = ? AND r.user_id = ?'
);
$s->execute([$id, $_SESSION['user_id']]);
$r = $s->fetch();
if (!$r) { flash('error', 'Not found.'); redirect('my-report.php'); }

$hist = $pdo->prepare(
    'SELECT id, status, remarks, created_at FROM status_updates WHERE report_id = ? ORDER BY created_at ASC'
);
$hist->execute([$id]);
$hist = $hist->fetchAll();


$allPhotos = [];
if ($r['photo']) $allPhotos[] = $r['photo'];
$attStmt = $pdo->prepare('SELECT filename FROM report_attachments WHERE report_id = ? ORDER BY id ASC');
$attStmt->execute([$id]);
foreach ($attStmt->fetchAll() as $a) $allPhotos[] = $a['filename'];


$proofStmt = $pdo->prepare(
    'SELECT m.filename, m.caption, m.created_at, a.full_name AS admin_name
     FROM report_progress_media m
     JOIN admins a ON a.id = m.admin_id
     WHERE m.report_id = ?
     ORDER BY m.created_at ASC'
);
$proofStmt->execute([$id]);
$proofMedia = $proofStmt->fetchAll();

$hasMap = ($r['latitude'] !== null && $r['longitude'] !== null);

if ($hasMap) {
    $EXTRA_HEAD = '
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
  <style>
    .report-map { height: 260px; border-radius: 10px; border: 1px solid var(--border); margin-top:.5rem; z-index:0; }
  </style>';
}

$b = ['Pending' => 'warning', 'In Progress' => 'info', 'Resolved' => 'success',
      'Closed' => 'neutral', 'Rejected' => 'danger'][$r['status']] ?? 'neutral';

$PAGE_TITLE = 'Report #' . $r['id'];
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header flex-between">
  <div>
    <h1><?= e($r['title']) ?></h1>
    <p>Report #<?= (int)$r['id'] ?> · <?= e($r['category']) ?> · <?= e(date('M j, Y g:i a', strtotime($r['created_at']))) ?></p>
  </div>
  <div>
    <span class="badge badge-<?= $b ?>" style="font-size:.85rem;"><?= e($r['status']) ?></span>
    <?php if ($r['status'] === 'Pending'): ?>
      <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/edit-report.php?id=<?= (int)$r['id'] ?>">Edit</a>
    <?php endif; ?>
  </div>
</div>

<div class="container">
  <div class="row-grid">

    <!-- Left: report details -->
    <div>
      <div class="card" style="margin-bottom:1.5rem;">

        <?php if (count($allPhotos) === 1): ?>
          <img class="thumb mb-2" src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>" alt="">
        <?php elseif (count($allPhotos) > 1): ?>
          <div style="margin-bottom:1rem;">
            <img id="mainPhoto" class="thumb mb-2"
                 src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>"
                 alt="" style="width:100%;max-height:320px;object-fit:cover;border-radius:10px;cursor:pointer;"
                 onclick="openLightbox(this.src)">
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.5rem;">
              <?php foreach ($allPhotos as $i => $ph): ?>
                <img src="<?= BASE_URL ?>/assets/uploads/<?= e($ph) ?>"
                     onclick="document.getElementById('mainPhoto').src=this.src; setActive(this)"
                     style="width:64px;height:64px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid <?= $i === 0 ? 'var(--navy)' : 'var(--border)' ?>;"
                     class="thumb-mini">
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <h3 style="margin-bottom:.25rem;">Description</h3>
        <p style="color:var(--text2); white-space:pre-wrap;"><?= e($r['description']) ?></p>
        <div class="mt-3 text-muted"><strong>Location:</strong> <?= e($r['location']) ?></div>

        <?php if ($hasMap): ?>
          <div class="mt-3">
            <strong style="font-size:.9rem;">📍 Pinned Location</strong>
            <div id="reportMap" class="report-map"></div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Admin proof media -->
      <?php if (!empty($proofMedia)): ?>
      <div class="card">
        <h3 style="margin-bottom:1rem;">🔧 Work Progress — Admin Proof</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;">
          <?php foreach ($proofMedia as $pm): ?>
            <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;">
              <img src="<?= BASE_URL ?>/assets/uploads/<?= e($pm['filename']) ?>"
                   alt="Proof" style="width:100%;height:130px;object-fit:cover;cursor:pointer;"
                   onclick="openLightbox(this.src)">
              <div style="padding:.5rem .6rem;">
                <?php if ($pm['caption']): ?>
                  <div style="font-size:.8rem;font-weight:500;margin-bottom:.2rem;"><?= e($pm['caption']) ?></div>
                <?php endif; ?>
                <div style="font-size:.72rem;color:var(--text2);">
                  <?= e($pm['admin_name']) ?> · <?= e(date('M j, Y', strtotime($pm['created_at']))) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Right: status history -->
    <div class="card">
      <h3 style="margin-bottom:.5rem;">Status History</h3>
      <div class="timeline">
        <?php foreach ($hist as $h): ?>
          <div class="timeline-item">
            <div class="timeline-time"><?= e(date('M j, Y g:i a', strtotime($h['created_at']))) ?></div>
            <div class="timeline-text">
              <strong><?= e($h['status']) ?></strong>
              <?= $h['remarks'] ? ' — ' . e($h['remarks']) : '' ?>
            </div>
          </div>
        <?php endforeach; ?>
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
  var map = L.map('reportMap', { zoomControl: true, scrollWheelZoom: false }).setView([lat, lng], 18);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(map);
  L.marker([lat, lng]).addTo(map)
    .bindPopup('<strong><?= e(addslashes($r['location'])) ?></strong>').openPopup();
}());
</script>
<?php endif; ?>

<?php include __DIR__.'/includes/lightbox.php'; ?>
<?php include __DIR__.'/includes/footer.php'; ?>

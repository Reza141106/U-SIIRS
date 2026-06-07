<?php require_once __DIR__.'/includes/auth-check.php';
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT r.id, r.title, r.category, r.description, r.location, r.photo, r.status, r.priority, r.created_at, u.full_name FROM reports r JOIN users u ON u.id=r.user_id WHERE r.id=? AND r.user_id=?');
$s->execute([$id,$_SESSION['user_id']]); $r = $s->fetch();
if (!$r) { flash('error','Not found.'); redirect('my-report.php'); }

$hist = $pdo->prepare('SELECT id, status, remarks, created_at FROM status_updates WHERE report_id=? ORDER BY created_at ASC');
$hist->execute([$id]); $hist=$hist->fetchAll();

// Fetch all photos: cover + attachments
$allPhotos = [];
if ($r['photo']) $allPhotos[] = $r['photo'];
$attStmt = $pdo->prepare('SELECT filename FROM report_attachments WHERE report_id=? ORDER BY id ASC');
$attStmt->execute([$id]);
foreach ($attStmt->fetchAll() as $a) $allPhotos[] = $a['filename'];

$b = ['Pending'=>'warning','In Progress'=>'info','Resolved'=>'success'][$r['status']] ?? 'neutral';
$PAGE_TITLE='Report #'.$r['id'];
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header flex-between">
  <div><h1><?= e($r['title']) ?></h1><p>Report #<?= (int)$r['id'] ?> · <?= e($r['category']) ?> · <?= e(date('M j, Y g:i a',strtotime($r['created_at']))) ?></p></div>
  <div>
    <span class="badge badge-<?= $b ?>" style="font-size:.85rem;"><?= e($r['status']) ?></span>
    <?php if($r['status']==='Pending'): ?>
      <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/edit-report.php?id=<?= (int)$r['id'] ?>">Edit</a>
    <?php endif; ?>
  </div>
</div>
<div class="container">
  <div class="row-grid">
    <div class="card">

      <?php if(count($allPhotos) === 1): ?>
        <!-- Single photo -->
        <img class="thumb mb-2" src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>" alt="">
      <?php elseif(count($allPhotos) > 1): ?>
        <!-- Photo gallery -->
        <div style="margin-bottom:1rem;">
          <img id="mainPhoto" class="thumb mb-2"
               src="<?= BASE_URL ?>/assets/uploads/<?= e($allPhotos[0]) ?>"
               alt="" style="width:100%;max-height:320px;object-fit:cover;border-radius:10px;cursor:pointer;"
               onclick="openLightbox(this.src)">
          <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.5rem;">
            <?php foreach($allPhotos as $i=>$ph): ?>
              <img src="<?= BASE_URL ?>/assets/uploads/<?= e($ph) ?>"
                   onclick="document.getElementById('mainPhoto').src=this.src; setActive(this)"
                   style="width:64px;height:64px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid <?= $i===0?'var(--navy)':'var(--border)' ?>;"
                   class="thumb-mini">
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <h3 style="margin-bottom:.25rem;">Description</h3>
      <p style="color:var(--text2); white-space:pre-wrap;"><?= e($r['description']) ?></p>
      <div class="mt-3 text-muted"><strong>Location:</strong> <?= e($r['location']) ?></div>
    </div>
    <div class="card">
      <h3 style="margin-bottom:.5rem;">Status History</h3>
      <div class="timeline">
      <?php foreach($hist as $h): ?>
        <div class="timeline-item">
          <div class="timeline-time"><?= e(date('M j, Y g:i a',strtotime($h['created_at']))) ?></div>
          <div class="timeline-text"><strong><?= e($h['status']) ?></strong><?= $h['remarks']?' — '.e($h['remarks']):'' ?></div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/lightbox.php'; ?>
<?php include __DIR__.'/includes/footer.php'; ?>

<?php require_once __DIR__.'/includes/auth-check.php';
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del_att'])) {
    csrf_check();
    $aid = (int)$_POST['del_att'];
    $rid = (int)($_POST['id'] ?? 0);
    // Make sure attachment belongs to a report owned by this user
    $chk = $pdo->prepare('SELECT ra.filename FROM report_attachments ra JOIN reports r ON r.id=ra.report_id WHERE ra.id=? AND r.user_id=? AND r.status="Pending"');
    $chk->execute([$aid,$uid]); $att=$chk->fetch();
    if ($att) {
        @unlink(__DIR__.'/assets/uploads/'.$att['filename']);
        $pdo->prepare('DELETE FROM report_attachments WHERE id=?')->execute([$aid]);
    }
    redirect('edit-report.php?id='.$rid);
}

// Delete whole report (POST only — CSRF-safe)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    csrf_check();
    $id = (int)$_POST['delete'];
    $s = $pdo->prepare('SELECT id, title, category, description, location, photo, status, priority, created_at FROM reports WHERE id=? AND user_id=?');
    $s->execute([$id,$uid]); $r = $s->fetch();
    if (!$r) { flash('error','Not found.'); redirect('my-report.php'); }
    if ($r['status']!=='Pending') { flash('error','Only Pending reports can be deleted.'); redirect('my-report.php'); }
    // Clean up all images
    if ($r['photo']) @unlink(__DIR__.'/assets/uploads/'.$r['photo']);
    $atts = $pdo->prepare('SELECT filename FROM report_attachments WHERE report_id=?');
    $atts->execute([$id]);
    foreach ($atts->fetchAll() as $a) @unlink(__DIR__.'/assets/uploads/'.$a['filename']);
    $pdo->prepare('DELETE FROM reports WHERE id=?')->execute([$id]);
    flash('success','Report deleted.');
    redirect('my-report.php');
}

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT id, title, category, description, location, photo, status, priority, created_at FROM reports WHERE id=? AND user_id=?');
$s->execute([$id,$uid]); $rep = $s->fetch();
if (!$rep) { flash('error','Report not found.'); redirect('my-report.php'); }
if ($rep['status']!=='Pending') { flash('error','Only Pending reports can be edited.'); redirect('report-details.php?id='.$id); }

// Fetch existing attachments
$attStmt = $pdo->prepare('SELECT id, filename FROM report_attachments WHERE report_id=? ORDER BY id ASC');
$attStmt->execute([$id]); $attachments = $attStmt->fetchAll();

// FIXED: Use REPORT_CATEGORIES constant instead of local $categories array
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $cat   = trim($_POST['category'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $loc   = trim($_POST['location'] ?? '');
    if (strlen($title)<3)                             $err='Title required.';
    elseif (!in_array($cat, REPORT_CATEGORIES, true)) $err='Invalid category.';
    elseif (strlen($desc)<10)                         $err='Description too short.';
    elseif (strlen($loc)<2)                           $err='Location required.';

    // Validate new uploads
    $newFiles = [];
    if (!$err && !empty($_FILES['photos']['name'][0])) {
        $existingCount = ($rep['photo'] ? 1 : 0) + count($attachments);
        $slots = 5 - $existingCount;
        foreach ($_FILES['photos']['name'] as $i => $fname) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            if ($i >= $slots) { $err='You can only have 5 photos total. Remove some first.'; break; }
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) { $err='Upload error on file '.($i+1).'.'; break; }
            if ($_FILES['photos']['size'][$i] > 5*1024*1024) { $err='Each file must be under 5MB.'; break; }
            $info = getimagesize($_FILES['photos']['tmp_name'][$i]);
            if (!$info || !in_array($info['mime'],['image/jpeg','image/png'])) { $err='JPG/PNG only.'; break; }
            $newFiles[] = ['tmp'=>$_FILES['photos']['tmp_name'][$i], 'ext'=>$info['mime']==='image/png'?'png':'jpg'];
        }
    }

    if (!$err) {
        $pdo->prepare('UPDATE reports SET title=?, category=?, description=?, location=?, updated_at=NOW() WHERE id=? AND user_id=?')
            ->execute([$title,$cat,$desc,$loc,$id,$uid]);

        $attIns = $pdo->prepare('INSERT INTO report_attachments(report_id,filename) VALUES(?,?)');
        foreach ($newFiles as $vf) {
            $name = 'rep_'.time().'_'.bin2hex(random_bytes(6)).'.'.$vf['ext'];
            if (move_uploaded_file($vf['tmp'], __DIR__.'/assets/uploads/'.$name))
                $attIns->execute([$id, $name]);
        }
        flash('success','Report updated.');
        redirect('report-details.php?id='.$id);
    }
}
$PAGE_TITLE='Edit Report';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header"><h1>Edit Report</h1></div>
<div class="container submit-container">
  <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
  <div class="card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
      <label class="form-label">Title</label>
      <input class="form-control" name="title" required value="<?= e($rep['title']) ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Category</label>
      <select class="form-control" name="category" required>
        <?php foreach(REPORT_CATEGORIES as $c): ?>
          <option <?= $rep['category']===$c?'selected':'' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Location</label>
      <input class="form-control" name="location" required value="<?= e($rep['location']) ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="description" required><?= e($rep['description']) ?></textarea>
    </div>

    <?php
      $allPhotos = [];
      if ($rep['photo']) $allPhotos[] = ['src'=>$rep['photo'], 'id'=>null, 'isCover'=>true];
      foreach ($attachments as $a) $allPhotos[] = ['src'=>$a['filename'], 'id'=>$a['id'], 'isCover'=>false];
    ?>
    <?php if($allPhotos): ?>
    <div class="form-group">
      <label class="form-label">Current Photos</label>
      <!-- FIXED: replaced inline styles with .current-photos-grid / .photo-item / .photo-cover-label / .photo-remove-btn -->
      <div class="current-photos-grid">
        <?php foreach($allPhotos as $ph): ?>
        <div class="photo-item">
          <img src="<?= BASE_URL ?>/assets/uploads/<?= e($ph['src']) ?>"
               alt="Report photo">
          <?php if($ph['isCover']): ?>
            <span class="photo-cover-label">Cover</span>
          <?php else: ?>
            <!-- FIXED: was GET link ?del_att=… now POST form (re-evaluation issue) -->
            <form method="post" style="display:contents;">
              <input type="hidden" name="csrf"    value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="del_att" value="<?= (int)$ph['id'] ?>">
              <input type="hidden" name="id"      value="<?= (int)$id ?>">
              <button type="submit" class="photo-remove-btn"
                      data-confirm="Remove this photo?"
                      aria-label="Remove photo">&#x2715;</button>
            </form>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="photo-hint">The cover photo cannot be removed. Extra photos can be removed individually.</p>
    </div>
    <?php endif; ?>

    <?php if(count($allPhotos) < 5): ?>
    <div class="form-group">
      <label class="form-label">Add More Photos (<?= 5-count($allPhotos) ?> slot(s) remaining)</label>
      <input id="photoInput" type="file" name="photos[]" accept="image/jpeg,image/png" multiple class="visually-hidden">
      <div class="upload-area" onclick="document.getElementById('photoInput').click()">
        <div class="upload-icon">📷</div>
        <div id="uploadLabel">Click to upload photos</div>
        <div id="photoPreviewGrid" class="photo-preview-grid"></div>
      </div>
    </div>
    <?php endif; ?>

    <div class="flex-between">
      <a class="btn btn-outline" href="<?= BASE_URL ?>/my-report.php">Cancel</a>
      <button class="btn btn-primary">Save Changes</button>
    </div>
  </form>
  </div>
</div>
<?php
// FIXED: Removed duplicate previewPhotos() function — app.js already handles it.
// The photoInput element triggers previewPhotos() via the centralised handler in app.js.
?>
<?php include __DIR__.'/includes/footer.php'; ?>

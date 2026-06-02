<?php require_once __DIR__.'/includes/auth-check.php';
$uid = $_SESSION['user_id'];

if (isset($_GET['del_att'])) {
    if (!isset($_GET['csrf']) || !hash_equals($_SESSION['csrf']??'', $_GET['csrf'])) die('Invalid token');
    $aid = (int)$_GET['del_att'];
    $rid = (int)($_GET['id'] ?? 0);
    // Make sure attachment belongs to a report owned by this user
    $chk = $pdo->prepare('SELECT ra.filename FROM report_attachments ra JOIN reports r ON r.id=ra.report_id WHERE ra.id=? AND r.user_id=? AND r.status="Pending"');
    $chk->execute([$aid,$uid]); $att=$chk->fetch();
    if ($att) {
        @unlink(__DIR__.'/assets/uploads/'.$att['filename']);
        $pdo->prepare('DELETE FROM report_attachments WHERE id=?')->execute([$aid]);
    }
    redirect('edit-report.php?id='.$rid);
}

if (isset($_GET['delete'])) {
    if (!isset($_GET['csrf']) || !hash_equals($_SESSION['csrf']??'', $_GET['csrf'])) die('Invalid token');
    $id = (int)$_GET['delete'];
    $s = $pdo->prepare('SELECT * FROM reports WHERE id=? AND user_id=?');
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
$s = $pdo->prepare('SELECT * FROM reports WHERE id=? AND user_id=?');
$s->execute([$id,$uid]); $rep = $s->fetch();
if (!$rep) { flash('error','Report not found.'); redirect('my-report.php'); }
if ($rep['status']!=='Pending') { flash('error','Only Pending reports can be edited.'); redirect('report-details.php?id='.$id); }

$attStmt = $pdo->prepare('SELECT * FROM report_attachments WHERE report_id=? ORDER BY id ASC');
$attStmt->execute([$id]); $attachments = $attStmt->fetchAll();

$categories = ['Electrical','Plumbing','Furniture','Cleanliness','Safety','Network/IT','Building','Other'];
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $cat   = trim($_POST['category'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $loc   = trim($_POST['location'] ?? '');
    if (strlen($title)<3)              $err='Title required.';
    elseif (!in_array($cat,$categories,true)) $err='Invalid category.';
    elseif (strlen($desc)<10)          $err='Description too short.';
    elseif (strlen($loc)<2)            $err='Location required.';

     $newFiles = [];
    if (!$err && !empty($_FILES['photos']['name'][0])) {
        // Count existing attachments (cover + attachments table)
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

        // Save new files as attachments
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
<div class="container" style="max-width:720px;">
  <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
  <div class="card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="form-group"><label class="form-label">Title</label><input class="form-control" name="title" required value="<?= e($rep['title']) ?>"></div>
    <div class="form-group"><label class="form-label">Category</label>
      <select class="form-control" name="category" required>
        <?php foreach($categories as $c): ?>
          <option <?= $rep['category']===$c?'selected':'' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Location</label><input class="form-control" name="location" required value="<?= e($rep['location']) ?>"></div>
    <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" required><?= e($rep['description']) ?></textarea></div>

    <?php
      // Collect all current photos: cover first, then attachments
      $allPhotos = [];
      if ($rep['photo']) $allPhotos[] = ['src'=>$rep['photo'], 'id'=>null, 'isCover'=>true];
      foreach ($attachments as $a) $allPhotos[] = ['src'=>$a['filename'], 'id'=>$a['id'], 'isCover'=>false];
    ?>
    <?php if($allPhotos): ?>
    <div class="form-group">
      <label class="form-label">Current Photos</label>
      <div style="display:flex; flex-wrap:wrap; gap:.75rem; margin-top:.5rem;">
        <?php foreach($allPhotos as $ph): ?>
        <div style="position:relative; width:100px;">
          <img src="<?= BASE_URL ?>/assets/uploads/<?= e($ph['src']) ?>"
               style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:2px solid var(--border);">
          <?php if($ph['isCover']): ?>
            <span style="position:absolute;bottom:4px;left:0;right:0;text-align:center;font-size:.65rem;background:rgba(0,0,0,.6);color:#fff;border-radius:0 0 6px 6px;">Cover</span>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/edit-report.php?id=<?= (int)$id ?>&del_att=<?= (int)$ph['id'] ?>&csrf=<?= e(csrf_token()) ?>"
               onclick="return confirm('Remove this photo?')"

           style="position:absolute;top:2px;right:2px;background:rgba(220,38,38,.85);color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:.75rem;text-decoration:none;">✕</a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="text-muted" style="font-size:.8rem;margin-top:.4rem;">The cover photo cannot be removed (it's part of the original submission). You can remove extra photos individually.</p>
    </div>
    <?php endif; ?>
    <?php if(count($allPhotos) < 5): ?>
    <div class="form-group">
      <label class="form-label">Add More Photos (<?= 5-count($allPhotos) ?> slot(s) remaining)</label>
      <input id="photoInput" type="file" name="photos[]" accept="image/jpeg,image/png" multiple style="display:none;" onchange="previewPhotos(this)">
      <div class="upload-area" onclick="document.getElementById('photoInput').click()">
        <div class="upload-icon">📷</div>
        <div id="uploadLabel">Click to upload photos</div>
        <div id="photoPreviewGrid" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem;justify-content:center;"></div>
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
<script>
function previewPhotos(input) {
  const grid = document.getElementById('photoPreviewGrid');
  const label = document.getElementById('uploadLabel');
  grid.innerHTML = '';
  const files = Array.from(input.files);
  if (files.length) {
    label.textContent = files.length + ' new photo(s) selected';
    files.forEach(file => {
      const img = document.createElement('img');
      img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:2px solid var(--border)';
      const reader = new FileReader();
      reader.onload = e => img.src = e.target.result;
      reader.readAsDataURL(file);
      grid.appendChild(img);
    });
  } else {
    label.textContent = 'Click to choose photos';
  }
}
</script>
<?php include __DIR__.'/includes/footer.php'; ?>

               

<?php require_once __DIR__ . '/includes/auth-check.php'; ?>
$err='';
$categories =
    [
        'Electrical',
        'Plumbing',
        'Structural',
        'Cleanliness',
        'Safety Hazard',
        'IT / Network',
        'Landscaping',
        'Other',
    ];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crsf_check();
    $title = trim($_POST['title'] ?? '');
    $cat = $_POST['category'] ?? '';
    $desc = trim($_POST['description'] ?? '');
    $loc = trim($_POST['location'] ?? '');

    if (strlen($title)<3 || strlen($title)>150) $err='Title 3-150 chars.';
        else if (!in_array($cat, $categories, true)) $err='Invalid category.';
        else if (strlen($desc)<10 || strlen($desc)>1000) $err='Description 10-1000 chars.';
        else if (strlen($loc)<2 || strlen($loc)>200) $err='Location 2-200 chars.';

        $validatedFiles =[];
        if (!$err && !empty($_FILES['photos']['name'][0])){
            foreach ($_FILES['photos']['name'] as $i => $fname){
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) { $err='One or more uploads failed.'; break;}
                if ($_FILES['photos']['size'][$i] > 5*1024*1024) { $err='Each file must be under 5MB.'; break;}
                $info = getimagesize($_FILES['photos']['tmp_name'][$i]);
                if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png'])) { $err='JPG/PNG only.'; break;}
                $ext = $info['mime']==='image/png' ? 'png' : 'jpg';
                $validatedFiles[] = ['tmp' => $_FILES['photos']['tmp_name'][$i], 'ext' => $ext];
            }
        }

        if (!$err){
            $savedNames = [];
            foreach ($validatedFiles as $vf){
                $name = 'rep_'.time().'_'.bin2hex(random_bytes(16)).'.'.$vf['ext'];
                $dest = __DIR__.'/assets/uploads/'.$name;
                if (!move_uploaded_file($vf['tmp'], $dest)) {
                    $err = 'Could not save upload.';
                    break;
                }
                $savedNames[] = $name;
            }
        }

        if (!$err){
            $coverPhoto = $savedNames[0] ?? null;

            $ins = $pdo->prepare('INSERT INTO reports (user_id, title, category, description, location, cover_photo) VALUES (?, ?, ?, ?, ?, ?,\"Pending\")');
            $ins->execute([$_SESSION['user_id'], $title, $cat, $desc, $loc, $coverPhoto]);
            $rid = $pdo->lastInsertId();

            $attStmt = $pdo->prepare('INSERT INTO report_attachments (report_id, filename) VALUES (?, ?)');
            foreach ($savedNames as $idx => $name){
                if ($idx === 0) continue;
                $attStmt->execute([$rid, $name]);
            }

            $pdo->prepare('INSERT INTO status_updates (report_id, report_id, status,remarks,changed_by_admin_id) VALUES (?, ?, ?)')->execute([$rid, 'Pending','Report submitted by user.']);
            flash('success', 'Report submitted successfully.');
            redirect('report-details.php?id=' . $rid);
        }
}
%PAGE_TITLE% = 'Submit Report';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="page-header"><h1>Submit a report</h1><p>Describe the issue and attach up to 5 photos if possible.</p></div>
<div class="container" style="max-width:720px;">
    <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
        <div class="card">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <div class="form-group"><label class="form-label">Title</label><input class="form-control" name="title" required maxlength="150" value="<?= e($_POST['title']??'') ?>"></div>
                <div class="form-group"><label class="form-label">Category</label>
                    <select class="form-control" name="category" required>
                        <option value="">-Select -</option>
                        <?php foreach ($categories as $c): ?>
                            <option <?= (($_POST['category']??'') === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Location</label><input class="form-control" name="location" required maxlength="200" placeholder="e.g. FTMK Block A, Level 2" value="<?= e($_POST['location']??'') ?>"></div>
    <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" required maxlength="2000"><?= e($_POST['description']??'') ?></textarea></div>
    <div class="form-group">
      <label class="form-label">Photos (JPG/PNG, max 5MB each, up to 5 photos)</label>
      <input id="photoInput" type="file" name="photos[]" accept="image/jpeg,image/png" multiple style="display:none;" onchange="previewPhotos(this)">
      <div class="upload-area" onclick="document.getElementById('photoInput').click()">
        <div class="upload-icon">📷</div>
        <div id="uploadLabel">Click to upload photos</div>
        <div id="photoPreviewGrid" style="display:flex; flex-wrap:wrap; gap:.5rem; margin-top:1rem; justify-content:center;"></div>
        </div>
    </div>
    <div class="flex-between">
      <a class="btn btn-outline" href="<?= BASE_URL ?>/my-report.php">Cancel</a>
      <button class="btn btn-primary">Submit Report</button>
    </div>
  </form>
  </div>
</div>
<script>
function previewPhotos(input) {
  const grid = document.getElementById('photoPreviewGrid');
  const label = document.getElementById('uploadLabel');
  grid.innerHTML = '';
  const files = Array.from(input.files).slice(0, 5);
  if (files.length) {
    label.textContent = files.length + ' photo(s) selected';
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

    
               
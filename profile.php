<?php require_once __DIR__.'/includes/auth-check.php';
$err=''; $u = $CURRENT_USER;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $name = trim($_POST['full_name'] ?? '');
    if (strlen($name)<2) $err='Name required.';
    else {
        $avatar = $u['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $f = $_FILES['avatar'];
            if ($f['size'] > 2*1024*1024) $err='Avatar max 2MB.';
            else {
                $info = getimagesize($f['tmp_name']);
                if (!$info || !in_array($info['mime'],['image/jpeg','image/png'])) $err='JPG/PNG only.';
                else {
                    $ext = $info['mime']==='image/png' ? 'png':'jpg';
                    $name2 = 'ava_'.$u['id'].'_'.time().'.'.$ext;
                    move_uploaded_file($f['tmp_name'], __DIR__.'/assets/uploads/'.$name2);
                    $avatar = $name2;
                }
            }
        }
        if (!$err) {
            $pdo->prepare('UPDATE users SET full_name=?, avatar=? WHERE id=?')->execute([$name,$avatar,$u['id']]);
            flash('success','Profile updated.');
            redirect('profile.php');
        }
    }
}
$PAGE_TITLE='Profile';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="profile-layout">
  <div class="profile-card">
    <div class="profile-banner"></div>
    <div class="profile-header">
      <div class="profile-ava-wrap">
        <div class="profile-ava">
          <?php if($u['avatar']): ?>
            <img src="<?= BASE_URL ?>/assets/uploads/<?= e($u['avatar']) ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
          <?php else: ?><?= strtoupper(substr($u['full_name'],0,1)) ?><?php endif; ?>
        </div>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?= e($u['full_name']) ?></div>
        <div class="profile-handle"><?= e($u['email']) ?></div>
      </div>
    </div>
    <div class="profile-content">
      <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Full Name</label><input class="form-control" name="full_name" value="<?= e($u['full_name']) ?>" required></div>
        <div class="form-group"><label class="form-label">Email (read-only)</label><input class="form-control" value="<?= e($u['email']) ?>" disabled></div>
        <div class="form-group"><label class="form-label">Avatar (JPG/PNG, max 2MB)</label><input type="file" class="form-control" name="avatar" accept="image/jpeg,image/png"></div>
        <button class="btn btn-primary">Save</button>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/settings.php">Change Password</a>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
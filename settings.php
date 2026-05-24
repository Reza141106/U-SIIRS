<?php require_once __DIR__.'/includes/auth-check.php';
$err=$ok='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $cur = $_POST['current'] ?? '';
    $pw  = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (!password_verify($cur, $CURRENT_USER['password_hash'])) $err='Current password incorrect.';
    elseif (strlen($pw)<8 || !preg_match('/[A-Z]/',$pw) || !preg_match('/[a-z]/',$pw) || !preg_match('/[0-9]/',$pw)) $err='New password must be 8+ chars, include upper, lower and digit.';
    elseif ($pw !== $pw2) $err='Passwords do not match.';
    else {
        $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($pw, PASSWORD_DEFAULT), $CURRENT_USER['id']]);
        flash('success','Password changed.');
        redirect('settings.php');
    }
}
$PAGE_TITLE='Settings';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="settings-layout">
  <aside class="settings-sidebar">
    <div class="settings-item active"><span class="settings-icon">🔒</span> Security</div>
    <a class="settings-item" href="<?= BASE_URL ?>/profile.php" style="text-decoration:none;"><span class="settings-icon">👤</span> Profile</a>
  </aside>
  <div class="settings-content">
    <div class="settings-card">
      <div class="settings-title">Change Password</div>
      <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post" style="max-width:420px;">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current" required></div>
        <div class="form-group"><label class="form-label">New Password</label><input type="password" class="form-control" name="password" required minlength="8"></div>
        <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" class="form-control" name="password2" required minlength="8"></div>
        <button class="btn btn-primary">Update Password</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
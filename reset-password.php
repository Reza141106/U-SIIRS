<?php require_once __DIR__.'/config/database.php';
$err = '';
$token = trim($_GET['token'] ?? '');

$s = $pdo->prepare('SELECT * FROM users WHERE reset_token=? AND reset_expires > NOW()');
$s->execute([$token]);
$u = $s->fetch();

if (!$u) {
    die('<p style="text-align:center;margin-top:3rem;">This reset link is invalid or has expired. <a href="'.BASE_URL.'/forgot-password.php">Request a new one</a>.</p>');
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $pw  = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (strlen($pw)<8 || !preg_match('/[A-Z]/',$pw) || !preg_match('/[a-z]/',$pw) || !preg_match('/[0-9]/',$pw))
        $err='Password must be 8+ chars, include upper, lower and digit.';
    elseif ($pw !== $pw2)
        $err='Passwords do not match.';
    else {
        $pdo->prepare('UPDATE users SET password_hash=?, reset_token=NULL, reset_expires=NULL WHERE id=?')
            ->execute([password_hash($pw, PASSWORD_DEFAULT), $u['id']]);
        flash('success', 'Password reset successful — please login.');
        redirect('login.php');
    }
}
$PAGE_TITLE = 'Reset Password';
include __DIR__.'/includes/header.php';
?>
<div class="auth-layout">
  <div class="auth-left">
    <div style="color:#fff; text-align:center;">
      <div style="font-family:'DM Serif Display',serif; font-size:2.5rem;">New Password</div>
      <p style="opacity:.85; margin-top:1rem;">Choose a strong password for your account.</p>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-box">
      <h1 class="auth-title">Reset Password</h1>
      <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" class="form-control" name="password" required minlength="8">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" name="password2" required minlength="8">
        </div>
        <button class="btn btn-primary" style="width:100%;">Reset Password</button>
      </form>
    </div>
  </div>
</div>
</div></body></html>
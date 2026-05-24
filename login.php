<?php require_once __DIR__.'/config/database.php';
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? '';
    $s = $pdo->prepare('SELECT * FROM users WHERE email=?');
    $s->execute([$email]);
    $u = $s->fetch();
    if (!$u || !password_verify($pw, $u['password_hash'])) $err='Invalid email or password.';
    elseif (!empty($u['is_banned'])) $err='Your account has been suspended.';
    else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        redirect('dashboard.php');
    }
}
$PAGE_TITLE='Login';
include __DIR__.'/includes/header.php';
?>
<div class="auth-layout">
  <div class="auth-left">
    <div style="color:#fff; text-align:center;">
      <div style="font-family:'DM Serif Display',serif; font-size:2.5rem;">Welcome back</div>
      <p style="opacity:.85; margin-top:1rem;">Sign in to continue reporting and tracking issues.</p>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-box">
      <h1 class="auth-title">Login</h1>
      <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <?php if($msg=flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
        <button class="btn btn-primary" style="width:100%;">Login</button>
      </form>
      <div class="auth-switch">No account? <a class="link" href="<?= BASE_URL ?>/register.php">Sign up</a></div>
      <div class="auth-switch"><a class="link" href="<?= BASE_URL ?>/forgot-password.php">Forgot password?</a></div>
      <div class="auth-switch text-muted">Admin? <a class="link" href="<?= BASE_URL ?>/admin/login.php">Admin login</a></div>
    </div>
  </div>
</div>
</div></body></html>
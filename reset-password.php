<?php require_once __DIR__.'/config/database.php';
$err = '';
$token = trim($_GET['token'] ?? '');

$s = $pdo->prepare('SELECT id, email FROM users WHERE reset_token=? AND reset_expires > NOW()');
$s->execute([$token]);
$u = $s->fetch();

if (!$u) {
    // FIXED: replaced die() + raw HTML with flash + redirect (re-evaluation issue)
    flash('error', 'This reset link is invalid or has expired. Please request a new one.');
    redirect('forgot-password.php');
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
    <div class="auth-shape" style="width:250px;height:250px;top:-60px;right:-60px;"></div>
    <div class="auth-left-content">
      <div class="auth-brand-badge">&#x1F512; Secure Reset</div>
      <div class="auth-brand-title">New Password</div>
      <p class="auth-brand-sub">Choose a strong password<br>for your account.</p>
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
          <div class="input-wrap">
            <input id="rp-pw1" type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password">
            <button type="button" class="pw-toggle" onclick="togglePw('rp-pw1', this)" aria-label="Show/hide password">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="form-hint">8+ chars, include upper, lower and a digit.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <div class="input-wrap">
            <input id="rp-pw2" type="password" class="form-control" name="password2" required minlength="8" autocomplete="new-password">
            <button type="button" class="pw-toggle" onclick="togglePw('rp-pw2', this)" aria-label="Show/hide password">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>
        <button class="btn btn-login btn-full">Reset Password</button>
      </form>
      <div class="auth-switch"><a class="link" href="<?= BASE_URL ?>/login.php">← Back to Login</a></div>
    </div>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?

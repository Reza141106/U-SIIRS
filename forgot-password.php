<?php require_once __DIR__.'/config/database.php';
$err = $ok = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $s = $pdo->prepare('SELECT id, full_name FROM users WHERE email=?');
    $s->execute([$email]);
    $u = $s->fetch();
    if ($u) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare('UPDATE users SET reset_token=?, reset_expires=? WHERE id=?')
            ->execute([$token, $expires, $u['id']]);
        $link = BASE_URL . '/reset-password.php?token=' . $token;
        require_once __DIR__.'/includes/mailer.php';
        $sent = send_reset_email($email, $u['full_name'], $link);
        if (!$sent) {
            $err = 'Failed to send email. Please try again later.';
        } else {
            $ok = 'If that email exists, a reset link has been sent to your UTeM inbox.';
        }
    } else {
        $ok = 'If that email exists, a reset link has been sent to your UTeM inbox.';
    }
}
$PAGE_TITLE = 'Forgot Password';
include __DIR__.'/includes/header.php';
?>
<div class="auth-layout">
  <div class="auth-left">
    <div style="color:#fff; text-align:center;">
      <div style="font-family:'DM Serif Display',serif; font-size:2.5rem;">Reset Password</div>
      <p style="opacity:.85; margin-top:1rem;">Enter your UTeM email to receive a reset link.</p>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-box">
      <h1 class="auth-title">Forgot Password</h1>
      <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <?php if($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group">
          <label class="form-label">UTeM Student Email</label>
          <input type="email" class="form-control" name="email" required placeholder="D032410372@student.utem.edu.my">
        </div>
        <button class="btn btn-primary" style="width:100%;">Send Reset Link</button>
      </form>
      <div class="auth-switch"><a class="link" href="<?= BASE_URL ?>/login.php">Back to Login</a></div>
    </div>
  </div>
</div>
</div></body></html>
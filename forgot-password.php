<?php require_once __DIR__.'/config/database.php';
$err = $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // --- Rate limit: max 3 reset emails per 15 minutes per IP ---
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'pw_reset_' . md5($ip);
    if (!isset($_SESSION[$key])) $_SESSION[$key] = ['count' => 0, 'first' => time()];
    $att = &$_SESSION[$key];
    if (time() - $att['first'] > 900) { $att['count'] = 0; $att['first'] = time(); }

    if ($att['count'] >= 3) {
        $wait = max(1, (int)ceil((900 - (time() - $att['first'])) / 60));
        $err  = "Too many requests. Please wait {$wait} minute(s) before trying again.";
    } else {
        $att['count']++;
        $email = strtolower(trim($_POST['email'] ?? ''));
        $s     = $pdo->prepare('SELECT id, full_name FROM users WHERE email=?');
        $s->execute([$email]);
        $u = $s->fetch();

        if ($u) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $pdo->prepare('UPDATE users SET reset_token=?, reset_expires=? WHERE id=?')
                ->execute([$token, $expires, $u['id']]);
            $link = BASE_URL . '/reset-password.php?token=' . $token;
            require_once __DIR__.'/includes/mailer.php';
            send_reset_email($email, $u['full_name'], $link);
        }
        // Always show the same message to prevent account enumeration
        $ok = 'If that email exists, a reset link has been sent to your UTeM inbox.';
    }
}

$PAGE_TITLE = 'Forgot Password';
include __DIR__.'/includes/header.php';
?>
<div class="auth-layout">
  <div class="auth-left">
    <div class="auth-shape" style="width:240px;height:240px;top:-50px;right:-60px;"></div>
    <div class="auth-left-content">
      <div class="auth-brand-badge">&#x1F511; Account Recovery</div>
      <div class="auth-brand-title">Reset Password</div>
      <p class="auth-brand-sub">Enter your UTeM email to receive<br>a secure reset link.</p>
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
          <input type="email" class="form-control" name="email" required placeholder="D032410372@student.utem.edu.my" autocomplete="email">
        </div>
        <button class="btn btn-login btn-full">Send Reset Link</button>
      </form>
      <div class="auth-switch"><a class="link" href="<?= BASE_URL ?>/login.php">← Back to Login</a></div>
    </div>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>

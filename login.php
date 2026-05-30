<?php
require_once __DIR__.'/config/database.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'login_attempts_' . md5($ip);
    if (!isset($_SESSION[$key])) $_SESSION[$key] = ['count' => 0, 'first' => time()];
    $att = &$_SESSION[$key];

    if (time() - $att['first'] > 900) { $att['count'] = 0; $att['first'] = time(); }

    if ($att['count'] >= 5) {
        $wait = max(1, (int)ceil((900 - (time() - $att['first'])) / 60));
        $err = "Too many failed attempts. Please wait {$wait} minute(s) before trying again.";
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pw    = $_POST['password'] ?? '';
        $s     = $pdo->prepare('SELECT id, full_name, email, password_hash, is_banned FROM users WHERE email=?');
        $s->execute([$email]);
        $u = $s->fetch();

        if (!$u || !password_verify($pw, $u['password_hash'])) {
            $att['count']++;
            $err = 'Invalid email or password.';
        } elseif (!empty($u['is_banned'])) {
            $err = 'Your account has been suspended.';
        } else {
            unset($_SESSION[$key]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $u['id'];

            // IMPROVEMENT: Track last login timestamp and IP
            $pdo->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?')
                ->execute([$_SERVER['REMOTE_ADDR'] ?? null, $u['id']]);

            redirect('dashboard.php');
        }
    }
}
$PAGE_TITLE = 'Login';
include __DIR__.'/includes/header.php';
?>
<div class="auth-layout">
  <div class="auth-left">
    <div class="auth-shape" style="width:280px;height:280px;top:-60px;left:-80px;"></div>
    <div class="auth-shape" style="width:160px;height:160px;bottom:80px;right:-40px;animation:auth-float 7s ease-in-out infinite;"></div>
    <div class="auth-shape" style="width:90px;height:90px;top:45%;left:15%;animation:auth-float 9s ease-in-out infinite reverse;"></div>
    <div class="auth-left-content">
      <div class="auth-brand-badge">&#x1F3DB; U-SIIRS Portal</div>
      <div class="auth-brand-title">Welcome back</div>
      <p class="auth-brand-sub">Sign in to continue reporting<br>and tracking campus issues.</p>
      <ul class="auth-features">
        <li><span class="auth-feat-icon">&#x1F4CB;</span> Submit and track issue reports</li>
        <li><span class="auth-feat-icon">&#x1F514;</span> Real-time status notifications</li>
        <li><span class="auth-feat-icon">&#x1F512;</span> Secure UTeM student access</li>
        <li><span class="auth-feat-icon">&#x1F4CA;</span> View your report history</li>
      </ul>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-box">
      <h1 class="auth-title">Login</h1>
      <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <?php if($msg=flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
      <form method="post" id="loginForm">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input id="email" type="email" class="form-control" name="email" required
                 autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="pwField">Password</label>
          <div class="input-wrap">
            <input id="pwField" type="password" class="form-control" name="password" required
                   autocomplete="current-password">
            <button type="button" class="pw-toggle" onclick="togglePw('pwField', this)" aria-label="Show or hide password">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>
        <button id="loginBtn" class="btn btn-login" type="submit">Login</button>
      </form>
      <div class="auth-switch">No account? <a class="link" href="<?= BASE_URL ?>/register.php">Sign up</a></div>
      <div class="auth-switch"><a class="link" href="<?= BASE_URL ?>/forgot-password.php">Forgot password?</a></div>
      <div class="auth-admin-divider"><span class="auth-admin-divider-label">Administrator Access</span></div>
      <div class="admin-access-card">
        <div class="admin-access-icon">
          <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
        </div>
        <div class="admin-access-text">
          <div class="admin-access-label">Admin Portal <span class="admin-badge">Staff Only</span></div>
          <div class="admin-access-desc">Manage reports, users &amp; system settings.</div>
        </div>
        <a class="btn-admin-access" href="<?= BASE_URL ?>/admin/login.php">Login
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  var form = document.getElementById('loginForm');
  if (form) {
    form.addEventListener('submit', function () {
      var btn = document.getElementById('loginBtn');
      if (btn) { btn.classList.add('loading'); btn.disabled = true; }
    });
  }
}());
</script>
<?php include __DIR__.'/includes/footer.php'; ?>

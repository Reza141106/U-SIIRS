<?php
/**
 * admin/login.php
 * Admin authentication page — redesigned with premium UI.
 * IMPROVEMENT: Brute-force rate limiting (matches user login.php pattern).
 * IMPROVEMENT: Inline styles replaced with CSS classes.
 * IMPROVEMENT: Premium admin login layout with distinct visual identity.
 */
require_once __DIR__.'/../config/database.php';

// Redirect already-logged-in admins
if (!empty($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // ── Rate limiting: 5 attempts per 15 minutes ──────────────────────────────
    $now      = time();
    $window   = 15 * 60;
    $maxTries = 5;
    $rl       = &$_SESSION['admin_login_rl'];

    if (!empty($rl['attempts'])) {
        $rl['attempts'] = array_filter($rl['attempts'], fn($t) => ($now - $t) < $window);
    }

    if (!empty($rl['attempts']) && count($rl['attempts']) >= $maxTries) {
        $oldest    = min($rl['attempts']);
        $remaining = ceil(($window - ($now - $oldest)) / 60);
        $err = "Too many failed attempts. Please wait $remaining minute(s).";
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pw    = $_POST['password'] ?? '';

        // ── UTeM domain restriction ───────────────────────────────────────────
        if (!str_ends_with($email, '@utem.edu.my')) {
            $rl['attempts'][] = $now;
            $err = 'Admin access is restricted to @utem.edu.my email addresses only.';
        } else {
            $s = $pdo->prepare('SELECT id, password_hash FROM admins WHERE email = ?');
            $s->execute([$email]);
            $a = $s->fetch();

            if (!$a || !password_verify($pw, $a['password_hash'])) {
                $rl['attempts'][] = $now;
                $err = 'Invalid admin credentials.';
            } else {
                unset($_SESSION['admin_login_rl']);
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $a['id'];
                redirect('admin/dashboard.php');
            }
        }
    }
}

$PAGE_TITLE = 'Admin Login';
include __DIR__.'/../includes/header.php';
?>
<div class="auth-layout admin-login-layout">

  <!-- ── LEFT: ADMIN BRANDING PANEL ── -->
  <div class="auth-left admin-login-left">
    <div class="auth-shape" style="width:320px;height:320px;top:-90px;right:-90px;"></div>
    <div class="auth-shape" style="width:180px;height:180px;bottom:60px;left:-50px;animation:auth-float 11s ease-in-out infinite;"></div>
    <div class="auth-shape" style="width:80px;height:80px;top:38%;left:12%;animation:auth-float 7s ease-in-out infinite reverse;"></div>

    <div class="auth-left-content">
      <div class="auth-brand-badge">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="opacity:.9;">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0110 0v4"/>
        </svg>
        U-SIIRS Admin Console
      </div>

      <div class="auth-brand-title">Admin Portal</div>
      <p class="auth-brand-sub">Authorized personnel only.<br>Manage reports, users &amp; settings.</p>

      <ul class="auth-features">
        <li>
          <span class="auth-feat-icon">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </span>
          Review &amp; manage all reports
        </li>
        <li>
          <span class="auth-feat-icon">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          </span>
          Manage users &amp; access control
        </li>
        <li>
          <span class="auth-feat-icon">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          </span>
          Dashboard analytics &amp; reports
        </li>
        <li>
          <span class="auth-feat-icon">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>
          </span>
          System settings &amp; configuration
        </li>
      </ul>
    </div>

    <div class="admin-security-notice">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;opacity:.7;">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
      Secured with CSRF protection &amp; rate limiting
    </div>
  </div>

  <!-- ── RIGHT: ADMIN LOGIN FORM ── -->
  <div class="auth-right admin-login-right">
    <div class="auth-box">

      <div class="admin-login-header">
        <div class="admin-login-icon">
          <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
        </div>
        <div>
          <h1 class="auth-title" style="margin-bottom:0.15rem;">Admin Login</h1>
          <p class="admin-login-subtitle">U-SIIRS Management Console</p>
        </div>
      </div>

      <?php if ($err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off" id="adminLoginForm">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group">
          <label class="form-label" for="admin-email">Admin Email</label>
          <input id="admin-email"
                 type="email"
                 class="form-control"
                 name="email"
                 required
                 autocomplete="off"
                 placeholder="admin@utem.edu.my">
        </div>
        <div class="form-group">
          <label class="form-label" for="admin-pw">Password</label>
          <div class="input-wrap">
            <input id="admin-pw"
                   type="password"
                   class="form-control"
                   name="password"
                   required
                   autocomplete="off"
                   placeholder="••••••••">
            <button type="button"
                    class="pw-toggle"
                    onclick="togglePw('admin-pw', this)"
                    aria-label="Show/hide password">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <button id="adminLoginBtn" class="btn btn-login btn-full" type="submit">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:.3rem;">
            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
          </svg>
          Sign in to Admin Console
        </button>
      </form>

      <div class="auth-switch" style="margin-top:1.5rem;">
        <a class="link" href="<?= BASE_URL ?>/login.php">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:-1px;margin-right:.25rem;"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Back to Student Login
        </a>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('adminLoginForm');
  if (form) {
    form.addEventListener('submit', function () {
      var btn = document.getElementById('adminLoginBtn');
      if (btn) { btn.classList.add('loading'); btn.disabled = true; }
    });
  }
})();
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>
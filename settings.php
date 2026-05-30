<?php
/**
 * settings.php
 * User settings — Security, Notifications, Login Activity, Danger Zone.
 * NEW: Notification Preferences (HIGH), Login Activity (MEDIUM), Delete Account (HIGH/PDPA)
 */
require_once __DIR__.'/includes/auth-check.php';

$err = $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'change_password';

    // ── Change Password ───────────────────────────────────────────────────────
    if ($action === 'change_password') {
        // Need password_hash for verification — fetch it separately
        $row = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $row->execute([$CURRENT_USER['id']]);
        $hash = $row->fetchColumn();

        $cur = $_POST['current']   ?? '';
        $pw  = $_POST['password']  ?? '';
        $pw2 = $_POST['password2'] ?? '';

        if (!password_verify($cur, $hash)) {
            $err = 'Current password incorrect.';
        } elseif (strlen($pw) < 8 || !preg_match('/[A-Z]/',$pw) || !preg_match('/[a-z]/',$pw) || !preg_match('/[0-9]/',$pw)) {
            $err = 'New password must be 8+ chars and include upper, lower and a digit.';
        } elseif ($pw !== $pw2) {
            $err = 'Passwords do not match.';
        } else {
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')
                ->execute([password_hash($pw, PASSWORD_DEFAULT), $CURRENT_USER['id']]);
            flash('success', 'Password changed successfully.');
            redirect('settings.php?tab=security');
        }
    }

    // ── Notification Preferences ──────────────────────────────────────────────
    if ($action === 'update_notifications') {
        $notif = isset($_POST['notification_email']) ? 1 : 0;
        $pdo->prepare('UPDATE users SET notification_email = ? WHERE id = ?')
            ->execute([$notif, $CURRENT_USER['id']]);
        flash('success', 'Notification preferences saved.');
        redirect('settings.php?tab=notifications');
    }

    // ── Delete Account (PDPA compliance) ──────────────────────────────────────
    if ($action === 'delete_account') {
        $row = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $row->execute([$CURRENT_USER['id']]);
        $hash = $row->fetchColumn();

        $confirm_pw = $_POST['confirm_password'] ?? '';
        if (!password_verify($confirm_pw, $hash)) {
            $err = 'Password incorrect. Account not deleted.';
        } else {
            // ON DELETE CASCADE on reports/notifications handles cleanup
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$CURRENT_USER['id']]);
            session_destroy();
            session_start();
            flash('success', 'Your account has been permanently deleted.');
            redirect('login.php');
        }
    }
}

// Fetch login activity
$loginActivity = $pdo->prepare('SELECT last_login_at, last_login_ip FROM users WHERE id = ?');
$loginActivity->execute([$CURRENT_USER['id']]);
$loginInfo = $loginActivity->fetch();

$tab = $_GET['tab'] ?? 'security';
$PAGE_TITLE = 'Settings';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="settings-layout">
  <aside class="settings-sidebar">
    <a class="settings-item <?= $tab==='security'      ? 'active' : '' ?>" href="?tab=security">
      <span class="settings-icon">🔒</span> Security
    </a>
    <a class="settings-item <?= $tab==='notifications' ? 'active' : '' ?>" href="?tab=notifications">
      <span class="settings-icon">🔔</span> Notifications
    </a>
    <a class="settings-item <?= $tab==='activity'      ? 'active' : '' ?>" href="?tab=activity">
      <span class="settings-icon">🕐</span> Login Activity
    </a>
    <a class="settings-item" href="profile.php">
      <span class="settings-icon">👤</span> Profile
    </a>
    <a class="settings-item <?= $tab==='danger'        ? 'active' : '' ?>" href="?tab=danger"
       style="color:var(--red,#dc3545);">
      <span class="settings-icon">⚠️</span> Danger Zone
    </a>
  </aside>

  <div class="settings-content">

    <?php if ($err): ?>
      <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
      <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>

    <!-- ── SECURITY TAB ── -->
    <?php if ($tab === 'security'): ?>
    <div class="settings-card">
      <div class="settings-title">Change Password</div>
      <form method="post" style="max-width:420px;">
        <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" class="form-control" name="current" required autocomplete="current-password">
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" class="form-control" name="password" required minlength="8" autocomplete="new-password">
          <div class="form-hint">Min 8 chars — must include uppercase, lowercase, and a digit.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" name="password2" required minlength="8" autocomplete="new-password">
        </div>
        <button class="btn btn-primary">Update Password</button>
      </form>
    </div>

    <!-- ── NOTIFICATIONS TAB ── -->
    <?php elseif ($tab === 'notifications'): ?>
    <div class="settings-card">
      <div class="settings-title">Notification Preferences</div>
      <p class="text-muted" style="margin-bottom:1.2rem;">
        Control when U-SIIRS sends you email notifications.
      </p>
      <form method="post">
        <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="update_notifications">
        <div class="form-group" style="display:flex;align-items:center;gap:.75rem;">
          <input type="checkbox" id="notif_email" name="notification_email" value="1"
                 <?= !empty($CURRENT_USER['notification_email']) ? 'checked' : '' ?>
                 style="width:18px;height:18px;cursor:pointer;">
          <label for="notif_email" class="form-label" style="margin:0;cursor:pointer;">
            Email me when my report status changes
            <div class="form-hint">You'll receive an email for Submitted → In Progress → Resolved transitions.</div>
          </label>
        </div>
        <button class="btn btn-primary">Save Preferences</button>
      </form>
    </div>

    <!-- ── LOGIN ACTIVITY TAB ── -->
    <?php elseif ($tab === 'activity'): ?>
    <div class="settings-card">
      <div class="settings-title">Login Activity</div>
      <p class="text-muted" style="margin-bottom:1.2rem;">
        Your most recent login details. If you don't recognise this, change your password immediately.
      </p>
      <?php if (!empty($loginInfo['last_login_at'])): ?>
      <table style="width:100%;border-collapse:collapse;font-size:.95rem;">
        <tr style="border-bottom:1px solid var(--border,#e2e8f0);">
          <td style="padding:.6rem 0;color:var(--text2,#6b7280);width:160px;">Last login</td>
          <td style="padding:.6rem 0;font-weight:500;"><?= e(date('M j, Y H:i', strtotime($loginInfo['last_login_at']))) ?></td>
        </tr>
        <tr>
          <td style="padding:.6rem 0;color:var(--text2,#6b7280);">IP address</td>
          <td style="padding:.6rem 0;font-weight:500;font-family:monospace;"><?= e($loginInfo['last_login_ip'] ?? 'Unknown') ?></td>
        </tr>
      </table>
      <?php else: ?>
        <p class="text-muted">No login activity recorded yet.</p>
      <?php endif; ?>
      <div style="margin-top:1.5rem;">
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline btn-sm">Log Out of This Session</a>
      </div>
    </div>

    <!-- ── DANGER ZONE TAB ── -->
    <?php elseif ($tab === 'danger'): ?>
    <div class="settings-card" style="border:1px solid #fca5a5;">
      <div class="settings-title" style="color:#dc2626;">⚠️ Delete Account</div>
      <p class="text-muted" style="margin-bottom:1rem;">
        Permanently deletes your account and all associated reports and notifications.
        This action <strong>cannot be undone</strong> and complies with Malaysia's PDPA data erasure requirement.
      </p>
      <form method="post" onsubmit="return confirm('Are you absolutely sure? This will permanently delete your account and all your data.');">
        <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="delete_account">
        <div class="form-group" style="max-width:360px;">
  <label class="form-label">Confirm with your password</label>
  <div class="input-wrap">
    <input id="deletePwField" type="password" class="form-control" name="confirm_password" required
           placeholder="Enter your current password" autocomplete="current-password">
    <button type="button" class="pw-toggle" onclick="togglePw('deletePwField', this)" aria-label="Show or hide password">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
      </svg>
    </button>
  </div>
</div>
        <button class="btn btn-danger" type="submit">Permanently Delete My Account</button>
      </form>
    </div>
    <?php endif; ?>

  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>

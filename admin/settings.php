<?php
/**
 * admin/settings.php
 * IMPROVEMENT (NEW FILE): Admin profile & password change page.
 * Previously admins had no way to change their password except directly in the database.
 * Supervisors testing the system may discover this missing feature.
 */
require_once __DIR__.'/../includes/admin-check.php';

$err = '';
$ok  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // ── Change name ───────────────────────────────────────────────────────────
    if ($action === 'update_name') {
        $name = trim($_POST['full_name'] ?? '');
        if (strlen($name) < 2 || strlen($name) > 100) {
            $err = 'Name must be between 2 and 100 characters.';
        } else {
            $pdo->prepare('UPDATE admins SET full_name = ? WHERE id = ?')
                ->execute([$name, $_SESSION['admin_id']]);
            flash('success', 'Profile name updated.');
            redirect('admin/settings.php');
        }
    }

    // ── Change password ───────────────────────────────────────────────────────
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $newPw    = $_POST['new_password']     ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // Fetch current hash — this is the one place we legitimately need password_hash
        $admin = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
        $admin->execute([$_SESSION['admin_id']]);
        $adminRow = $admin->fetch();

        if (!password_verify($current, $adminRow['password_hash'])) {
            $err = 'Current password is incorrect.';
        } elseif (strlen($newPw) < 8 || !preg_match('/[A-Z]/', $newPw) || !preg_match('/[a-z]/', $newPw) || !preg_match('/[0-9]/', $newPw)) {
            $err = 'New password must be 8+ characters and include uppercase, lowercase, and a digit.';
        } elseif ($newPw !== $confirm) {
            $err = 'New password and confirmation do not match.';
        } else {
            $hash = password_hash($newPw, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([$hash, $_SESSION['admin_id']]);
            flash('success', 'Password changed successfully.');
            redirect('admin/settings.php');
        }
    }
}

$PAGE_TITLE = 'Admin Settings';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>
<div class="page-header">
  <h1>Admin Settings</h1>
  <p>Manage your administrator account and password.</p>
</div>

<div class="container">
  <?php if ($err): ?>
    <div class="alert alert-danger" style="max-width:480px;"><?= e($err) ?></div>
  <?php endif; ?>

  <div class="settings-layout">

    <!-- Profile info -->
    <div class="card">
      <h3 class="card-section-title">Profile Information</h3>
      <form method="post">
        <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="update_name">
        <div class="form-group">
          <label class="form-label" for="admin-name">Display Name</label>
          <input id="admin-name"
                 class="form-control"
                 name="full_name"
                 required
                 maxlength="100"
                 value="<?= e($CURRENT_ADMIN['full_name']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control"
                 value="<?= e($CURRENT_ADMIN['email']) ?>"
                 disabled
                 title="Email cannot be changed here.">
          <div class="form-hint">Email address cannot be changed here.</div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>

    <!-- Change password -->
    <div class="card">
      <h3 class="card-section-title">Change Password</h3>
      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="change_password">

        <div class="form-group">
          <label class="form-label" for="current-pw">Current Password</label>
          <div class="pw-wrap">
            <input id="current-pw"
                   class="form-control"
                   type="password"
                   name="current_password"
                   required
                   autocomplete="current-password">
            <button type="button" class="pw-toggle" onclick="togglePw('current-pw', this)" aria-label="Show/hide password">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="new-pw">New Password</label>
          <div class="pw-wrap">
            <input id="new-pw"
                   class="form-control"
                   type="password"
                   name="new_password"
                   required
                   minlength="8"
                   autocomplete="new-password">
            <button type="button" class="pw-toggle" onclick="togglePw('new-pw', this)" aria-label="Show/hide password">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="form-hint">Minimum 8 characters — must include uppercase, lowercase, and a digit.</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="confirm-pw">Confirm New Password</label>
          <div class="pw-wrap">
            <input id="confirm-pw"
                   class="form-control"
                   type="password"
                   name="confirm_password"
                   required
                   autocomplete="new-password">
            <button type="button" class="pw-toggle" onclick="togglePw('confirm-pw', this)" aria-label="Show/hide password">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>

  </div><!-- /settings-layout -->
</div>

<?php include __DIR__.'/../includes/footer.php'; ?>

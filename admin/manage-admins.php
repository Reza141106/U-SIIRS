<?php
/**
 * admin/manage-admins.php
 * NEW: Manage Admin Accounts (super_admin only).
 * Priority MEDIUM — no UI previously existed to add/deactivate admins.
 */
require_once __DIR__.'/../includes/admin-check.php';
require_super_admin(); // Only super_admin can access this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name  = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pw    = $_POST['password'] ?? '';
        $role  = $_POST['role'] === 'super_admin' ? 'super_admin' : 'admin';

        $errors = [];
        if (strlen($name) < 2)  $errors[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (!str_ends_with($email, '@utem.edu.my')) $errors[] = 'Admin email must end with @utem.edu.my.';
        if (strlen($pw) < 8)    $errors[] = 'Password must be at least 8 characters.';

        // Check email unique
        $check = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'An admin with that email already exists.';

        if (empty($errors)) {
            $pdo->prepare(
                'INSERT INTO admins (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)'
            )->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT), $role]);

            $newId = (int)$pdo->lastInsertId();
            $pdo->prepare(
                'INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)'
            )->execute([$_SESSION['admin_id'], 'create_admin', 'admin', $newId, "Created admin account for {$name} ({$email}) with role {$role}"]);

            flash('success', "Admin account created for {$name}.");
            redirect('admin/manage-admins.php');
        } else {
            $createErr = implode(' ', $errors);
        }
    }

    if ($action === 'deactivate') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$_SESSION['admin_id']) {
            flash('error', 'You cannot deactivate your own account.');
        } else {
            $pdo->prepare("UPDATE admins SET role = 'deactivated' WHERE id = ?")->execute([$id]);
            $pdo->prepare(
                'INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)'
            )->execute([$_SESSION['admin_id'], 'deactivate_admin', 'admin', $id, "Admin account deactivated"]);
            flash('success', 'Admin account deactivated.');
        }
        redirect('admin/manage-admins.php');
    }

    if ($action === 'reset_password') {
        $id    = (int)($_POST['id'] ?? 0);
        $newPw = $_POST['new_password'] ?? '';
        if (strlen($newPw) < 8) {
            flash('error', 'Password must be at least 8 characters.');
        } else {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($newPw, PASSWORD_DEFAULT), $id]);
            $pdo->prepare(
                'INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)'
            )->execute([$_SESSION['admin_id'], 'reset_admin_password', 'admin', $id, "Password reset by super_admin"]);
            flash('success', 'Password reset successfully.');
        }
        redirect('admin/manage-admins.php');
    }
}

$admins = $pdo->query(
    "SELECT id, full_name, email, role, created_at FROM admins ORDER BY created_at DESC"
)->fetchAll();

$PAGE_TITLE = 'Manage Admins';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>
<div class="page-header">
  <h1>Manage Admin Accounts</h1>
  <p>Create, deactivate, or reset passwords for admin accounts. Super Admin access required.</p>
</div>
<div class="container">

  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger"><?= e($msg) ?></div>
  <?php endif; ?>

  <!-- Create Admin Form -->
  <div class="card" style="max-width:520px;margin-bottom:2rem;">
    <h3 class="card-section-title">Create New Admin</h3>
    <?php if (!empty($createErr)): ?>
      <div class="alert alert-danger"><?= e($createErr) ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input class="form-control" name="full_name" required maxlength="100">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required placeholder="name@utem.edu.my">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div style="position:relative;display:flex;align-items:center;">
          <input type="password" class="form-control" name="password" id="create-admin-pw" required minlength="8" style="padding-right:2.8rem;">
          <button type="button"
                  onclick="togglePw('create-admin-pw', this)"
                  style="position:absolute;right:.6rem;background:none;border:none;cursor:pointer;color:var(--text-muted,#6b7280);padding:0;line-height:1;"
                  title="Show/hide password"
                  aria-label="Toggle password visibility">
            <svg id="create-admin-pw-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="form-hint">Minimum 8 characters.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select class="form-control" name="role">
          <option value="admin">Admin</option>
          <option value="super_admin">Super Admin</option>
        </select>
      </div>
      <button class="btn btn-primary">Create Admin</button>
    </form>
  </div>

  <!-- Admin List -->
  <div class="table-wrapper">
    <div class="table-scroll">
      <table>
        <thead>
          <tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
          <tr>
            <td class="fw-500"><?= e($a['full_name']) ?></td>
            <td><?= e($a['email']) ?></td>
            <td>
              <?php if ($a['role'] === 'super_admin'): ?>
                <span class="badge badge-info">Super Admin</span>
              <?php elseif ($a['role'] === 'deactivated'): ?>
                <span class="badge badge-danger">Deactivated</span>
              <?php else: ?>
                <span class="badge badge-neutral">Admin</span>
              <?php endif; ?>
            </td>
            <td class="text-muted-cell"><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
            <td class="table-actions">
              <?php if ($a['id'] !== (int)$_SESSION['admin_id'] && $a['role'] !== 'deactivated'): ?>
                <form method="post" class="form-inline">
                  <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="deactivate">
                  <input type="hidden" name="id"     value="<?= (int)$a['id'] ?>">
                  <button class="btn btn-sm btn-outline"
                          data-confirm="Deactivate this admin account?">Deactivate</button>
                </form>
              <?php endif; ?>
              <details style="display:inline-block;">
                <summary class="btn btn-sm btn-outline" style="cursor:pointer;list-style:none;">Reset PW</summary>
                <form method="post" style="margin-top:.5rem;background:var(--surface2,#f8fafc);padding:.75rem;border-radius:6px;min-width:220px;">
                  <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="reset_password">
                  <input type="hidden" name="id"     value="<?= (int)$a['id'] ?>">
                  <div style="position:relative;display:flex;align-items:center;margin-bottom:.5rem;">
                    <input type="password" class="form-control" name="new_password" id="reset-pw-<?= (int)$a['id'] ?>"
                           placeholder="New password" required minlength="8" style="padding-right:2.8rem;">
                    <button type="button"
                            onclick="togglePw('reset-pw-<?= (int)$a['id'] ?>', this)"
                            style="position:absolute;right:.6rem;background:none;border:none;cursor:pointer;color:var(--text-muted,#6b7280);padding:0;line-height:1;"
                            title="Show/hide password"
                            aria-label="Toggle password visibility">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                  </div>
                  <button class="btn btn-sm btn-primary">Set Password</button>
                </form>
              </details>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
function togglePw(inputId, btn) {
  var input = document.getElementById(inputId);
  var isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  btn.querySelector('svg').innerHTML = isHidden
    ? '<line x1="1" y1="1" x2="23" y2="23"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>'
    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>
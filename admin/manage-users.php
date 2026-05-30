<?php
/**
 * admin/manage-users.php
 * UPDATED: Ban/unban/delete actions now log to admin_activity_log.
 */
require_once __DIR__.'/../includes/admin-check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0) {
        switch ($action) {
            case 'ban':
                $pdo->prepare('UPDATE users SET is_banned = 1 WHERE id = ?')->execute([$id]);
                $pdo->prepare('INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
                    ->execute([$_SESSION['admin_id'], 'ban_user', 'user', $id, "User account suspended"]);
                flash('success', 'User has been suspended.');
                break;
            case 'unban':
                $pdo->prepare('UPDATE users SET is_banned = 0 WHERE id = ?')->execute([$id]);
                $pdo->prepare('INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
                    ->execute([$_SESSION['admin_id'], 'unban_user', 'user', $id, "User account reinstated"]);
                flash('success', 'User has been reinstated.');
                break;
            case 'delete':
                // Fetch name for log before deleting
                $nameRow = $pdo->prepare('SELECT full_name, email FROM users WHERE id = ?');
                $nameRow->execute([$id]);
                $nameData = $nameRow->fetch();
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
                $pdo->prepare('INSERT INTO admin_activity_log (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
                    ->execute([$_SESSION['admin_id'], 'delete_user', 'user', $id,
                        "Deleted user: " . ($nameData['full_name'] ?? '?') . " (" . ($nameData['email'] ?? '?') . ")"]);
                flash('success', 'User and all their data have been deleted.');
                break;
            default:
                flash('error', 'Unknown action.');
        }
    }
    redirect('admin/manage-users.php');
}

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;
$q       = trim($_GET['q'] ?? '');

$where  = '1=1';
$params = [];
if ($q !== '') {
    $where   .= ' AND (u.full_name LIKE ? OR u.email LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$total      = (int)$pdo->prepare("SELECT COUNT(*) FROM users u WHERE $where")->execute($params) ? $pdo->prepare("SELECT COUNT(*) FROM users u WHERE $where")->execute($params) : 0;
$countStmt  = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE $where");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$userStmt = $pdo->prepare(
    "SELECT u.id, u.full_name, u.email, u.is_banned, u.created_at,
            (SELECT COUNT(*) FROM reports WHERE user_id = u.id) AS reports
     FROM users u
     WHERE $where
     ORDER BY u.created_at DESC
     LIMIT $perPage OFFSET $offset"
);
$userStmt->execute($params);
$users = $userStmt->fetchAll();

$PAGE_TITLE = 'Manage Users';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>
<div class="page-header">
  <h1>User Management</h1>
  <p>View, suspend, or remove user accounts. Showing <?= $total ?> user<?= $total !== 1 ? 's' : '' ?>.</p>
</div>
<div class="container">
  <div class="table-wrapper">
    <form method="get" class="table-toolbar">
      <input name="q" id="tableSearch" class="search-input" placeholder="Search by name or email…"
             value="<?= e($q) ?>" autocomplete="off">
      <button type="submit" class="btn btn-outline btn-sm">Search</button>
      <?php if ($q): ?>
        <a href="<?= BASE_URL ?>/admin/manage-users.php" class="btn btn-sm" style="color:var(--text2);">Clear</a>
      <?php endif; ?>
      <span class="table-count"><?= $total ?> user<?= $total !== 1 ? 's' : '' ?></span>
    </form>

    <?php if ($msg = flash('success')): ?>
      <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>

    <div class="table-scroll">
      <table>
        <thead>
          <tr><th>Name</th><th>Email</th><th>Reports</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="6">
            <div class="empty-state"><div class="ic">👥</div>No users found<?= $q ? ' matching "'.e($q).'"' : '' ?>.</div>
          </td></tr>
        <?php else: foreach ($users as $u): ?>
          <tr>
            <td class="fw-500"><?= e($u['full_name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= (int)$u['reports'] ?></td>
            <td>
              <?php if ($u['is_banned']): ?>
                <span class="badge badge-danger">Suspended</span>
              <?php else: ?>
                <span class="badge badge-success">Active</span>
              <?php endif; ?>
            </td>
            <td class="text-muted-cell"><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
            <td class="table-actions">
              <?php if ($u['is_banned']): ?>
                <form method="post" class="form-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="unban">
                  <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline">Unban</button>
                </form>
              <?php else: ?>
                <form method="post" class="form-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="ban">
                  <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline"
                          data-confirm="Suspend this user? They will not be able to log in.">Suspend</button>
                </form>
              <?php endif; ?>
              <form method="post" class="form-inline">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger"
                        data-confirm="Permanently delete this user and all their reports?">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php
        $base = BASE_URL . '/admin/manage-users.php' . ($q ? '?q='.urlencode($q).'&' : '?');
        for ($i = 1; $i <= $totalPages; $i++):
      ?>
        <a href="<?= $base ?>page=<?= $i ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>

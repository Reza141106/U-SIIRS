<?php
/**
 * admin/activity-log.php
 * NEW: Audit log page — shows all recorded admin actions.
 * Priority HIGH — required for accountability in a university incident system.
 */
require_once __DIR__.'/../includes/admin-check.php';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

$filterAdmin = (int)($_GET['admin_id'] ?? 0);
$where  = '1=1';
$params = [];
if ($filterAdmin > 0) {
    $where   .= ' AND l.admin_id = ?';
    $params[] = $filterAdmin;
}

$total      = (int)$pdo->query("SELECT COUNT(*) FROM admin_activity_log l WHERE $where")->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare(
    "SELECT l.id, l.action, l.target_type, l.target_id, l.details, l.created_at,
            a.full_name AS admin_name
     FROM admin_activity_log l
     LEFT JOIN admins a ON a.id = l.admin_id
     WHERE $where
     ORDER BY l.created_at DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Admin list for filter dropdown
$admins = $pdo->query('SELECT id, full_name FROM admins ORDER BY full_name')->fetchAll();

$PAGE_TITLE = 'Audit Log';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>
<div class="page-header">
  <h1>Audit Log</h1>
  <p>A full record of all admin actions. Showing <?= $total ?> event<?= $total !== 1 ? 's' : '' ?>.</p>
</div>
<div class="container">

  <!-- Filter toolbar -->
  <form method="get" class="table-toolbar" style="margin-bottom:1rem;">
    <select name="admin_id" class="form-control" style="width:auto;min-width:180px;" onchange="this.form.submit()">
      <option value="0">All admins</option>
      <?php foreach ($admins as $a): ?>
        <option value="<?= (int)$a['id'] ?>" <?= $filterAdmin === (int)$a['id'] ? 'selected' : '' ?>>
          <?= e($a['full_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if ($filterAdmin): ?>
      <a href="<?= BASE_URL ?>/admin/activity-log.php" class="btn btn-sm btn-outline">Clear</a>
    <?php endif; ?>
    <span class="table-count"><?= $total ?> event<?= $total !== 1 ? 's' : '' ?></span>
  </form>

  <div class="table-wrapper">
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Date &amp; Time</th>
            <th>Admin</th>
            <th>Action</th>
            <th>Target</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($logs)): ?>
          <tr><td colspan="5">
            <div class="empty-state"><div class="ic">📋</div>No activity recorded yet.</div>
          </td></tr>
        <?php else: foreach ($logs as $log): ?>
          <tr>
            <td class="text-muted-cell" style="white-space:nowrap;">
              <?= e(date('M j, Y H:i', strtotime($log['created_at']))) ?>
            </td>
            <td class="fw-500"><?= e($log['admin_name'] ?? '— deleted —') ?></td>
            <td>
              <?php
                $actionLabels = [
                    'update_status' => ['label' => 'Update Status', 'badge' => 'info'],
                    'ban_user'      => ['label' => 'Suspend User',  'badge' => 'warning'],
                    'unban_user'    => ['label' => 'Reinstate User','badge' => 'success'],
                    'delete_user'   => ['label' => 'Delete User',   'badge' => 'danger'],
                    'delete_report' => ['label' => 'Delete Report', 'badge' => 'danger'],
                    'create_admin'  => ['label' => 'Create Admin',  'badge' => 'info'],
                    'deactivate_admin' => ['label' => 'Deactivate Admin', 'badge' => 'warning'],
                ];
                $al = $actionLabels[$log['action']] ?? ['label' => e($log['action']), 'badge' => 'neutral'];
              ?>
              <span class="badge badge-<?= $al['badge'] ?>"><?= $al['label'] ?></span>
            </td>
            <td>
              <?php if ($log['target_type'] && $log['target_id']): ?>
                <?php if ($log['target_type'] === 'report'): ?>
                  <a class="link" href="<?= BASE_URL ?>/admin/report-view.php?id=<?= (int)$log['target_id'] ?>">
                    Report #<?= (int)$log['target_id'] ?>
                  </a>
                <?php else: ?>
                  <?= e(ucfirst($log['target_type'])) ?> #<?= (int)$log['target_id'] ?>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted-cell">—</span>
              <?php endif; ?>
            </td>
            <td style="max-width:320px;font-size:.88rem;color:var(--text2,#6b7280);">
              <?= e($log['details'] ?? '') ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php
        $base = BASE_URL . '/admin/activity-log.php' . ($filterAdmin ? '?admin_id='.$filterAdmin.'&' : '?');
        for ($i = 1; $i <= $totalPages; $i++):
      ?>
        <a href="<?= $base ?>page=<?= $i ?>"
           class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>

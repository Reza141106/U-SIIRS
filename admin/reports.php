<?php
/**
 * admin/reports.php
 * Lists, filters, and paginates all submitted reports.
 * CRITICAL BUG FIX: Delete form changed from GET to POST to protect CSRF token
 * and prevent destructive actions via URL/bookmarks/browser history.
 */
require_once __DIR__.'/../includes/admin-check.php';

$status  = $_GET['status'] ?? 'all';
$q       = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

// Validate status filter against constants (no magic strings)
$validFilter = in_array($status, REPORT_STATUSES, true) ? $status : 'all';

$where  = '1=1';
$params = [];

if ($validFilter !== 'all') {
    $where   .= ' AND r.status = ?';
    $params[] = $validFilter;
}
if ($q !== '') {
    $where   .= ' AND (r.title LIKE ? OR u.full_name LIKE ? OR r.location LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// Total count for pagination
$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM reports r JOIN users u ON u.id = r.user_id WHERE $where"
);
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

// Fetch page of results — explicit columns, no SELECT *
$sql = "SELECT r.id, r.title, r.category, r.priority, r.status, r.created_at,
               u.full_name
        FROM reports r
        JOIN users u ON u.id = r.user_id
        WHERE $where
        ORDER BY r.created_at DESC
        LIMIT $perPage OFFSET $offset";
$s = $pdo->prepare($sql);
$s->execute($params);
$rows = $s->fetchAll();

$PAGE_TITLE = 'All Reports';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>
<div class="page-header">
  <h1>All Reports</h1>
  <p>Browse, filter and manage every submitted report.</p>
</div>
<div class="container">

  <!-- Status filter chips -->
  <div class="filter-chips">
    <span class="chip <?= $status === 'all'        ? 'active' : '' ?>" data-status="all">All</span>
    <span class="chip <?= $status === 'Pending'    ? 'active' : '' ?>" data-status="Pending">Pending</span>
    <span class="chip <?= $status === 'In Progress' ? 'active' : '' ?>" data-status="In Progress">In Progress</span>
    <span class="chip <?= $status === 'Resolved'   ? 'active' : '' ?>" data-status="Resolved">Resolved</span>
    <span class="chip <?= $status === 'Closed'     ? 'active' : '' ?>" data-status="Closed">Closed</span>
    <span class="chip <?= $status === 'Rejected'   ? 'active' : '' ?>" data-status="Rejected">Rejected</span>
  </div>

  <div class="table-wrapper">
    <div class="table-toolbar">
      <input id="tableSearch"
             class="search-input"
             placeholder="Search title, user or location…"
             value="<?= e($q) ?>">
      <span class="table-count"><?= $total ?> report<?= $total !== 1 ? 's' : '' ?></span>
    </div>

    <!-- Overflow wrapper ensures table scrolls horizontally on mobile -->
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>User</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <div class="ic">📭</div>
                No reports match your filter.
              </div>
            </td>
          </tr>
        <?php else: foreach ($rows as $r):
            $statusBadge   = ['Pending' => 'warning', 'In Progress' => 'info',
                              'Resolved' => 'success', 'Closed' => 'neutral',
                              'Rejected' => 'danger'][$r['status']] ?? 'neutral';
            $priorityBadge = ['Low' => 'neutral', 'Medium' => 'info',
                              'High' => 'warning', 'Critical' => 'danger'][$r['priority']] ?? 'neutral';
        ?>
          <tr>
            <td class="text-muted-cell">#<?= (int)$r['id'] ?></td>
            <td class="fw-500"><?= e($r['title']) ?></td>
            <td><?= e($r['full_name']) ?></td>
            <td><?= e($r['category']) ?></td>
            <td><span class="badge badge-<?= $priorityBadge ?>"><?= e($r['priority']) ?></span></td>
            <td><span class="badge badge-<?= $statusBadge ?>"><?= e($r['status']) ?></span></td>
            <td class="text-muted-cell"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
            <td class="table-actions">
              <!-- View -->
              <a class="btn-icon"
                 title="View report"
                 href="<?= BASE_URL ?>/admin/report-view.php?id=<?= (int)$r['id'] ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </a>

              <!-- Delete — CRITICAL BUG FIX: POST method (was GET) ──────────────
                   CSRF token is now in the POST body, NOT visible in the URL.
                   Prevents token leakage via browser history and server logs.
                   ──────────────────────────────────────────────────────────── -->
              <form method="post"
                    action="<?= BASE_URL ?>/admin/delete-report.php"
                    class="form-inline">
                <input type="hidden" name="id"   value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit"
                        class="btn-icon del"
                        title="Delete report"
                        data-confirm="Permanently delete report #<?= (int)$r['id'] ?>?">
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4h6v2"/>
                  </svg>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div><!-- /table-scroll -->

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php
        $baseUrl = BASE_URL . '/admin/reports.php?status=' . urlencode($status)
                 . ($q ? '&q=' . urlencode($q) : '');
        for ($i = 1; $i <= $totalPages; $i++):
      ?>
        <a href="<?= $baseUrl ?>&page=<?= $i ?>"
           class="page-link <?= $i === $page ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

  </div><!-- /table-wrapper -->
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>

<?php require_once __DIR__.'/includes/auth-check.php';
$uid    = $_SESSION['user_id'];
$status = $_GET['status'] ?? 'all';
$q      = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = 'user_id=?';
$params = [$uid];
if (in_array($status, ['Pending','In Progress','Resolved'], true)) {
    $where .= ' AND status=?'; $params[] = $status;
}
if ($q !== '') {
    $where .= ' AND (title LIKE ? OR location LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%";
}

// Count for pagination
$cntStmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE $where");
$cntStmt->execute($params);
$total      = (int)$cntStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$s = $pdo->prepare("SELECT id, title, category, location, status, priority, created_at FROM reports WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$s->execute($params);
$rows = $s->fetchAll();

$PAGE_TITLE = 'My Reports';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header flex-between">
  <div><h1>My Reports</h1><p>All issues you have submitted.</p></div>
  <a class="btn btn-primary" href="<?= BASE_URL ?>/submit-report.php">+ New Report</a>
</div>
<div class="container">
  <div class="filter-chips">
    <span class="chip <?= $status==='all'?'active':'' ?>" data-status="all">All</span>
    <span class="chip <?= $status==='Pending'?'active':'' ?>" data-status="Pending">Pending</span>
    <span class="chip <?= $status==='In Progress'?'active':'' ?>" data-status="In Progress">In Progress</span>
    <span class="chip <?= $status==='Resolved'?'active':'' ?>" data-status="Resolved">Resolved</span>
  </div>
  <div class="table-wrapper">
    <div class="table-toolbar">
      <input id="tableSearch" class="search-input" placeholder="Search title or location…" value="<?= e($q) ?>">
      <span style="color:var(--text3);font-size:.85rem;"><?= $total ?> report<?= $total!==1?'s':'' ?></span>
    </div>
    <table>
      <thead><tr><th>Title</th><th>Category</th><th>Location</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if(!$rows): ?>
        <tr><td colspan="6"><div class="empty-state"><div class="ic">📭</div>No reports found.</div></td></tr>
      <?php else: foreach($rows as $r):
        $b = ['Pending'=>'warning','In Progress'=>'info','Resolved'=>'success'][$r['status']] ?? 'neutral'; ?>
        <tr>
          <td><a class="link" href="<?= BASE_URL ?>/report-details.php?id=<?= (int)$r['id'] ?>"><?= e($r['title']) ?></a></td>
          <td><?= e($r['category']) ?></td>
          <td><?= e($r['location']) ?></td>
          <td><span class="badge badge-<?= $b ?>"><?= e($r['status']) ?></span></td>
          <td><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
          <td class="table-actions">
            <?php if($r['status']==='Pending'): ?>
              <a class="btn-icon edit" title="Edit" href="<?= BASE_URL ?>/edit-report.php?id=<?= (int)$r['id'] ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <form method="post" action="<?= BASE_URL ?>/edit-report.php" style="display:inline;">
                <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="delete" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn-icon del" title="Delete" data-confirm="Delete this report?">
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
              </form>
            <?php else: ?>
              <a class="btn-icon" title="View" href="<?= BASE_URL ?>/report-details.php?id=<?= (int)$r['id'] ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>

    <?php if($totalPages > 1): ?>
    <div class="pagination">
      <?php
        $baseUrl = BASE_URL.'/my-report.php?status='.urlencode($status).($q?'&q='.urlencode($q):'');
        for ($i = 1; $i <= $totalPages; $i++):
      ?>
        <a href="<?= $baseUrl ?>&page=<?= $i ?>"
           class="page-link <?= $i===$page ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>

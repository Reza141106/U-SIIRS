<?php
/**
 * dashboard.php
 * User dashboard — shows personal report statistics and recent activity.
 * IMPROVEMENT: Inline style replaced with .section-h2 CSS class.
 * IMPROVEMENT: SELECT * replaced with specific columns.
 * The "Mark all as read" POST handler was already correct and is preserved here.
 */
require_once __DIR__.'/includes/auth-check.php';

// ── Mark all notifications as read via POST ───────────────────────────────────
// The trigger for this comes from the navbar form (includes/navbar.php).
// This is the correct POST-based approach (was previously a GET link — now fixed).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    csrf_check();
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')
        ->execute([$_SESSION['user_id']]);
    flash('success', 'All notifications marked as read.');
    redirect('dashboard.php');
}

$uid = $_SESSION['user_id'];

// Aggregate stats for this user
$counts = $pdo->prepare(
    "SELECT
        SUM(status = 'Pending')     AS p,
        SUM(status = 'In Progress') AS ip,
        SUM(status = 'Resolved')    AS r,
        COUNT(*)                    AS total
     FROM reports
     WHERE user_id = ?"
);
$counts->execute([$uid]);
$c = $counts->fetch();

// Recent 5 reports — explicit columns only (no SELECT *)
$recent = $pdo->prepare(
    'SELECT id, title, category, status, created_at
     FROM reports
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5'
);
$recent->execute([$uid]);
$recent = $recent->fetchAll();

$PAGE_TITLE = 'Dashboard';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header">
  <h1>Welcome back, <?= e(explode(' ', $CURRENT_USER['full_name'])[0]) ?></h1>
  <p>Here's an overview of your reports.</p>
</div>

<div class="container">
  <!-- Stat cards -->
  <div class="stat-cards stat-cards-4">
    <div class="stat-card stat-navy">
      <div class="stat-icon">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
          <rect x="9" y="3" width="6" height="4" rx="1"/>
        </svg>
      </div>
      <div class="stat-label">Total Reports</div>
      <div class="stat-value text-navy"><?= (int)$c['total'] ?></div>
    </div>

    <div class="stat-card stat-warn">
      <div class="stat-icon">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      <div class="stat-label">Pending</div>
      <div class="stat-value text-warning"><?= (int)$c['p'] ?></div>
    </div>

    <div class="stat-card stat-info">
      <div class="stat-icon">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <div class="stat-label">In Progress</div>
      <div class="stat-value text-info"><?= (int)$c['ip'] ?></div>
    </div>

    <div class="stat-card stat-ok">
      <div class="stat-icon">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div class="stat-label">Resolved</div>
      <div class="stat-value text-success"><?= (int)$c['r'] ?></div>
    </div>
  </div>

  <!-- Recent Reports Table — inline style replaced with .section-h2 class -->
  <div class="flex-between mb-2">
    <h2 class="section-h2">Recent Reports</h2>
    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/submit-report.php">+ New Report</a>
  </div>

  <div class="table-wrapper">
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($recent)): ?>
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <div class="ic">📭</div>
                No reports yet.
                <a class="link" href="<?= BASE_URL ?>/submit-report.php">Submit your first →</a>
              </div>
            </td>
          </tr>
        <?php else: foreach ($recent as $r):
            $b = ['Pending' => 'warning', 'In Progress' => 'info',
                  'Resolved' => 'success', 'Closed' => 'neutral',
                  'Rejected' => 'danger'][$r['status']] ?? 'neutral';
        ?>
          <tr>
            <td class="fw-500"><?= e($r['title']) ?></td>
            <td><?= e($r['category']) ?></td>
            <td><span class="badge badge-<?= $b ?>"><?= e($r['status']) ?></span></td>
            <td class="text-muted-cell"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
            <td>
              <a class="link" href="<?= BASE_URL ?>/report-details.php?id=<?= (int)$r['id'] ?>">View</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ((int)$c['total'] > 5): ?>
    <div style="text-align:center;margin-top:1rem;">
      <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/my-report.php">
        View All <?= (int)$c['total'] ?> Reports →
      </a>
    </div>
  <?php endif; ?>

</div>
<?php include __DIR__.'/includes/footer.php'; ?>

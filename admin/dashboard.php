<?php
/**
 * admin/dashboard.php
 * Admin analytics dashboard with optional date-range filter.
 * IMPROVEMENT: Added period filter (All Time / This Month / This Week / Custom).
 * Previously showed only all-time totals — no way to see trends over time.
 */
require_once __DIR__.'/../includes/admin-check.php';

// ── Date range filter ─────────────────────────────────────────────────────────
$period    = $_GET['period'] ?? 'all';
$dateWhere = '1=1';
$dateLabel = 'All Time';

switch ($period) {
    case 'week':
        $dateWhere = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $dateLabel = 'Last 7 Days';
        break;
    case 'month':
        $dateWhere = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $dateLabel = 'Last 30 Days';
        break;
    case 'year':
        $dateWhere = "YEAR(created_at) = YEAR(NOW())";
        $dateLabel = 'This Year';
        break;
    default:
        $period    = 'all';
}

// ── Core stats (filtered by period) ──────────────────────────────────────────
$c = $pdo->query(
    "SELECT
        SUM(status = 'Pending')     AS p,
        SUM(status = 'In Progress') AS ip,
        SUM(status = 'Resolved')    AS r,
        SUM(status = 'Closed')      AS cl,
        SUM(status = 'Rejected')    AS rej,
        COUNT(*) AS total
     FROM reports
     WHERE $dateWhere"
)->fetch();

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

// ── Category breakdown (filtered) ────────────────────────────────────────────
$catRows = $pdo->query(
    "SELECT category, COUNT(*) AS cnt
     FROM reports
     WHERE $dateWhere
     GROUP BY category
     ORDER BY cnt DESC
     LIMIT 8"
)->fetchAll();
$maxCat = !empty($catRows) ? max(array_column($catRows, 'cnt')) : 1;

// ── Recent reports (always most recent 8, regardless of filter) ──────────────
$recent = $pdo->query(
    'SELECT r.id, r.title, r.category, r.priority, r.status, r.created_at, u.full_name
     FROM reports r
     JOIN users u ON u.id = r.user_id
     ORDER BY r.created_at DESC
     LIMIT 8'
)->fetchAll();

// ── Monthly trend (last 6 months) — for sparkline display ────────────────────
$trend = $pdo->query(
    "SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label,
            COUNT(*) AS cnt
     FROM reports
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY YEAR(created_at), MONTH(created_at)
     ORDER BY MIN(created_at) ASC"
)->fetchAll();

$PAGE_TITLE = 'Admin Dashboard';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1>Admin Dashboard</h1>
      <p>System overview — <?= date('l, d F Y') ?> · Showing: <strong><?= $dateLabel ?></strong></p>
    </div>
    <!-- ── PERIOD FILTER ── -->
    <form method="get" class="period-filter-form">
      <label class="form-label" for="periodSelect">Filter period:</label>
      <select id="periodSelect" name="period" class="form-control form-control-sm" onchange="this.form.submit()">
        <option value="all"   <?= $period === 'all'   ? 'selected' : '' ?>>All Time</option>
        <option value="week"  <?= $period === 'week'  ? 'selected' : '' ?>>Last 7 Days</option>
        <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
        <option value="year"  <?= $period === 'year'  ? 'selected' : '' ?>>This Year</option>
      </select>
    </form>
  </div>
</div>

<div class="container">

  <!-- ── STAT CARDS ── -->
  <div class="stat-cards stat-cards-5">
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

    <div class="stat-card stat-user">
      <div class="stat-icon">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 00-3-3.87"/>
          <path d="M16 3.13a4 4 0 010 7.75"/>
        </svg>
      </div>
      <div class="stat-label">Registered Users</div>
      <div class="stat-value stat-value-accent"><?= $totalUsers ?></div>
    </div>
  </div>

  <!-- ── TWO-COLUMN: Category Chart + Resolution Rate ── -->
  <div class="row-grid" style="margin-bottom:2rem;">

    <!-- Category breakdown bar chart -->
    <div class="card">
      <h3 class="card-section-title">Reports by Category <span class="period-badge"><?= e($dateLabel) ?></span></h3>
      <?php if (empty($catRows)): ?>
        <div class="empty-state"><div class="ic">📊</div>No data for this period.</div>
      <?php else: foreach ($catRows as $row):
          $pct = $maxCat > 0 ? round(($row['cnt'] / $maxCat) * 100) : 0;
      ?>
        <div class="cat-bar-row">
          <div class="cat-bar-label">
            <span><?= e($row['category']) ?></span>
            <span class="cat-bar-count"><?= (int)$row['cnt'] ?></span>
          </div>
          <div class="cat-bar-track">
            <div class="cat-bar-fill" style="width:<?= $pct ?>%;"></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Resolution rate + status breakdown -->
    <div class="card resolution-card">
      <h3 class="card-section-title">Resolution Rate</h3>
      <?php
        $total    = (int)$c['total'];
        $resolved = (int)$c['r'];
        $rate     = $total > 0 ? round(($resolved / $total) * 100) : 0;
      ?>
      <div class="resolution-big">
        <div class="resolution-pct"><?= $rate ?>%</div>
        <div class="resolution-sub">of reports resolved (<?= $dateLabel ?>)</div>
      </div>
      <div class="resolution-list">
        <div class="resolution-row">
          <span>Pending</span><strong><?= (int)$c['p'] ?></strong>
        </div>
        <div class="resolution-row">
          <span>In Progress</span><strong><?= (int)$c['ip'] ?></strong>
        </div>
        <div class="resolution-row">
          <span>Resolved</span><strong><?= (int)$c['r'] ?></strong>
        </div>
        <div class="resolution-row">
          <span>Closed</span><strong><?= (int)$c['cl'] ?></strong>
        </div>
        <div class="resolution-row resolution-row-last">
          <span>Rejected</span><strong><?= (int)$c['rej'] ?></strong>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Monthly Trend (last 6 months — always shown regardless of filter) ── -->
  <?php if (!empty($trend)): ?>
  <div class="card" style="margin-bottom:2rem;">
    <h3 class="card-section-title">Monthly Trend (Last 6 Months)</h3>
    <?php $tMax = max(array_column($trend, 'cnt'), 1); ?>
    <div class="trend-bars">
      <?php foreach ($trend as $t):
          $tPct = round(($t['cnt'] / $tMax) * 100);
      ?>
        <div class="trend-bar-col">
          <div class="trend-bar-val"><?= (int)$t['cnt'] ?></div>
          <div class="trend-bar-track">
            <div class="trend-bar-fill" style="height:<?= $tPct ?>%;"></div>
          </div>
          <div class="trend-bar-label"><?= e($t['month_label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Recent Reports Table ── -->
  <div class="flex-between mb-2">
    <h2 class="section-h2">Recent Reports</h2>
    <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/reports.php">View All →</a>
  </div>

  <div class="table-wrapper">
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Title</th><th>User</th><th>Category</th>
            <th>Priority</th><th>Status</th><th>Date</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $r):
            $b  = ['Pending' => 'warning', 'In Progress' => 'info',
                   'Resolved' => 'success', 'Closed' => 'neutral',
                   'Rejected' => 'danger'][$r['status']] ?? 'neutral';
            $pb = ['Low' => 'neutral', 'Medium' => 'info',
                   'High' => 'warning', 'Critical' => 'danger'][$r['priority']] ?? 'neutral';
        ?>
          <tr>
            <td class="text-muted-cell">#<?= (int)$r['id'] ?></td>
            <td class="fw-500"><?= e($r['title']) ?></td>
            <td><?= e($r['full_name']) ?></td>
            <td><?= e($r['category']) ?></td>
            <td><span class="badge badge-<?= $pb ?>"><?= e($r['priority']) ?></span></td>
            <td><span class="badge badge-<?= $b ?>"><?= e($r['status']) ?></span></td>
            <td class="text-muted-cell"><?= e(date('M j', strtotime($r['created_at']))) ?></td>
            <td>
              <a class="btn btn-sm btn-outline"
                 href="<?= BASE_URL ?>/admin/report-view.php?id=<?= (int)$r['id'] ?>">Open</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php include __DIR__.'/../includes/footer.php'; ?>

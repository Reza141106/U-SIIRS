<?php
/**
 * admin/notifications.php
 * Admin notification centre — shows alerts for new reports.
 * Marks all as read on page load.
 */
require_once __DIR__.'/../includes/admin-check.php';

// Mark all as read when admin opens this page
$pdo->query('UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0');

// Fetch all notifications, newest first
$notifs = $pdo->query(
    'SELECT n.id, n.report_id, n.message, n.is_read, n.created_at,
            r.title AS report_title, r.status AS report_status
     FROM admin_notifications n
     LEFT JOIN reports r ON r.id = n.report_id
     ORDER BY n.created_at DESC
     LIMIT 100'
)->fetchAll();

$IS_ADMIN_PAGE = true;
$PAGE_TITLE    = 'Notifications';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>

<div class="page-header flex-between">
  <div>
    <h1>🔔 Notifications</h1>
    <p>New report alerts — showing latest 100</p>
  </div>
  <?php if (!empty($notifs)): ?>
    <form method="post" action="<?= BASE_URL ?>/admin/notifications-clear.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button type="submit" class="btn btn-outline btn-sm"
              data-confirm="Clear all notifications?">Clear All</button>
    </form>
  <?php endif; ?>
</div>

<div class="container" style="max-width:760px;">

  <?php if (empty($notifs)): ?>
    <div class="card" style="text-align:center;padding:3rem 1rem;">
      <div style="font-size:2.5rem;margin-bottom:.75rem;">🔕</div>
      <h3 style="margin-bottom:.5rem;">All caught up!</h3>
      <p class="text-muted">No notifications yet. New report alerts will appear here.</p>
    </div>
  <?php else: ?>
    <div class="card" style="padding:0;overflow:hidden;">
      <?php foreach ($notifs as $i => $n):
        $statusBadge = ['Pending'     => 'warning', 'In Progress' => 'info',
                        'Resolved'    => 'success',  'Closed'      => 'neutral',
                        'Rejected'    => 'danger'][$n['report_status'] ?? ''] ?? 'neutral';
        $isNew = !$n['is_read'];
      ?>
        <div style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.25rem;
                    border-bottom:1px solid var(--border);
                    background:<?= $isNew ? 'var(--bg2)' : 'transparent' ?>;">

          <!-- Icon -->
          <div style="flex-shrink:0;width:38px;height:38px;border-radius:50%;
                      background:var(--navy);display:flex;align-items:center;justify-content:center;">
            <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
              <rect x="9" y="3" width="6" height="4" rx="1"/>
            </svg>
          </div>

          <!-- Content -->
          <div style="flex:1;min-width:0;">
            <div style="font-size:.9rem;line-height:1.5;margin-bottom:.3rem;">
              <?= e($n['message']) ?>
            </div>
            <div style="font-size:.75rem;color:var(--text2);display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
              <span><?= e(date('M j, Y g:i a', strtotime($n['created_at']))) ?></span>
              <?php if ($n['report_status']): ?>
                <span class="badge badge-<?= $statusBadge ?>" style="font-size:.65rem;">
                  <?= e($n['report_status']) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- View link -->
          <?php if ($n['report_id']): ?>
            <a class="btn btn-sm btn-outline" style="flex-shrink:0;"
               href="<?= BASE_URL ?>/admin/report-view.php?id=<?= (int)$n['report_id'] ?>">
              View →
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php
/**
 * includes/navbar.php
 * Top navigation bar — included on every user-facing page.
 *
 * FIX: "Mark all as read" changed from a plain <a> link (GET) to a proper POST form.
 *      GET requests must never mutate data.
 * FIX: Mobile notification panel now uses CSS class instead of inline min-width.
 * FIX: Login/signup button inline styles replaced with CSS classes.
 */
$loggedIn = !empty($_SESSION['user_id']);
$current  = basename($_SERVER['PHP_SELF']);
$unread   = 0;
$nrows    = [];

if ($loggedIn) {
    // Unread notification count
    $s = $pdo->prepare(
        'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
    );
    $s->execute([$_SESSION['user_id']]);
    $unread = (int)$s->fetchColumn();

    // Recent 10 notifications — only the columns the panel needs
    $s = $pdo->prepare(
        'SELECT id, message, is_read, created_at FROM notifications
         WHERE user_id = ? ORDER BY created_at DESC LIMIT 10'
    );
    $s->execute([$_SESSION['user_id']]);
    $nrows = $s->fetchAll();
}
?>
<nav class="navbar">
  <a class="nav-logo" href="<?= BASE_URL ?>/index.php">U-<span>SIIRS</span></a>

  <!-- Hamburger — visible only on mobile -->
  <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu">
    <span></span><span></span><span></span>
  </button>

  <div class="nav-links">
    <a href="<?= BASE_URL ?>/index.php"
       class="<?= $current === 'index.php' ? 'active' : '' ?>">Home</a>
    <?php if ($loggedIn): ?>
      <a href="<?= BASE_URL ?>/dashboard.php"
         class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
      <a href="<?= BASE_URL ?>/my-report.php"
         class="<?= $current === 'my-report.php' ? 'active' : '' ?>">My Reports</a>
      <a href="<?= BASE_URL ?>/submit-report.php"
         class="<?= $current === 'submit-report.php' ? 'active' : '' ?>">Submit</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/contact.php"
       class="<?= $current === 'contact.php' ? 'active' : '' ?>">Contact</a>
  </div>

  <div class="nav-right">
    <?php if ($loggedIn): ?>

      <!-- Notification Bell -->
      <div class="bell-wrap" id="bell" aria-label="Notifications" role="button" tabindex="0">
        🔔
        <?php if ($unread > 0): ?>
          <span class="notif-dot"><?= $unread ?></span>
        <?php endif; ?>

        <!-- Notification Panel -->
        <div class="notif-panel" id="notifPanel" onclick="event.stopPropagation()">
          <div class="notif-panel-header">
            <strong>Notifications</strong>
            <?php if ($unread > 0): ?>
              <span class="notif-unread-count"><?= $unread ?> unread</span>
            <?php endif; ?>
          </div>

          <?php if (empty($nrows)): ?>
            <div class="notif-item text-muted">No notifications yet.</div>
          <?php else: foreach ($nrows as $n): ?>
            <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
              <div><?= e($n['message']) ?></div>
              <div class="notif-time"><?= time_ago($n['created_at']) ?></div>
            </div>
          <?php endforeach; endif; ?>

          <?php if ($unread > 0): ?>
            <!-- ── CRITICAL BUG FIX ──────────────────────────────────────────
                 Previously: <a href="dashboard.php?mark_read=1"> — a plain GET link.
                 GET requests must never mutate data. Any page crawler or prefetch
                 plugin would silently mark all notifications as read.
                 Fix: POST form with CSRF token, handled in dashboard.php.
                 ──────────────────────────────────────────────────────────── -->
            <div class="notif-footer">
              <form method="post" action="<?= BASE_URL ?>/dashboard.php">
                <input type="hidden" name="csrf"      value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="mark_read" value="1">
                <button type="submit" class="notif-mark-all-btn">
                  Mark all as read
                </button>
              </form>
            </div>
          <?php endif; ?>
        </div><!-- /notif-panel -->
      </div><!-- /bell-wrap -->

      <!-- User Dropdown -->
      <div class="nav-user" id="navUser">
        <div class="avatar">
          <?php if (!empty($CURRENT_USER['avatar'])): ?>
            <img src="<?= BASE_URL ?>/assets/uploads/<?= e($CURRENT_USER['avatar']) ?>"
                 alt="<?= e($CURRENT_USER['full_name']) ?>">
          <?php else: ?>
            <?= strtoupper(substr($CURRENT_USER['full_name'] ?? 'U', 0, 1)) ?>
          <?php endif; ?>
        </div>
        <span class="nav-user-name"><?= e($CURRENT_USER['full_name'] ?? 'User') ?> ▾</span>
        <div class="dropdown-menu" id="navDrop">
          <a href="<?= BASE_URL ?>/profile.php">Profile</a>
          <a href="<?= BASE_URL ?>/settings.php">Settings</a>
          <a class="danger" href="<?= BASE_URL ?>/logout.php">Logout</a>
        </div>
      </div>

    <?php else: ?>
      <a class="btn btn-outline btn-sm btn-nav-ghost" href="<?= BASE_URL ?>/login.php">Login</a>
      <a class="btn btn-primary btn-sm btn-nav-white" href="<?= BASE_URL ?>/register.php">Sign Up</a>
    <?php endif; ?>
  </div><!-- /nav-right -->
</nav>

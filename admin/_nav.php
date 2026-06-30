<?php
/**
 * admin/_nav.php
 * UPDATED: Added Audit Log and Manage Admins links (super_admin only).
 */
$current          = basename($_SERVER['PHP_SELF']);
$unreadCount      = (int)$pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
$unreadAdminNotif = (int)$pdo->query('SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0')->fetchColumn();
$isSuperAdmin     = ($CURRENT_ADMIN['role'] ?? 'admin') === 'super_admin';
?>
<nav class="navbar">
  <div class="nav-logo nav-logo-admin">U-<span>SIIRS</span><span class="nav-admin-badge">· Admin</span></div>
  <div class="nav-right">
    <button class="hamburger admin-hamburger" id="adminHamburger" aria-label="Toggle sidebar">
      <span></span><span></span><span></span>
    </button>
    <!-- Admin notification bell -->
    <a href="<?= BASE_URL ?>/admin/notifications.php"
       title="New report notifications"
       style="position:relative;display:inline-flex;align-items:center;margin-right:.5rem;color:var(--text1);text-decoration:none;">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 01-3.46 0"/>
      </svg>
      <?php if ($unreadAdminNotif > 0): ?>
        <span style="position:absolute;top:-5px;right:-6px;background:#e53935;color:#fff;
                     font-size:.6rem;font-weight:700;border-radius:50%;min-width:16px;height:16px;
                     display:flex;align-items:center;justify-content:center;padding:0 3px;line-height:1;">
          <?= $unreadAdminNotif > 9 ? '9+' : $unreadAdminNotif ?>
        </span>
      <?php endif; ?>
    </a>
    <div class="avatar avatar-admin"><?= strtoupper(substr($CURRENT_ADMIN['full_name'], 0, 1)) ?></div>
    <span class="nav-admin-name"><?= e($CURRENT_ADMIN['full_name']) ?>
      <?php if ($isSuperAdmin): ?>
        <span class="badge badge-info" style="font-size:.65rem;vertical-align:middle;margin-left:4px;">Super</span>
      <?php endif; ?>
    </span>
    <a class="btn btn-sm btn-outline btn-nav-ghost" href="<?= BASE_URL ?>/admin/logout.php">Logout</a>
  </div>
</nav>

<div class="admin-layout">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-title">Main Menu</div>

    <a href="<?= BASE_URL ?>/admin/dashboard.php"
       class="admin-nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
      </span>Dashboard
    </a>

    <a href="<?= BASE_URL ?>/admin/reports.php"
       class="admin-nav-link <?= $current === 'reports.php' ? 'active' : '' ?>">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
          <rect x="9" y="3" width="6" height="4" rx="1"/>
        </svg>
      </span>Reports
    </a>

    <a href="<?= BASE_URL ?>/admin/manage-users.php"
       class="admin-nav-link <?= $current === 'manage-users.php' ? 'active' : '' ?>">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
        </svg>
      </span>Users
    </a>

    <a href="<?= BASE_URL ?>/admin/contact-messages.php"
       class="admin-nav-link <?= $current === 'contact-messages.php' ? 'active' : '' ?>">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
          <polyline points="22,6 12,13 2,6"/>
        </svg>
      </span>Messages
      <?php if ($unreadCount): ?>
        <span class="sidebar-badge"><?= (int)$unreadCount ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>/admin/activity-log.php"
       class="admin-nav-link <?= $current === 'activity-log.php' ? 'active' : '' ?>">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
          <polyline points="10 9 9 9 8 9"/>
        </svg>
      </span>Audit Log
    </a>

    <div class="admin-sidebar-title admin-sidebar-title-spaced">System</div>

    <a href="<?= BASE_URL ?>/admin/settings.php"
       class="admin-nav-link <?= $current === 'settings.php' ? 'active' : '' ?>">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
        </svg>
      </span>Settings
    </a>

    <?php if ($isSuperAdmin): ?>
    <a href="<?= BASE_URL ?>/admin/manage-admins.php"
       class="admin-nav-link <?= $current === 'manage-admins.php' ? 'active' : '' ?>">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </span>Manage Admins
    </a>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/index.php" class="admin-nav-link" target="_blank" rel="noopener">
      <span class="admin-nav-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <line x1="2" y1="12" x2="22" y2="12"/>
          <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
        </svg>
      </span>View Site ↗
    </a>
  </aside>

  <div class="admin-main">
<script>
(function () {
  var btn = document.getElementById('adminHamburger');
  var sb  = document.getElementById('adminSidebar');
  if (!btn || !sb) return;
  btn.addEventListener('click', function () {
    btn.classList.toggle('open');
    sb.classList.toggle('admin-sidebar-open');
  });
  sb.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () {
      btn.classList.remove('open');
      sb.classList.remove('admin-sidebar-open');
    });
  });
}());
</script>

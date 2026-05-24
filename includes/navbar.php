<?php
$loggedIn = !empty($_SESSION['user_id']);
$current = basename($_SERVER['PHP_SELF']);
$unread = 0;
if ($loggedIn) {
    $s = $pdo->prepare('SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0');
    $s->execute([$_SESSION['user_id']]);
    $unread = (int)$s->fetchColumn();
    $nrows = $pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10');
    $nrows->execute([$_SESSION['user_id']]);
    $nrows = $nrows->fetchAll();
}
?>
<nav class="navbar">
  <div class="nav-logo" onclick="location.href='<?= BASE_URL ?>/index.php'">U-<span>SIIRS</span></div>
  <div class="nav-links">
    <a href="<?= BASE_URL ?>/index.php" class="<?= $current==='index.php'?'active':'' ?>">Home</a>
    <?php if ($loggedIn): ?>
      <a href="<?= BASE_URL ?>/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">Dashboard</a>
      <a href="<?= BASE_URL ?>/my-report.php" class="<?= $current==='my-report.php'?'active':'' ?>">My Reports</a>
      <a href="<?= BASE_URL ?>/submit-report.php" class="<?= $current==='submit-report.php'?'active':'' ?>">Submit</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/contact.php" class="<?= $current==='contact.php'?'active':'' ?>">Contact</a>
  </div>
  <div style="display:flex; align-items:center; gap:.5rem;">
  <?php if ($loggedIn): ?>
    <div class="bell-wrap" id="bell">🔔
      <?php if ($unread>0): ?><span class="notif-dot"><?= $unread ?></span><?php endif; ?>
      <div class="notif-panel" id="notifPanel" onclick="event.stopPropagation()">
        <?php if (!$nrows): ?>
          <div class="notif-item text-muted">No notifications</div>
        <?php else: foreach($nrows as $n): ?>
          <div class="notif-item <?= $n['is_read']?'':'unread' ?>">
            <div><?= e($n['message']) ?></div>
            <div class="text-muted" style="font-size:.72rem;"><?= e($n['created_at']) ?></div>
          </div>
        <?php endforeach; endif; ?>
        <?php if ($unread>0): ?>
          <div style="padding:.5rem 1rem; border-top:1px solid var(--border); text-align:center;">
            <a class="link" href="<?= BASE_URL ?>/dashboard.php?mark_read=1">Mark all as read</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="nav-user" id="navUser">
      <div class="avatar">
        <?php if (!empty($CURRENT_USER['avatar'])): ?>
          <img src="<?= BASE_URL ?>/assets/uploads/<?= e($CURRENT_USER['avatar']) ?>" alt="">
        <?php else: ?>
          <?= strtoupper(substr($CURRENT_USER['full_name']??'U',0,1)) ?>
        <?php endif; ?>
      </div>
      <span><?= e($CURRENT_USER['full_name'] ?? 'User') ?> ▾</span>
      <div class="dropdown-menu" id="navDrop">
        <a href="<?= BASE_URL ?>/profile.php">Profile</a>
        <a href="<?= BASE_URL ?>/settings.php">Settings</a>
        <a class="danger" href="<?= BASE_URL ?>/logout.php">Logout</a>
      </div>
    </div>
  <?php else: ?>
    <a class="btn btn-outline btn-sm" style="color:#fff; border-color: rgba(255,255,255,.4);" href="<?= BASE_URL ?>/login.php">Login</a>
    <a class="btn btn-primary btn-sm" style="background:#fff; color:var(--navy);" href="<?= BASE_URL ?>/register.php">Sign Up</a>
  <?php endif; ?>
  </div>
</nav>

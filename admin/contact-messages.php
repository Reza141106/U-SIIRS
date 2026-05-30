<?php require_once __DIR__.'/../includes/admin-check.php';

// Mark as read when viewing
if (isset($_GET['id'])) {
    $mid = (int)$_GET['id'];
    $pdo->prepare('UPDATE contact_messages SET is_read=1 WHERE id=?')->execute([$mid]);
    $msg = $pdo->prepare('SELECT id, name, email, subject, message, is_read, created_at FROM contact_messages WHERE id=?');
    $msg->execute([$mid]); $msg = $msg->fetch();
}

// FIXED: Delete now uses POST form + CSRF — was GET link (re-evaluation issue)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    csrf_check();
    $pdo->prepare('DELETE FROM contact_messages WHERE id=?')->execute([(int)$_POST['delete']]);
    flash('success','Message deleted.');
    redirect('admin/contact-messages.php');
}

$messages = $pdo->query('SELECT id, name, email, subject, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC')->fetchAll();
$unread = array_filter($messages, fn($m) => !$m['is_read']);

$PAGE_TITLE = 'Contact Messages';
include __DIR__.'/../includes/header.php';
include __DIR__.'/_nav.php';
?>
<div class="page-header flex-between">
  <div>
    <h1>Contact Messages</h1>
    <p><?= count($messages) ?> total<?= count($unread) ? ', <strong>'.count($unread).' unread</strong>' : '' ?></p>
  </div>
</div>
<div class="container">
  <?php if(isset($msg)): ?>
  <!-- Message detail view -->
  <div class="card mb-2">
    <div class="flex-between" style="margin-bottom:.75rem;">
      <div>
        <h3 style="margin:0;"><?= e($msg['subject']) ?></h3>
        <p class="text-muted" style="margin:.25rem 0 0;"><?= e($msg['name']) ?> · <a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a> · <?= e(date('M j, Y g:i a', strtotime($msg['created_at']))) ?></p>
      </div>
      <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
        <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/contact-messages.php">← Back</a>
        <!-- FIXED: delete is now a POST form, not a GET link -->
        <form method="post" style="display:inline;">
          <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="delete" value="<?= (int)$msg['id'] ?>">
          <button type="submit" class="btn btn-outline btn-sm"
                  style="color:var(--danger);"
                  data-confirm="Delete this message?">Delete</button>
        </form>
        <a class="btn btn-primary btn-sm" href="mailto:<?= e($msg['email']) ?>?subject=Re: <?= e($msg['subject']) ?>">Reply via Email</a>
      </div>
    </div>
    <hr>
    <p style="white-space:pre-wrap; color:var(--text2); margin-top:.75rem;"><?= e($msg['message']) ?></p>
  </div>
  <?php else: ?>
  <!-- Message list -->
  <?php if(empty($messages)): ?>
    <div class="card text-muted">No contact messages yet.</div>
  <?php else: ?>
  <div class="card" style="padding:0;overflow:hidden;">
    <table class="table">
      <thead><tr><th>From</th><th>Subject</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach($messages as $m): ?>
        <tr style="<?= !$m['is_read']?'font-weight:600;background:var(--surface2,#f9fafb);':'' ?>">
          <td><?= e($m['name']) ?><br><span class="text-muted" style="font-size:.8rem;"><?= e($m['email']) ?></span></td>
          <td><?= e($m['subject']) ?></td>
          <td class="text-muted" style="white-space:nowrap;"><?= e(date('M j, Y', strtotime($m['created_at']))) ?></td>
          <td><?= $m['is_read']?'<span class="badge badge-neutral">Read</span>':'<span class="badge badge-info">Unread</span>' ?></td>
          <td class="table-actions">
            <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/contact-messages.php?id=<?= (int)$m['id'] ?>">View</a>
            <!-- FIXED: delete is now a POST form, not a GET link -->
            <form method="post" style="display:inline;">
              <input type="hidden" name="csrf"   value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="delete" value="<?= (int)$m['id'] ?>">
              <button type="submit" class="btn btn-outline btn-sm"
                      style="color:var(--danger);"
                      data-confirm="Delete this message?">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>

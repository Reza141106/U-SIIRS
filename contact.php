<?php require_once __DIR__.'/config/database.php';

// Handle contact form submission — save to DB
$err = $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (strlen($name) < 2 || strlen($name) > 100)        $err = 'Name must be 2-100 characters.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))   $err = 'Please enter a valid email address.';
    elseif (strlen($subject) < 3 || strlen($subject) > 150) $err = 'Subject must be 3-150 characters.';
    elseif (strlen($message) < 10 || strlen($message) > 2000) $err = 'Message must be 10-2000 characters.';
    else {
        $pdo->prepare('INSERT INTO contact_messages(name,email,subject,message) VALUES(?,?,?,?)')
            ->execute([$name, $email, $subject, $message]);
        $ok = 'Your message has been sent. We\'ll get back to you as soon as possible.';
    }
}

$PAGE_TITLE = 'Contact';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header"><h1>Contact Us</h1><p>Reach out to the U-SIIRS team or facilities office.</p></div>
<div class="contact-layout">
  <div class="contact-card">
    <div>
      <h3 style="margin-bottom:.5rem;">Send a message</h3>

      <?php if($ok): ?>
        <div class="alert alert-success"><?= e($ok) ?></div>
      <?php else: ?>
        <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <div class="form-group">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" required maxlength="100" value="<?= e($_POST['name'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Subject</label>
            <input class="form-control" name="subject" required maxlength="150" value="<?= e($_POST['subject'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Message</label>
            <textarea class="form-control" name="message" required maxlength="2000"><?= e($_POST['message'] ?? '') ?></textarea>
          </div>
          <button class="btn btn-primary">Send Message</button>
        </form>
      <?php endif; ?>
    </div>
    <div>
      <h3 style="margin-bottom:.5rem;">Facilities Office</h3>
      <p class="text-muted">Jabatan Pembangunan & Pengurusan Aset (JPPA)<br>Universiti Teknikal Malaysia Melaka<br>Hang Tuah Jaya, 76100 Durian Tunggal, Melaka</p>
      <p class="text-muted mt-2">📞 +606-270 1000<br>✉️ jppa@utem.edu.my</p>
    </div>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>

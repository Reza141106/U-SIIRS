<?php
/**
 * contact.php
 * Public contact form — saves messages to the database.
 * IMPROVEMENT: Session-based rate limiting (3 submissions per 15 min) added.
 * This matches the protection pattern used on login.php and forgot-password.php.
 */
require_once __DIR__.'/config/database.php';

$err = '';
$ok  = false;

// Load user for navbar — consistent with auth-check.php
$CURRENT_USER = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT id, full_name, email, avatar FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $CURRENT_USER = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // ── Rate limiting: 3 contact messages per 15-minute window per session ────
    $now             = time();
    $window          = 15 * 60;   // 15 minutes in seconds
    $maxAttempts     = 3;
    $rl              = &$_SESSION['contact_rl'];   // shorthand reference

    // Clean up old attempts outside the window
    if (!empty($rl['attempts'])) {
        $rl['attempts'] = array_filter($rl['attempts'], fn($t) => ($now - $t) < $window);
    }

    if (!empty($rl['attempts']) && count($rl['attempts']) >= $maxAttempts) {
        $oldest    = min($rl['attempts']);
        $remaining = ceil(($window - ($now - $oldest)) / 60);
        $err = "Too many messages sent. Please wait $remaining minute(s) before trying again.";
    }
    // ─────────────────────────────────────────────────────────────────────────

    if (!$err) {
        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (strlen($name) < 2 || strlen($name) > 100) {
            $err = 'Name must be between 2 and 100 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Please enter a valid email address.';
        } elseif (strlen($subject) < 3 || strlen($subject) > 150) {
            $err = 'Subject must be between 3 and 150 characters.';
        } elseif (strlen($message) < 10 || strlen($message) > 2000) {
            $err = 'Message must be between 10 and 2000 characters.';
        } else {
            $pdo->prepare(
                'INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)'
            )->execute([$name, $email, $subject, $message]);

            // Record this attempt for rate limiting
            $rl['attempts'][] = $now;

            $ok = true;
        }
    }
}

$PAGE_TITLE = 'Contact';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header">
  <h1>Contact Us</h1>
  <p>Reach out to the U-SIIRS team or facilities office.</p>
</div>

<div class="contact-layout">
  <div class="contact-card">

    <!-- Contact Form -->
    <div class="contact-form-col">
      <h3 class="contact-col-title">Send a message</h3>

      <?php if ($ok): ?>
        <div class="alert alert-success">
          ✅ Your message has been sent. We'll get back to you as soon as possible.
        </div>
      <?php else: ?>
        <?php if ($err): ?>
          <div class="alert alert-danger"><?= e($err) ?></div>
        <?php endif; ?>

        <form method="post" id="contactForm">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div class="form-group">
            <label class="form-label" for="contact-name">Name</label>
            <input id="contact-name"
                   class="form-control"
                   name="name"
                   required
                   maxlength="100"
                   value="<?= e($_POST['name'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label" for="contact-email">Email</label>
            <input id="contact-email"
                   type="email"
                   class="form-control"
                   name="email"
                   required
                   value="<?= e($_POST['email'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label" for="contact-subject">Subject</label>
            <input id="contact-subject"
                   class="form-control"
                   name="subject"
                   required
                   maxlength="150"
                   value="<?= e($_POST['subject'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label" for="contact-message">Message</label>
            <textarea id="contact-message"
                      class="form-control"
                      name="message"
                      required
                      maxlength="2000"
                      rows="5"><?= e($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary" id="contactSubmitBtn">
            Send Message
          </button>
        </form>
      <?php endif; ?>
    </div>

     <!-- Office Info -->
    <div class="contact-info-col">
      <h3 class="contact-col-title">Facilities Office</h3>
      <div class="contact-info-block">
        <p class="text-muted">
          <strong>Pejabat Pengurusan Fasiliti (PPF)</strong><br>
          Universiti Teknikal Malaysia Melaka (UTeM)<br>
          Hang Tuah Jaya, 76100 Durian Tunggal, Melaka, Malaysia
        </p>
        <p class="text-muted contact-office-links">
          📞 <a href="tel:+60627021150" class="link">Tel: +062702115</a><br>
          📠 <a href="tel:+60627010590" class="link">Faks: +062701059</a><br>
          ✉️ <a href="mailto:ppf@utem.edu.my" class="link">ppf@utem.edu.my</a>
        </p>
        <div class="contact-hours">
          <strong>Office Hours</strong>
          <p>Monday – Friday: 8:00 AM – 5:00 PM</p>
          <p>Saturday – Sunday: Closed</p>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>

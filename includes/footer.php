<?php
/**
 * includes/footer.php
 * Site-wide footer — included on every page.
 * IMPROVEMENT: Inline styles replaced with CSS classes.
 */
?>
<?php if (!empty($IS_ADMIN_PAGE)): ?>
  </div><!-- /admin-main -->
</div><!-- /admin-layout -->
<?php endif; ?>

<footer class="footer">
  <div class="footer-grid">
    <div>
      <div class="footer-logo">U-SIIRS</div>
      <p class="footer-tagline">
        UTeM Smart Infrastructure Issue Reporting System — report, track, and resolve campus issues.
      </p>
    </div>
    <div class="footer-col">
      <h4>Navigate</h4>
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <a href="<?= BASE_URL ?>/contact.php">Contact</a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/submit-report.php">Submit Report</a>
      <?php endif; ?>
      <?php if (!empty($_SESSION['admin_id'])): ?>
        <a href="<?= BASE_URL ?>/admin/dashboard.php">Admin Panel</a>
      <?php endif; ?>
    </div>
    <div class="footer-col">
      <h4>Account</h4>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="<?= BASE_URL ?>/profile.php">Profile</a>
        <a href="<?= BASE_URL ?>/settings.php">Settings</a>
        <a href="<?= BASE_URL ?>/logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php">Login</a>
        <a href="<?= BASE_URL ?>/register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="footer-bottom">
    © <?= date('Y') ?> U-SIIRS · Universiti Teknikal Malaysia Melaka
  </div>
</footer>

</div><!-- /page.active -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>

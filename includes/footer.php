<footer class="footer">
  <div class="footer-grid">
    <div>
      <div class="footer-logo">U-SIIRS</div>
      <div style="font-size:.85rem; max-width:280px;">UTeM Smart Infrastructure Issue Reporting System — report, track, resolve campus issues.</div>
    </div>
    <div class="footer-col">
      <h4>Navigate</h4>
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <a href="<?= BASE_URL ?>/contact.php">Contact</a>
      <?php if(!empty($_SESSION['user_id'])): ?>
        <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
      <?php endif; ?>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <a href="#">Privacy</a>
      <a href="#">Terms</a>
    </div>
  </div>
  <div class="footer-bottom">© <?= date('Y') ?> U-SIIRS · Universiti Teknikal Malaysia Melaka</div>
</footer>
</div>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>

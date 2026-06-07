<?php
/**
 * index.php — Landing page
 * IMPROVEMENT: Added live statistics from the database.
 * Previously the landing page had no dynamic data — it looked like a brochure
 * rather than a live system. 3 queries now power a statistics banner.
 */
require_once __DIR__.'/config/database.php';

$PAGE_TITLE = 'Home';
$loggedIn   = !empty($_SESSION['user_id']);

// Load user for navbar — same columns as auth-check.php
$CURRENT_USER = null;
if ($loggedIn) {
    $stmt = $pdo->prepare('SELECT id, full_name, email, avatar FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $CURRENT_USER = $stmt->fetch() ?: null;
}

// ── Live statistics for the hero stats banner ──────────────────────────────
$stats = $pdo->query(
    "SELECT
        COUNT(*) AS total_reports,
        SUM(status = 'Resolved') AS resolved,
        SUM(status = 'Pending' OR status = 'In Progress') AS active
     FROM reports"
)->fetch();
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<!-- ── HERO ──────────────────────────────────────────────────────────────── -->
<section class="hero-bg">
  <div class="hero-content">
    <h1 class="hero-title">Report. Track. Resolve.</h1>
    <p class="hero-sub">
      UTeM Smart Infrastructure Issue Reporting System — keep our campus safe and well-maintained.
    </p>
    <div class="hero-actions">
      <?php if ($loggedIn): ?>
        <a class="btn btn-primary btn-lg btn-hero-primary" href="<?= BASE_URL ?>/submit-report.php">
          Submit a Report
        </a>
        <a class="btn btn-outline btn-lg btn-hero-outline" href="<?= BASE_URL ?>/dashboard.php">
          Dashboard
        </a>
      <?php else: ?>
        <a class="btn btn-primary btn-lg btn-hero-primary" href="<?= BASE_URL ?>/register.php">
          Get Started
        </a>
        <a class="btn btn-outline btn-lg btn-hero-outline" href="<?= BASE_URL ?>/login.php">
          Login
        </a>
      <?php endif; ?>
    </div>

    <!-- ── LIVE STATISTICS BANNER ─────────────────────────────────────────
         Previously: static brochure with no dynamic data.
         Now: real counts from the database on every page load.
         ─────────────────────────────────────────────────────────────────── -->
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-value"><?= number_format((int)$stats['total_reports']) ?></div>
        <div class="hero-stat-label">Reports Submitted</div>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <div class="hero-stat-value"><?= number_format((int)$stats['resolved']) ?></div>
        <div class="hero-stat-label">Issues Resolved</div>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <div class="hero-stat-value"><?= number_format((int)$stats['active']) ?></div>
        <div class="hero-stat-label">Active Reports</div>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <div class="hero-stat-value"><?= number_format($totalUsers) ?></div>
        <div class="hero-stat-label">Registered Users</div>
      </div>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ──────────────────────────────────────────────────────── -->
<section class="section">
  <h2 class="section-title">How It Works</h2>
  <p class="section-sub">Three simple steps to a better campus</p>
  <div class="how-grid">

    <div class="how-card">
      <div class="how-step-num">1</div>
      <div class="how-card-body">
        <div class="how-card-icon">
          <svg width="32" height="32" fill="none" stroke="var(--navy)" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
        </div>
        <div class="how-card-title">Submit</div>
        <div class="how-card-text">Fill in details, select the location and attach a photo of the issue.</div>
      </div>
    </div>

    <div class="how-card">
      <div class="how-step-num">2</div>
      <div class="how-card-body">
        <div class="how-card-icon">
          <svg width="32" height="32" fill="none" stroke="var(--navy)" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <div class="how-card-title">Track</div>
        <div class="how-card-text">Follow real-time status updates from Pending → In Progress → Resolved.</div>
      </div>
    </div>

    <div class="how-card">
      <div class="how-step-num">3</div>
      <div class="how-card-body">
        <div class="how-card-icon">
          <svg width="32" height="32" fill="none" stroke="var(--navy)" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <div class="how-card-title">Resolve</div>
        <div class="how-card-text">Facilities admin reviews, prioritises and resolves your report.</div>
      </div>
    </div>

  </div>
</section>

<!-- ── WHY USE U-SIIRS ───────────────────────────────────────────────────── -->
<section class="section">
  <h2 class="section-title">Why Use U-SIIRS</h2>
  <div class="why-grid">
    <ul class="why-list">
      <li><span class="why-check">✓</span> Fast and structured reporting</li>
      <li><span class="why-check">✓</span> Photo evidence with each report</li>
      <li><span class="why-check">✓</span> Real-time status notifications</li>
      <li><span class="why-check">✓</span> Direct line to facilities admin</li>
      <li><span class="why-check">✓</span> Full audit trail for every issue</li>
      <li><span class="why-check">✓</span> Secure — CSRF-protected & encrypted passwords</li>
    </ul>
    <div class="card why-card">
      <h3 class="why-card-title">Built for UTeM</h3>
      <p class="text-muted">
        Designed specifically for UTeM staff and students to keep our campus infrastructure
        in top shape. Any issue — from broken lights to flooded corridors — can be tracked
        from submission to resolution in one place.
      </p>
      <?php if (!$loggedIn): ?>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/register.php" style="margin-top:1rem;">
          Create Your Account →
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────────────────────── -->
<section class="cta-section">
  <h2>Ready to make your campus better?</h2>
  <p>Join <?= number_format($totalUsers) ?> students and staff already using U-SIIRS.</p>
  <a class="btn btn-primary btn-lg btn-hero-primary"
     href="<?= BASE_URL ?>/<?= $loggedIn ? 'submit-report.php' : 'register.php' ?>">
    <?= $loggedIn ? 'Submit a Report' : 'Get Started — It\'s Free' ?>
  </a>
</section>

<?php include __DIR__.'/includes/footer.php'; ?>

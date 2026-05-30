<?php
/**
 * profile.php
 * User profile page with tabbed interface: Overview, Edit Profile, My Reports.
 * FIX: "Total Reports" count now shows the actual total via a COUNT(*) query.
 *      Previously showed count($myReports)+ which was always "1–5+" (misleading).
 * IMPROVEMENT: Inline styles replaced with CSS classes throughout.
 */
require_once __DIR__.'/includes/auth-check.php';

$err = '';
$u   = $CURRENT_USER;
$tab = $_GET['tab'] ?? 'edit';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['full_name'] ?? '');

    if (strlen($name) < 2 || strlen($name) > 100) {
        $err = 'Name must be between 2 and 100 characters.';
    } else {
        $avatar = $u['avatar'];

        if (!empty($_FILES['avatar']['name'])) {
            $f = $_FILES['avatar'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $err = 'File upload error. Please try again.';
            } elseif ($f['size'] > 2 * 1024 * 1024) {
                $err = 'Avatar image must be smaller than 2 MB.';
            } else {
                $info = getimagesize($f['tmp_name']);
                if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png'], true)) {
                    $err = 'Only JPG and PNG images are accepted.';
                } else {
                    $ext    = ($info['mime'] === 'image/png') ? 'png' : 'jpg';
                    $newFile = 'ava_' . $u['id'] . '_' . time() . '.' . $ext;
                    move_uploaded_file($f['tmp_name'], __DIR__ . '/assets/uploads/' . $newFile);
                    // Remove old avatar from disk if it exists
                    if ($avatar && file_exists(__DIR__ . '/assets/uploads/' . $avatar)) {
                        @unlink(__DIR__ . '/assets/uploads/' . $avatar);
                    }
                    $avatar = $newFile;
                }
            }
        }

        if (!$err) {
            $pdo->prepare('UPDATE users SET full_name = ?, avatar = ? WHERE id = ?')
                ->execute([$name, $avatar, $u['id']]);
            flash('success', 'Profile updated successfully.');
            redirect('profile.php?tab=edit');
        }
    }
}

// ── FIX: Actual total report count (not limited by LIMIT 5) ──────────────────
// Previously: count($myReports) + "+" was always "1+" to "5+" — never the real total.
// Fix: Separate COUNT(*) query for accurate total.
$totalReportsStmt = $pdo->prepare('SELECT COUNT(*) FROM reports WHERE user_id = ?');
$totalReportsStmt->execute([$u['id']]);
$totalReports = (int)$totalReportsStmt->fetchColumn();

// Recent 5 reports for the Reports tab preview (explicit columns only)
$myReports = $pdo->prepare(
    'SELECT id, title, category, status, created_at
     FROM reports
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5'
);
$myReports->execute([$u['id']]);
$myReports = $myReports->fetchAll();

$PAGE_TITLE = 'Profile';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="profile-layout">
  <div class="profile-card">
    <div class="profile-banner"></div>
    <div class="profile-header">
      <div class="profile-ava-wrap">
        <div class="profile-ava">
          <?php if ($u['avatar']): ?>
            <img src="<?= BASE_URL ?>/assets/uploads/<?= e($u['avatar']) ?>"
                 alt="<?= e($u['full_name']) ?>"
                 class="profile-ava-img">
          <?php else: ?>
            <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?= e($u['full_name']) ?></div>
        <div class="profile-handle"><?= e($u['email']) ?></div>
      </div>
    </div>

    <!-- Tab navigation -->
    <div class="profile-tabs">
      <a href="?tab=overview" class="profile-tab <?= $tab === 'overview' ? 'active' : '' ?>">Overview</a>
      <a href="?tab=edit"     class="profile-tab <?= $tab === 'edit'     ? 'active' : '' ?>">Edit Profile</a>
      <a href="?tab=reports"  class="profile-tab <?= $tab === 'reports'  ? 'active' : '' ?>">My Reports</a>
    </div>

    <div class="profile-content">
      <?php if ($err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
      <?php endif; ?>

      <!-- ── OVERVIEW TAB ── -->
      <?php if ($tab === 'overview'): ?>
        <h3 class="profile-section-title">Account Overview</h3>
        <div class="profile-overview-grid">
          <div class="stat-card stat-navy">
            <div class="stat-label">Member Since</div>
            <div class="profile-overview-val text-navy">
              <?= e(date('M Y', strtotime($u['created_at']))) ?>
            </div>
          </div>
          <div class="stat-card stat-ok">
            <div class="stat-label">Total Reports</div>
            <!-- FIX: Shows actual total (was always "5+" before) -->
            <div class="profile-overview-val text-success">
              <?= $totalReports ?>
            </div>
          </div>
        </div>
        <p class="text-muted">Email: <?= e($u['email']) ?></p>
        <p class="text-muted profile-status-row">
          Account status: <span class="badge badge-success">Active</span>
        </p>

      <!-- ── EDIT PROFILE TAB ── -->
      <?php elseif ($tab === 'edit'): ?>
        <h3 class="profile-section-title">Edit Profile</h3>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div class="form-group">
            <label class="form-label" for="profile-name">Full Name</label>
            <input id="profile-name"
                   class="form-control"
                   name="full_name"
                   required
                   maxlength="100"
                   value="<?= e($u['full_name']) ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-control" value="<?= e($u['email']) ?>" disabled>
            <div class="form-hint">Email address cannot be changed.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="profile-avatar">Profile Picture (JPG/PNG, max 2 MB)</label>
            <input id="profile-avatar"
                   type="file"
                   class="form-control"
                   name="avatar"
                   accept="image/jpeg,image/png">
          </div>

          <div class="form-row-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/settings.php">Change Password</a>
          </div>
        </form>

      <!-- ── MY REPORTS TAB ── -->
      <?php elseif ($tab === 'reports'): ?>
        <h3 class="profile-section-title">Recent Reports</h3>
        <?php if (empty($myReports)): ?>
          <div class="empty-state">
            <div class="ic">📭</div>
            No reports yet.
            <a class="link" href="<?= BASE_URL ?>/submit-report.php">Submit your first report →</a>
          </div>
        <?php else: ?>
          <div class="table-scroll">
            <table>
              <thead>
                <tr><th>Title</th><th>Status</th><th>Date</th><th></th></tr>
              </thead>
              <tbody>
              <?php foreach ($myReports as $r):
                  $b = ['Pending' => 'warning', 'In Progress' => 'info',
                        'Resolved' => 'success', 'Closed' => 'neutral',
                        'Rejected' => 'danger'][$r['status']] ?? 'neutral';
              ?>
                <tr>
                  <td class="fw-500"><?= e($r['title']) ?></td>
                  <td><span class="badge badge-<?= $b ?>"><?= e($r['status']) ?></span></td>
                  <td class="text-muted-cell"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
                  <td>
                    <a class="link" href="<?= BASE_URL ?>/report-details.php?id=<?= (int)$r['id'] ?>">View</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="profile-view-all">
            <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/my-report.php">
              View All Reports (<?= $totalReports ?>) →
            </a>
          </div>
        <?php endif; ?>

      <?php endif; ?>
    </div><!-- /profile-content -->
  </div><!-- /profile-card -->
</div>

<?php include __DIR__.'/includes/footer.php'; ?>

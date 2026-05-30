<?php require_once __DIR__.'/config/database.php';
$err = $ok = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (strlen($name)<2 || strlen($name)>100) $err='Name must be 2-100 chars.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err='Invalid email.';
    elseif (!preg_match('/^[a-z]\d{9}@student\.utem\.edu\.my$/i', $email)) $err='Must use a valid UTeM student email (e.g. D032410372@student.utem.edu.my).';
    elseif (strlen($pw)<8) $err='Password must be at least 8 characters.';
    elseif (!preg_match('/[A-Z]/',$pw) || !preg_match('/[a-z]/',$pw) || !preg_match('/[0-9]/',$pw)) $err='Password must include upper, lower and digit.';
    elseif ($pw !== $pw2) $err='Passwords do not match.';
    else {
        $exist = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $exist->execute([$email]);
        if ($exist->fetch()) $err='Email already registered.';
        else {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users(full_name,email,password_hash) VALUES(?,?,?)');
            $ins->execute([$name,$email,$hash]);
            flash('success','Registration successful — please login.');
            redirect('login.php');
        }
    }
}
$PAGE_TITLE='Sign Up';
include __DIR__.'/includes/header.php';
?>
<div class="auth-layout">
  <div class="auth-left">
    <div class="auth-shape" style="width:260px;height:260px;top:-50px;right:-70px;"></div>
    <div class="auth-shape" style="width:130px;height:130px;bottom:60px;left:-30px;animation:auth-float 9s ease-in-out infinite;"></div>
    <div class="auth-left-content">
      <div class="auth-brand-badge">&#x1F3DB; U-SIIRS Portal</div>
      <div class="auth-brand-title">U-SIIRS</div>
      <p class="auth-brand-sub">Join the community keeping<br>UTeM at its best.</p>
      <ul class="auth-features">
        <li><span class="auth-feat-icon">&#x1F4CB;</span> Submit infrastructure issue reports</li>
        <li><span class="auth-feat-icon">&#x1F4E7;</span> Get email updates on your reports</li>
        <li><span class="auth-feat-icon">&#x1F512;</span> UTeM student accounts only</li>
      </ul>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-box">
      <h1 class="auth-title">Create your account</h1>
      <?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Full Name</label><input class="form-control" name="full_name" required maxlength="100" value="<?= e($_POST['full_name'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">UTeM Student Email</label><input type="email" class="form-control" name="email" required maxlength="255" value="<?= e($_POST['email'] ?? '') ?>" placeholder="D032410372@student.utem.edu.my"></div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-wrap">
            <input type="password" class="form-control" name="password" id="pwField" required minlength="8" autocomplete="new-password">
            <button type="button" class="pw-toggle" onclick="togglePw('pwField', this)" aria-label="Show/hide password"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <div class="input-wrap">
            <input type="password" class="form-control" name="password2" id="pwField2" required minlength="8" autocomplete="new-password">
            <button type="button" class="pw-toggle" onclick="togglePw('pwField2', this)" aria-label="Show/hide password"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
        </div>
        <button class="btn btn-login btn-full">Create Account</button>
      </form>
      <div class="auth-switch">Already have an account? <a class="link" href="<?= BASE_URL ?>/login.php">Login</a></div>
    </div>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>

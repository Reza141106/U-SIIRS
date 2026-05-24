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
    elseif (!preg_match('/^[a-z]\d{9}@student\.utem\.edu\.my$/i', $email)) $err='Must use a valid UTeM student email (e.g. D123456789@student.utem.edu.my).';
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
    <div style="color:#fff; text-align:center;">
      <div style="font-family:'DM Serif Display',serif; font-size:2.5rem;">U-SIIRS</div>
      <p style="opacity:.85; margin-top:1rem;">Join the community keeping UTeM at its best.</p>
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
        <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required minlength="8"></div>
        <div class="form-group"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="password2" required minlength="8"></div>
        <button class="btn btn-primary" style="width:100%;">Create Account</button>
      </form>
      <div class="auth-switch">Already have an account? <a class="link" href="<?= BASE_URL ?>/login.php">Login</a></div>
    </div>
  </div>
</div>
</div></body></html>
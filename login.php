<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$db = getDB(); $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verifyCsrf();
  $email = filter_var(clean($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
  $pass = $_POST['password'] ?? '';
  if (!$email || !$pass) { $error='Enter valid email and password.'; }
  if (!$error) {
    $stmt = $db->prepare('SELECT id, password_hash, locked_until, login_attempts FROM users WHERE email = ?');
    $stmt->execute([$email]); $user = $stmt->fetch();
    if ($user && !empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
      $error = 'Account locked. Try again later.';
    } elseif ($user && password_verify($pass, $user['password_hash'])) {
      $db->prepare('UPDATE users SET login_attempts=0, locked_until=NULL WHERE id=?')->execute([$user['id']]);
      session_regenerate_id(true);
      $_SESSION['user_id'] = $user['id'];
      header('Location: dashboard.php'); exit;
    } else {
      if ($user) {
        $attempts = ((int)$user['login_attempts']) + 1;
        if ($attempts >= 5) {
          $db->prepare('UPDATE users SET login_attempts=?, locked_until=DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id=?')->execute([$attempts, $user['id']]);
          $error = 'Account locked for 15 minutes after 5 failed attempts.';
        } else {
          $db->prepare('UPDATE users SET login_attempts=? WHERE id=?')->execute([$attempts, $user['id']]);
          $error = 'Invalid email or password.';
        }
      } else { $error='Invalid email or password.'; }
    }
  }
}
$pageTitle='Login - WanderWise'; require_once 'includes/header.php';
?>

<section class="auth-shell">
  <div class="auth-card">
    <!-- LEFT: VISUAL PANEL -->
    <div class="auth-media">
      <div class="auth-media-badge">🇮🇳 India's Travel Companion</div>
      <div>
        <h2>Welcome Back, Explorer</h2>
        <p>Your next incredible Indian journey is just one login away.</p>
        <div class="auth-features">
          <div class="auth-feature"><i class="fas fa-check-circle"></i> AI-powered day-by-day itineraries</div>
          <div class="auth-feature"><i class="fas fa-check-circle"></i> Smart budget tracking</div>
          <div class="auth-feature"><i class="fas fa-check-circle"></i> Find travel buddies across India</div>
          <div class="auth-feature"><i class="fas fa-check-circle"></i> Weather forecasts & festival guide</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: FORM -->
    <div class="auth-form">
      <div style="margin-bottom:32px">
        <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:800;color:var(--saffron);margin-bottom:4px">
          <i class="fas fa-compass"></i> WanderWise
        </div>
      </div>
      <h2 style="margin-bottom:4px">Sign In</h2>
      <p>New here? <a href="signup.php" style="color:var(--saffron);font-weight:700">Create free account</a></p>
      <?php if($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="form-group">
          <label class="form-label"><i class="fas fa-envelope" style="color:var(--muted);margin-right:6px"></i>Email Address</label>
          <input type="email" name="email" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label class="form-label" style="display:flex;justify-content:space-between">
            <span><i class="fas fa-lock" style="color:var(--muted);margin-right:6px"></i>Password</span>
            <a href="forgot-password.php" style="color:var(--teal);font-weight:600;font-size:.85rem">Forgot password?</a>
          </label>
          <input type="password" name="password" placeholder="Enter your password" required>
        </div>
        <button class="btn-primary" type="submit" style="width:100%;padding:14px;font-size:1rem;margin-top:8px">
          <i class="fas fa-arrow-right-to-bracket"></i> Sign In to WanderWise
        </button>
      </form>
      <div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid var(--border)">
        <p style="font-size:.88rem;color:var(--muted)">Don't have an account? <a href="signup.php" style="color:var(--saffron);font-weight:700">Sign up free →</a></p>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

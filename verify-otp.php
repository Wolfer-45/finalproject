<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$db = getDB();
$error = '';
$email = $_SESSION['otp_email'] ?? '';
if (!$email) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf();
  $otp = clean($_POST['otp'] ?? '');
  $st = $db->prepare('SELECT id, otp_code, otp_expires FROM users WHERE email = ?');
  $st->execute([$email]);
  $u = $st->fetch();
  if ($u && $u['otp_code'] && strtotime($u['otp_expires']) >= time() && password_verify($otp, $u['otp_code'])) {
    $db->prepare('UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires = NULL WHERE id = ?')->execute([$u['id']]);
    $_SESSION['user_id'] = $u['id'];
    unset($_SESSION['otp_email']);
    setFlash('success', 'Account verified. Welcome!');
    header('Location: dashboard.php');
    exit;
  }
  $error = 'Invalid or expired OTP.';
}
$pageTitle = 'Verify OTP';
require_once 'includes/header.php';
?>
<div class="page-container section">
  <div class="card" style="max-width:500px;margin:auto">
    <h2>Verify OTP</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <input name="otp" maxlength="6" placeholder="6-digit OTP" required>
      <button class="btn-primary">Verify</button>
    </form>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>

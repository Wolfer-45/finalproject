<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$db=getDB();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrf();
 $name=clean($_POST['name']??'');$email=filter_var(clean($_POST['email']??''),FILTER_VALIDATE_EMAIL);$phone=clean($_POST['phone']??'');$p=$_POST['password']??'';$c=$_POST['confirm_password']??'';
 if(!$name||!$email||strlen($p)<8||$p!==$c){$error='Please fill valid details and matching passwords (min 8 chars).';}
 if(!$error){$st=$db->prepare('SELECT id FROM users WHERE email=?');$st->execute([$email]);if($st->fetch()){setFlash('error','Email already registered. Please login.');header('Location: login.php');exit;}}
 if(!$error){
   $hash=password_hash($p,PASSWORD_BCRYPT,['cost'=>12]);
  $db->prepare('INSERT INTO users (name,email,phone,password_hash,is_verified) VALUES (?,?,?,?,1)')->execute([$name,$email,$phone,$hash]);
  setFlash('success','Account created! Welcome to WanderWise. Please login.');
  header('Location: login.php');
  exit;
 }
}
$pageTitle='Sign Up - WanderWise'; require_once 'includes/header.php';
?>

<section class="auth-shell">
  <div class="auth-card">
    <!-- LEFT: VISUAL PANEL -->
    <div class="auth-media" style="background:linear-gradient(160deg,rgba(14,107,107,.65),rgba(28,16,7,.75)),url('https://images.unsplash.com/photo-1544015759-237f43a3e0f8?w=900&q=85') center/cover no-repeat">
      <div class="auth-media-badge">🧭 Free Forever</div>
      <div>
        <h2>Start Your Journey Across India</h2>
        <p>Create your WanderWise account and unlock the full power of AI travel planning.</p>
        <div class="auth-features">
          <div class="auth-feature"><i class="fas fa-wand-magic-sparkles"></i> AI day-by-day itineraries in seconds</div>
          <div class="auth-feature"><i class="fas fa-wallet"></i> Trip budget planner & expense tracker</div>
          <div class="auth-feature"><i class="fas fa-user-group"></i> Find travel buddies going your way</div>
          <div class="auth-feature"><i class="fas fa-book-open"></i> Keep your travel memories forever</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: FORM -->
    <div class="auth-form">
      <div style="margin-bottom:28px">
        <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:800;color:var(--saffron);margin-bottom:4px">
          <i class="fas fa-compass"></i> WanderWise
        </div>
      </div>
      <h2 style="margin-bottom:4px">Create Your Account</h2>
      <p>Already have one? <a href="login.php" style="color:var(--saffron);font-weight:700">Sign in here</a></p>
      <?php if($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="form-group">
          <label class="form-label"><i class="fas fa-user" style="color:var(--muted);margin-right:6px"></i>Full Name *</label>
          <input name="name" placeholder="Your full name" required>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-envelope" style="color:var(--muted);margin-right:6px"></i>Email Address *</label>
          <input type="email" name="email" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-phone" style="color:var(--muted);margin-right:6px"></i>Phone <span style="color:var(--muted);font-weight:400">(optional)</span></label>
          <input name="phone" placeholder="+91 98765 43210">
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-lock" style="color:var(--muted);margin-right:6px"></i>Password *</label>
          <input type="password" id="pw" name="password" placeholder="Min. 8 characters" required>
          <small id="pw-strength" style="display:block;margin-top:4px;font-size:.82rem;font-weight:600"></small>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-lock" style="color:var(--muted);margin-right:6px"></i>Confirm Password *</label>
          <input type="password" name="confirm_password" placeholder="Repeat your password" required>
        </div>
        <button class="btn-primary" type="submit" style="width:100%;padding:14px;font-size:1rem;margin-top:8px">
          <i class="fas fa-rocket"></i> Create My Account — It's Free
        </button>
        <p style="font-size:.78rem;color:var(--muted);text-align:center;margin-top:12px">By signing up, you agree to our Terms & Privacy Policy.</p>
      </form>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

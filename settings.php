<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();$db=getDB();$user=getCurrentUser();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();
 if(isset($_POST['change_password'])){$curr=$_POST['current_password']??'';$new=$_POST['new_password']??'';$conf=$_POST['confirm_password']??'';
 $st=$db->prepare('SELECT password_hash FROM users WHERE id=?');$st->execute([$_SESSION['user_id']]);$u=$st->fetch();if(!$u||!password_verify($curr,$u['password_hash'])){$error='Current password is incorrect.';}elseif(strlen($new)<8||$new!==$conf){$error='New passwords must match and be at least 8 characters.';}else{$h=password_hash($new,PASSWORD_BCRYPT,['cost'=>12]);$db->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$h,$_SESSION['user_id']]);setFlash('success','Password updated successfully.');header('Location: settings.php');exit;}}
 if(isset($_POST['change_email'])){$email=filter_var(clean($_POST['new_email']??''),FILTER_VALIDATE_EMAIL);$pw=$_POST['password_confirm']??'';$st=$db->prepare('SELECT password_hash FROM users WHERE id=?');$st->execute([$_SESSION['user_id']]);$u=$st->fetch();if(!$email||!$u||!password_verify($pw,$u['password_hash'])){$error='Invalid email or password.';}else{$c=$db->prepare('SELECT id FROM users WHERE email=? AND id<>?');$c->execute([$email,$_SESSION['user_id']]);if($c->fetch()){$error='Email already taken.';}else{$db->prepare('UPDATE users SET email=? WHERE id=?')->execute([$email,$_SESSION['user_id']]);setFlash('success','Email updated.');header('Location: settings.php');exit;}}}
 if(isset($_POST['save_notify'])){$notify=json_encode(['email'=>!empty($_POST['notify_email']),'trip'=>!empty($_POST['notify_trip'])]);$db->prepare('UPDATE users SET notify_json=? WHERE id=?')->execute([$notify,$_SESSION['user_id']]);setFlash('success','Preferences saved.');header('Location: settings.php');exit;}
 if(isset($_POST['delete_account'])){if(clean($_POST['delete_confirm']??'')==='DELETE ME'){$db->prepare('DELETE FROM users WHERE id=?')->execute([$_SESSION['user_id']]);session_destroy();header('Location: index.php');exit;}else{$error='Type DELETE ME exactly to confirm account deletion.';}}
}
$pageTitle='Settings';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(28,16,7,0.88),rgba(14,107,107,0.75)),url('https://images.unsplash.com/photo-1590273853454-4da1d1dcd7e4?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-cog" style="color:var(--gold-light)"></i> Account Settings
      </div>
      <h1 style="color:#fff;margin-bottom:8px">Account Settings</h1>
      <p style="color:rgba(255,255,255,.8)">Manage your security, notification preferences, and account details.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px;max-width:880px">
  <?php if($error): ?><div class="alert alert-error" style="margin-bottom:24px"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>

  <div style="display:flex;flex-direction:column;gap:28px">

    <!-- CHANGE PASSWORD -->
    <div class="card" style="border-top:4px solid var(--teal)">
      <h3 style="color:var(--teal);margin-bottom:20px"><i class="fas fa-lock"></i> Change Password</h3>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="change_password" value="1">
        <div class="grid-3" style="gap:16px;margin-bottom:16px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" placeholder="Your current password" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" placeholder="Min. 8 characters" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Repeat new password" required>
          </div>
        </div>
        <button type="submit" class="btn-teal"><i class="fas fa-save"></i> Update Password</button>
      </form>
    </div>

    <!-- CHANGE EMAIL -->
    <div class="card" style="border-top:4px solid var(--saffron)">
      <h3 style="color:var(--saffron);margin-bottom:20px"><i class="fas fa-envelope"></i> Change Email</h3>
      <p style="font-size:.88rem;color:var(--muted);margin-bottom:20px">Current email: <strong><?= e($user['email'] ?? '') ?></strong></p>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="change_email" value="1">
        <div class="grid-2" style="gap:16px;margin-bottom:16px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">New Email Address</label>
            <input type="email" name="new_email" placeholder="your-new-email@example.com" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Confirm with Password</label>
            <input type="password" name="password_confirm" placeholder="Enter your password" required>
          </div>
        </div>
        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Email</button>
      </form>
    </div>

    <!-- NOTIFICATIONS -->
    <div class="card" style="border-top:4px solid var(--gold)">
      <h3 style="color:var(--gold);margin-bottom:20px"><i class="fas fa-bell"></i> Notification Preferences</h3>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="save_notify" value="1">
        <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:20px">
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer">
            <input type="checkbox" name="notify_email" style="width:18px;height:18px;accent-color:var(--teal)">
            <div>
              <div style="font-weight:700">Email Notifications</div>
              <div style="font-size:.85rem;color:var(--muted)">Receive news and updates via email</div>
            </div>
          </label>
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer">
            <input type="checkbox" name="notify_trip" style="width:18px;height:18px;accent-color:var(--teal)">
            <div>
              <div style="font-weight:700">Trip Reminders</div>
              <div style="font-size:.85rem;color:var(--muted)">Get reminders before your trips</div>
            </div>
          </label>
        </div>
        <button type="submit" class="btn-outline"><i class="fas fa-save"></i> Save Preferences</button>
      </form>
    </div>

    <!-- QUICK LINKS -->
    <div class="card">
      <h3 style="margin-bottom:16px"><i class="fas fa-link" style="color:var(--teal)"></i> Quick Links</h3>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="profile.php" class="btn-outline btn-sm"><i class="fas fa-user"></i> My Profile</a>
        <a href="dashboard.php" class="btn-outline btn-sm"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="logout.php" class="btn-outline btn-sm" style="border-color:#dc2626;color:#dc2626"><i class="fas fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>

    <!-- DANGER ZONE -->
    <div class="card" style="border:2px solid #dc2626">
      <h3 style="color:#dc2626;margin-bottom:8px"><i class="fas fa-triangle-exclamation"></i> Danger Zone</h3>
      <p style="font-size:.88rem;color:var(--muted);margin-bottom:20px">Permanently delete your account and all your data. This cannot be undone.</p>
      <form method="post" onsubmit="return confirm('Are you absolutely sure? This will permanently delete your account and all your data.')">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="delete_account" value="1">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
          <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px">
            <label class="form-label">Type <strong>DELETE ME</strong> to confirm</label>
            <input name="delete_confirm" placeholder="DELETE ME">
          </div>
          <button type="submit" style="background:#dc2626;color:#fff;border:none;padding:11px 20px;border-radius:var(--radius-sm);font-weight:700;cursor:pointer;flex-shrink:0" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
            <i class="fas fa-trash"></i> Delete Account
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

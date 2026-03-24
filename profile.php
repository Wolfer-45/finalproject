<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();$db=getDB();$user=getCurrentUser();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();$name=clean($_POST['name']??'');$age=(int)($_POST['age']??0);$gender=clean($_POST['gender']??'');$lang=clean($_POST['language']??'en');$style=clean($_POST['travel_style']??'');$types=implode(',',$_POST['travel_types']??[]);$en=clean($_POST['emergency_name']??'');$ep=clean($_POST['emergency_phone']??'');$er=clean($_POST['emergency_rel']??'');$avatar=$user['avatar']??'default-avatar.png';
 if(!empty($_FILES['avatar']['name'])){if($_FILES['avatar']['size']>MAX_FILE_SIZE){$error='Avatar too large.';}else{$info=@getimagesize($_FILES['avatar']['tmp_name']);$allowed=[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP];if(!$info||!in_array($info[2],$allowed,true)){$error='Invalid avatar format.';}else{$dir=UPLOAD_PATH.$_SESSION['user_id'].'/';if(!is_dir($dir)){mkdir($dir,0755,true);} $fname=uniqid('avatar_',true).'.jpg';if(move_uploaded_file($_FILES['avatar']['tmp_name'],$dir.$fname)){$avatar=$_SESSION['user_id'].'/'.$fname;}}}}
 if(!$error){$db->prepare('UPDATE users SET name=?,age=?,gender=?,language=?,travel_style=?,travel_types=?,emergency_name=?,emergency_phone=?,emergency_rel=?,avatar=? WHERE id=?')->execute([$name,$age,$gender,$lang,$style,$types,$en,$ep,$er,$avatar,$_SESSION['user_id']]);setFlash('success','Profile updated successfully.');header('Location: profile.php');exit;}}
$pageTitle='My Profile';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(28,16,7,0.88),rgba(14,107,107,0.75)),url('https://images.unsplash.com/photo-1590273853454-4da1d1dcd7e4?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-user" style="color:var(--gold-light)"></i> My Profile
      </div>
      <h1 style="color:#fff;margin-bottom:8px">Traveller Profile</h1>
      <p style="color:rgba(255,255,255,.8)">Update your travel preferences, personal details, and emergency contacts.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

    <div style="display:grid;grid-template-columns:300px 1fr;gap:32px;align-items:start" class="profile-layout">

      <!-- LEFT: AVATAR & QUICK INFO -->
      <div>
        <div class="card" style="text-align:center;padding:32px">
          <?php
          $avatarPath = $user['avatar'] ?? '';
          $hasAvatar = $avatarPath && $avatarPath !== 'default-avatar.png' && file_exists(UPLOAD_PATH . $avatarPath);
          ?>
          <?php if($hasAvatar): ?>
          <img src="<?= e(UPLOAD_URL . $avatarPath) ?>" alt="Avatar" class="profile-avatar" style="margin:0 auto 16px;display:block">
          <?php else: ?>
          <div class="profile-avatar-placeholder" style="margin-bottom:16px"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
          <?php endif; ?>
          <h3 style="margin-bottom:4px"><?= e($user['name'] ?? 'Traveller') ?></h3>
          <p style="font-size:.88rem;color:var(--muted);margin-bottom:20px"><?= e($user['email'] ?? '') ?></p>
          <?php if($user['travel_style']): ?>
          <span class="badge badge-saffron"><i class="fas fa-sliders"></i> <?= e($user['travel_style']) ?></span>
          <?php endif; ?>
          <div style="margin-top:20px">
            <label class="form-label" style="font-size:.82rem">Update Profile Photo</label>
            <input type="file" name="avatar" accept="image/*" style="font-size:.85rem">
          </div>
        </div>

        <div class="card" style="margin-top:20px">
          <h4 style="color:var(--teal);margin-bottom:16px;font-family:'Inter',sans-serif"><i class="fas fa-link"></i> Quick Links</h4>
          <a href="dashboard.php" class="btn-outline btn-sm" style="width:100%;margin-bottom:10px;display:flex;justify-content:center"><i class="fas fa-th-large"></i> Dashboard</a>
          <a href="settings.php" class="btn-outline btn-sm" style="width:100%;margin-bottom:10px;display:flex;justify-content:center"><i class="fas fa-cog"></i> Settings</a>
          <a href="storybook.php" class="btn-outline btn-sm" style="width:100%;display:flex;justify-content:center"><i class="fas fa-book-open"></i> My Storybook</a>
        </div>
      </div>

      <!-- RIGHT: FORM -->
      <div style="display:flex;flex-direction:column;gap:24px">
        <!-- PERSONAL INFO -->
        <div class="card">
          <h3 style="color:var(--saffron);margin-bottom:20px"><i class="fas fa-user-circle"></i> Personal Information</h3>
          <div class="grid-2" style="gap:16px">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Full Name *</label>
              <input name="name" value="<?= e($user['name'] ?? '') ?>" placeholder="Your full name" required>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Age</label>
              <input type="number" name="age" value="<?= e((string)($user['age'] ?? '')) ?>" placeholder="Your age" min="13" max="100">
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Gender</label>
              <select name="gender">
                <option value="">Prefer not to say</option>
                <?php foreach(['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not'=>'Prefer not to say'] as $v=>$l): ?>
                <option value="<?= e($v) ?>" <?= ($user['gender']??'') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Language</label>
              <select name="language">
                <option value="en" <?= ($user['language']??'en')==='en'?'selected':'' ?>>English</option>
                <option value="hi" <?= ($user['language']??'')==='hi'?'selected':'' ?>>Hindi</option>
                <option value="ta" <?= ($user['language']??'')==='ta'?'selected':'' ?>>Tamil</option>
                <option value="mr" <?= ($user['language']??'')==='mr'?'selected':'' ?>>Marathi</option>
              </select>
            </div>
          </div>
        </div>

        <!-- TRAVEL PREFERENCES -->
        <div class="card">
          <h3 style="color:var(--teal);margin-bottom:20px"><i class="fas fa-route"></i> Travel Preferences</h3>
          <div class="form-group">
            <label class="form-label">Preferred Travel Style</label>
            <select name="travel_style">
              <option value="">Not specified</option>
              <?php foreach(['Budget','Mid-Range','Luxury'] as $s): ?>
              <option value="<?= e($s) ?>" <?= ($user['travel_style']??'')===$s?'selected':'' ?>><?= e($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Favourite Travel Types</label>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px">
              <?php $saved=explode(',',$user['travel_types']??''); foreach(['historical'=>'🏛️ Historical','food'=>'🍽️ Food','adventure'=>'🏔️ Adventure','nature'=>'🌿 Nature','spiritual'=>'🕌 Spiritual','beach'=>'🏖️ Beach','culture'=>'🎭 Cultural'] as $tt=>$label): ?>
              <label style="cursor:pointer">
                <input type="checkbox" name="travel_types[]" value="<?= e($tt) ?>" <?= in_array($tt,$saved,true)?'checked':'' ?> style="display:none" class="travel-type-cb">
                <div class="interest-chip" style="padding:10px 16px;border:2px solid var(--border);border-radius:999px;font-size:.88rem;font-weight:600;transition:all .2s;cursor:pointer;<?= in_array($tt,$saved,true)?'border-color:var(--saffron);background:rgba(212,98,26,.08);color:var(--saffron)':'' ?>"><?= $label ?></div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- EMERGENCY CONTACT -->
        <div class="card" style="border-top:4px solid #dc2626">
          <h3 style="color:#dc2626;margin-bottom:8px"><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
          <p style="font-size:.88rem;color:var(--muted);margin-bottom:20px">In case of emergencies, this contact will be notified.</p>
          <div class="grid-3" style="gap:16px">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Contact Name</label>
              <input name="emergency_name" value="<?= e($user['emergency_name'] ?? '') ?>" placeholder="Full name">
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Phone Number</label>
              <input name="emergency_phone" value="<?= e($user['emergency_phone'] ?? '') ?>" placeholder="+91 XXXXX XXXXX">
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Relationship</label>
              <input name="emergency_rel" value="<?= e($user['emergency_rel'] ?? '') ?>" placeholder="e.g. Parent, Spouse">
            </div>
          </div>
        </div>

        <button type="submit" class="btn-primary" style="align-self:flex-start;padding:14px 32px">
          <i class="fas fa-save"></i> Save Profile
        </button>
      </div>
    </div>
  </form>
</div>

<style>
@media(max-width:768px){.profile-layout{grid-template-columns:1fr !important}}
.travel-type-cb:checked + .interest-chip, .travel-type-cb + .interest-chip:hover{border-color:var(--saffron);background:rgba(212,98,26,.08);color:var(--saffron)}
</style>

<?php require_once 'includes/footer.php'; ?>

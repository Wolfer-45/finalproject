<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();$db=getDB();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();$title=clean($_POST['title']??'');$trip=(int)($_POST['trip_id']??0);$day=(int)($_POST['day_number']??1);$loc=clean($_POST['location']??'');$date=clean($_POST['travel_date']??'');$mood=clean($_POST['mood']??'good');$content=clean($_POST['content']??'');$photoPath='';
 if(!empty($_FILES['photo']['name'])){if($_FILES['photo']['size']>MAX_FILE_SIZE){$error='Photo too large.';}else{$info=@getimagesize($_FILES['photo']['tmp_name']);$allowed=[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP];if(!$info||!in_array($info[2],$allowed,true)){$error='Only JPG/PNG/WEBP allowed.';}else{$userDir=UPLOAD_PATH.$_SESSION['user_id'].'/';if(!is_dir($userDir)){mkdir($userDir,0755,true);} $fname=uniqid('photo_',true).'.jpg';if(move_uploaded_file($_FILES['photo']['tmp_name'],$userDir.$fname)){$photoPath=$_SESSION['user_id'].'/'.$fname;}}}}
 if(!$error){$db->prepare('INSERT INTO storybook (user_id,trip_id,day_number,title,content,location,mood,photo_path,travel_date) VALUES (?,?,?,?,?,?,?,?,?)')->execute([$_SESSION['user_id'],$trip?:null,$day,$title,$content,$loc,$mood,$photoPath,$date?:null]);setFlash('success','Memory saved to your storybook!');header('Location: storybook.php');exit;}}
$tr=$db->prepare('SELECT id,destination FROM trips WHERE user_id=? ORDER BY created_at DESC');$tr->execute([$_SESSION['user_id']]);$trips=$tr->fetchAll();
$pageTitle='Add Memory';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(123,30,50,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-book-open" style="color:var(--gold-light)"></i> Storybook
      </div>
      <h1 style="color:#fff;margin-bottom:8px">Capture a Travel Memory</h1>
      <p style="color:rgba(255,255,255,.8)">Every journey deserves to be remembered. Write your story, attach a photo, and relive it forever.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <div style="max-width:700px;margin:0 auto">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px">
      <a href="storybook.php" class="btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Storybook</a>
    </div>
    <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>
    <div class="card" style="border-top:4px solid var(--maroon)">
      <h3 style="color:var(--maroon);margin-bottom:24px"><i class="fas fa-pen"></i> Add a Memory</h3>
      <form method="post" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:18px">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Memory Title *</label>
          <input name="title" placeholder="e.g. Sunrise at Taj Mahal, Streets of Varanasi..." required>
        </div>
        <div class="grid-2" style="gap:16px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Linked Trip <span style="color:var(--muted);font-weight:400">(optional)</span></label>
            <select name="trip_id">
              <option value="0">Standalone Memory</option>
              <?php foreach($trips as $t): ?>
              <option value="<?= (int)$t['id'] ?>"><?= e($t['destination']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Day Number</label>
            <input type="number" name="day_number" value="1" min="1" max="60">
          </div>
        </div>
        <div class="grid-2" style="gap:16px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label"><i class="fas fa-map-pin" style="color:var(--saffron)"></i> Location</label>
            <input name="location" placeholder="e.g. Agra, Uttar Pradesh">
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label"><i class="fas fa-calendar" style="color:var(--teal)"></i> Date</label>
            <input type="date" name="travel_date">
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Trip Mood</label>
          <div style="display:flex;gap:10px;margin-top:6px;flex-wrap:wrap">
            <?php foreach(['amazing'=>['😍','Amazing'],'good'=>['😊','Good'],'okay'=>['😐','Okay'],'tough'=>['😓','Tough']] as $v=>$l): ?>
            <label style="cursor:pointer">
              <input type="radio" name="mood" value="<?= e($v) ?>" style="display:none" class="mood-radio" <?= $v==='good'?'checked':'' ?>>
              <div class="mood-option" data-mood="<?= e($v) ?>" style="padding:10px 18px;border:2px solid var(--border);border-radius:999px;font-size:.9rem;font-weight:600;cursor:pointer;transition:all .2s;<?= $v==='good'?'border-color:var(--saffron);background:rgba(212,98,26,.08);color:var(--saffron)':'' ?>">
                <?= $l[0] ?> <?= e($l[1]) ?>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Your Story *</label>
          <textarea name="content" rows="8" placeholder="Write about this moment. What did you see, feel, experience? What made it unforgettable?" required style="min-height:200px"></textarea>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label"><i class="fas fa-camera" style="color:var(--maroon)"></i> Photo <span style="color:var(--muted);font-weight:400">(optional, max 5MB)</span></label>
          <input type="file" name="photo" accept="image/*">
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <button type="submit" class="btn-primary" style="flex:1"><i class="fas fa-save"></i> Save Memory</button>
          <a href="storybook.php" class="btn-outline" style="flex:1;text-align:center"><i class="fas fa-times"></i> Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.mood-radio').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.mood-option').forEach(o => { o.style.borderColor='var(--border)'; o.style.background=''; o.style.color=''; });
    const opt = document.querySelector('.mood-option[data-mood="'+r.value+'"]');
    if(opt) { opt.style.borderColor='var(--saffron)'; opt.style.background='rgba(212,98,26,.08)'; opt.style.color='var(--saffron)'; }
  });
});
</script>

<?php require_once 'includes/footer.php'; ?>

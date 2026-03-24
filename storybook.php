<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();$db=getDB();$filter=(int)($_GET['trip_id']??0);
if($filter){$st=$db->prepare('SELECT id,title,location,travel_date,mood,photo_path FROM storybook WHERE user_id=? AND trip_id=? ORDER BY created_at DESC');$st->execute([$_SESSION['user_id'],$filter]);}
else{$st=$db->prepare('SELECT id,title,location,travel_date,mood,photo_path FROM storybook WHERE user_id=? ORDER BY created_at DESC');$st->execute([$_SESSION['user_id']]);}
$rows=$st->fetchAll();$t=$db->prepare('SELECT id,destination FROM trips WHERE user_id=? ORDER BY created_at DESC');$t->execute([$_SESSION['user_id']]);$trips=$t->fetchAll();
$pageTitle='My Storybook';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(123,30,50,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-book-open" style="color:var(--gold-light)"></i> My Storybook
      </div>
      <h1 style="color:#fff;margin-bottom:8px">Travel Memories & Stories</h1>
      <p style="color:rgba(255,255,255,.8)">Every journey tells a story. Capture yours with photos, moods, and beautiful memories.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">

  <!-- ACTIONS & FILTER -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:16px">
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <form method="get" style="display:flex;gap:10px;align-items:center">
        <select name="trip_id" style="min-width:180px;margin:0">
          <option value="0">All Trips</option>
          <?php foreach($trips as $tr): ?>
          <option value="<?= (int)$tr['id'] ?>" <?= $filter===(int)$tr['id']?'selected':'' ?>><?= e($tr['destination']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
      </form>
    </div>
    <a href="storybook-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add Memory</a>
  </div>

  <?php if(count($rows) > 0): ?>
  <div class="grid-3">
    <?php foreach($rows as $r):
      $moodEmojis = ['amazing'=>'😍','good'=>'😊','okay'=>'😐','tough'=>'😓'];
      $emoji = $moodEmojis[$r['mood']] ?? '📖';
    ?>
    <a class="story-card" href="storybook-view.php?id=<?= (int)$r['id'] ?>" style="display:block;color:inherit">
      <?php if($r['photo_path']): ?>
      <img src="<?= e(UPLOAD_URL . $r['photo_path']) ?>" alt="<?= e($r['title']) ?>" class="story-card-img" loading="lazy">
      <?php else: ?>
      <div class="story-card-img" style="background:linear-gradient(135deg,rgba(212,98,26,.12),rgba(14,107,107,.12));display:flex;align-items:center;justify-content:center;font-size:3rem;height:180px"><?= $emoji ?></div>
      <?php endif; ?>
      <div class="story-card-body">
        <div class="story-card-meta">
          <?php if($r['location']): ?><span><i class="fas fa-map-pin" style="color:var(--saffron)"></i> <?= e($r['location']) ?></span><?php endif; ?>
          <?php if($r['travel_date']): ?><span><i class="fas fa-calendar" style="color:var(--teal)"></i> <?= date('d M Y', strtotime($r['travel_date'])) ?></span><?php endif; ?>
        </div>
        <h3><?= e($r['title']) ?></h3>
        <?php if($r['mood']): ?>
        <span class="mood-<?= e($r['mood']) ?>" style="font-size:.82rem"><?= $emoji ?> <?= e(ucfirst($r['mood'])) ?> trip</span>
        <?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state" style="padding:80px 20px">
    <i class="fas fa-camera-retro"></i>
    <h3>Your storybook is empty</h3>
    <p>Start capturing your travel memories. Add stories, photos, and moods from your trips!</p>
    <a href="storybook-add.php" class="btn-primary" style="margin-top:20px"><i class="fas fa-plus"></i> Add My First Memory</a>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';require_once 'includes/gemini.php';requireLogin();$db=getDB();$id=(int)($_GET['id']??0);
$st=$db->prepare('SELECT s.*,t.destination FROM storybook s LEFT JOIN trips t ON t.id=s.trip_id WHERE s.id=? AND s.user_id=?');$st->execute([$id,$_SESSION['user_id']]);$row=$st->fetch();if(!$row){die('Not found');}
$caption='';if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();if(aiRateLimitExceeded()){$caption='AI limit reached.';}else{$caption=callGemini('Write a poetic 2-line caption for this travel memory in '.($row['location']?:$row['destination']).'. Mood: '.$row['mood']);}}
$pageTitle=e($row['title']);require_once 'includes/header.php';
$moodColors=['amazing'=>'var(--teal)','good'=>'var(--saffron)','okay'=>'var(--gold)','tough'=>'var(--maroon)'];
$moodEmojis=['amazing'=>'😍','good'=>'😊','okay'=>'😐','tough'=>'😓'];
$moodColor=$moodColors[$row['mood']]??'var(--muted)';
$moodEmoji=$moodEmojis[$row['mood']]??'📖';
?>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <div style="max-width:760px;margin:0 auto">

    <!-- NAVIGATION -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px">
      <a href="storybook.php" class="btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Storybook</a>
      <a href="storybook-add.php" class="btn-outline btn-sm"><i class="fas fa-plus"></i> Add Memory</a>
    </div>

    <!-- STORY CARD -->
    <div class="card" style="padding:0;overflow:hidden">
      <?php if($row['photo_path']): ?>
      <div style="position:relative">
        <img src="<?= e(UPLOAD_URL . $row['photo_path']) ?>" alt="<?= e($row['title']) ?>" style="width:100%;height:400px;object-fit:cover;display:block">
        <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.7));padding:24px">
          <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);color:#fff;padding:6px 14px;border-radius:999px;font-size:.82rem;font-weight:700"><?= $moodEmoji ?> <?= e(ucfirst($row['mood'])) ?> trip</span>
        </div>
      </div>
      <?php endif; ?>

      <div style="padding:32px">
        <!-- META -->
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px">
          <?php if($row['location']): ?>
          <span style="display:flex;align-items:center;gap:6px;font-size:.85rem;color:var(--muted)"><i class="fas fa-map-pin" style="color:var(--saffron)"></i> <?= e($row['location']) ?></span>
          <?php endif; ?>
          <?php if($row['travel_date']): ?>
          <span style="display:flex;align-items:center;gap:6px;font-size:.85rem;color:var(--muted)"><i class="fas fa-calendar" style="color:var(--teal)"></i> <?= date('d F Y', strtotime($row['travel_date'])) ?></span>
          <?php endif; ?>
          <?php if($row['destination']): ?>
          <span style="display:flex;align-items:center;gap:6px;font-size:.85rem;color:var(--muted)"><i class="fas fa-route" style="color:var(--gold)"></i> <?= e($row['destination']) ?></span>
          <?php endif; ?>
          <?php if(!$row['photo_path']): ?>
          <span style="display:flex;align-items:center;gap:6px;font-size:.85rem;font-weight:700;color:<?= $moodColor ?>"><?= $moodEmoji ?> <?= e(ucfirst($row['mood'])) ?> trip</span>
          <?php endif; ?>
        </div>

        <h1 style="margin-bottom:24px"><?= e($row['title']) ?></h1>

        <!-- AI CAPTION -->
        <?php if($caption && $caption !== 'AI limit reached.'): ?>
        <div style="background:linear-gradient(135deg,rgba(212,98,26,.08),rgba(14,107,107,.08));border:1px solid var(--border);border-radius:var(--radius-sm);padding:18px 20px;margin-bottom:24px;font-style:italic;font-family:'Playfair Display',serif;font-size:1.05rem;line-height:1.7">
          <i class="fas fa-quote-left" style="color:var(--saffron);margin-right:8px"></i>
          <?= nl2br(e($caption)) ?>
        </div>
        <?php elseif($caption === 'AI limit reached.'): ?>
        <div class="alert alert-error" style="margin-bottom:24px"><i class="fas fa-exclamation-circle"></i> AI rate limit reached. Try again in an hour.</div>
        <?php endif; ?>

        <!-- STORY CONTENT -->
        <div style="font-size:1rem;line-height:1.9;color:var(--text);margin-bottom:32px">
          <?= nl2br(e($row['content'])) ?>
        </div>

        <!-- ACTIONS -->
        <div style="display:flex;gap:12px;flex-wrap:wrap;padding-top:24px;border-top:1px solid var(--border)">
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn-teal"><i class="fas fa-wand-magic-sparkles"></i> Generate AI Caption</button>
          </form>
          <?php if($row['trip_id']): ?>
          <a href="itinerary.php?trip_id=<?= (int)$row['trip_id'] ?>" class="btn-outline"><i class="fas fa-route"></i> View Itinerary</a>
          <?php endif; ?>
          <a href="storybook.php" class="btn-outline"><i class="fas fa-book-open"></i> All Memories</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

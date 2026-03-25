<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();
$db=getDB();$filter=(int)($_GET['trip_id']??0);
if($filter){$st=$db->prepare('SELECT id,title,content,location,travel_date,mood,photo_path FROM storybook WHERE user_id=? AND trip_id=? ORDER BY travel_date ASC, created_at ASC');$st->execute([$_SESSION['user_id'],$filter]);}
else{$st=$db->prepare('SELECT id,title,content,location,travel_date,mood,photo_path FROM storybook WHERE user_id=? ORDER BY travel_date ASC, created_at ASC');$st->execute([$_SESSION['user_id']]);}
$rows=$st->fetchAll();
$t=$db->prepare('SELECT id,destination FROM trips WHERE user_id=? ORDER BY created_at DESC');$t->execute([$_SESSION['user_id']]);$trips=$t->fetchAll();
$totalStories = count($rows);
$moodEmojis = ['amazing'=>'😍','good'=>'😊','okay'=>'😐','tough'=>'😓'];
$pageTitle='My Storybook';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(0,50,98,0.88),rgba(0,112,187,0.72)),url('https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-book-open" style="color:rgba(255,255,255,.8)"></i> My Travel Album
      </div>
      <h1 style="color:#fff;margin-bottom:8px">My Storybook</h1>
      <p style="color:rgba(255,255,255,.8)">Your journey, told one page at a time. Flip through your memories like a real travel album.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">

  <!-- FILTER + ADD -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:16px">
    <form method="get" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <select name="trip_id" style="min-width:180px;margin:0" onchange="this.form.submit()">
        <option value="0">All Trips</option>
        <?php foreach($trips as $tr): ?>
        <option value="<?= (int)$tr['id'] ?>" <?= $filter===(int)$tr['id']?'selected':'' ?>><?= e($tr['destination']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <a href="storybook-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add Memory</a>
  </div>

  <!-- BOOK UI -->
  <div class="book-wrap">
    <div style="width:100%;max-width:940px">

      <!-- Book title strip -->
      <div style="text-align:center;margin-bottom:24px">
        <div style="display:inline-flex;align-items:center;gap:10px;background:var(--primary-dark);color:#fff;padding:10px 28px;border-radius:999px;font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;letter-spacing:.04em">
          <i class="fas fa-book"></i> My Travel Stories
          <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:999px;font-size:.82rem;font-weight:600"><?= $totalStories ?> <?= $totalStories===1?'Memory':'Memories' ?></span>
        </div>
      </div>

      <div class="book" id="storybook">
        <div class="book-spine"></div>

        <?php if($totalStories === 0): ?>
        <!-- Empty book - both pages blank with add CTA -->
        <div class="book-page book-page-left">
          <div class="book-empty-page" style="min-height:460px">
            <i class="fas fa-book-open"></i>
            <h3 style="font-family:'Playfair Display',serif;color:var(--primary-dark);margin-bottom:10px">Your story begins here</h3>
            <p style="font-style:italic;margin-bottom:24px">This page is waiting for your first memory.<br>Every great journey starts with a single step.</p>
            <a href="storybook-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add My First Memory</a>
          </div>
          <div class="book-page-num">Page 1</div>
        </div>
        <div class="book-page book-page-right">
          <div class="book-empty-page" style="min-height:460px">
            <i class="fas fa-camera" style="font-size:2rem;opacity:.2;margin-bottom:16px"></i>
            <p style="font-style:italic;color:var(--muted)">Add photos, locations and moods<br>to fill these pages with colour.</p>
          </div>
          <div class="book-page-num">Page 2</div>
        </div>
        <?php else: ?>
        <!-- Render book pages - 2 stories per spread (left + right) -->
        <?php
        $spreads = array_chunk($rows, 2);
        $totalSpreads = count($spreads);
        foreach($spreads as $si => $spread):
          $isLastSpread = ($si === $totalSpreads - 1);
          $leftStory = $spread[0] ?? null;
          $rightStory = $spread[1] ?? null;
        ?>
        <div class="book-page book-page-left" data-spread="<?= $si ?>" style="<?= $si > 0 ? 'display:none' : '' ?>">
          <?php if($leftStory): ?>
          <div class="book-story-content">
            <?php if($leftStory['photo_path']): ?>
            <img src="<?= e(UPLOAD_URL . $leftStory['photo_path']) ?>" alt="<?= e($leftStory['title']) ?>" class="story-img">
            <?php endif; ?>
            <h3><?= e($leftStory['title']) ?></h3>
            <div class="story-meta">
              <?php if($leftStory['location']): ?><span><i class="fas fa-map-pin"></i> <?= e($leftStory['location']) ?></span><?php endif; ?>
              <?php if($leftStory['travel_date']): ?><span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($leftStory['travel_date'])) ?></span><?php endif; ?>
              <?php if($leftStory['mood']): ?><span><?= $moodEmojis[$leftStory['mood']] ?? '📖' ?> <?= e(ucfirst($leftStory['mood'])) ?></span><?php endif; ?>
            </div>
            <p><?= nl2br(e(mb_substr($leftStory['content'] ?? '', 0, 600))) ?><?= mb_strlen($leftStory['content'] ?? '') > 600 ? '…' : '' ?></p>
            <div style="margin-top:16px">
              <a href="storybook-view.php?id=<?= (int)$leftStory['id'] ?>" style="font-size:.8rem;color:var(--primary);font-weight:700;font-family:'Inter',sans-serif"><i class="fas fa-arrow-right"></i> Read full story</a>
            </div>
          </div>
          <?php else: ?>
          <div class="book-empty-page">
            <i class="fas fa-book-open"></i>
            <p>Page waiting for a story...</p>
          </div>
          <?php endif; ?>
          <div class="book-page-num">Page <?= ($si * 2) + 1 ?></div>
        </div>

        <div class="book-page book-page-right" data-spread="<?= $si ?>" style="<?= $si > 0 ? 'display:none' : '' ?>">
          <?php if($rightStory): ?>
          <div class="book-story-content">
            <?php if($rightStory['photo_path']): ?>
            <img src="<?= e(UPLOAD_URL . $rightStory['photo_path']) ?>" alt="<?= e($rightStory['title']) ?>" class="story-img">
            <?php endif; ?>
            <h3><?= e($rightStory['title']) ?></h3>
            <div class="story-meta">
              <?php if($rightStory['location']): ?><span><i class="fas fa-map-pin"></i> <?= e($rightStory['location']) ?></span><?php endif; ?>
              <?php if($rightStory['travel_date']): ?><span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($rightStory['travel_date'])) ?></span><?php endif; ?>
              <?php if($rightStory['mood']): ?><span><?= $moodEmojis[$rightStory['mood']] ?? '📖' ?> <?= e(ucfirst($rightStory['mood'])) ?></span><?php endif; ?>
            </div>
            <p><?= nl2br(e(mb_substr($rightStory['content'] ?? '', 0, 600))) ?><?= mb_strlen($rightStory['content'] ?? '') > 600 ? '…' : '' ?></p>
            <div style="margin-top:16px">
              <a href="storybook-view.php?id=<?= (int)$rightStory['id'] ?>" style="font-size:.8rem;color:var(--primary);font-weight:700;font-family:'Inter',sans-serif"><i class="fas fa-arrow-right"></i> Read full story</a>
            </div>
          </div>
          <?php else: ?>
          <!-- Last right page: add another story -->
          <div class="book-empty-page" style="justify-content:center">
            <i class="fas fa-plus-circle" style="font-size:2.5rem;color:var(--primary);opacity:.5;margin-bottom:16px"></i>
            <h3 style="font-family:'Playfair Display',serif;color:var(--primary-dark);margin-bottom:10px">Continue your story</h3>
            <p style="margin-bottom:20px">Every adventure deserves its own page.</p>
            <a href="storybook-add.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Memory</a>
          </div>
          <?php endif; ?>
          <div class="book-page-num">Page <?= ($si * 2) + 2 ?></div>
        </div>
        <?php endforeach; ?>

        <?php if($totalStories > 0 && $totalStories % 2 === 0): ?>
        <!-- Even number of stories: add extra "end" spread with add-story CTA -->
        <div class="book-page book-page-left" data-spread="<?= $totalSpreads ?>" style="display:none">
          <div class="book-empty-page" style="min-height:460px">
            <i class="fas fa-bookmark" style="font-size:2.5rem;color:var(--primary);opacity:.4;margin-bottom:16px"></i>
            <h3 style="font-family:'Playfair Display',serif;color:var(--primary-dark);margin-bottom:10px">Your journey continues...</h3>
            <p style="margin-bottom:20px;font-style:italic">Add your next memory to keep the story going.</p>
            <a href="storybook-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Memory</a>
          </div>
          <div class="book-page-num">Page <?= ($totalSpreads * 2) + 1 ?></div>
        </div>
        <div class="book-page book-page-right" data-spread="<?= $totalSpreads ?>" style="display:none">
          <div class="book-empty-page" style="min-height:460px">
            <i class="fas fa-star" style="font-size:2rem;opacity:.2;margin-bottom:16px;display:block"></i>
            <p style="font-style:italic;color:var(--muted)">The best chapters are yet to be written.</p>
          </div>
          <div class="book-page-num">Page <?= ($totalSpreads * 2) + 2 ?></div>
        </div>
        <?php endif; ?>

        <?php endif; ?>
      </div>

      <!-- Book navigation -->
      <?php $totalBookSpreads = $totalStories > 0 ? ($totalStories % 2 === 0 ? $totalSpreads + 1 : $totalSpreads) : 1; ?>
      <div class="book-nav" style="margin-top:28px">
        <button class="book-nav-btn" id="prev-btn" onclick="turnPage(-1)" disabled title="Previous pages">
          <i class="fas fa-chevron-left"></i>
        </button>
        <div class="book-page-indicator">
          <span id="spread-label">Pages 1–2</span>
          <div style="font-size:.75rem;color:var(--muted);margin-top:4px">of <?= $totalStories === 0 ? 1 : $totalBookSpreads ?> spread<?= $totalBookSpreads!==1?'s':'' ?></div>
        </div>
        <button class="book-nav-btn" id="next-btn" onclick="turnPage(1)" <?= ($totalBookSpreads <= 1 && $totalStories <= 1) ? 'disabled' : '' ?> title="Next pages">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let currentSpread = 0;
const totalSpreads = <?= $totalStories === 0 ? 1 : $totalBookSpreads ?>;

function turnPage(dir) {
  const newSpread = currentSpread + dir;
  if (newSpread < 0 || newSpread >= totalSpreads) return;

  // Hide current
  document.querySelectorAll(`[data-spread="${currentSpread}"]`).forEach(p => {
    p.style.transition = 'opacity .25s ease';
    p.style.opacity = '0';
    setTimeout(() => { p.style.display = 'none'; p.style.opacity = ''; }, 250);
  });

  currentSpread = newSpread;

  // Show new
  setTimeout(() => {
    document.querySelectorAll(`[data-spread="${currentSpread}"]`).forEach(p => {
      p.style.display = 'block';
      p.style.opacity = '0';
      requestAnimationFrame(() => { p.style.transition = 'opacity .3s ease'; p.style.opacity = '1'; });
    });
    updateNav();
  }, 260);
}

function updateNav() {
  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  const label = document.getElementById('spread-label');
  if (prevBtn) prevBtn.disabled = currentSpread === 0;
  if (nextBtn) nextBtn.disabled = currentSpread >= totalSpreads - 1;
  if (label) {
    const p1 = currentSpread * 2 + 1;
    const p2 = currentSpread * 2 + 2;
    label.textContent = `Pages ${p1}–${p2}`;
  }
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
  if (e.key === 'ArrowLeft') turnPage(-1);
  if (e.key === 'ArrowRight') turnPage(1);
});

updateNav();
</script>

<?php require_once 'includes/footer.php'; ?>

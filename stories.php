<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';require_once 'includes/gemini.php';
$db=getDB();$dest=clean($_GET['dest']??$_POST['destination']??'');$story='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();$dest=clean($_POST['destination']??'');}
if($dest){$cache=$db->prepare('SELECT story_text,created_at FROM stories_cache WHERE destination=?');$cache->execute([$dest]);$c=$cache->fetch();
if($c && strtotime($c['created_at'])>strtotime('-30 days')){$story=$c['story_text'];}
else{if(aiRateLimitExceeded()){$error='AI limit reached (10/hour).';}else{$prompt="Write an engaging travel story about {$dest} in two parts:\nPART 1 - HISTORY (200 words): The fascinating history of this place.\nPART 2 - LOCAL LEGEND (150 words): A famous local legend or folklore story.\nMake it exciting and make the reader want to visit.";$story=callGemini($prompt);$db->prepare('INSERT INTO stories_cache (destination,story_text,created_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE story_text=VALUES(story_text), created_at=NOW()')->execute([$dest,$story]);}}
}
$pageTitle='Destination Stories';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(123,30,50,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1524492412937-b28074a5d7da?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-scroll" style="color:var(--gold-light)"></i> Destination Stories
      </div>
      <h1 style="color:#fff;margin-bottom:8px">History & Local Legends</h1>
      <p style="color:rgba(255,255,255,.8)">Discover the rich history, folklore, and untold stories behind every Indian destination before you visit.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <div style="display:grid;grid-template-columns:340px 1fr;gap:32px;align-items:start" class="stories-layout">

    <!-- SIDEBAR: FORM + POPULAR -->
    <div style="display:flex;flex-direction:column;gap:20px">
      <div class="card" style="border-top:4px solid var(--maroon)">
        <h3 style="color:var(--maroon);margin-bottom:20px"><i class="fas fa-scroll"></i> Discover a Story</h3>
        <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>
        <form method="post" style="display:flex;flex-direction:column;gap:16px">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Destination *</label>
            <input name="destination" placeholder="e.g. Hampi, Varanasi, Jaisalmer..." value="<?= e($dest) ?>" required>
          </div>
          <button type="submit" class="btn-primary"><i class="fas fa-wand-magic-sparkles"></i> Discover Story</button>
        </form>
      </div>

      <div class="card">
        <div style="font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:16px">Popular Destinations</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php foreach(['Varanasi','Hampi','Jaisalmer','Ellora Caves','Khajuraho','Ajanta Caves','Mahabalipuram'] as $d): ?>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="destination" value="<?= e($d) ?>">
            <button type="submit" style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;cursor:pointer;text-align:left;width:100%;font-size:.88rem;font-weight:600;transition:border-color .2s;color:var(--text)" onmouseover="this.style.borderColor='var(--maroon)'" onmouseout="this.style.borderColor='var(--border)'">
              <i class="fas fa-map-marker-alt" style="color:var(--saffron);margin-right:8px"></i><?= e($d) ?>
            </button>
          </form>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- MAIN: STORY -->
    <div>
      <?php if($story):
        $parts = preg_split('/PART\s+\d+\s*[-–]\s*/i', $story, -1, PREG_SPLIT_NO_EMPTY);
        $partTitles = ['History','Local Legend'];
        $partIcons = ['fas fa-landmark','fas fa-ghost'];
        $partColors = ['var(--teal)','var(--maroon)'];
      ?>
      <div style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <h2 style="margin:0"><i class="fas fa-scroll" style="color:var(--maroon)"></i> Stories of <?= e($dest) ?></h2>
        <a href="plan-trip.php" class="btn-primary btn-sm"><i class="fas fa-route"></i> Plan Trip Here</a>
      </div>
      <?php foreach($parts as $i=>$part):
        $title = $partTitles[$i] ?? 'Story';
        $icon = $partIcons[$i] ?? 'fas fa-book';
        $color = $partColors[$i] ?? 'var(--teal)';
        $lines = preg_replace('/^(HISTORY|LOCAL\s+LEGEND)[:\s]*/i', '', trim($part));
      ?>
      <div class="card" style="margin-bottom:20px;border-left:4px solid <?= $color ?>">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
          <div style="width:40px;height:40px;background:<?= $color ?>1a;border-radius:50%;display:flex;align-items:center;justify-content:center;color:<?= $color ?>"><i class="<?= $icon ?>"></i></div>
          <h3 style="margin:0;color:<?= $color ?>"><?= e($title) ?></h3>
        </div>
        <p style="line-height:1.8;color:var(--text)"><?= nl2br(e(trim($lines))) ?></p>
      </div>
      <?php endforeach; ?>

      <?php if(count($parts) === 1): ?>
      <div class="card" style="margin-bottom:20px">
        <p style="line-height:1.8;color:var(--text)"><?= nl2br(e(trim($story))) ?></p>
      </div>
      <?php endif; ?>

      <a href="plan-trip.php" class="btn-primary"><i class="fas fa-route"></i> Plan a Trip to <?= e($dest) ?></a>

      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-scroll"></i>
        <h3>Every destination has a story</h3>
        <p>Enter the name of any Indian destination to discover its history, folklore, and the legends that make it unique.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>@media(max-width:768px){.stories-layout{grid-template-columns:1fr !important}}</style>
<?php require_once 'includes/footer.php'; ?>

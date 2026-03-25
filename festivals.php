<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';require_once 'includes/gemini.php';
$destination=clean($_POST['destination']??'');$month=clean($_POST['month']??'');$result='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();if(aiRateLimitExceeded()){$error='AI limit reached (10/hour).';}else{$prompt="List the top 5 festivals and events in {$destination} during {$month}. For each festival format as: FESTIVAL NAME | Dates | Description (1 sentence) | Why visit it (1 sentence)";$result=callGemini($prompt);}}
$pageTitle='Festival Finder';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(0,50,98,0.88),rgba(0,112,187,0.72)),url('https://images.unsplash.com/photo-1592179325397-4c5f04e8be0d?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-star-and-crescent" style="color:var(--gold-light)"></i> Festival Calendar
      </div>
      <h1 style="color:#fff;margin-bottom:8px">India's Festival Finder</h1>
      <p style="color:rgba(255,255,255,.8)">India has a festival for every month. Discover what's happening at your destination and plan around the celebrations.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <div style="display:grid;grid-template-columns:340px 1fr;gap:32px;align-items:start" class="festival-layout">

    <div class="card" style="border-top:4px solid var(--saffron)">
      <h3 style="color:var(--saffron);margin-bottom:20px"><i class="fas fa-search"></i> Find Festivals</h3>
      <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>
      <form method="post" style="display:flex;flex-direction:column;gap:16px">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Destination / State *</label>
          <input name="destination" placeholder="e.g. Rajasthan, Kerala, Varanasi" value="<?= e($destination) ?>" required>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Month *</label>
          <select name="month">
            <?php foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m): ?>
            <option value="<?= e($m) ?>" <?= $month===$m?'selected':'' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn-primary"><i class="fas fa-wand-magic-sparkles"></i> Find Festivals</button>
      </form>

      <!-- POPULAR FESTIVALS QUICK -->
      <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border)">
        <div style="font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:12px">Popular Festivals</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php foreach([['Holi','Mathura / Vrindavan','March','🎨'],['Diwali','All India','October-November','🪔'],['Onam','Kerala','August-September','🌸'],['Pushkar Fair','Rajasthan','November','🐪'],['Durga Puja','Kolkata','October','🎭']] as $f): ?>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="destination" value="<?= e($f[1]) ?>">
            <input type="hidden" name="month" value="<?= e($f[2]) ?>">
            <button type="submit" style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;cursor:pointer;text-align:left;width:100%;font-size:.85rem;font-weight:600;transition:border-color .2s" onmouseover="this.style.borderColor='var(--saffron)'" onmouseout="this.style.borderColor='var(--border)'">
              <span style="margin-right:6px"><?= $f[3] ?></span>
              <strong><?= e($f[0]) ?></strong>
              <span style="display:block;font-size:.78rem;color:var(--muted);font-weight:400"><?= e($f[1]) ?> · <?= e($f[2]) ?></span>
            </button>
          </form>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div>
      <?php if($result):
        $lines = array_filter(explode("\n", $result), fn($l) => strpos(trim($l),'|') !== false);
      ?>
      <div style="margin-bottom:20px">
        <h3 style="margin:0"><i class="fas fa-star-and-crescent" style="color:var(--saffron)"></i> Festivals in <?= e($destination) ?> — <?= e($month) ?></h3>
      </div>
      <div style="display:flex;flex-direction:column;gap:20px">
        <?php $count=1; foreach($lines as $line):
          $p = array_map('trim', explode('|', $line, 4));
          $festEmoji = ['Holi'=>'🎨','Diwali'=>'🪔','Eid'=>'🌙','Christmas'=>'🎄','Onam'=>'🌸','Navratri'=>'💃','Pongal'=>'🎉','Holi'=>'🎨'];
          $em = '🎊';
          foreach($festEmoji as $k=>$v) { if(stripos($p[0]??'', $k)!==false) { $em=$v; break; } }
        ?>
        <div class="card" style="border-left:4px solid var(--saffron);padding:20px 24px">
          <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="font-size:2rem;flex-shrink:0;line-height:1"><?= $em ?></div>
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
                <h3 style="margin:0;color:var(--saffron)"><?= e($p[0] ?? 'Festival') ?></h3>
                <?php if(!empty($p[1])): ?>
                <span class="badge badge-saffron"><?= e($p[1]) ?></span>
                <?php endif; ?>
              </div>
              <?php if(!empty($p[2])): ?><p style="margin-bottom:6px;font-size:.92rem"><?= e($p[2]) ?></p><?php endif; ?>
              <?php if(!empty($p[3])): ?><p style="font-weight:600;color:var(--teal);font-size:.88rem"><i class="fas fa-star"></i> <?= e($p[3]) ?></p><?php endif; ?>
              <a href="plan-trip.php" class="btn-outline btn-sm" style="margin-top:8px"><i class="fas fa-route"></i> Plan Trip Around This</a>
            </div>
          </div>
        </div>
        <?php $count++; endforeach; ?>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-star-and-crescent"></i>
        <h3>Discover Indian Festivals</h3>
        <p>Enter a destination and month to find festivals, melas, and cultural events happening during your trip.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>@media(max-width:768px){.festival-layout{grid-template-columns:1fr !important}}</style>
<?php require_once 'includes/footer.php'; ?>

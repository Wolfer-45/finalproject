<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';require_once 'includes/gemini.php';requireLogin();
$db=getDB();$tripId=(int)($_GET['trip_id']??0);$destination='';$duration='';$month='';$travelType='city';
if($tripId){$st=$db->prepare('SELECT destination,duration,start_date,travel_type FROM trips WHERE id=? AND user_id=?');$st->execute([$tripId,$_SESSION['user_id']]);$t=$st->fetch();if($t){$destination=$t['destination'];$duration=(string)$t['duration'];$month=date('F',strtotime($t['start_date']));$travelType=$t['travel_type']?:'city';}}
$result='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  verifyCsrf();
  $destination=clean($_POST['destination']??'');$month=clean($_POST['month']??'');$duration=clean($_POST['duration']??'');$travelType=clean($_POST['travel_type']??'city');$weather=clean($_POST['weather_summary']??'Mixed weather');
  if(aiRateLimitExceeded()){$error='AI limit reached (10/hour).';}else{
    $prompt="Create a premium, destination-smart packing checklist for a {$duration}-day trip in {$destination} during {$month}.
Trip type: {$travelType}
Weather context: {$weather}

Requirements:
1) Customize items to the destination/state conditions, terrain, culture, and transport reality.
2) Include only high-value practical items, no fluff.
3) Keep total under 55 items.
4) Organize exactly into:
Clothing
Toiletries
Documents
Electronics
Medicines
Safety and Comfort
Destination-Specific Extras
5) For each item, write one short line in this format:
- item name | why needed
6) Output plain text only.";
    $result=callGemini($prompt, ['temperature' => 0.75, 'max_tokens' => 3000]);
  }
}
$pageTitle='Packing List Generator';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(14,107,107,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-suitcase-rolling" style="color:var(--gold-light)"></i> Smart Packing
      </div>
      <h1 style="color:#fff;margin-bottom:8px">AI Packing List Generator</h1>
      <p style="color:rgba(255,255,255,.8)">Get a smart, destination-specific packing list tailored to your trip type and season.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <div style="display:grid;grid-template-columns:380px 1fr;gap:32px;align-items:start" class="packing-layout">

    <div>
      <div class="card" style="border-top:4px solid var(--teal)">
        <h3 style="color:var(--teal);margin-bottom:20px"><i class="fas fa-magic"></i> Generate List</h3>
        <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>
        <form method="post" style="display:flex;flex-direction:column;gap:16px">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Destination *</label>
            <input name="destination" placeholder="e.g. Manali, Goa, Varanasi" value="<?= e($destination) ?>" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Month of Travel *</label>
            <input name="month" placeholder="e.g. December, March" value="<?= e($month) ?>" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Number of Days *</label>
            <input type="number" name="duration" placeholder="e.g. 5" value="<?= e($duration) ?>" min="1" max="30" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Trip Type</label>
            <select name="travel_type">
              <?php foreach(['adventure'=>'Adventure / Trekking','beach'=>'Beach & Coastal','city'=>'City & Culture','mountains'=>'Mountains & Hill Station','pilgrimage'=>'Pilgrimage / Spiritual','wildlife'=>'Wildlife Safari'] as $v=>$l): ?>
              <option value="<?= e($v) ?>" <?= $travelType===$v?'selected':'' ?>><?= e($l) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Weather Notes <span style="color:var(--muted);font-weight:400">(optional)</span></label>
            <input name="weather_summary" placeholder="e.g. Hot and humid, might rain">
          </div>
          <button type="submit" class="btn-teal" id="pack-btn"><i class="fas fa-list-check"></i> Generate Packing List</button>
        </form>
      </div>
    </div>

    <div>
      <?php if($result):
        $categories = ['Clothing','Toiletries','Documents','Electronics','Medicines','Safety and Comfort','Destination-Specific Extras'];
        $catIcons = ['Clothing'=>'fas fa-tshirt','Toiletries'=>'fas fa-soap','Documents'=>'fas fa-id-card','Electronics'=>'fas fa-plug','Medicines'=>'fas fa-pills','Safety and Comfort'=>'fas fa-shield-alt','Destination-Specific Extras'=>'fas fa-star'];
        $catColors = ['Clothing'=>'var(--saffron)','Toiletries'=>'var(--teal)','Documents'=>'var(--gold)','Electronics'=>'var(--maroon)','Medicines'=>'#16a34a','Safety and Comfort'=>'#dc2626','Destination-Specific Extras'=>'#7c3aed'];
        $grouped = [];
        $currentCat = 'Other';
        foreach(explode("\n", $result) as $line) {
          $line = trim($line);
          if(!$line) continue;
          foreach($categories as $cat) {
            if(stripos($line, $cat) !== false && strpos($line, '|') === false) { $currentCat = $cat; continue 2; }
          }
          if(strpos($line, '-') === 0 || strpos($line, '•') === 0) {
            $grouped[$currentCat][] = ltrim($line, '-•* ');
          }
        }
      ?>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <h3 style="margin:0"><i class="fas fa-suitcase-rolling" style="color:var(--teal)"></i> Your Packing List — <?= e($destination) ?></h3>
        <button onclick="window.print()" class="btn-outline btn-sm"><i class="fas fa-print"></i> Print List</button>
      </div>
      <?php foreach($grouped as $cat=>$items):
        $icon = $catIcons[$cat] ?? 'fas fa-check';
        $color = $catColors[$cat] ?? 'var(--muted)';
      ?>
      <div class="card" style="margin-bottom:20px;border-left:4px solid <?= $color ?>">
        <h3 style="color:<?= $color ?>;margin-bottom:16px"><i class="<?= $icon ?>"></i> <?= e($cat) ?></h3>
        <div style="display:flex;flex-direction:column;gap:0">
          <?php foreach($items as $idx=>$item):
            $parts = explode('|', $item, 2);
            $itemName = trim($parts[0]);
            $itemWhy = isset($parts[1]) ? trim($parts[1]) : '';
            $cbId = 'pack_' . md5($cat . $idx);
          ?>
          <label class="pack-item" style="cursor:pointer;display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)">
            <input type="checkbox" id="<?= $cbId ?>" style="margin-top:3px;accent-color:var(--saffron);width:17px;height:17px;flex-shrink:0" onchange="this.closest('.pack-item').classList.toggle('done',this.checked);localStorage.setItem('<?= $cbId ?>',this.checked?'1':'0')">
            <div>
              <span style="font-weight:600;font-size:.9rem"><?= e($itemName) ?></span>
              <?php if($itemWhy): ?><span style="color:var(--muted);font-size:.82rem;display:block"><?= e($itemWhy) ?></span><?php endif; ?>
            </div>
          </label>
          <script>if(localStorage.getItem('<?= $cbId ?>')==='1'){document.getElementById('<?= $cbId ?>').checked=true;document.getElementById('<?= $cbId ?>').closest('.pack-item').classList.add('done');}</script>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-suitcase-rolling"></i>
        <h3>Generate Your Packing List</h3>
        <p>Fill in the form and let our AI create a smart, destination-specific packing checklist for you.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>@media(max-width:768px){.packing-layout{grid-template-columns:1fr !important}}</style>
<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();
$db=getDB();$tripId=(int)($_GET['trip_id']??$_POST['trip_id']??0);if($tripId<=0){die('Invalid trip.');}
$st=$db->prepare('SELECT t.id,t.destination,t.start_date,t.end_date,t.duration,t.title,i.content FROM trips t JOIN itineraries i ON i.trip_id=t.id WHERE t.id=? AND t.user_id=?');$st->execute([$tripId,$_SESSION['user_id']]);$row=$st->fetch();if(!$row){die('Forbidden or not found.');}
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();$content=trim($_POST['content']??'');$db->prepare('UPDATE itineraries SET content=? WHERE trip_id=? AND user_id=?')->execute([$content,$tripId,$_SESSION['user_id']]);setFlash('success','Itinerary updated.');header('Location: itinerary.php?trip_id='.$tripId);exit;}
$pageTitle='Itinerary: '.$row['title'];require_once 'includes/header.php';
$parts=preg_split('/DAY\s+\d+\s*-/i',$row['content']);
$dayHeaders=[];preg_match_all('/DAY\s+\d+\s*-([^\n]+)/i',$row['content'],$hm);
foreach($hm[1] as $h) $dayHeaders[]=trim($h);

// Destination images for Unsplash
$destQuery = urlencode(strtolower($row['destination']) . ' india travel');
?>

<!-- PAGE BANNER -->
<div style="background:linear-gradient(160deg,rgba(28,16,7,0.85),rgba(14,107,107,0.7)),url('https://images.unsplash.com/photo-1524168272322-bf73616d9cb5?w=1400&q=80') center/cover;padding:60px 0 48px">
  <div class="page-container">
    <div style="color:#fff">
      <div style="font-size:.8rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.6);margin-bottom:10px">
        <a href="dashboard.php" style="color:rgba(255,255,255,.6)">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:.7rem"></i> My Itinerary
      </div>
      <h1 style="color:#fff;margin-bottom:8px"><?= e($row['title']) ?></h1>
      <p style="color:rgba(255,255,255,.75);margin-bottom:24px">
        <i class="fas fa-map-pin" style="color:var(--saffron-light)"></i> <?= e($row['destination']) ?> &nbsp;|&nbsp;
        <i class="fas fa-calendar" style="color:var(--gold-light)"></i> <?= date('d M Y', strtotime($row['start_date'])) ?> – <?= date('d M Y', strtotime($row['end_date'])) ?> &nbsp;|&nbsp;
        <i class="fas fa-sun" style="color:var(--gold-light)"></i> <?= (int)$row['duration'] ?> Days
      </p>
      <div style="display:flex;flex-wrap:wrap;gap:12px">
        <a class="btn-outline btn-sm" href="stories.php?dest=<?= urlencode($row['destination']) ?>" style="border-color:rgba(255,255,255,.4);color:#fff"><i class="fas fa-book-open"></i> Hear the Story</a>
        <a class="btn-outline btn-sm" href="map.php?trip_id=<?= $tripId ?>" style="border-color:rgba(255,255,255,.4);color:#fff"><i class="fas fa-map"></i> View on Map</a>
        <a class="btn-outline btn-sm" href="packing.php?trip_id=<?= $tripId ?>" style="border-color:rgba(255,255,255,.4);color:#fff"><i class="fas fa-suitcase-rolling"></i> Packing List</a>
        <button onclick="window.print()" class="btn-outline btn-sm" style="border-color:rgba(255,255,255,.4);color:#fff"><i class="fas fa-print"></i> Print Plan</button>
      </div>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <!-- DESTINATION PHOTO HEADER -->
  <div style="border-radius:var(--radius-lg);overflow:hidden;margin-bottom:40px;position:relative;height:280px">
    <img src="https://images.unsplash.com/featured/800x400?<?= $destQuery ?>" alt="<?= e($row['destination']) ?>" style="width:100%;height:100%;object-fit:cover">
    <div style="position:absolute;inset:0;background:linear-gradient(to right,rgba(28,16,7,0.7),rgba(28,16,7,0.1))"></div>
    <div style="position:absolute;bottom:0;left:0;padding:32px 36px;color:#fff">
      <div style="font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.65);margin-bottom:6px"><?= (int)$row['duration'] ?>-Day Plan</div>
      <h2 style="color:#fff;margin-bottom:0;font-size:1.8rem"><?= e($row['destination']) ?></h2>
    </div>
  </div>

  <!-- QUICK NAV -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:40px;padding:16px;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow-sm)">
    <span style="font-weight:700;font-size:.85rem;color:var(--muted);display:flex;align-items:center;margin-right:4px"><i class="fas fa-list"></i>&nbsp; Jump to Day:</span>
    <?php for($i=1;$i<count($parts);$i++): ?>
    <a href="#day-<?= $i ?>" style="padding:6px 14px;background:rgba(212,98,26,.08);border:1px solid rgba(212,98,26,.2);border-radius:999px;font-size:.82rem;font-weight:700;color:var(--saffron);transition:all .2s" onmouseover="this.style.background='var(--saffron)';this.style.color='#fff'" onmouseout="this.style.background='rgba(212,98,26,.08)';this.style.color='var(--saffron)'">Day <?= $i ?></a>
    <?php endfor; ?>
  </div>

  <!-- DAY CARDS -->
  <?php for($i=1;$i<count($parts);$i++):
    $dayText = trim($parts[$i]);
    $dayTitle = isset($dayHeaders[$i-1]) ? $dayHeaders[$i-1] : '';

    // Extract sections
    $sections = [
      'Morning' => ['fas fa-sun', 'Morning'],
      'Afternoon' => ['fas fa-cloud-sun', 'Afternoon'],
      'Evening' => ['fas fa-moon', 'Evening'],
      'Food' => ['fas fa-utensils', 'Local Food & Restaurants'],
      'Budget Note' => ['fas fa-wallet', 'Budget Breakdown'],
      'Tip' => ['fas fa-lightbulb', 'Local Tip'],
    ];

    // Parse day text into sections
    $parsed = [];
    $remaining = $dayText;
    foreach($sections as $key => $meta) {
      if(preg_match('/'.preg_quote($key,'/').':\s*(.+?)(?=(?:Morning|Afternoon|Evening|Food|Budget Note|Tip):|$)/si', $remaining, $m)) {
        $parsed[$key] = trim($m[1]);
      }
    }
    if(empty($parsed)) {
      $parsed['_raw'] = $dayText;
    }

    // Destination image for each day  
    $dayImageQuery = urlencode(strtolower($row['destination']) . ' india ' . ($i % 3 === 0 ? 'culture food' : ($i % 2 === 0 ? 'landmark architecture' : 'landscape nature')));
    $imgSeed = $tripId * 100 + $i;
    $unsplashImg = "https://images.unsplash.com/photo-" . ['1524492412937-b28074a5d7da','1564507592333-c60657eea523','1567591370429-a2f6c82e5a00','1602216056096-3b40cc0c9944','1477587458883-47145ed94245','1512343879784-a960bf40e7f2','1561361058-c24cecae35ca','1595658658481-d53d3f999875','1548013146-72479768bada','1551632811-561732d1e306'][$i % 10] . "?w=800&q=80";
  ?>
  <div class="day-card" id="day-<?= $i ?>">
    <div class="day-card-header">
      <div class="day-number"><?= $i ?></div>
      <div>
        <h3 style="margin-bottom:2px">Day <?= $i ?><?= $dayTitle ? ' — ' . e($dayTitle) : '' ?></h3>
        <p><?= date('D, d M Y', strtotime($row['start_date'] . ' +' . ($i-1) . ' days')) ?></p>
      </div>
    </div>
    <img src="<?= e($unsplashImg) ?>" alt="Day <?= $i ?> - <?= e($row['destination']) ?>" class="day-card-img" loading="lazy">
    <div class="day-card-body">
      <?php if(!empty($parsed['_raw'])): ?>
        <p style="line-height:1.8;color:var(--text-2)"><?= nl2br(e($parsed['_raw'])) ?></p>
      <?php else:
        foreach($sections as $key => [$icon, $label]):
          if(!isset($parsed[$key])) continue;
      ?>
      <div class="day-section">
        <div class="day-section-icon"><i class="<?= e($icon) ?>"></i></div>
        <div class="day-section-content">
          <strong><?= e($label) ?></strong>
          <p><?= nl2br(e($parsed[$key])) ?></p>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <?php endfor; ?>

  <!-- EDIT PLAN -->
  <div class="card" style="margin-top:40px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
      <div>
        <h3 style="color:var(--saffron);margin-bottom:4px"><i class="fas fa-pen-to-square"></i> Edit Itinerary</h3>
        <p style="font-size:.88rem;color:var(--muted);margin:0">Fine-tune the AI-generated plan to match your preferences</p>
      </div>
    </div>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="trip_id" value="<?= $tripId ?>">
      <textarea name="content" rows="16" style="font-family:monospace;font-size:.88rem;line-height:1.7"><?= e($row['content']) ?></textarea>
      <button class="btn-primary" style="margin-top:12px"><i class="fas fa-save"></i> Save Changes</button>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();
$db=getDB();$user=getCurrentUser();$pageTitle='Dashboard';
$t=$db->prepare('SELECT id,title,destination,start_date,end_date,status FROM trips WHERE user_id=? ORDER BY created_at DESC LIMIT 8');$t->execute([$_SESSION['user_id']]);$trips=$t->fetchAll();
$s=$db->prepare('SELECT id,title,location,travel_date,photo_path,mood FROM storybook WHERE user_id=? ORDER BY created_at DESC LIMIT 3');$s->execute([$_SESSION['user_id']]);$stories=$s->fetchAll();
require_once 'includes/header.php'; ?>

<div class="page-container" style="padding-top:40px;padding-bottom:80px">

  <!-- DASH HERO -->
  <div class="dash-hero">
    <div style="max-width:600px">
      <div style="font-size:.8rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:10px">Welcome Back</div>
      <h1 style="color:#fff;margin-bottom:8px">Namaste, <?= e(explode(' ', $user['name'])[0] ?? 'Explorer') ?>! 🙏</h1>
      <p>Ready for your next Indian adventure? Your trips, memories, and tools — all in one place.</p>
    </div>
    <div class="dash-quick-links">
      <a href="plan-trip.php" class="dash-quick-link"><i class="fas fa-route"></i> Plan New Trip</a>
      <a href="travel-buddy.php" class="dash-quick-link"><i class="fas fa-user-group"></i> Find Buddy</a>
      <a href="chatbot.php" class="dash-quick-link"><i class="fas fa-robot"></i> AI Chat</a>
      <a href="storybook.php" class="dash-quick-link"><i class="fas fa-book-open"></i> Storybook</a>
      <a href="weather.php" class="dash-quick-link"><i class="fas fa-cloud-sun"></i> Weather</a>
    </div>
  </div>

  <!-- STATS ROW -->
  <div class="grid-4" style="margin-bottom:48px">
    <?php
    $totalTrips = count($trips);
    $activeTrips = count(array_filter($trips, fn($t) => $t['status'] === 'active'));
    $completedTrips = count(array_filter($trips, fn($t) => $t['status'] === 'completed'));
    $stats = [
      ['fas fa-route', 'My Trips', $totalTrips, 'var(--saffron)', 'rgba(212,98,26,.08)'],
      ['fas fa-plane-departure', 'Active Trips', $activeTrips, 'var(--teal)', 'rgba(14,107,107,.08)'],
      ['fas fa-check-circle', 'Completed', $completedTrips, 'var(--gold)', 'rgba(200,146,10,.08)'],
      ['fas fa-book-open', 'Stories', count($stories), 'var(--maroon)', 'rgba(123,30,50,.08)'],
    ];
    foreach($stats as $stat): ?>
    <div class="card" style="text-align:center;padding:24px 16px">
      <div style="width:52px;height:52px;background:<?= $stat[4] ?>;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.3rem;color:<?= $stat[3] ?>">
        <i class="<?= $stat[2] === count($stories) ? 'fas fa-book-open' : $stat[0] ?>"></i>
      </div>
      <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:800;color:<?= $stat[3] ?>;line-height:1"><?= $stat[2] ?></div>
      <div style="font-size:.82rem;color:var(--muted);margin-top:4px;font-weight:600"><?= $stat[1] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- MY TRIPS -->
  <div style="margin-bottom:48px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <h2 style="margin:0"><i class="fas fa-suitcase-rolling" style="color:var(--saffron);margin-right:8px"></i>My Trips</h2>
      <a href="plan-trip.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Plan New Trip</a>
    </div>
    <?php if(count($trips) > 0): ?>
    <div style="display:flex;flex-direction:column;gap:0">
      <?php foreach($trips as $trip):
        $statusColors = ['planned'=>'badge-saffron','active'=>'badge-teal','completed'=>'badge-green'];
        $statusIcons = ['planned'=>'fas fa-clock','active'=>'fas fa-plane','completed'=>'fas fa-check'];
        $sc = $statusColors[$trip['status']] ?? 'badge-saffron';
        $si = $statusIcons[$trip['status']] ?? 'fas fa-clock';
      ?>
      <div class="trip-row-card">
        <div class="trip-row-icon"><i class="fas fa-map-location-dot"></i></div>
        <div class="trip-row-info">
          <h4><?= e($trip['destination']) ?></h4>
          <p><?= date('d M Y', strtotime($trip['start_date'])) ?> → <?= date('d M Y', strtotime($trip['end_date'])) ?></p>
        </div>
        <span class="badge <?= e($sc) ?>"><i class="<?= e($si) ?>"></i> <?= e(ucfirst($trip['status'])) ?></span>
        <div class="trip-row-links">
          <a class="btn-outline btn-sm" href="itinerary.php?trip_id=<?= (int)$trip['id'] ?>"><i class="fas fa-route"></i> Itinerary</a>
          <a class="btn-outline btn-sm" href="map.php?trip_id=<?= (int)$trip['id'] ?>" style="border-color:var(--teal);color:var(--teal)"><i class="fas fa-map"></i> Map</a>
          <a class="btn-outline btn-sm" href="budget.php?trip_id=<?= (int)$trip['id'] ?>" style="border-color:var(--gold);color:var(--gold)"><i class="fas fa-wallet"></i> Budget</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-plane-departure"></i>
      <h3>No trips planned yet</h3>
      <p>Start your first adventure with an AI-powered itinerary!</p>
      <a href="plan-trip.php" class="btn-primary" style="margin-top:16px"><i class="fas fa-route"></i> Plan My First Trip</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- RECENT STORIES -->
  <div style="margin-bottom:48px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <h2 style="margin:0"><i class="fas fa-book-open" style="color:var(--gold);margin-right:8px"></i>Recent Storybook</h2>
      <a href="storybook.php" class="btn-outline btn-sm"><i class="fas fa-plus"></i> Add Story</a>
    </div>
    <?php if(count($stories) > 0): ?>
    <div class="grid-3">
      <?php foreach($stories as $st): ?>
      <a class="story-card" href="storybook-view.php?id=<?= (int)$st['id'] ?>" style="display:block">
        <?php if($st['photo_path']): ?>
        <img src="<?= e(SITE_URL . '/uploads/storybook/' . $st['photo_path']) ?>" alt="Story photo" class="story-card-img" loading="lazy">
        <?php else: ?>
        <div class="story-card-img" style="background:linear-gradient(135deg,rgba(212,98,26,.15),rgba(14,107,107,.15));display:flex;align-items:center;justify-content:center;font-size:2.5rem">📖</div>
        <?php endif; ?>
        <div class="story-card-body">
          <div class="story-card-meta">
            <?php if($st['location']): ?><span><i class="fas fa-map-pin"></i> <?= e($st['location']) ?></span><?php endif; ?>
            <?php if($st['travel_date']): ?><span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($st['travel_date'])) ?></span><?php endif; ?>
            <?php if($st['mood']): ?><span class="mood-<?= e($st['mood']) ?>">• <?= e(ucfirst($st['mood'])) ?></span><?php endif; ?>
          </div>
          <h3><?= e($st['title']) ?></h3>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-camera-retro"></i>
      <h3>Your storybook is empty</h3>
      <p>Start capturing your travel memories with photos and stories!</p>
      <a href="storybook-add.php" class="btn-outline" style="margin-top:16px"><i class="fas fa-plus"></i> Add First Story</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- QUICK TOOLS -->
  <h2 style="margin-bottom:24px"><i class="fas fa-toolbox" style="color:var(--teal);margin-right:8px"></i>Quick Tools</h2>
  <div class="grid-4">
    <?php
    $tools = [
      ['fas fa-cloud-sun', 'Weather', 'weather.php', 'Live forecasts for your destination', 'var(--teal)', 'rgba(14,107,107,.08)'],
      ['fas fa-star-and-crescent', 'Festivals', 'festivals.php', 'Indian festivals & events calendar', 'var(--gold)', 'rgba(200,146,10,.08)'],
      ['fas fa-shield-alt', 'Safety Tips', 'safety.php', 'Travel safe with essential tips', 'var(--maroon)', 'rgba(123,30,50,.08)'],
      ['fas fa-cog', 'Settings', 'settings.php', 'Manage your account preferences', 'var(--muted)', 'rgba(140,114,96,.08)'],
    ];
    foreach($tools as $tool): ?>
    <a class="card card-hover" href="<?= e($tool[2]) ?>" style="text-align:center;padding:24px 16px;display:block">
      <div style="width:52px;height:52px;background:<?= $tool[5] ?>;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.3rem;color:<?= $tool[4] ?>">
        <i class="<?= $tool[0] ?>"></i>
      </div>
      <h3 style="color:var(--text);margin-bottom:6px;font-size:1rem"><?= $tool[1] ?></h3>
      <p style="font-size:.82rem;margin:0"><?= $tool[3] ?></p>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

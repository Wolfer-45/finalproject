<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();
$db=getDB();$user=getCurrentUser();$pageTitle='Dashboard';
$t=$db->prepare('SELECT id,title,destination,start_date,end_date,status FROM trips WHERE user_id=? ORDER BY created_at DESC LIMIT 8');$t->execute([$_SESSION['user_id']]);$trips=$t->fetchAll();
$s=$db->prepare('SELECT id,title,location,travel_date,photo_path,mood FROM storybook WHERE user_id=? ORDER BY created_at DESC LIMIT 3');$s->execute([$_SESSION['user_id']]);$stories=$s->fetchAll();
$totalTrips = count($trips);
$activeTrips = count(array_filter($trips, fn($t) => $t['status'] === 'active'));
$completedTrips = count(array_filter($trips, fn($t) => $t['status'] === 'completed'));
$plannedTrips = count(array_filter($trips, fn($t) => $t['status'] === 'planned'));
$totalStories = count($stories);
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
require_once 'includes/header.php'; ?>

<div class="page-container" style="padding-top:40px;padding-bottom:80px">

  <!-- DASH HERO -->
  <div class="dash-hero">
    <div style="max-width:600px">
      <div style="font-size:.8rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:10px">Welcome Back</div>
      <h1 style="color:#fff;margin-bottom:8px">Namaste, <?= e(explode(' ', $user['name'])[0] ?? 'Explorer') ?>! 🙏</h1>
      <p style="color:rgba(255,255,255,.75)">Ready for your next Indian adventure? Your trips, memories, and tools — all in one place.</p>
    </div>
    <div class="dash-quick-links">
      <a href="plan-trip.php" class="dash-quick-link"><i class="fas fa-route"></i> Plan New Trip</a>
      <a href="travel-buddy.php" class="dash-quick-link"><i class="fas fa-user-group"></i> Find Buddy</a>
      <a href="chatbot.php" class="dash-quick-link"><i class="fas fa-robot"></i> AI Chat</a>
      <a href="storybook.php" class="dash-quick-link"><i class="fas fa-book-open"></i> Storybook</a>
      <a href="weather.php" class="dash-quick-link"><i class="fas fa-cloud-sun"></i> Weather</a>
    </div>
  </div>

  <!-- YOUR ACTIVITY (Charts + Stats) -->
  <div style="margin-bottom:56px">
    <h2 style="margin-bottom:8px"><i class="fas fa-chart-pie" style="color:var(--primary);margin-right:8px"></i>Your Activity</h2>
    <p style="color:var(--muted);margin-bottom:28px;font-size:.9rem">A snapshot of your travel journey so far</p>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px" class="activity-grid">

      <!-- Trips Donut Chart -->
      <div class="card" style="text-align:center;padding:28px 20px">
        <h4 style="font-family:'Inter',sans-serif;font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:20px">Trip Status</h4>
        <?php if($totalTrips > 0): ?>
        <div style="position:relative;width:160px;height:160px;margin:0 auto 20px">
          <canvas id="tripsChart" width="160" height="160"></canvas>
          <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
            <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:800;color:var(--primary-dark)"><?= $totalTrips ?></div>
            <div style="font-size:.75rem;color:var(--muted);font-weight:600">Total Trips</div>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;text-align:left">
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:.82rem"><span style="display:flex;align-items:center;gap:8px"><span style="width:10px;height:10px;border-radius:50%;background:#4A9FD4;display:inline-block"></span>Planned</span><strong><?= $plannedTrips ?></strong></div>
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:.82rem"><span style="display:flex;align-items:center;gap:8px"><span style="width:10px;height:10px;border-radius:50%;background:#0070BB;display:inline-block"></span>Active</span><strong><?= $activeTrips ?></strong></div>
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:.82rem"><span style="display:flex;align-items:center;gap:8px"><span style="width:10px;height:10px;border-radius:50%;background:#003262;display:inline-block"></span>Completed</span><strong><?= $completedTrips ?></strong></div>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:20px 0">
          <i class="fas fa-route" style="font-size:2rem"></i>
          <p style="margin-top:10px;font-size:.88rem">No trips yet</p>
          <a href="plan-trip.php" class="btn-primary btn-sm" style="margin-top:12px"><i class="fas fa-plus"></i> Plan First Trip</a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Stories + Mood -->
      <div class="card" style="text-align:center;padding:28px 20px">
        <h4 style="font-family:'Inter',sans-serif;font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:20px">Memories</h4>
        <?php
        $storyCount=$db->prepare('SELECT COUNT(*) as c FROM storybook WHERE user_id=?');$storyCount->execute([$_SESSION['user_id']]);$sCount=(int)$storyCount->fetchColumn();
        $moodData=$db->prepare("SELECT mood,COUNT(*) as c FROM storybook WHERE user_id=? AND mood IS NOT NULL GROUP BY mood");$moodData->execute([$_SESSION['user_id']]);$moods=$moodData->fetchAll();
        ?>
        <?php if($sCount > 0): ?>
        <div style="position:relative;width:160px;height:160px;margin:0 auto 20px">
          <canvas id="moodChart" width="160" height="160"></canvas>
          <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
            <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:800;color:var(--primary-dark)"><?= $sCount ?></div>
            <div style="font-size:.75rem;color:var(--muted);font-weight:600">Stories</div>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;text-align:left">
          <?php $moodColors=['amazing'=>'#16a34a','good'=>'#0070BB','okay'=>'#d97706','tough'=>'#dc2626'];
          foreach($moods as $md): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:.82rem">
            <span style="display:flex;align-items:center;gap:8px"><span style="width:10px;height:10px;border-radius:50%;background:<?= $moodColors[$md['mood']] ?? '#aaa' ?>;display:inline-block"></span><?= e(ucfirst($md['mood'])) ?></span><strong><?= (int)$md['c'] ?></strong>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:20px 0">
          <i class="fas fa-book-open" style="font-size:2rem"></i>
          <p style="margin-top:10px;font-size:.88rem">No stories yet</p>
          <a href="storybook-add.php" class="btn-primary btn-sm" style="margin-top:12px"><i class="fas fa-plus"></i> Add First Memory</a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Quick Stats -->
      <div class="card" style="padding:28px 20px">
        <h4 style="font-family:'Inter',sans-serif;font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:20px">Quick Stats</h4>
        <?php
        $buddyCount=$db->prepare('SELECT COUNT(*) FROM buddy_listings WHERE user_id=?');$buddyCount->execute([$_SESSION['user_id']]);$bCount=(int)$buddyCount->fetchColumn();
        $expCount=$db->prepare('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=?');$expCount->execute([$_SESSION['user_id']]);$totalSpent=(float)$expCount->fetchColumn();
        $stats=[
          ['fas fa-plane','Total Trips',$totalTrips,'var(--primary)'],
          ['fas fa-check-circle','Completed',$completedTrips,'var(--primary-dark)'],
          ['fas fa-book-open','Memories',$sCount,'var(--primary)'],
          ['fas fa-user-group','Buddy Posts',$bCount,'var(--primary-dark)'],
        ]; ?>
        <div style="display:flex;flex-direction:column;gap:16px">
          <?php foreach($stats as $stat): ?>
          <div style="display:flex;align-items:center;gap:14px;padding-bottom:14px;border-bottom:1px solid var(--border)">
            <div style="width:40px;height:40px;background:rgba(0,112,187,.08);border-radius:10px;display:flex;align-items:center;justify-content:center;color:<?= $stat[3] ?>;font-size:1rem;flex-shrink:0">
              <i class="<?= $stat[0] ?>"></i>
            </div>
            <div style="flex:1">
              <div style="font-size:.8rem;color:var(--muted);font-weight:600"><?= $stat[1] ?></div>
            </div>
            <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:800;color:<?= $stat[3] ?>"><?= $stat[2] ?></div>
          </div>
          <?php endforeach; ?>
          <div style="display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;background:rgba(0,112,187,.08);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1rem;flex-shrink:0"><i class="fas fa-wallet"></i></div>
            <div style="flex:1"><div style="font-size:.8rem;color:var(--muted);font-weight:600">Total Spent</div></div>
            <div style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:800;color:var(--primary-dark)">₹<?= number_format($totalSpent) ?></div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- MY TRIPS -->
  <div style="margin-bottom:48px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <h2 style="margin:0"><i class="fas fa-suitcase-rolling" style="color:var(--primary);margin-right:8px"></i>My Trips</h2>
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
          <a class="btn-outline btn-sm" href="map.php?trip_id=<?= (int)$trip['id'] ?>" style="border-color:var(--primary-dark);color:var(--primary-dark)"><i class="fas fa-map"></i> Map</a>
          <a class="btn-outline btn-sm" href="budget.php?trip_id=<?= (int)$trip['id'] ?>" style="border-color:var(--primary);color:var(--primary)"><i class="fas fa-wallet"></i> Budget</a>
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
      <h2 style="margin:0"><i class="fas fa-book-open" style="color:var(--primary-dark);margin-right:8px"></i>Recent Storybook</h2>
      <a href="storybook.php" class="btn-outline btn-sm"><i class="fas fa-book"></i> Open Book</a>
    </div>
    <?php if(count($stories) > 0): ?>
    <div class="grid-3">
      <?php foreach($stories as $st): ?>
      <a class="story-card" href="storybook-view.php?id=<?= (int)$st['id'] ?>" style="display:block">
        <?php if($st['photo_path']): ?>
        <img src="<?= e(SITE_URL . '/uploads/storybook/' . $st['photo_path']) ?>" alt="Story photo" class="story-card-img" loading="lazy">
        <?php else: ?>
        <div class="story-card-img" style="background:linear-gradient(135deg,rgba(0,112,187,.12),rgba(0,50,98,.12));display:flex;align-items:center;justify-content:center;font-size:2.5rem">📖</div>
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
  <h2 style="margin-bottom:24px"><i class="fas fa-toolbox" style="color:var(--primary);margin-right:8px"></i>Quick Tools</h2>
  <div class="grid-4">
    <?php $tools = [
      ['fas fa-cloud-sun', 'Weather', 'weather.php', 'Live forecasts for your destination'],
      ['fas fa-star-and-crescent', 'Festivals', 'festivals.php', 'Indian festivals & events calendar'],
      ['fas fa-shield-alt', 'Safety Tips', 'safety.php', 'Travel safe with essential tips'],
      ['fas fa-cog', 'Settings', 'settings.php', 'Manage your account preferences'],
    ];
    foreach($tools as $tool): ?>
    <a class="card card-hover" href="<?= e($tool[2]) ?>" style="text-align:center;padding:24px 16px;display:block;border-top:3px solid var(--primary)">
      <div style="width:52px;height:52px;background:rgba(0,112,187,.08);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.3rem;color:var(--primary)">
        <i class="<?= $tool[0] ?>"></i>
      </div>
      <h3 style="color:var(--text);margin-bottom:6px;font-size:1rem"><?= $tool[1] ?></h3>
      <p style="font-size:.82rem;margin:0"><?= $tool[3] ?></p>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<style>@media(max-width:900px){.activity-grid{grid-template-columns:1fr !important}}</style>

<script>
<?php if($totalTrips > 0): ?>
new Chart(document.getElementById('tripsChart'), {
  type: 'doughnut',
  data: {
    labels: ['Planned', 'Active', 'Completed'],
    datasets: [{
      data: [<?= $plannedTrips ?>, <?= $activeTrips ?>, <?= $completedTrips ?>],
      backgroundColor: ['#4A9FD4', '#0070BB', '#003262'],
      borderWidth: 0,
      hoverOffset: 6
    }]
  },
  options: {
    cutout: '72%',
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } },
    animation: { animateScale: true }
  }
});
<?php endif; ?>

<?php if($sCount > 0 && count($moods) > 0): ?>
const moodColors = { amazing: '#16a34a', good: '#0070BB', okay: '#d97706', tough: '#dc2626' };
const moodLabels = <?= json_encode(array_column($moods, 'mood')) ?>;
const moodValues = <?= json_encode(array_map(fn($m) => (int)$m['c'], $moods)) ?>;
new Chart(document.getElementById('moodChart'), {
  type: 'doughnut',
  data: {
    labels: moodLabels.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
    datasets: [{
      data: moodValues,
      backgroundColor: moodLabels.map(m => moodColors[m] || '#aaa'),
      borderWidth: 0,
      hoverOffset: 6
    }]
  },
  options: {
    cutout: '72%',
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } },
    animation: { animateScale: true }
  }
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>

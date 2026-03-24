<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/weather-api.php';
requireLogin();
$dest = clean($_GET['dest'] ?? $_POST['dest'] ?? '');
$weather = ['ok' => false, 'days' => [], 'message' => ''];
if ($dest) {
  $weather = fetchWeatherForecast($dest);
}
$pageTitle = 'Weather Check';
require_once 'includes/header.php';
?>

<div style="background:linear-gradient(160deg,rgba(14,107,107,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1504608524841-42584120d1cc?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-cloud-sun" style="color:var(--gold-light)"></i> Live Weather
      </div>
      <h1 style="color:#fff;margin-bottom:8px">Weather Forecast</h1>
      <p style="color:rgba(255,255,255,.8)">Check live weather conditions for any Indian destination before you pack your bags.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">

  <!-- SEARCH BAR -->
  <div class="card" style="margin-bottom:36px">
    <form method="get" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
      <div style="flex:1;min-width:220px">
        <label class="form-label"><i class="fas fa-map-pin" style="color:var(--saffron)"></i> Destination</label>
        <input name="dest" placeholder="e.g. Mumbai, Shimla, Jaisalmer..." value="<?= e($dest) ?>">
      </div>
      <button type="submit" class="btn-teal" style="flex-shrink:0;align-self:flex-end"><i class="fas fa-cloud-sun"></i> Check Weather</button>
    </form>
  </div>

  <?php if($weather['ok']): ?>
    <?php
    $rainDays = [];
    foreach($weather['days'] as $d) {
      if((int)$d['rain'] > 70) $rainDays[] = date('D d M', strtotime($d['day']));
    }
    ?>
    <?php if($rainDays): ?>
    <div class="travel-card" style="border-color:#3b82f6;margin-bottom:28px">
      <i class="fas fa-cloud-rain" style="color:#3b82f6;font-size:1.3rem;flex-shrink:0;margin-top:2px"></i>
      <div>
        <strong style="display:block;margin-bottom:4px">Rain Alert ☔</strong>
        <p style="margin:0;font-size:.88rem">Rain expected on: <strong><?= e(implode(', ', $rainDays)) ?></strong>. Consider packing a raincoat and planning indoor activities for those days.</p>
      </div>
    </div>
    <?php endif; ?>

    <div style="margin-bottom:12px">
      <h3 style="margin:0"><i class="fas fa-cloud-sun" style="color:var(--teal)"></i> 5-Day Forecast for <span style="color:var(--saffron)"><?= e($dest) ?></span></h3>
    </div>

    <div class="grid-5">
      <?php foreach($weather['days'] as $d):
        $high = round((float)$d['high']);
        $low = round((float)$d['low']);
        $rain = (int)$d['rain'];
        $humidty = (int)$d['humidity'];
        $wind = (float)$d['wind'];
        $tempColor = $high > 35 ? '#dc2626' : ($high > 25 ? 'var(--saffron)' : 'var(--teal)');
      ?>
      <div class="card" style="text-align:center;padding:20px 12px">
        <div style="font-weight:700;font-size:.9rem;color:var(--muted);margin-bottom:12px"><?= date('D', strtotime($d['day'])) ?></div>
        <div style="font-size:.8rem;color:var(--muted);margin-bottom:12px"><?= date('d M', strtotime($d['day'])) ?></div>
        <img src="https://openweathermap.org/img/wn/<?= e($d['icon']) ?>@2x.png" alt="weather icon" style="width:60px;height:60px;margin:0 auto 10px">
        <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:800;color:<?= $tempColor ?>"><?= $high ?>°</div>
        <div style="font-size:.85rem;color:var(--muted)">Low <?= $low ?>°C</div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:5px">
          <div style="font-size:.78rem;color:#3b82f6"><i class="fas fa-droplet"></i> <?= $rain ?>% rain</div>
          <div style="font-size:.78rem;color:var(--muted)"><i class="fas fa-wind"></i> <?= $wind ?> m/s</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  <?php elseif($dest): ?>
    <div class="travel-card" style="border-color:#dc2626">
      <i class="fas fa-exclamation-triangle" style="color:#dc2626;font-size:1.3rem;flex-shrink:0;margin-top:2px"></i>
      <div>
        <strong style="display:block;margin-bottom:4px">Couldn't fetch weather</strong>
        <p style="margin:0;font-size:.88rem"><?= e($weather['message']) ?></p>
      </div>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-cloud-sun"></i>
      <h3>Search for a destination</h3>
      <p>Enter any Indian city or town to get a 5-day weather forecast to help you prepare for your trip.</p>
    </div>
  <?php endif; ?>

  <!-- INDIA WEATHER TIPS -->
  <div class="card" style="margin-top:48px;border-top:4px solid var(--gold)">
    <h3 style="color:var(--gold);margin-bottom:20px"><i class="fas fa-lightbulb"></i> India Weather Travel Guide</h3>
    <div class="grid-3" style="gap:16px">
      <?php foreach([
        ['fas fa-sun','Oct – Feb','Best Season','The ideal time to visit most of India. Cool weather in the north, pleasant coast in the south.','var(--gold)'],
        ['fas fa-cloud-rain','Jun – Sep','Monsoon Season','Lush greenery across India. Perfect for Kerala backwaters, Coorg, and Cherrapunji.','#3b82f6'],
        ['fas fa-temperature-high','Mar – May','Summer Season','Avoid plains; great for hill stations like Shimla, Manali, Darjeeling, and Munnar.','#dc2626'],
      ] as $g): ?>
      <div style="padding:16px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border)">
        <div style="color:<?= $g[4] ?>;font-size:1.5rem;margin-bottom:8px"><i class="<?= $g[0] ?>"></i></div>
        <div style="font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:4px"><?= e($g[1]) ?></div>
        <h4 style="margin-bottom:6px;color:<?= $g[4] ?>"><?= e($g[2]) ?></h4>
        <p style="font-size:.85rem;margin:0;color:var(--muted)"><?= e($g[3]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<style>.grid-5{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}@media(max-width:900px){.grid-5{grid-template-columns:repeat(3,1fr)}}@media(max-width:600px){.grid-5{grid-template-columns:repeat(2,1fr)}}</style>
<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();
$db=getDB();$tripId=(int)($_GET['trip_id']??0);if($tripId<=0){die('Invalid trip');}
$st=$db->prepare('SELECT t.destination,i.content FROM trips t JOIN itineraries i ON i.trip_id=t.id WHERE t.id=? AND t.user_id=?');$st->execute([$tripId,$_SESSION['user_id']]);$trip=$st->fetch();if(!$trip){die('Forbidden');}
function geocode(string $place): array { $url='https://nominatim.openstreetmap.org/search?q='.urlencode($place).'&format=json&limit=1'; $opts=stream_context_create(['http'=>['header'=>'User-Agent: WanderWise/1.0']]); $resp=@file_get_contents($url,false,$opts); $data=json_decode($resp,true); return ['lat'=>$data[0]['lat']??0,'lng'=>$data[0]['lon']??0]; }
$points=[];preg_match_all('/DAY\s*(\d+)\s*-\s*([^\n\r]+)/i',$trip['content'],$m,PREG_SET_ORDER);foreach($m as $row){$g=geocode(trim($row[2].', '.$trip['destination']));$points[]=['day'=>(int)$row[1],'place'=>trim($row[2]),'lat'=>$g['lat'],'lng'=>$g['lng']];}
$pageTitle='Trip Map - '.e($trip['destination']);
$extraHead='<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />';
require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(14,107,107,0.85),rgba(28,16,7,0.9));padding:40px 0">
  <div class="page-container">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div style="color:#fff">
        <div style="font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.6);margin-bottom:6px">Interactive Route Map</div>
        <h1 style="color:#fff;margin:0"><i class="fas fa-map-location-dot"></i> <?= e($trip['destination']) ?></h1>
      </div>
      <div style="display:flex;gap:10px">
        <a href="itinerary.php?trip_id=<?= $tripId ?>" class="btn-outline btn-sm" style="border-color:rgba(255,255,255,.4);color:#fff"><i class="fas fa-route"></i> View Itinerary</a>
        <a href="dashboard.php" class="btn-outline btn-sm" style="border-color:rgba(255,255,255,.4);color:#fff"><i class="fas fa-th-large"></i> Dashboard</a>
      </div>
    </div>
  </div>
</div>

<div style="display:flex;gap:0;height:calc(100vh - 240px);min-height:500px">
  <!-- SIDEBAR: DAY LIST -->
  <div style="width:280px;flex-shrink:0;overflow-y:auto;border-right:1px solid var(--border);padding:20px;background:#fff">
    <h4 style="font-size:.85rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:16px"><i class="fas fa-route"></i> Day-by-Day Route</h4>
    <?php if(count($points) > 0): ?>
    <div style="display:flex;flex-direction:column;gap:8px">
      <?php foreach($points as $i=>$p): ?>
      <div class="day-map-item" onclick="highlightMarker(<?= (int)$p['day'] - 1 ?>)" style="cursor:pointer;padding:12px;border-radius:var(--radius-sm);border:1px solid var(--border);transition:all .2s" onmouseover="this.style.borderColor='var(--teal)';this.style.background='rgba(14,107,107,.05)'" onmouseout="this.style.borderColor='var(--border)';this.style.background=''">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:32px;height:32px;background:var(--saffron);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:800;flex-shrink:0"><?= (int)$p['day'] ?></div>
          <div>
            <div style="font-weight:700;font-size:.88rem"><?= e($p['place']) ?></div>
            <div style="font-size:.78rem;color:var(--muted)">Day <?= (int)$p['day'] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--muted);font-size:.88rem">No geocodable locations found in your itinerary.</p>
    <?php endif; ?>
  </div>

  <!-- MAP -->
  <div style="flex:1;position:relative">
    <div id="trip-map" style="width:100%;height:100%"></div>
  </div>
</div>

<?php $extraScripts='<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script src="assets/js/map.js"></script><script>initTripMap('.json_encode($points).');function highlightMarker(i){if(window.mapMarkers&&window.mapMarkers[i]){window.mapMarkers[i].openPopup();window.mapInstance&&window.mapInstance.flyTo(window.mapMarkers[i].getLatLng(),14,{duration:1});}}</script>'; require_once 'includes/footer.php'; ?>

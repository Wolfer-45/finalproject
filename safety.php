<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();$db=getDB();$user=getCurrentUser();
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();if(isset($_POST['sos'])){$msg='EMERGENCY ALERT: '.$user['name'].' needs help. Sent from WanderWise at '.date('Y-m-d H:i:s').'. Last known location: Unknown.';if(!empty($user['emergency_phone'])){sendMailSimple($user['email'],'Emergency Alert',$msg);}setFlash('success','Emergency alert sent to your emergency contact.');header('Location: safety.php');exit;}}
$pageTitle='Safety Center';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(123,30,50,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-shield-alt" style="color:var(--gold-light)"></i> Safety Center
      </div>
      <h1 style="color:#fff;margin-bottom:8px">Travel Safe Across India</h1>
      <p style="color:rgba(255,255,255,.8)">Your safety is our priority. Keep your emergency contacts updated and travel with confidence.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">

  <div style="display:grid;grid-template-columns:340px 1fr;gap:32px;align-items:start" class="safety-layout">

    <!-- LEFT: SOS + EMERGENCY CONTACT -->
    <div style="display:flex;flex-direction:column;gap:20px">
      <div class="card" style="border:2px solid #dc2626;text-align:center">
        <div style="font-size:3rem;margin-bottom:12px">🚨</div>
        <h3 style="color:#dc2626;margin-bottom:8px">Emergency SOS</h3>
        <p style="font-size:.88rem;color:var(--muted);margin-bottom:20px">Click below to immediately alert your emergency contact by email.</p>
        <form method="post" onsubmit="return confirm('Send emergency alert to your contact? Only use in a genuine emergency.')">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="sos" value="1">
          <button type="submit" style="background:#dc2626;color:#fff;border:none;padding:16px 24px;border-radius:var(--radius-sm);font-size:1rem;font-weight:700;cursor:pointer;width:100%;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
            <i class="fas fa-bell"></i> Send Emergency Alert
          </button>
        </form>
      </div>

      <!-- EMERGENCY CONTACT CARD -->
      <div class="card">
        <h3 style="color:var(--teal);margin-bottom:16px"><i class="fas fa-id-card"></i> Your Emergency Contact</h3>
        <?php if(!empty($user['emergency_name'])): ?>
        <div style="display:flex;flex-direction:column;gap:10px">
          <div class="buddy-info-row">
            <i class="fas fa-user"></i>
            <span><?= e($user['emergency_name']) ?></span>
          </div>
          <div class="buddy-info-row">
            <i class="fas fa-phone"></i>
            <span><?= e($user['emergency_phone'] ?? 'Not set') ?></span>
          </div>
          <div class="buddy-info-row">
            <i class="fas fa-heart"></i>
            <span><?= e($user['emergency_rel'] ?? 'Not set') ?></span>
          </div>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:20px 0">
          <i class="fas fa-id-card"></i>
          <p>No emergency contact set</p>
        </div>
        <?php endif; ?>
        <a href="profile.php" class="btn-outline btn-sm" style="margin-top:16px;width:100%;justify-content:center;display:flex"><i class="fas fa-edit"></i> Update Contact</a>
      </div>

      <!-- EMERGENCY NUMBERS -->
      <div class="card" style="border-top:4px solid var(--saffron)">
        <h3 style="color:var(--saffron);margin-bottom:16px"><i class="fas fa-phone-alt"></i> India Emergency Numbers</h3>
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php foreach([['112','Police, Fire, Medical (National)','fas fa-phone','#dc2626'],['100','Police','fas fa-shield-alt','var(--maroon)'],['101','Fire Brigade','fas fa-fire-extinguisher','#ea580c'],['102','Ambulance','fas fa-truck-medical','#16a34a'],['1800-11-1363','Tourist Helpline','fas fa-headset','var(--teal)']] as $n): ?>
          <div style="display:flex;align-items:center;gap:12px;padding:10px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border)">
            <div style="width:36px;height:36px;background:<?= $n[3] ?>1a;border-radius:50%;display:flex;align-items:center;justify-content:center;color:<?= $n[3] ?>;flex-shrink:0">
              <i class="<?= $n[2] ?>"></i>
            </div>
            <div>
              <div style="font-family:'Playfair Display',serif;font-weight:800;font-size:1rem;color:<?= $n[3] ?>"><?= e($n[0]) ?></div>
              <div style="font-size:.8rem;color:var(--muted)"><?= e($n[1]) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT: SAFETY TIPS -->
    <div>
      <h3 style="margin-bottom:20px"><i class="fas fa-lightbulb" style="color:var(--gold)"></i> Essential Travel Safety Tips</h3>
      <div style="display:flex;flex-direction:column;gap:14px">
        <?php
        $tips = [
          ['fas fa-share-nodes','Share Your Itinerary','Always share your trip plan with family or friends. Let them know your travel dates, accommodation details, and check in with them regularly.','var(--teal)'],
          ['fas fa-moon','Avoid Isolated Areas at Night','Be cautious in unfamiliar areas after dark. Stick to well-lit, populated areas and use trusted transport services.','var(--saffron)'],
          ['fas fa-copy','Keep Copies of IDs','Store digital copies of your passport, Aadhaar, driving license, and other important documents in a secure cloud storage.','var(--gold)'],
          ['fas fa-taxi','Use Verified Transport','Always use government-approved taxis, Ola/Uber, or pre-booked transport. Avoid sharing cabs with strangers.','var(--maroon)'],
          ['fas fa-money-bill','Carry Emergency Cash','Keep a small amount of cash in a separate place from your wallet for emergencies. Not all places accept UPI.','#16a34a'],
          ['fas fa-droplet','Stay Hydrated','India\'s heat can be intense. Carry a reusable water bottle and always drink clean, sealed water, especially in rural areas.','var(--teal)'],
          ['fas fa-hands-praying','Respect Local Culture','Dress modestly at temples and religious sites. Remove shoes when required. Ask before photographing people.','var(--saffron)'],
          ['fas fa-battery-full','Keep Devices Charged','Carry a power bank. Keep your phone charged, especially when travelling to areas with limited power access.','var(--gold)'],
          ['fas fa-heart-pulse','Know Local Medical Facilities','Research the nearest hospitals or clinics at your destination before you travel. Note their numbers in advance.','#dc2626'],
          ['fas fa-brain','Trust Your Instincts','If something feels wrong, it probably is. Don\'t ignore red flags. Your safety is more important than being polite.','var(--maroon)'],
        ];
        foreach($tips as $tip): ?>
        <div class="card card-hover" style="padding:18px;display:flex;align-items:flex-start;gap:14px;cursor:default">
          <div style="width:40px;height:40px;background:<?= $tip[3] ?>1a;border-radius:50%;display:flex;align-items:center;justify-content:center;color:<?= $tip[3] ?>;flex-shrink:0">
            <i class="<?= $tip[0] ?>"></i>
          </div>
          <div>
            <h4 style="margin-bottom:4px;font-size:.95rem"><?= e($tip[1]) ?></h4>
            <p style="margin:0;font-size:.87rem;color:var(--muted)"><?= e($tip[2]) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<style>@media(max-width:768px){.safety-layout{grid-template-columns:1fr !important}}</style>
<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();
$db=getDB();$id=(int)($_GET['id']??0);$st=$db->prepare('SELECT b.*,u.name,u.email,u.avatar FROM buddy_listings b JOIN users u ON u.id=b.user_id WHERE b.id=?');$st->execute([$id]);$listing=$st->fetch();if(!$listing){die('Listing not found');}
$already=$db->prepare('SELECT id FROM buddy_requests WHERE listing_id=? AND sender_id=?');$already->execute([$id,$_SESSION['user_id']]);$sent=(bool)$already->fetch();
if($_SERVER['REQUEST_METHOD']==='POST' && !$sent){verifyCsrf();$msg=clean($_POST['message']??'');$db->prepare('INSERT INTO buddy_requests (listing_id,sender_id,message) VALUES (?,?,?)')->execute([$id,$_SESSION['user_id'],$msg]);$u=getCurrentUser();sendMailSimple($listing['email'],'New WanderWise buddy request',$u['name']." sent a request:\n\n".$msg);setFlash('success','Connection request sent!');header('Location: buddy-detail.php?id='.$id);exit;}
$pageTitle='Buddy: '.e($listing['name']);require_once 'includes/header.php';
$initial = strtoupper(substr($listing['name'], 0, 1));
?>

<div style="background:linear-gradient(160deg,rgba(14,107,107,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-user-group" style="color:var(--gold-light)"></i> Travel Buddy
      </div>
      <h1 style="color:#fff;margin-bottom:8px"><?= e($listing['destination']) ?></h1>
      <p style="color:rgba(255,255,255,.8)"><?= date('d M', strtotime($listing['start_date'])) ?> – <?= date('d M Y', strtotime($listing['end_date'])) ?></p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">
  <a href="travel-buddy.php" class="btn-outline btn-sm" style="margin-bottom:28px;display:inline-flex"><i class="fas fa-arrow-left"></i> Back to Finder</a>

  <div style="display:grid;grid-template-columns:320px 1fr;gap:32px;align-items:start" class="buddy-detail-layout">

    <!-- PROFILE SIDEBAR -->
    <div style="display:flex;flex-direction:column;gap:20px">
      <div class="card" style="text-align:center;padding:32px">
        <div class="buddy-avatar" style="margin:0 auto 16px"><?= e($initial) ?></div>
        <h2 style="margin-bottom:6px"><?= e($listing['name']) ?></h2>
        <span class="badge badge-teal" style="margin-bottom:20px"><?= e(ucfirst($listing['travel_style'] ?: 'Flexible')) ?></span>
        <div style="display:flex;flex-direction:column;gap:12px;text-align:left;margin-top:8px">
          <div class="buddy-info-row">
            <i class="fas fa-map-location-dot"></i>
            <span><?= e($listing['destination']) ?></span>
          </div>
          <div class="buddy-info-row">
            <i class="fas fa-calendar-days"></i>
            <span><?= date('d M', strtotime($listing['start_date'])) ?> – <?= date('d M Y', strtotime($listing['end_date'])) ?></span>
          </div>
          <div class="buddy-info-row">
            <i class="fas fa-users"></i>
            <span><?= e(ucfirst($listing['group_size'] ?: 'Any')) ?> trip</span>
          </div>
          <?php if($listing['gender_pref'] && $listing['gender_pref'] !== 'any'): ?>
          <div class="buddy-info-row">
            <i class="fas fa-person"></i>
            <span><?= e(ucfirst($listing['gender_pref'])) ?> preference</span>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- INTERESTS -->
      <?php if($listing['interests']): ?>
      <div class="card">
        <h4 style="color:var(--teal);margin-bottom:14px"><i class="fas fa-tag"></i> Interests</h4>
        <div class="buddy-tags">
          <?php foreach(explode(',', $listing['interests']) as $tag): ?>
          <span class="buddy-tag"><?= e(trim($tag)) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- MAIN: ABOUT + CONNECT -->
    <div style="display:flex;flex-direction:column;gap:24px">
      <?php if($listing['about_me']): ?>
      <div class="card">
        <h3 style="color:var(--saffron);margin-bottom:16px"><i class="fas fa-user-circle"></i> About <?= e(explode(' ', $listing['name'])[0]) ?></h3>
        <p style="line-height:1.8;font-size:.95rem"><?= nl2br(e($listing['about_me'])) ?></p>
      </div>
      <?php endif; ?>

      <!-- CONNECT / SEND REQUEST -->
      <div class="card" style="border-top:4px solid var(--teal)">
        <?php if($listing['user_id'] === $_SESSION['user_id']): ?>
        <div class="travel-card">
          <i class="fas fa-info-circle" style="color:var(--teal);font-size:1.2rem;flex-shrink:0;margin-top:2px"></i>
          <div>
            <strong style="display:block;margin-bottom:4px">This is your listing</strong>
            <p style="margin:0;font-size:.88rem">You posted this listing. Other travellers will be able to connect with you through it.</p>
          </div>
        </div>
        <a href="travel-buddy.php" class="btn-outline" style="margin-top:12px;display:inline-flex"><i class="fas fa-list-ul"></i> Manage My Listings</a>
        <?php elseif($sent): ?>
        <div style="text-align:center;padding:16px">
          <div style="font-size:3rem;margin-bottom:12px">✅</div>
          <h3 style="color:var(--teal);margin-bottom:6px">Request Sent!</h3>
          <p style="color:var(--muted);font-size:.9rem">Your connection request has been sent to <?= e(explode(' ', $listing['name'])[0]) ?>. They'll be in touch soon!</p>
          <p style="font-size:.82rem;color:var(--muted);margin-top:8px">You can connect at: <strong><?= $listing['contact_email'] ? e($listing['contact_email']) : e($listing['email']) ?></strong></p>
        </div>
        <?php else: ?>
        <h3 style="color:var(--teal);margin-bottom:8px"><i class="fas fa-envelope"></i> Connect with <?= e(explode(' ', $listing['name'])[0]) ?></h3>
        <p style="font-size:.88rem;color:var(--muted);margin-bottom:20px">Introduce yourself and let them know why you'd make a great travel buddy!</p>
        <div class="travel-card" style="margin-bottom:20px">
          <i class="fas fa-shield-alt" style="color:var(--gold);font-size:1.2rem;flex-shrink:0;margin-top:2px"></i>
          <div style="font-size:.85rem">
            <strong style="display:block;margin-bottom:2px">Safety reminder</strong>
            Meet in a public place first. Never send money or share sensitive personal details before meeting.
          </div>
        </div>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <div class="form-group">
            <label class="form-label">Your Message *</label>
            <textarea name="message" rows="6" placeholder="Hi! I'm heading to <?= e($listing['destination']) ?> around the same time. I'm interested in... [tell them about yourself and your travel style]" required></textarea>
          </div>
          <button type="submit" class="btn-teal" style="width:100%"><i class="fas fa-paper-plane"></i> Send Connection Request</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>@media(max-width:768px){.buddy-detail-layout{grid-template-columns:1fr !important}}</style>
<?php require_once 'includes/footer.php'; ?>

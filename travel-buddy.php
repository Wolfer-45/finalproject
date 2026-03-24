<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();
$db=getDB();$error='';$dest=clean($_GET['dest']??$_POST['destination']??'');$month=clean($_GET['month']??$_POST['month']??'');
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();if(isset($_POST['post_listing'])){$db->prepare('INSERT INTO buddy_listings (user_id,destination,start_date,end_date,group_size,gender_pref,travel_style,interests,about_me,contact_email) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([$_SESSION['user_id'],clean($_POST['destination']),clean($_POST['start_date']),clean($_POST['end_date']),clean($_POST['group_size']),clean($_POST['gender_pref']),clean($_POST['travel_style']),clean($_POST['interests']),clean($_POST['about_me']),clean($_POST['contact_email'])]);setFlash('success','Listing posted!');header('Location: travel-buddy.php');exit;}if(isset($_POST['delete_id'])){$db->prepare('DELETE FROM buddy_listings WHERE id=? AND user_id=?')->execute([(int)$_POST['delete_id'],$_SESSION['user_id']]);setFlash('success','Listing deleted.');header('Location: travel-buddy.php');exit;}}
$find=$db->prepare('SELECT b.id,b.destination,b.start_date,b.end_date,b.group_size,b.interests,b.travel_style,u.name,u.avatar FROM buddy_listings b JOIN users u ON u.id=b.user_id WHERE b.is_active=1 AND (?="" OR b.destination LIKE ?) ORDER BY b.created_at DESC');$find->execute([$dest,'%'.$dest.'%']);$rows=$find->fetchAll();
$mine=$db->prepare('SELECT id,destination,start_date,end_date,is_active FROM buddy_listings WHERE user_id=? ORDER BY created_at DESC');$mine->execute([$_SESSION['user_id']]);$my=$mine->fetchAll();
$pageTitle='Find a Travel Buddy';require_once 'includes/header.php'; ?>

<!-- HERO BANNER -->
<div style="background:linear-gradient(160deg,rgba(14,107,107,0.88),rgba(28,16,7,0.8)),url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1400&q=80') center/cover;padding:60px 0 56px">
  <div class="page-container">
    <div style="color:#fff;max-width:580px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-user-group" style="color:var(--gold-light)"></i> Travel Buddy Finder
      </div>
      <h1 style="color:#fff;margin-bottom:10px">Find Your Perfect Travel Companion</h1>
      <p style="color:rgba(255,255,255,.8)">Connect with fellow explorers heading to the same destinations. Share experiences, split costs, make memories.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">

  <!-- SAFETY NOTICE -->
  <div class="travel-card" style="margin-bottom:36px">
    <i class="fas fa-shield-alt" style="color:var(--gold);font-size:1.3rem;flex-shrink:0;margin-top:2px"></i>
    <div>
      <strong style="display:block;margin-bottom:4px">Safety First</strong>
      <p style="margin:0;font-size:.88rem">Always meet potential travel buddies in public spaces first. Verify their identity before sharing personal details or meeting for a trip.</p>
    </div>
  </div>

  <!-- SEARCH BAR -->
  <div class="card" style="margin-bottom:40px">
    <h3 style="margin-bottom:16px;color:var(--teal)"><i class="fas fa-search"></i> Search Travel Buddies</h3>
    <form method="get" style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:2;min-width:200px">
        <label class="form-label">Destination</label>
        <input name="dest" placeholder="e.g. Goa, Rajasthan, Kerala..." value="<?= e($dest) ?>">
      </div>
      <div style="flex:1;min-width:160px">
        <label class="form-label">Month</label>
        <input type="month" name="month" value="<?= e($month) ?>">
      </div>
      <button type="submit" class="btn-teal" style="flex-shrink:0;align-self:flex-end;margin-bottom:0"><i class="fas fa-search"></i> Find Buddies</button>
    </form>
  </div>

  <!-- RESULTS -->
  <?php if($dest): ?>
  <div style="margin-bottom:10px;font-size:.9rem;color:var(--muted)">
    Found <strong style="color:var(--text)"><?= count($rows) ?></strong> buddy<?= count($rows) !== 1 ? 'ies' : '' ?> for <strong style="color:var(--teal)"><?= e($dest) ?></strong>
  </div>
  <?php endif; ?>

  <?php if(count($rows) > 0): ?>
  <div class="grid-3" style="margin-bottom:56px">
    <?php foreach($rows as $r):
      $firstName = explode(' ', $r['name'])[0];
      $initial = strtoupper(substr($r['name'], 0, 1));
      $styleColors = ['Budget'=>'badge-teal','Mid-Range'=>'badge-gold','Luxury'=>'badge-saffron'];
      $styleClass = $styleColors[$r['travel_style']] ?? 'badge-teal';
    ?>
    <div class="buddy-card">
      <div class="buddy-card-top">
        <div class="buddy-avatar"><?= e($initial) ?></div>
        <h3><?= e($firstName) ?></h3>
        <span><?= e($r['destination']) ?></span>
      </div>
      <div class="buddy-card-body">
        <div class="buddy-info-row">
          <i class="fas fa-calendar"></i>
          <span><?= date('d M', strtotime($r['start_date'])) ?> – <?= date('d M Y', strtotime($r['end_date'])) ?></span>
        </div>
        <div class="buddy-info-row">
          <i class="fas fa-users"></i>
          <span><?= e(ucfirst($r['group_size'])) ?> trip</span>
        </div>
        <?php if($r['travel_style']): ?>
        <div class="buddy-info-row">
          <i class="fas fa-sliders"></i>
          <span><?= e($r['travel_style']) ?> style</span>
        </div>
        <?php endif; ?>
        <?php if($r['interests']): ?>
        <div class="buddy-tags">
          <?php foreach(array_slice(explode(',', $r['interests']), 0, 4) as $tag): ?>
          <span class="buddy-tag"><?= e(trim($tag)) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <a class="btn-teal btn-sm" href="buddy-detail.php?id=<?= (int)$r['id'] ?>" style="width:100%;justify-content:center;margin-top:8px">
          <i class="fas fa-envelope"></i> View & Connect
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php elseif($dest): ?>
  <div class="empty-state" style="margin-bottom:56px">
    <i class="fas fa-user-slash"></i>
    <h3>No buddies found for "<?= e($dest) ?>"</h3>
    <p>Be the first to post a listing for this destination!</p>
  </div>
  <?php else: ?>
  <div class="empty-state" style="margin-bottom:56px">
    <i class="fas fa-compass"></i>
    <h3>Search for a destination to find travel buddies</h3>
    <p>Or scroll down to post your own listing.</p>
  </div>
  <?php endif; ?>

  <!-- POST LISTING -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start" class="buddy-layout-grid">
    <div class="card" style="border-top:4px solid var(--teal)">
      <h3 style="color:var(--teal);margin-bottom:6px"><i class="fas fa-plus-circle"></i> Post My Listing</h3>
      <p style="font-size:.88rem;color:var(--muted);margin-bottom:24px">Let fellow travellers find you for your upcoming trip</p>
      <form method="post" style="display:flex;flex-direction:column;gap:14px">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="post_listing" value="1">
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Destination *</label>
          <input name="destination" placeholder="e.g. Manali, Coorg, Andamans" required>
        </div>
        <div class="grid-2" style="gap:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">End Date *</label>
            <input type="date" name="end_date" required>
          </div>
        </div>
        <div class="grid-2" style="gap:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Group Size</label>
            <select name="group_size">
              <option value="duo">Duo (2)</option>
              <option value="trio">Trio (3)</option>
              <option value="group">Group (4+)</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Gender Preference</label>
            <select name="gender_pref">
              <option value="any">Any</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="mixed">Mixed</option>
            </select>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Travel Style</label>
          <input name="travel_style" placeholder="e.g. Budget, Mid-Range, Luxury">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Interests</label>
          <input name="interests" placeholder="e.g. Trekking, Photography, Food">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">About Yourself</label>
          <textarea name="about_me" placeholder="Tell potential buddies about yourself, your travel vibe..."></textarea>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label">Contact Email</label>
          <input type="email" name="contact_email" placeholder="For buddies to reach you">
        </div>
        <button type="submit" class="btn-teal"><i class="fas fa-paper-plane"></i> Post My Listing</button>
      </form>
    </div>

    <!-- MY LISTINGS -->
    <div>
      <div class="card" style="border-top:4px solid var(--saffron)">
        <h3 style="color:var(--saffron);margin-bottom:20px"><i class="fas fa-list-ul"></i> My Listings</h3>
        <?php if(count($my) > 0): ?>
        <div style="display:flex;flex-direction:column;gap:12px">
          <?php foreach($my as $m): ?>
          <div style="background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border);padding:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
            <div>
              <strong style="display:block;margin-bottom:3px"><?= e($m['destination']) ?></strong>
              <span style="font-size:.82rem;color:var(--muted)"><?= date('d M', strtotime($m['start_date'])) ?> – <?= date('d M Y', strtotime($m['end_date'])) ?></span>
              <span class="badge <?= $m['is_active'] ? 'badge-green' : 'badge-saffron' ?>" style="margin-left:8px"><?= $m['is_active'] ? 'Active' : 'Inactive' ?></span>
            </div>
            <form method="post" onsubmit="return confirm('Delete this listing?')">
              <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="delete_id" value="<?= (int)$m['id'] ?>">
              <button type="submit" class="btn-outline btn-sm" style="border-color:#dc2626;color:#dc2626"><i class="fas fa-trash"></i> Delete</button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:32px 20px">
          <i class="fas fa-clipboard-list"></i>
          <h3>No listings yet</h3>
          <p>Post your first listing to find a travel buddy!</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
@media (max-width:768px) {
  .buddy-layout-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once 'includes/footer.php'; ?>

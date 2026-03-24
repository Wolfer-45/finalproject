<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';requireLogin();$db=getDB();$trip=(int)($_GET['trip_id']??0);
if(!$trip){$s=$db->prepare('SELECT id,destination FROM trips WHERE user_id=? ORDER BY created_at DESC');$s->execute([$_SESSION['user_id']]);$trips=$s->fetchAll();}
if($_SERVER['REQUEST_METHOD']==='POST'){verifyCsrf();if(isset($_POST['set_budget'])){$trip=(int)$_POST['trip_id'];$total=floatval($_POST['total_budget']);$db->prepare('INSERT INTO budgets (user_id,trip_id,total_budget,est_transport,est_hotel,est_food,est_activities,est_misc) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE total_budget=VALUES(total_budget),est_transport=VALUES(est_transport),est_hotel=VALUES(est_hotel),est_food=VALUES(est_food),est_activities=VALUES(est_activities),est_misc=VALUES(est_misc)')->execute([$_SESSION['user_id'],$trip,$total,$total*.30,$total*.35,$total*.20,$total*.10,$total*.05]);}
if(isset($_POST['add_expense'])){$db->prepare('INSERT INTO expenses (user_id,trip_id,category,amount,note,expense_date) VALUES (?,?,?,?,?,?)')->execute([$_SESSION['user_id'],(int)$_POST['trip_id'],clean($_POST['category']),floatval($_POST['amount']),clean($_POST['note']),clean($_POST['expense_date'])]);}
header('Location: budget.php?trip_id='.(int)($_POST['trip_id']??$trip));exit;}
$budget=null;$spent=0;$rows=[];if($trip){$b=$db->prepare('SELECT * FROM budgets WHERE user_id=? AND trip_id=?');$b->execute([$_SESSION['user_id'],$trip]);$budget=$b->fetch();$e=$db->prepare('SELECT * FROM expenses WHERE user_id=? AND trip_id=? ORDER BY created_at DESC');$e->execute([$_SESSION['user_id'],$trip]);$rows=$e->fetchAll();foreach($rows as $r){$spent+=(float)$r['amount'];}}
$pageTitle='Budget Planner';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(200,146,10,0.85),rgba(28,16,7,0.9)),url('https://images.unsplash.com/photo-1633158829585-23ba8f7c8caf?w=1200&q=80') center/cover;padding:56px 0 52px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-wallet" style="color:var(--gold-light)"></i> Budget Tracker
      </div>
      <h1 style="color:#fff;margin-bottom:8px">Trip Budget Planner</h1>
      <p style="color:rgba(255,255,255,.8)">Track your spending, stay on budget, and make every rupee count on your journey.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">

  <?php if(!$trip): ?>
  <!-- TRIP SELECTOR -->
  <div class="card" style="max-width:480px;margin:0 auto">
    <h3 style="color:var(--gold);margin-bottom:16px"><i class="fas fa-route"></i> Select a Trip</h3>
    <form method="get">
      <div class="form-group">
        <label class="form-label">Choose your trip</label>
        <select name="trip_id">
          <?php foreach($trips as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= e($t['destination']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn-gold" style="width:100%"><i class="fas fa-arrow-right"></i> Open Budget</button>
    </form>
    <?php if(empty($trips)): ?>
    <div class="empty-state" style="padding:24px 0 0">
      <i class="fas fa-route"></i>
      <p>No trips yet. <a href="plan-trip.php" style="color:var(--saffron);font-weight:700">Plan your first trip →</a></p>
    </div>
    <?php endif; ?>
  </div>

  <?php else:
    $total = (float)($budget['total_budget'] ?? 0);
    $remaining = $total - $spent;
    $pct = $total > 0 ? min(100, ($spent / $total) * 100) : 0;
    $catIcons = ['transport'=>'fas fa-bus','hotel'=>'fas fa-hotel','food'=>'fas fa-utensils','activities'=>'fas fa-hiking','misc'=>'fas fa-box-open'];
    $catColors = ['transport'=>'var(--teal)','hotel'=>'var(--saffron)','food'=>'var(--gold)','activities'=>'var(--maroon)','misc'=>'var(--muted)'];
  ?>

  <!-- BUDGET OVERVIEW -->
  <div class="grid-3" style="margin-bottom:36px">
    <div class="card" style="text-align:center;border-top:4px solid var(--gold)">
      <div style="font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Total Budget</div>
      <div style="font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:800;color:var(--gold)">₹<?= number_format($total) ?></div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid var(--saffron)">
      <div style="font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Spent</div>
      <div style="font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:800;color:var(--saffron)">₹<?= number_format($spent) ?></div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid <?= $remaining >= 0 ? 'var(--teal)' : '#dc2626' ?>">
      <div style="font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Remaining</div>
      <div style="font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:800;color:<?= $remaining >= 0 ? 'var(--teal)' : '#dc2626' ?>">₹<?= number_format(abs($remaining)) ?><?= $remaining < 0 ? ' over' : '' ?></div>
    </div>
  </div>

  <!-- PROGRESS BAR -->
  <?php if($total > 0): ?>
  <div class="card" style="margin-bottom:36px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <span style="font-weight:700;font-size:.9rem">Budget Used</span>
      <span style="font-weight:800;color:<?= $pct >= 90 ? '#dc2626' : 'var(--teal)' ?>"><?= round($pct) ?>%</span>
    </div>
    <div class="budget-bar-wrap">
      <div class="budget-bar <?= $pct >= 100 ? 'over' : '' ?>" style="width:<?= $pct ?>%"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--muted);margin-top:8px">
      <span>₹0</span><span>₹<?= number_format($total) ?></span>
    </div>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start" class="budget-layout">

    <!-- SET BUDGET + ADD EXPENSE -->
    <div style="display:flex;flex-direction:column;gap:24px">
      <div class="card" style="border-top:4px solid var(--gold)">
        <h3 style="color:var(--gold);margin-bottom:16px"><i class="fas fa-piggy-bank"></i> Set / Update Budget</h3>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="set_budget" value="1">
          <input type="hidden" name="trip_id" value="<?= $trip ?>">
          <div class="form-group">
            <label class="form-label">Total Budget (INR ₹)</label>
            <input type="number" step="0.01" name="total_budget" placeholder="e.g. 25000" value="<?= $total > 0 ? $total : '' ?>" required>
          </div>
          <button class="btn-gold" style="width:100%"><i class="fas fa-save"></i> Save Budget</button>
        </form>
      </div>

      <div class="card" style="border-top:4px solid var(--saffron)">
        <h3 style="color:var(--saffron);margin-bottom:16px"><i class="fas fa-plus-circle"></i> Add Expense</h3>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="add_expense" value="1">
          <input type="hidden" name="trip_id" value="<?= $trip ?>">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category">
              <option value="transport"><i class="fas fa-bus"></i> Transport</option>
              <option value="hotel">Hotel / Stay</option>
              <option value="food">Food & Dining</option>
              <option value="activities">Activities & Sightseeing</option>
              <option value="misc">Miscellaneous</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Amount (₹)</label>
            <input type="number" step="0.01" name="amount" placeholder="e.g. 1500" required>
          </div>
          <div class="form-group">
            <label class="form-label">Note</label>
            <input name="note" placeholder="e.g. Auto from airport">
          </div>
          <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required>
          </div>
          <button class="btn-primary" style="width:100%"><i class="fas fa-plus"></i> Add Expense</button>
        </form>
      </div>
    </div>

    <!-- EXPENSE LIST -->
    <div class="card" style="border-top:4px solid var(--teal)">
      <h3 style="color:var(--teal);margin-bottom:20px"><i class="fas fa-list-ul"></i> Expense Log</h3>
      <?php if(count($rows) > 0): ?>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach($rows as $r):
          $catIcon = $catIcons[$r['category']] ?? 'fas fa-receipt';
          $catColor = $catColors[$r['category']] ?? 'var(--muted)';
        ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border)">
          <div style="width:38px;height:38px;background:rgba(0,0,0,.05);border-radius:50%;display:flex;align-items:center;justify-content:center;color:<?= $catColor ?>;flex-shrink:0">
            <i class="<?= $catIcon ?>"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:.9rem;text-transform:capitalize"><?= e($r['category']) ?></div>
            <div style="font-size:.8rem;color:var(--muted)"><?= e($r['note'] ?: '—') ?> · <?= date('d M', strtotime($r['expense_date'])) ?></div>
          </div>
          <div style="font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:700;color:var(--saffron);flex-shrink:0">₹<?= number_format((float)$r['amount']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;justify-content:space-between">
        <strong>Total Spent</strong>
        <strong style="color:var(--saffron)">₹<?= number_format($spent) ?></strong>
      </div>
      <?php else: ?>
      <div class="empty-state" style="padding:32px 0">
        <i class="fas fa-receipt"></i>
        <h3>No expenses yet</h3>
        <p>Add your first expense to start tracking</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>
</div>

<style>@media(max-width:768px){.budget-layout{grid-template-columns:1fr !important}}</style>
<?php require_once 'includes/footer.php'; ?>

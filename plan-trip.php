<?php
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';require_once 'includes/gemini.php';
$db=getDB();$user=isLoggedIn()?getCurrentUser():null;$error='';

function countItineraryDays(string $text): int {
  preg_match_all('/DAY\\s*\\d+\\s*-/i', $text, $matches);
  return count($matches[0]);
}

function getBudgetTier(float $totalBudget, int $days): string {
  if ($days <= 0) { return 'mid-range'; }
  $perDay = $totalBudget / $days;
  if ($perDay < 2500) { return 'budget'; }
  if ($perDay <= 7000) { return 'mid-range'; }
  return 'premium';
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!isLoggedIn()){ header('Location: '.SITE_URL.'/login.php?next=plan-trip.php'); exit; }
  verifyCsrf();
  $destination=clean($_POST['destination']??'');$start=clean($_POST['start_date']??'');$end=clean($_POST['end_date']??'');
  $types=implode(', ', $_POST['travel_type'] ?? []);$style=clean($_POST['travel_style']??'Budget');$interests=implode(', ', $_POST['interests'] ?? []);$budget=floatval($_POST['budget']??0);
  if(!$destination||!$start||!$end){$error='Destination and dates are required.';}
  if(!$error && strtotime($end) < strtotime($start)){$error='End date must be on or after start date.';}
  $duration=(new DateTime($start))->diff(new DateTime($end))->days+1;
  if(!$error && $duration > 21){$error='For best quality, choose up to 21 days at once.';}
  if(!$error){
    $title=$destination.' Trip';
    $db->prepare('INSERT INTO trips (user_id,title,destination,start_date,end_date,duration,travel_type,travel_style,interests,budget,status) VALUES (?,?,?,?,?,?,?,?,?,? ,"planned")')->execute([$_SESSION['user_id'],$title,$destination,$start,$end,$duration,$types,$style,$interests,$budget]);
    $tripId=(int)$db->lastInsertId();
    if(aiRateLimitExceeded()){setFlash('error','AI limit reached (10/hour).');header('Location: dashboard.php');exit;}
    $selectedTypes = $types !== '' ? $types : 'general exploration';
    $selectedInterests = $interests !== '' ? $interests : 'sightseeing, food, local culture';
    $budgetTier = getBudgetTier($budget, $duration);
    $dailyBudget = $duration > 0 ? round($budget / $duration) : 0;
    $prompt="You are a premium India travel consultant.
Create a COMPLETE {$duration}-day FULL PACKAGE itinerary for {$destination}.
Do not limit the plan to one city unless destination itself is a single-city trip.
Cover the best circuit across the state/region and distribute days smartly.

Trip details:
- Start date: {$start}
- End date: {$end}
- Duration: {$duration} days
- Travel type filters: {$selectedTypes}
- Travel style: {$style}
- Total budget: INR {$budget}
- Approx daily budget: INR {$dailyBudget}
- Budget tier: {$budgetTier}
- Interests: {$selectedInterests}

Hard requirements:
1) Return EXACTLY {$duration} day sections. Do not skip any day.
2) Use this exact structure for EVERY day:
DAY N - [City/Area and main zone]
Morning: [Specific attraction + suggested timing + travel note]
Afternoon: [Specific attraction + suggested timing + travel note]
Evening: [Specific attraction/activity + suggested timing]
Food: [2-3 local dishes or iconic places that match budget]
Budget Note: [Approx spend for the day in INR + how it fits total budget]
Tip: [One local practical tip]
---
3) Ensure day-wise coverage is optimized to help traveler get the MOST from {$duration} days.
4) Balance iconic highlights + hidden gems + local experiences + food.
5) Respect budget strongly.
6) Include realistic intra-city/inter-city transfers.
7) Add season/date awareness for {$start} to {$end}.
8) Avoid generic statements; use specific names.
9) Output plain text only. No markdown.";
    $content=callGemini($prompt, ['temperature' => 0.9, 'max_tokens' => 4096]);
    $dayCount = countItineraryDays($content);
    if ($dayCount !== $duration) {
      $retryPrompt = $prompt . "\n\nIMPORTANT: Your previous output had {$dayCount} days. Regenerate now with EXACTLY {$duration} day sections.";
      $content = callGemini($retryPrompt, ['temperature' => 0.8, 'max_tokens' => 4096]);
    }
    $db->prepare('INSERT INTO itineraries (trip_id,user_id,content) VALUES (?,?,?)')->execute([$tripId,$_SESSION['user_id'],$content]);
    header('Location: itinerary.php?trip_id='.$tripId);exit;
  }
}
$pageTitle='Plan My Trip';require_once 'includes/header.php'; ?>

<div style="background:linear-gradient(160deg,rgba(0,50,98,0.92),rgba(0,112,187,0.78)),url('https://images.unsplash.com/photo-1587474260584-136574528ed5?w=1400&q=80') center/cover;padding:56px 0 64px;margin-bottom:-24px">
  <div class="page-container">
    <div style="color:#fff;max-width:560px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);color:#fff;padding:7px 16px;border-radius:999px;font-size:.8rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:18px">
        <i class="fas fa-wand-magic-sparkles" style="color:rgba(255,255,255,.8)"></i> AI-Powered Planning
      </div>
      <h1 style="color:#fff;margin-bottom:10px">Plan Your Perfect Indian Adventure</h1>
      <p style="color:rgba(255,255,255,.8)">Answer a few questions and get a detailed, day-by-day itinerary crafted just for you.</p>
    </div>
  </div>
</div>

<div class="page-container" style="padding-top:48px;padding-bottom:80px">

  <?php if(!isLoggedIn()): ?>
  <div class="guest-banner">
    <i class="fas fa-info-circle"></i>
    <p><strong>Browsing as guest.</strong> You can explore the planner below. Sign up free to generate your personalized AI itinerary and save your trips.</p>
    <a href="signup.php" class="btn-primary btn-sm"><i class="fas fa-user-plus"></i> Sign Up Free</a>
  </div>
  <?php endif; ?>

  <?php if($error): ?>
  <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
  <?php endif; ?>

  <!-- WIZARD PROGRESS -->
  <div class="wizard-progress">
    <?php $wSteps = ['Destination', 'Dates', 'Trip Style', 'Interests', 'Budget'];
    foreach ($wSteps as $i => $wLabel): ?>
    <div class="wizard-step <?= $i === 0 ? 'active' : '' ?>">
      <?php if ($i > 0): ?><div class="wizard-step-line"></div><?php endif; ?>
      <div class="wizard-step-dot">
        <div class="wizard-step-num"><?= $i+1 ?></div>
        <div class="wizard-step-label"><?= e($wLabel) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <form method="post" id="trip-form">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

    <!-- STEP 1: DESTINATION -->
    <div class="wizard-pane active step-panel">
      <div class="step-title">
        <div class="step-title-num">1</div>
        <div class="step-title-text">
          <h2>Where Do You Want to Go?</h2>
          <p>Enter a state, city, region or a specific place in India</p>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Destination *</label>
        <input type="text" name="destination" id="destination" placeholder="e.g. Rajasthan, Goa, Hampi, Coorg..." required style="font-size:1.1rem;padding:16px 20px">
      </div>
      <p class="form-label" style="margin-bottom:12px">Popular destinations</p>
      <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px">
        <?php foreach(['Goa','Rajasthan','Kerala','Himachal Pradesh','Varanasi','Agra','Coorg','Leh-Ladakh','Ooty','Andaman Islands','Rishikesh','Munnar'] as $pop): ?>
        <button type="button" class="chip" onclick="document.getElementById('destination').value='<?= e($pop) ?>'">
          <i class="fas fa-map-pin"></i> <?= e($pop) ?>
        </button>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;justify-content:flex-end">
        <button type="button" class="btn-primary wizard-next">Next: Choose Dates <i class="fas fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- STEP 2: DATES -->
    <div class="wizard-pane step-panel">
      <div class="step-title">
        <div class="step-title-num">2</div>
        <div class="step-title-text">
          <h2>When Are You Travelling?</h2>
          <p>Select your start and end dates (maximum 21 days)</p>
        </div>
      </div>
      <div class="grid-2" style="margin-bottom:20px">
        <div class="form-group">
          <label class="form-label"><i class="fas fa-calendar-alt" style="color:var(--primary);margin-right:6px"></i>Start Date *</label>
          <input type="date" name="start_date" id="start_date" required>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-calendar-check" style="color:var(--primary-dark);margin-right:6px"></i>End Date *</label>
          <input type="date" name="end_date" id="end_date" required>
        </div>
      </div>
      <div id="duration-note" style="margin-bottom:24px;min-height:32px"></div>
      <p style="font-size:.88rem;color:var(--muted);margin-bottom:28px"><i class="fas fa-info-circle"></i> WanderWise plans the best circuit to cover maximum places in your trip duration.</p>
      <div style="display:flex;justify-content:space-between">
        <button type="button" class="btn-outline wizard-back"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-primary wizard-next">Next: Trip Style <i class="fas fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- STEP 3: TRIP TYPE -->
    <div class="wizard-pane step-panel">
      <div class="step-title">
        <div class="step-title-num">3</div>
        <div class="step-title-text">
          <h2>What Kind of Trip?</h2>
          <p>Select all that apply — the AI will mix them perfectly</p>
        </div>
      </div>
      <div class="grid-4" style="margin-bottom:32px">
        <?php $types = [
          ['Historical', 'fas fa-monument', 'https://images.unsplash.com/photo-1564507592333-c60657eea523?w=400&q=75'],
          ['Cultural', 'fas fa-theater-masks', 'https://images.unsplash.com/photo-1567591370429-a2f6c82e5a00?w=400&q=75'],
          ['Adventure', 'fas fa-mountain', 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=400&q=75'],
          ['Nature', 'fas fa-leaf', 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=400&q=75'],
          ['Food', 'fas fa-utensils', 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=400&q=75'],
          ['Beach', 'fas fa-umbrella-beach', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=400&q=75'],
          ['Spiritual', 'fas fa-om', 'https://images.unsplash.com/photo-1561361058-c24cecae35ca?w=400&q=75'],
          ['Shopping', 'fas fa-shopping-bag', 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=400&q=75'],
        ];
        foreach ($types as $t): ?>
        <label class="type-card">
          <input type="checkbox" name="travel_type[]" value="<?= e($t[0]) ?>">
          <img src="<?= e($t[2]) ?>" alt="<?= e($t[0]) ?>" loading="lazy">
          <div class="type-card-overlay">
            <i class="<?= e($t[1]) ?>"></i>
            <span><?= e($t[0]) ?></span>
          </div>
          <div class="check-tick"><i class="fas fa-check"></i></div>
        </label>
        <?php endforeach; ?>
      </div>
      <div class="form-group">
        <label class="form-label"><i class="fas fa-sliders" style="color:var(--primary);margin-right:6px"></i>Travel Style</label>
        <div style="display:flex;gap:16px;flex-wrap:wrap">
          <?php foreach(['Budget','Mid-Range','Luxury'] as $idx => $s):
            $styleColors = ['var(--primary-dark)','var(--primary)','var(--primary-light)'];
            $styleIcons = ['fas fa-piggy-bank','fas fa-balance-scale','fas fa-crown'];
          ?>
          <label style="flex:1;min-width:140px;cursor:pointer">
            <input type="radio" name="travel_style" value="<?= e($s) ?>" <?= $s==='Budget'?'checked':'' ?> style="display:none" class="style-radio">
            <div class="style-option-card" style="border:2px solid var(--border);border-radius:var(--radius);padding:16px;text-align:center;transition:all .2s;cursor:pointer">
              <i class="<?= e($styleIcons[$idx]) ?>" style="font-size:1.5rem;color:<?= e($styleColors[$idx]) ?>;display:block;margin-bottom:8px"></i>
              <strong style="display:block;font-size:.95rem"><?= e($s) ?></strong>
              <span style="font-size:.78rem;color:var(--muted)"><?= ['Under ₹2,500/day','₹2,500–₹7,000/day','₹7,000+/day'][$idx] ?></span>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:24px">
        <button type="button" class="btn-outline wizard-back"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-primary wizard-next">Next: Interests <i class="fas fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- STEP 4: INTERESTS -->
    <div class="wizard-pane step-panel">
      <div class="step-title">
        <div class="step-title-num">4</div>
        <div class="step-title-text">
          <h2>Your Interests & Preferences</h2>
          <p>Help us tailor your itinerary even further</p>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">What are you most interested in? <span style="color:var(--muted);font-weight:400">(select all that apply)</span></label>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
          <?php foreach(['Photography','Street Food','Shopping','Nightlife','Wildlife','Architecture','Yoga & Wellness','History & Museums','Handicrafts','Local Markets','Temples & Shrines','Trekking'] as $interest): ?>
          <label style="cursor:pointer">
            <input type="checkbox" name="interests[]" value="<?= e($interest) ?>" style="display:none" class="interest-cb">
            <div class="interest-chip" style="padding:10px 18px;border:2px solid var(--border);border-radius:999px;font-size:.88rem;font-weight:600;color:var(--text-2);transition:all .2s;cursor:pointer">
              <?= e($interest) ?>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="grid-2" style="margin-top:24px">
        <div class="form-group">
          <label class="form-label"><i class="fas fa-users" style="color:var(--primary);margin-right:6px"></i>Travelling As</label>
          <select name="group_type">
            <option value="">Any</option>
            <option value="solo">Solo Explorer</option>
            <option value="couple">Couple</option>
            <option value="family">Family with Kids</option>
            <option value="group">Friends Group</option>
            <option value="senior">Senior Traveller</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-bed" style="color:var(--primary-dark);margin-right:6px"></i>Stay Preference</label>
          <select name="stay_type">
            <option value="">Any</option>
            <option value="hostel">Hostels / Guesthouses</option>
            <option value="hotel">Mid-Range Hotels</option>
            <option value="resort">Resorts & Heritage Hotels</option>
            <option value="homestay">Homestays & Local Stays</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-tachometer-alt" style="color:var(--primary);margin-right:6px"></i>Trip Pace</label>
          <select name="pace">
            <option value="">Balanced</option>
            <option value="slow">Slow & Relaxed</option>
            <option value="balanced">Balanced (recommended)</option>
            <option value="packed">Packed & Adventurous</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fas fa-utensils" style="color:var(--primary);margin-right:6px"></i>Food Preference</label>
          <select name="food_pref">
            <option value="">All Food</option>
            <option value="vegetarian">Vegetarian Only</option>
            <option value="vegan">Vegan</option>
            <option value="nonveg">Non-Vegetarian OK</option>
            <option value="jain">Jain Food</option>
          </select>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:24px">
        <button type="button" class="btn-outline wizard-back"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-primary wizard-next">Next: Budget <i class="fas fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- STEP 5: BUDGET + SUBMIT -->
    <div class="wizard-pane step-panel">
      <div class="step-title">
        <div class="step-title-num">5</div>
        <div class="step-title-text">
          <h2>What's Your Total Budget?</h2>
          <p>Set your total trip budget in ₹ INR for the full duration</p>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Total Trip Budget (₹ INR)</label>
        <div style="position:relative">
          <span style="position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:1.3rem;font-weight:700;color:var(--primary)">₹</span>
          <input type="number" name="budget" id="budget_amount" placeholder="e.g. 25000" min="0" step="500" style="padding-left:40px;font-size:1.1rem">
        </div>
        <div id="budget-display" style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--primary);margin-top:8px;min-height:48px"></div>
        <p style="font-size:.85rem;color:var(--muted);margin-top:4px">Includes accommodation, food, transport & activities for the entire trip</p>
      </div>
      <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px">
        <?php foreach([5000,10000,20000,35000,50000,100000] as $v): ?>
        <button type="button" class="chip" onclick="document.getElementById('budget_amount').value='<?= $v ?>';document.getElementById('budget-display').textContent='₹<?= number_format($v) ?>'">
          ₹<?= number_format($v) ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- REVIEW CARD -->
      <div style="background:linear-gradient(135deg,rgba(0,50,98,.06),rgba(0,112,187,.04));border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:28px">
        <h3 style="margin-bottom:16px;color:var(--primary-dark)"><i class="fas fa-clipboard-list"></i> Your Trip at a Glance</h3>
        <div class="grid-2" style="gap:12px">
          <div style="font-size:.88rem"><strong style="color:var(--muted);display:block;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Destination</strong><span id="review-dest" style="font-weight:700">—</span></div>
          <div style="font-size:.88rem"><strong style="color:var(--muted);display:block;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Duration</strong><span id="review-duration">—</span></div>
          <div style="font-size:.88rem"><strong style="color:var(--muted);display:block;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Travel Style</strong><span id="review-style">Budget</span></div>
          <div style="font-size:.88rem"><strong style="color:var(--muted);display:block;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Budget</strong><span id="review-budget">—</span></div>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
        <button type="button" class="btn-outline wizard-back"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-primary" style="font-size:1rem;padding:15px 32px" id="generate-btn" onclick="handleGenerate(event)">
          <i class="fas fa-wand-magic-sparkles"></i> Generate My AI Itinerary
        </button>
      </div>
      <p style="text-align:center;color:var(--muted);font-size:.82rem;margin-top:16px">
        <i class="fas fa-clock"></i> AI generation takes 10–30 seconds. Please wait after clicking.
      </p>
    </div>
  </form>
</div>

<script>
const isLoggedIn = <?= isLoggedIn() ? 'true' : 'false' ?>;

function handleGenerate(e) {
  if (!isLoggedIn) {
    e.preventDefault();
    showLoginModal();
    return;
  }
  const btn = document.getElementById('generate-btn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating your perfect trip...';
  btn.disabled = true;
  btn.style.opacity = '.75';
  document.getElementById('trip-form').submit();
}

document.querySelectorAll('.style-radio').forEach(r => {
  r.addEventListener('change', function() {
    document.querySelectorAll('.style-option-card').forEach(c => {
      c.style.borderColor = 'var(--border)';
      c.style.background = '';
    });
    if (this.checked) {
      this.closest('label').querySelector('.style-option-card').style.borderColor = 'var(--primary)';
      this.closest('label').querySelector('.style-option-card').style.background = 'rgba(0,112,187,.05)';
    }
  });
});
const firstStyleCard = document.querySelector('.style-radio:checked')?.closest('label')?.querySelector('.style-option-card');
if (firstStyleCard) { firstStyleCard.style.borderColor = 'var(--primary)'; firstStyleCard.style.background = 'rgba(0,112,187,.05)'; }

document.querySelectorAll('.interest-cb').forEach(cb => {
  const chip = cb.closest('label').querySelector('.interest-chip');
  cb.addEventListener('change', function() {
    if (this.checked) { chip.style.borderColor='var(--primary)'; chip.style.background='rgba(0,112,187,.08)'; chip.style.color='var(--primary)'; }
    else { chip.style.borderColor='var(--border)'; chip.style.background=''; chip.style.color='var(--text-2)'; }
  });
});

function updateReview() {
  const dest = document.getElementById('destination')?.value || '—';
  const start = document.getElementById('start_date')?.value;
  const end = document.getElementById('end_date')?.value;
  const style = document.querySelector('.style-radio:checked')?.value || '—';
  const budget = document.getElementById('budget_amount')?.value;
  document.getElementById('review-dest').textContent = dest || '—';
  if (start && end) {
    const days = Math.round((new Date(end) - new Date(start)) / 86400000) + 1;
    document.getElementById('review-duration').textContent = days > 0 ? days + ' days' : '—';
  }
  document.getElementById('review-style').textContent = style;
  document.getElementById('review-budget').textContent = budget ? '₹' + parseInt(budget).toLocaleString('en-IN') : '—';
}
document.addEventListener('input', updateReview);
document.addEventListener('change', updateReview);

// Wizard navigation
const panes = document.querySelectorAll('.wizard-pane');
const steps = document.querySelectorAll('.wizard-step');
let cur = 0;
function goTo(n) {
  panes[cur].classList.remove('active'); steps[cur].classList.remove('active'); steps[cur].classList.add('done');
  if (n < cur) { steps[cur].classList.remove('done'); }
  cur = n;
  panes[cur].classList.add('active'); steps[cur].classList.remove('done'); steps[cur].classList.add('active');
  window.scrollTo({top: 0, behavior:'smooth'});
}
document.querySelectorAll('.wizard-next').forEach(btn => btn.addEventListener('click', () => { if (cur < panes.length-1) goTo(cur+1); }));
document.querySelectorAll('.wizard-back').forEach(btn => btn.addEventListener('click', () => { if (cur > 0) goTo(cur-1); }));
</script>

<?php require_once 'includes/footer.php'; ?>

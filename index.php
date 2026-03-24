<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$pageTitle = 'WanderWise - AI Travel Companion for India';
require_once 'includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="page-container">
    <div class="hero-content">
      <div class="hero-badge">
        <i class="fas fa-star" style="color:var(--gold-light)"></i>
        India's #1 AI Travel Planner
      </div>
      <h1>Discover <em>Incredible India</em> Like Never Before</h1>
      <p>From the peaks of the Himalayas to the beaches of Goa — plan your perfect Indian journey with AI-powered itineraries, live weather, and a community of fellow explorers.</p>
      <div class="hero-cta">
        <?php if(isLoggedIn()): ?>
          <a href="plan-trip.php" class="btn-primary btn-lg"><i class="fas fa-route"></i> Plan My Next Trip</a>
          <a href="dashboard.php" class="btn-outline" style="border-color:rgba(255,255,255,.5);color:#fff"><i class="fas fa-th-large"></i> My Dashboard</a>
        <?php else: ?>
          <a href="signup.php" class="btn-primary"><i class="fas fa-rocket"></i> Start Planning Free</a>
          <a href="#how" class="btn-outline" style="border-color:rgba(255,255,255,.5);color:#fff"><i class="fas fa-play"></i> See How It Works</a>
        <?php endif; ?>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><strong>50+</strong><span>Destinations</span></div>
        <div class="hero-stat"><strong>AI</strong><span>Day-by-Day Plans</span></div>
        <div class="hero-stat"><strong>Free</strong><span>Forever</span></div>
      </div>
    </div>
  </div>
  <div class="hero-scroll"><i class="fas fa-chevron-down"></i><br>Scroll to explore</div>
</section>

<!-- ===== FEATURES ===== -->
<section class="section" style="background:var(--surface)">
  <div class="page-container">
    <div class="section-header">
      <div class="eyebrow">Everything You Need</div>
      <h2>Your Complete Travel Companion</h2>
      <p>From first idea to last memory, WanderWise has every tool you need for an unforgettable Indian journey.</p>
    </div>
    <div class="grid-3">
      <?php
      $features = [
        ['fas fa-route', 'AI Trip Planner', 'Get a complete day-by-day itinerary tailored to your budget, style, and interests in seconds.', '#C8501A'],
        ['fas fa-user-group', 'Travel Buddy Finder', 'Connect with like-minded explorers going to the same destination at the same time.', '#0E6B6B'],
        ['fas fa-book-open', 'AI Storybook', 'Transform your trip memories into beautiful, shareable stories powered by AI.', '#C8920A'],
        ['fas fa-wallet', 'Budget Tracker', 'Track every expense, set category budgets, and stay in control of your travel spend.', '#7B1E32'],
        ['fas fa-cloud-sun', 'Live Weather', 'Real-time forecasts for your destination so you can pack smart and plan smart.', '#2563EB'],
        ['fas fa-robot', 'Wandi AI Chat', 'Ask anything about travel — destinations, tips, packing, local customs — anytime.', '#16A34A'],
      ];
      foreach ($features as $f): ?>
      <div class="feature-card card-hover">
        <div class="feature-icon" style="background:<?= e($f[3]) ?>18;color:<?= e($f[3]) ?>">
          <i class="<?= e($f[0]) ?>"></i>
        </div>
        <h3><?= e($f[1]) ?></h3>
        <p><?= e($f[2]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="section" id="how">
  <div class="page-container">
    <div class="section-header">
      <div class="eyebrow">Simple as 1-2-3</div>
      <h2>How WanderWise Works</h2>
    </div>
    <div class="grid-3">
      <?php
      $steps = [
        ['1', 'fas fa-map-pin', 'Choose Your Destination', 'Pick from hundreds of Indian destinations — states, cities, temples, hill stations, beaches and more.'],
        ['2', 'fas fa-sliders', 'Set Your Preferences', 'Tell us your budget, travel style, interests, and trip duration. The more detail, the better your plan.'],
        ['3', 'fas fa-magic', 'Get Your AI Itinerary', 'Receive a detailed, day-by-day plan with places, food, budget breakdowns, and local tips.'],
      ];
      foreach ($steps as $s): ?>
      <div class="card card-hover" style="text-align:center;padding:36px 28px">
        <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--saffron),var(--saffron-light));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.6rem;color:#fff">
          <i class="<?= e($s[1]) ?>"></i>
        </div>
        <div style="font-family:'Playfair Display',serif;font-size:3rem;font-weight:800;color:rgba(212,98,26,.1);margin-bottom:-16px;margin-top:-8px"><?= e($s[0]) ?></div>
        <h3 style="color:var(--text);margin-bottom:10px"><?= e($s[2]) ?></h3>
        <p style="font-size:.92rem"><?= e($s[3]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if(!isLoggedIn()): ?>
    <div style="text-align:center;margin-top:40px">
      <a href="signup.php" class="btn-primary"><i class="fas fa-arrow-right"></i> Start My Journey — It's Free</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== DESTINATIONS ===== -->
<section class="section" style="background:var(--surface)">
  <div class="page-container">
    <div class="section-header">
      <div class="eyebrow">Popular Destinations</div>
      <h2>Where Will You Go Next?</h2>
      <p>Explore India's most iconic destinations — each with its own magic, culture, and flavour.</p>
    </div>
    <?php
    $dests = [
      ['Goa', 'Beach Bliss & Nightlife', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=600&q=80'],
      ['Rajasthan', 'Royal Forts & Deserts', 'https://images.unsplash.com/photo-1477587458883-47145ed94245?w=600&q=80'],
      ['Kerala', 'Backwaters & Spices', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=600&q=80'],
      ['Himachal Pradesh', 'Snowy Peaks & Valleys', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=600&q=80'],
      ['Varanasi', 'Spiritual Heritage', 'https://images.unsplash.com/photo-1561361058-c24cecae35ca?w=600&q=80'],
      ['Mumbai', 'City of Dreams', 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=600&q=80'],
    ];
    ?>
    <div class="grid-3">
      <?php foreach ($dests as $d): ?>
      <a class="dest-card" href="stories.php?dest=<?= urlencode($d[0]) ?>">
        <img src="<?= e($d[2]) ?>" alt="<?= e($d[0]) ?>" loading="lazy">
        <div class="dest-card-overlay">
          <h3><?= e($d[0]) ?></h3>
          <span><?= e($d[1]) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:32px">
      <a href="<?= isLoggedIn() ? 'plan-trip.php' : 'signup.php' ?>" class="btn-outline">
        <i class="fas fa-compass"></i> Explore All Destinations
      </a>
    </div>
  </div>
</section>

<!-- ===== CTA BANNER ===== -->
<?php if(!isLoggedIn()): ?>
<section class="section" style="padding:0">
  <div class="page-container" style="padding-bottom:80px">
    <div style="background:linear-gradient(135deg,rgba(28,16,7,0.92),rgba(14,107,107,0.85)),url('https://images.unsplash.com/photo-1544015759-237f43a3e0f8?w=1200&q=80') center/cover;border-radius:var(--radius-lg);padding:64px 56px;text-align:center;color:#fff">
      <div style="max-width:560px;margin:0 auto">
        <h2 style="color:#fff;margin-bottom:12px">Ready to Explore Incredible India?</h2>
        <p style="color:rgba(255,255,255,.8);margin-bottom:32px;font-size:1.05rem">Join thousands of travellers planning smarter trips with WanderWise. Free forever.</p>
        <a href="signup.php" class="btn-gold"><i class="fas fa-rocket"></i> Create Free Account</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
ensureCsrf();
$autoPageClass = 'page-' . str_replace('.php', '', basename($_SERVER['PHP_SELF'] ?? 'index.php'));
$bodyClass = !empty($pageClass) ? $pageClass . ' ' . $autoPageClass : $autoPageClass;
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="WanderWise - AI-powered travel planner for India. Plan trips, find buddies, track budgets.">
  <title><?= e($pageTitle ?? SITE_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/travel-theme.css">
  <?php if (!empty($extraHead)): echo $extraHead; endif; ?>
</head>
<body class="<?= e($bodyClass) ?>">

<nav class="navbar" id="main-navbar">
  <a href="<?= SITE_URL ?>/index.php" class="brand">
    <i class="fas fa-compass"></i>
    Wander<span>Wise</span>
  </a>

  <div class="nav-links" id="nav-menu">
    <?php if (isLoggedIn()): ?>
      <a href="<?= SITE_URL ?>/dashboard.php" <?= $currentPage==='dashboard.php'?'class="nav-active"':'' ?>><i class="fas fa-th-large" style="margin-right:4px;opacity:.6"></i>Dashboard</a>
      <a href="<?= SITE_URL ?>/plan-trip.php" <?= $currentPage==='plan-trip.php'?'class="nav-active"':'' ?>><i class="fas fa-route" style="margin-right:4px;opacity:.6"></i>Plan Trip</a>
      <a href="<?= SITE_URL ?>/chatbot.php" <?= $currentPage==='chatbot.php'?'class="nav-active"':'' ?>><i class="fas fa-robot" style="margin-right:4px;opacity:.6"></i>AI Chat</a>
      <a href="<?= SITE_URL ?>/travel-buddy.php" <?= $currentPage==='travel-buddy.php'?'class="nav-active"':'' ?>><i class="fas fa-user-group" style="margin-right:4px;opacity:.6"></i>Find Buddy</a>
      <a href="<?= SITE_URL ?>/storybook.php" <?= $currentPage==='storybook.php'?'class="nav-active"':'' ?>><i class="fas fa-book-open" style="margin-right:4px;opacity:.6"></i>Stories</a>
      <a href="<?= SITE_URL ?>/festivals.php" <?= $currentPage==='festivals.php'?'class="nav-active"':'' ?>><i class="fas fa-star-and-crescent" style="margin-right:4px;opacity:.6"></i>Festivals</a>
      <a href="<?= SITE_URL ?>/profile.php" class="btn-primary btn-sm"><i class="fas fa-user"></i>Profile</a>
      <a href="<?= SITE_URL ?>/logout.php" class="btn-outline btn-sm">Logout</a>
    <?php else: ?>
      <a href="<?= SITE_URL ?>/index.php" <?= $currentPage==='index.php'?'class="nav-active"':'' ?>>Home</a>
      <a href="<?= SITE_URL ?>/plan-trip.php" <?= $currentPage==='plan-trip.php'?'class="nav-active"':'' ?>><i class="fas fa-route" style="margin-right:4px;opacity:.6"></i>Plan Trip</a>
      <a href="<?= SITE_URL ?>/chatbot.php" <?= $currentPage==='chatbot.php'?'class="nav-active"':'' ?>><i class="fas fa-robot" style="margin-right:4px;opacity:.6"></i>AI Chat</a>
      <a href="<?= SITE_URL ?>/travel-buddy.php" <?= $currentPage==='travel-buddy.php'?'class="nav-active"':'' ?>><i class="fas fa-user-group" style="margin-right:4px;opacity:.6"></i>Find Buddy</a>
      <a href="<?= SITE_URL ?>/festivals.php" <?= $currentPage==='festivals.php'?'class="nav-active"':'' ?>><i class="fas fa-star-and-crescent" style="margin-right:4px;opacity:.6"></i>Festivals</a>
      <a href="<?= SITE_URL ?>/login.php" class="btn-outline btn-sm">Login</a>
      <a href="<?= SITE_URL ?>/signup.php" class="btn-primary btn-sm">Get Started Free</a>
    <?php endif; ?>
  </div>

  <button class="nav-hamburger" id="nav-toggle" aria-label="Menu">
    <i class="fas fa-bars"></i>
  </button>
</nav>

<!-- Login Prompt Modal (shown by showLoginModal()) -->
<div class="modal-overlay" id="login-modal" style="display:none">
  <div class="modal-box" style="position:relative">
    <button class="modal-close" onclick="document.getElementById('login-modal').style.display='none'" aria-label="Close">&times;</button>
    <div class="modal-icon"><i class="fas fa-compass"></i></div>
    <h3>Join WanderWise to Continue</h3>
    <p>Sign up free to plan trips, chat with AI, track budgets and save your travel memories.</p>
    <div class="modal-actions">
      <a href="<?= SITE_URL ?>/signup.php" class="btn-primary" style="justify-content:center"><i class="fas fa-user-plus"></i> Create Free Account</a>
      <a href="<?= SITE_URL ?>/login.php" class="btn-outline" style="justify-content:center"><i class="fas fa-sign-in-alt"></i> Log In</a>
    </div>
  </div>
</div>

<script>
document.getElementById('nav-toggle')?.addEventListener('click', function() {
  document.getElementById('nav-menu').classList.toggle('open');
});
window.showLoginModal = function() {
  document.getElementById('login-modal').style.display = 'flex';
};
document.getElementById('login-modal')?.addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
window.addEventListener('scroll', function() {
  document.getElementById('main-navbar')?.classList.toggle('scrolled', window.scrollY > 20);
});
</script>

<?php $flash = getFlash(); if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>" style="width:min(1200px,92vw);margin:14px auto 0">
    <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
    <?= e($flash['msg']) ?>
  </div>
<?php endif; ?>

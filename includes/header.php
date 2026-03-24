<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
ensureCsrf();
$autoPageClass = 'page-' . str_replace('.php', '', basename($_SERVER['PHP_SELF'] ?? 'index.php'));
$bodyClass = !empty($pageClass) ? $pageClass . ' ' . $autoPageClass : $autoPageClass;
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
      <a href="<?= SITE_URL ?>/dashboard.php"><i class="fas fa-th-large" style="margin-right:4px;opacity:.6"></i>Dashboard</a>
      <a href="<?= SITE_URL ?>/plan-trip.php"><i class="fas fa-route" style="margin-right:4px;opacity:.6"></i>Plan Trip</a>
      <a href="<?= SITE_URL ?>/chatbot.php"><i class="fas fa-robot" style="margin-right:4px;opacity:.6"></i>AI Chat</a>
      <a href="<?= SITE_URL ?>/travel-buddy.php"><i class="fas fa-user-group" style="margin-right:4px;opacity:.6"></i>Find Buddy</a>
      <a href="<?= SITE_URL ?>/festivals.php"><i class="fas fa-star-and-crescent" style="margin-right:4px;opacity:.6"></i>Festivals</a>
      <a href="<?= SITE_URL ?>/storybook.php"><i class="fas fa-book-open" style="margin-right:4px;opacity:.6"></i>Stories</a>
      <a href="<?= SITE_URL ?>/profile.php" class="btn-primary btn-sm"><i class="fas fa-user"></i>Profile</a>
      <a href="<?= SITE_URL ?>/logout.php" class="btn-outline btn-sm">Logout</a>
    <?php else: ?>
      <a href="<?= SITE_URL ?>/index.php">Home</a>
      <a href="<?= SITE_URL ?>/stories.php">Stories</a>
      <a href="<?= SITE_URL ?>/festivals.php">Festivals</a>
      <a href="<?= SITE_URL ?>/login.php" class="btn-outline btn-sm">Login</a>
      <a href="<?= SITE_URL ?>/signup.php" class="btn-primary btn-sm">Get Started Free</a>
    <?php endif; ?>
  </div>

  <button class="nav-hamburger" id="nav-toggle" aria-label="Menu">
    <i class="fas fa-bars"></i>
  </button>
</nav>

<?php $flash = getFlash(); if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>" style="width:min(1200px,92vw);margin:14px auto 0">
    <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
    <?= e($flash['msg']) ?>
  </div>
<?php endif; ?>

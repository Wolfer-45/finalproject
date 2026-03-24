<?php
http_response_code(404);
require_once 'config.php';require_once 'includes/db.php';require_once 'includes/auth.php';require_once 'includes/functions.php';
$pageTitle='404 - Page Not Found';require_once 'includes/header.php'; ?>
<div class="page-container section"><div class="card"><h1>404</h1><p>The page you requested does not exist.</p><a class="btn-primary" href="index.php">Go Home</a></div></div>
<?php require_once 'includes/footer.php'; ?>

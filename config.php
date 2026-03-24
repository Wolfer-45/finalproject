<?php
// Optional local secrets (never commit this file)
if (file_exists(__DIR__ . '/config.private.php')) {
  require_once __DIR__ . '/config.private.php';
}

// Site
if (!defined('SITE_NAME')) define('SITE_NAME', 'WanderWise');
// Dynamically resolve SITE_URL for Replit or local environments
if (!defined('SITE_URL')) {
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
  $scheme = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
  define('SITE_URL', $scheme . '://' . $host);
}

// Database
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_SOCK')) define('DB_SOCK', '/home/runner/mysql-run/mysql.sock');
if (!defined('DB_NAME')) define('DB_NAME', 'wanderwise_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// APIs
if (!defined('GEMINI_API_KEY')) define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
if (!defined('GEMINI_API_URL')) define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
if (!defined('WEATHER_API_KEY')) define('WEATHER_API_KEY', 'YOUR_OPENWEATHER_API_KEY_HERE');
if (!defined('WEATHER_API_URL')) define('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5/forecast');

// Mail
if (!defined('MAIL_FROM')) define('MAIL_FROM', 'noreply@wanderwise.com');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'WanderWise');

// Upload
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/uploads/storybook/');
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', SITE_URL . '/uploads/storybook/');
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024);

session_start();
date_default_timezone_set('Asia/Kolkata');
?>

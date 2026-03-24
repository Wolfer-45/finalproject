<?php
function e(?string $value): string {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function clean(?string $value): string {
  return trim(strip_tags((string)$value));
}

function setFlash(string $type, string $message): void {
  $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

function getFlash(): array {
  $flash = $_SESSION['flash'] ?? [];
  unset($_SESSION['flash']);
  return $flash;
}

function ensureCsrf(): void {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
}

function verifyCsrf(): void {
  if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    die('Invalid request.');
  }
}

function aiRateLimitExceeded(int $limit = 10): bool {
  if (!isset($_SESSION['user_id'])) {
    return true;
  }
  $cacheKey = 'gemini_calls_' . $_SESSION['user_id'] . '_' . date('YmdH');
  $calls = (int)($_SESSION[$cacheKey] ?? 0);
  if ($calls >= $limit) {
    return true;
  }
  $_SESSION[$cacheKey] = $calls + 1;
  return false;
}

function sendMailSimple(string $to, string $subject, string $message): bool {
  $headers = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type:text/plain;charset=UTF-8' . "\r\n";
  $headers .= 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>' . "\r\n";
  return @mail($to, $subject, $message, $headers);
}

function randomOtp(): string {
  return (string)random_int(100000, 999999);
}
?>

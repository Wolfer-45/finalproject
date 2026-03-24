<?php
require_once __DIR__ . '/db.php';

function isLoggedIn(): bool {
  return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
  if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
  }
}

function getCurrentUser(): ?array {
  if (!isLoggedIn()) {
    return null;
  }
  $db = getDB();
  $stmt = $db->prepare('SELECT id, name, email, phone, avatar, age, gender, language, travel_style, travel_types, emergency_name, emergency_phone, emergency_rel FROM users WHERE id = ?');
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();
  return $user ?: null;
}
?>

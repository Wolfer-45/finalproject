<?php
require_once __DIR__ . '/../config.php';

function getDB(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    try {
      $dsn = defined('DB_SOCK') && DB_SOCK
        ? 'mysql:unix_socket=' . DB_SOCK . ';dbname=' . DB_NAME . ';charset=utf8mb4'
        : 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
      $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]
      );
    } catch (PDOException $e) {
      die('Database connection failed. Please try again later.');
    }
  }
  return $pdo;
}
?>

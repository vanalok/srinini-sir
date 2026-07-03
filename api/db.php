<?php
/* ============================================================
   Database connection + helpers for engagement / analytics API
   ------------------------------------------------------------
   GODADDY SETUP:
   1. cPanel → MySQL Databases → create a database + user, add
      the user to the database with ALL PRIVILEGES.
   2. Put those credentials below (DB_NAME/USER/PASS).
   3. Import api/schema.sql via phpMyAdmin.
   ============================================================ */

const DB_HOST = '127.0.0.1';
const DB_NAME = 'srinivas_cms';   // <-- GoDaddy DB name
const DB_USER = 'root';           // <-- GoDaddy DB user
const DB_PASS = '';               // <-- GoDaddy DB password

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $pdo = new PDO(
      'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
      DB_USER, DB_PASS,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
  }
  return $pdo;
}

/* Stable per-browser visitor id (1-year cookie), hashed for storage. */
function visitor_id(): string {
  if (empty($_COOKIE['sr_vid'])) {
    $v = bin2hex(random_bytes(16));
    setcookie('sr_vid', $v, [
      'expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax',
    ]);
    $_COOKIE['sr_vid'] = $v;
  }
  return sha1('sr|' . $_COOKIE['sr_vid']);
}

function json_out($data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

/* Same-origin only for writes (light CSRF guard for public API). */
function require_same_origin(): void {
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
  $host   = $_SERVER['HTTP_HOST'] ?? '';
  if ($origin && parse_url($origin, PHP_URL_HOST) !== $host) {
    json_out(['error' => 'bad origin'], 403);
  }
}

/* Counts for a slug (+ whether this visitor liked it). */
function engagement_stats(string $slug, string $vid): array {
  $pdo = db();
  $e = $pdo->prepare('SELECT views, likes FROM engagement WHERE slug = ?');
  $e->execute([$slug]); $row = $e->fetch() ?: ['views' => 0, 'likes' => 0];
  $c = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE slug = ? AND status = 'approved'");
  $c->execute([$slug]);
  $l = $pdo->prepare('SELECT 1 FROM likes_by_visitor WHERE slug = ? AND visitor = ?');
  $l->execute([$slug, $vid]);
  return [
    'views'    => (int)$row['views'],
    'likes'    => (int)$row['likes'],
    'comments' => (int)$c->fetchColumn(),
    'liked'    => (bool)$l->fetchColumn(),
  ];
}

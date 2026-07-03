<?php
/* GET  ?slug=X  -> approved comments for a post
   POST slug,name,body (+ honeypot 'website') -> submit a comment (pending) */
require_once __DIR__ . '/db.php';

try {
  $pdo = db();

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $slug = trim($_GET['slug'] ?? '');
    if ($slug === '') json_out([]);
    $q = $pdo->prepare("SELECT name, body, created_at FROM comments WHERE slug = ? AND status = 'approved' ORDER BY created_at DESC LIMIT 200");
    $q->execute([$slug]);
    json_out(['comments' => $q->fetchAll()]);
  }

  // POST — submit
  require_same_origin();
  $slug = trim($_POST['slug'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $body = trim($_POST['body'] ?? '');
  $trap = trim($_POST['website'] ?? '');           // honeypot — bots fill this
  if ($trap !== '') json_out(['ok' => true]);       // silently drop bots
  if ($slug === '' || mb_strlen($name) < 2 || mb_strlen($name) > 120
      || mb_strlen($body) < 2 || mb_strlen($body) > 2000) {
    json_out(['error' => 'Please enter your name and a comment.'], 400);
  }
  $vid = visitor_id();
  // rate limit: max 5 comments / visitor / hour
  $rl = $pdo->prepare('SELECT COUNT(*) FROM comments WHERE visitor = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
  $rl->execute([$vid]);
  if ((int)$rl->fetchColumn() >= 5) json_out(['error' => 'Too many comments — please try later.'], 429);

  $pdo->prepare('INSERT INTO comments (slug, name, body, visitor, status) VALUES (?,?,?,?,\'pending\')')
      ->execute([$slug, $name, $body, $vid]);
  json_out(['ok' => true, 'message' => 'Thanks! Your comment will appear after review.']);
} catch (Throwable $e) {
  json_out(['error' => 'server'], 500);
}

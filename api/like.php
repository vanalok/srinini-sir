<?php
/* Toggle a like for the current visitor on a slug. Returns {likes, liked}. */
require_once __DIR__ . '/db.php';
require_same_origin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);

try {
  $pdo  = db();
  $slug = trim($_REQUEST['slug'] ?? '');
  if ($slug === '') json_out(['error' => 'slug required'], 400);
  $vid = visitor_id();

  $has = $pdo->prepare('SELECT 1 FROM likes_by_visitor WHERE slug = ? AND visitor = ?');
  $has->execute([$slug, $vid]);

  if ($has->fetchColumn()) {
    $pdo->prepare('DELETE FROM likes_by_visitor WHERE slug = ? AND visitor = ?')->execute([$slug, $vid]);
    $pdo->prepare('UPDATE engagement SET likes = GREATEST(0, likes - 1) WHERE slug = ?')->execute([$slug]);
    $liked = false;
  } else {
    $pdo->prepare('INSERT INTO likes_by_visitor (slug, visitor) VALUES (?, ?)')->execute([$slug, $vid]);
    $pdo->prepare('INSERT INTO engagement (slug, likes) VALUES (?, 1) ON DUPLICATE KEY UPDATE likes = likes + 1')->execute([$slug]);
    $liked = true;
  }

  $e = $pdo->prepare('SELECT likes FROM engagement WHERE slug = ?');
  $e->execute([$slug]);
  json_out(['likes' => (int)$e->fetchColumn(), 'liked' => $liked]);
} catch (Throwable $e) {
  json_out(['error' => 'server'], 500);
}

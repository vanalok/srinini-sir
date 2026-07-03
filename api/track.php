<?php
/* Records a visit (analytics) and, for post pages, a de-duplicated view.
   Returns current engagement stats for the slug. */
require_once __DIR__ . '/db.php';
require_same_origin();

try {
  $pdo  = db();
  $slug = trim($_REQUEST['slug'] ?? '');
  $path = substr(trim($_REQUEST['path'] ?? ($_SERVER['HTTP_REFERER'] ?? '/')), 0, 255);
  $vid  = visitor_id();
  $ref  = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255) ?: null;
  $ua   = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255) ?: null;

  // A view counts once per visitor+path per 6h window
  $isNewView = true;
  if ($slug !== '') {
    $q = $pdo->prepare('SELECT 1 FROM visits WHERE visitor = ? AND path = ? AND created_at > (NOW() - INTERVAL 6 HOUR) LIMIT 1');
    $q->execute([$vid, $path]);
    $isNewView = !$q->fetchColumn();
  }

  $pdo->prepare('INSERT INTO visits (path, visitor, referrer, ua) VALUES (?,?,?,?)')
      ->execute([$path, $vid, $ref, $ua]);

  if ($slug !== '' && $isNewView) {
    $pdo->prepare('INSERT INTO engagement (slug, views) VALUES (?, 1) ON DUPLICATE KEY UPDATE views = views + 1')
        ->execute([$slug]);
  }

  json_out($slug !== '' ? engagement_stats($slug, $vid) : ['ok' => true]);
} catch (Throwable $e) {
  json_out(['error' => 'server'], 500);
}

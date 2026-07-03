<?php
require_once __DIR__ . '/lib.php';
require_login();

$pdo = null; $dbErr = '';
try { require_once __DIR__ . '/../api/db.php'; $pdo = db(); }
catch (Throwable $e) { $dbErr = 'Database not connected. Set credentials in api/db.php and import api/schema.sql first.'; }

$SEED = SITE_ROOT . '/api/seed-data.json';
$done = '';
if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $data = read_json($SEED);
  $ins = $pdo->prepare('INSERT INTO engagement (slug, views, likes) VALUES (?,?,?)
    ON DUPLICATE KEY UPDATE views = GREATEST(views, VALUES(views)), likes = GREATEST(likes, VALUES(likes))');
  $ne = 0;
  foreach ($data['engagement'] ?? [] as $e) { $ins->execute([$e['slug'], (int)$e['views'], (int)$e['likes']]); $ne++; }
  $nc = 0;
  $ci = $pdo->prepare("INSERT INTO comments (slug, name, body, status, created_at) VALUES (?,?,?, 'approved', ?)");
  foreach ($data['comments'] ?? [] as $c) { $ci->execute([$c['slug'], $c['name'], $c['body'], $c['created_at'] ?: date('Y-m-d H:i:s')]); $nc++; }
  flash("Imported $ne posts (views/likes)" . ($nc ? " and $nc comments" : '') . '.');
  header('Location: analytics.php'); exit;
}

$seed = is_file($SEED) ? read_json($SEED) : [];
$curViews = $pdo ? (int)$pdo->query('SELECT COALESCE(SUM(views),0) FROM engagement')->fetchColumn() : 0;

admin_head('Import Wix stats');
echo render_flash();
?>
<a class="a-back" href="analytics.php">← Analytics</a>
<h1 class="a-h1">Import historical stats</h1>
<?php if (!$pdo): ?><div class="flash flash-err"><?= h($dbErr) ?></div><?php admin_foot(); exit; endif; ?>
<section class="a-panel" style="max-width:560px">
  <p>This imports your <strong>Wix view &amp; like counts</strong> (from the backup) as the starting baseline, so numbers don't restart from zero.</p>
  <ul>
    <li>Posts in seed file: <strong><?= count($seed['engagement'] ?? []) ?></strong></li>
    <li>Total views to import: <strong><?= number_format(array_sum(array_map(fn($e)=>$e['views'], $seed['engagement'] ?? []))) ?></strong></li>
    <li>Current views in database: <strong><?= number_format($curViews) ?></strong></li>
  </ul>
  <p class="a-hint">Safe to run once. Re-running keeps the higher of existing vs seed values (won't reduce live counts).</p>
  <form method="post"><?= csrf_field() ?>
    <button class="a-btn a-btn-primary" type="submit">Import now</button>
  </form>
</section>
<?php admin_foot(); ?>

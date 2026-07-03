<?php
require_once __DIR__ . '/lib.php';
require_login();

$pdo = null; $dbErr = '';
try { require_once __DIR__ . '/../api/db.php'; $pdo = db(); }
catch (Throwable $e) { $dbErr = 'Database not connected. Set your credentials in api/db.php and import api/schema.sql.'; }

admin_head('Analytics');
echo render_flash();
echo '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">'
   . '<h1 class="a-h1" style="margin:0">📈 Analytics</h1>'
   . '<a class="a-btn a-btn-sm" href="seed.php">⇪ Import Wix stats</a></div>';

if (!$pdo) { echo '<div class="flash flash-err">' . h($dbErr) . '</div>'; admin_foot(); exit; }

/* ---- Metrics ---- */
$totVisits   = (int)$pdo->query('SELECT COUNT(*) FROM visits')->fetchColumn();
$uniqVis     = (int)$pdo->query('SELECT COUNT(DISTINCT visitor) FROM visits')->fetchColumn();
$totViews    = (int)$pdo->query('SELECT COALESCE(SUM(views),0) FROM engagement')->fetchColumn();
$totLikes    = (int)$pdo->query('SELECT COALESCE(SUM(likes),0) FROM engagement')->fetchColumn();
$pendComm    = (int)$pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
$vis30       = (int)$pdo->query('SELECT COUNT(DISTINCT visitor) FROM visits WHERE created_at > (NOW() - INTERVAL 30 DAY)')->fetchColumn();

/* ---- 14-day trend ---- */
$rows = $pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM visits WHERE created_at > (NOW() - INTERVAL 14 DAY) GROUP BY DATE(created_at)")->fetchAll();
$byDay = []; foreach ($rows as $r) $byDay[$r['d']] = (int)$r['c'];
$days = []; for ($i=13; $i>=0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $days[$d] = $byDay[$d] ?? 0; }
$maxDay = max(1, max($days));

/* ---- Top posts ---- */
$top = $pdo->query('SELECT slug, views, likes FROM engagement ORDER BY views DESC LIMIT 10')->fetchAll();
/* ---- Recent visits ---- */
$recent = $pdo->query('SELECT path, referrer, created_at FROM visits ORDER BY id DESC LIMIT 15')->fetchAll();

$tile = fn($n,$l) => '<div class="a-tile"><b>'.number_format($n).'</b><span>'.h($l).'</span></div>';
?>
<div class="a-tiles">
  <?= $tile($totViews,'Post views') ?>
  <?= $tile($uniqVis,'Unique visitors') ?>
  <?= $tile($totVisits,'Page visits') ?>
  <?= $tile($totLikes,'Likes') ?>
  <?= $tile($vis30,'Visitors · 30 days') ?>
  <?= $tile($pendComm,'Comments to review') ?>
</div>

<section class="a-panel">
  <h2>Visits — last 14 days</h2>
  <div class="a-chart">
    <?php foreach ($days as $d => $c): ?>
      <div class="a-bar" title="<?= h($d) ?>: <?= $c ?>">
        <span style="height:<?= (int)round($c / $maxDay * 100) ?>%"></span>
        <em><?= (int)date('j', strtotime($d)) ?></em>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<div class="a-two">
  <section class="a-panel">
    <h2>Top posts</h2>
    <div class="a-list">
      <?php foreach ($top as $t): ?>
        <div class="a-row"><div class="a-row-main">
          <div class="a-row-title"><a href="../posts/<?= h($t['slug']) ?>.html" target="_blank"><?= h($t['slug']) ?></a></div>
          <div class="a-row-meta"><?= (int)$t['views'] ?> views · <?= (int)$t['likes'] ?> likes</div>
        </div></div>
      <?php endforeach; ?>
      <?php if (!$top): ?><p class="a-empty">No views recorded yet.</p><?php endif; ?>
    </div>
  </section>
  <section class="a-panel">
    <h2>Recent visits</h2>
    <div class="a-list">
      <?php foreach ($recent as $r): ?>
        <div class="a-row"><div class="a-row-main">
          <div class="a-row-title" style="font-size:14px"><?= h($r['path']) ?></div>
          <div class="a-row-meta"><?= h(date('j M, H:i', strtotime($r['created_at']))) ?><?= $r['referrer'] ? ' · from ' . h(parse_url($r['referrer'], PHP_URL_HOST) ?: $r['referrer']) : '' ?></div>
        </div></div>
      <?php endforeach; ?>
      <?php if (!$recent): ?><p class="a-empty">No visits yet.</p><?php endif; ?>
    </div>
  </section>
</div>
<?php admin_foot(); ?>

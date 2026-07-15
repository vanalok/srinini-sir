<?php
require_once __DIR__ . '/lib.php';
require_login();

$pdo = null; $dbErr = '';
try { require_once __DIR__ . '/../api/db.php'; $pdo = db(); }
catch (Throwable $e) { $dbErr = 'Database not connected. Set your credentials in api/db.php and import api/schema.sql.'; }

admin_head('Analytics');
echo render_flash();

if (!$pdo) {
  echo '<h1 class="a-h1">📈 Analytics</h1><div class="flash flash-err">' . h($dbErr) . '</div>';
  admin_foot(); exit;
}

/* ---- Metrics ---- */
$totVisits = (int)$pdo->query('SELECT COUNT(*) FROM visits')->fetchColumn();
$uniqVis   = (int)$pdo->query('SELECT COUNT(DISTINCT visitor) FROM visits')->fetchColumn();
$totViews  = (int)$pdo->query('SELECT COALESCE(SUM(views),0) FROM engagement')->fetchColumn();
$totLikes  = (int)$pdo->query('SELECT COALESCE(SUM(likes),0) FROM engagement')->fetchColumn();
$pendComm  = (int)$pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
$vis30     = (int)$pdo->query('SELECT COUNT(DISTINCT visitor) FROM visits WHERE created_at > (NOW() - INTERVAL 30 DAY)')->fetchColumn();

/* ---- 14-day trend ---- */
$rows = $pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM visits WHERE created_at > (NOW() - INTERVAL 14 DAY) GROUP BY DATE(created_at)")->fetchAll();
$byDay = []; foreach ($rows as $r) $byDay[$r['d']] = (int)$r['c'];
$days = []; for ($i=13; $i>=0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $days[$d] = $byDay[$d] ?? 0; }
$maxDay  = max(1, max($days));
$total14 = array_sum($days);
// round the axis up to a "nice" ceiling
$mag = pow(10, max(0, floor(log10($maxDay))));
$niceMax = max(1, (int)(ceil($maxDay / $mag) * $mag));

/* ---- Top posts / Recent visits ---- */
$top = $pdo->query('SELECT slug, views, likes FROM engagement ORDER BY views DESC LIMIT 8')->fetchAll();
$maxViews = 1; foreach ($top as $t) $maxViews = max($maxViews, (int)$t['views']);
$recent = $pdo->query('SELECT path, referrer, created_at FROM visits ORDER BY id DESC LIMIT 12')->fetchAll();

/* ---- inline icons ---- */
$svg = [
  'eye'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
  'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  'activity'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
  'heart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"/></svg>',
  'cal'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
  'msg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
  'file'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
  'globe' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
];
function kpi($ico, $val, $lbl, $accent = false, $badge = '') {
  global $svg;
  $cls = 'an-kpi' . ($accent ? ' is-gold' : '');
  $b = $badge !== '' ? '<span class="an-kpi-badge warn">' . h($badge) . '</span>' : '';
  return '<div class="' . $cls . '">' . $b
    . '<div class="an-kpi-ico">' . $svg[$ico] . '</div>'
    . '<div class="an-kpi-val">' . number_format($val) . '</div>'
    . '<div class="an-kpi-lbl">' . h($lbl) . '</div></div>';
}
?>
<style>
/* ===== Advanced Analytics ===== */
.an-head{ display:flex; align-items:flex-end; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:24px; }
.an-head h1{ font-size:26px; margin:0 0 4px; display:flex; align-items:center; gap:10px; }
.an-head .an-sub{ color:var(--ink-2); font-size:13.5px; margin:0; }

.an-kpis{ display:grid; grid-template-columns:repeat(auto-fit,minmax(188px,1fr)); gap:16px; margin-bottom:24px; }
.an-kpi{ position:relative; background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:20px; overflow:hidden; transition:transform .16s, box-shadow .16s, border-color .16s; }
.an-kpi::after{ content:''; position:absolute; top:0; left:0; bottom:0; width:3px; background:linear-gradient(180deg,var(--gold),var(--gold-d)); opacity:0; transition:opacity .16s; }
.an-kpi:hover{ transform:translateY(-3px); box-shadow:0 16px 34px rgba(14,46,31,.1); border-color:rgba(201,168,76,.55); }
.an-kpi:hover::after{ opacity:1; }
.an-kpi-ico{ width:40px; height:40px; border-radius:11px; display:grid; place-items:center; background:linear-gradient(135deg,rgba(20,83,45,.13),rgba(20,83,45,.04)); color:var(--green-2); margin-bottom:15px; }
.an-kpi-ico svg{ width:20px; height:20px; }
.an-kpi.is-gold .an-kpi-ico{ background:linear-gradient(135deg,rgba(201,168,76,.24),rgba(201,168,76,.07)); color:var(--gold-d); }
.an-kpi-val{ font-family:Georgia,'Times New Roman',serif; font-size:33px; font-weight:700; color:var(--green); line-height:1; letter-spacing:-.01em; }
.an-kpi-lbl{ font-size:12.5px; color:var(--ink-2); margin-top:7px; letter-spacing:.02em; }
.an-kpi-badge{ position:absolute; top:16px; right:16px; font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:999px; letter-spacing:.04em; }
.an-kpi-badge.warn{ background:rgba(201,168,76,.16); color:var(--gold-d); }

/* chart card */
.an-card{ background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:22px 24px; margin-bottom:22px; }
.an-card-top{ display:flex; align-items:baseline; justify-content:space-between; gap:12px; margin-bottom:22px; flex-wrap:wrap; }
.an-card-top h2{ font-size:16px; margin:0; }
.an-card-top .an-tot{ font-size:13px; color:var(--ink-2); }
.an-card-top .an-tot b{ color:var(--green-2); font-size:15px; font-family:Georgia,serif; }

.an-plot{ display:flex; gap:12px; }
.an-yaxis{ width:30px; height:172px; display:flex; flex-direction:column; justify-content:space-between; }
.an-yaxis span{ font-size:10px; color:var(--ink-2); text-align:right; line-height:1; }
.an-plot-main{ flex:1; min-width:0; }
.an-bars-wrap{ position:relative; height:172px; }
.an-grid{ position:absolute; inset:0; display:flex; flex-direction:column; justify-content:space-between; z-index:0; }
.an-grid i{ display:block; height:1px; background:var(--line); }
.an-bars{ position:absolute; inset:0; display:flex; align-items:flex-end; gap:5px; z-index:1; }
.an-bar{ flex:1; display:flex; justify-content:center; align-items:flex-end; height:100%; }
.an-bar-fill{ position:relative; width:100%; max-width:30px; min-height:3px; background:linear-gradient(180deg,#dcc06a,var(--gold-d)); border-radius:5px 5px 0 0; transition:filter .15s; }
.an-bar-fill:hover{ filter:brightness(1.08) saturate(1.1); }
.an-bar-tip{ position:absolute; bottom:calc(100% + 9px); left:50%; transform:translateX(-50%) translateY(4px); background:var(--green); color:#fff; font-size:11px; font-weight:500; padding:6px 10px; border-radius:8px; white-space:nowrap; opacity:0; pointer-events:none; transition:opacity .14s, transform .14s; z-index:6; box-shadow:0 8px 20px rgba(0,0,0,.22); }
.an-bar-tip b{ color:var(--gold-light,#e8cc7a); }
.an-bar-tip::after{ content:''; position:absolute; top:100%; left:50%; transform:translateX(-50%); border:5px solid transparent; border-top-color:var(--green); }
.an-bar-fill:hover .an-bar-tip{ opacity:1; transform:translateX(-50%) translateY(0); }
.an-xaxis{ display:flex; gap:5px; margin-top:8px; }
.an-xaxis span{ flex:1; text-align:center; font-size:10px; color:var(--ink-2); }

/* two-column */
.an-two{ display:grid; grid-template-columns:1fr 1fr; gap:22px; }
@media (max-width:820px){ .an-two{ grid-template-columns:1fr; } }
.an-card h2.an-h2{ font-size:16px; margin:0 0 18px; }

/* top posts */
.an-top{ display:flex; flex-direction:column; gap:16px; }
.an-toprow{ display:flex; align-items:center; gap:14px; }
.an-rank{ flex-shrink:0; width:26px; height:26px; border-radius:8px; display:grid; place-items:center; font-size:12px; font-weight:700; background:var(--paper); color:var(--ink-2); border:1px solid var(--line); }
.an-toprow:nth-child(1) .an-rank{ background:linear-gradient(135deg,var(--gold),var(--gold-d)); color:#fff; border-color:transparent; }
.an-toprow:nth-child(2) .an-rank{ background:rgba(201,168,76,.2); color:var(--gold-d); border-color:transparent; }
.an-toprow:nth-child(3) .an-rank{ background:rgba(201,168,76,.12); color:var(--gold-d); border-color:transparent; }
.an-topmain{ flex:1; min-width:0; }
.an-toptitle{ display:block; font-size:13.5px; font-weight:600; color:var(--ink); text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.an-toptitle:hover{ color:var(--green-2); }
.an-topbar{ height:6px; border-radius:4px; background:var(--paper); margin-top:7px; overflow:hidden; }
.an-topbar i{ display:block; height:100%; border-radius:4px; background:linear-gradient(90deg,#dcc06a,var(--gold-d)); }
.an-topnums{ flex-shrink:0; text-align:right; font-size:12px; color:var(--ink-2); line-height:1.35; }
.an-topnums b{ display:block; font-size:15px; color:var(--green); font-family:Georgia,serif; }

/* recent visits */
.an-visits{ display:flex; flex-direction:column; }
.an-vrow{ display:flex; align-items:center; gap:12px; padding:11px 2px; border-top:1px solid var(--line); }
.an-vrow:first-child{ border-top:0; }
.an-vico{ flex-shrink:0; width:30px; height:30px; border-radius:9px; display:grid; place-items:center; background:var(--paper); color:var(--green-2); }
.an-vico svg{ width:15px; height:15px; }
.an-vmain{ flex:1; min-width:0; }
.an-vpath{ font-size:13px; font-weight:600; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.an-vmeta{ font-size:11.5px; color:var(--ink-2); margin-top:2px; }
.an-vmeta .an-src{ color:var(--green-2); }
.an-empty{ color:var(--ink-2); font-size:13px; padding:8px 0; }

@media (max-width:560px){
  .an-kpi-val{ font-size:28px; }
  .an-xaxis span:nth-child(even){ opacity:0; }
}
</style>

<div class="an-head">
  <div>
    <h1>📈 Analytics</h1>
    <p class="an-sub">Visitor insights &amp; content performance · updated live</p>
  </div>
  <a class="a-btn a-btn-sm" href="seed.php">⇪ Import Wix stats</a>
</div>

<div class="an-kpis">
  <?= kpi('eye',      $totViews, 'Post views',        true) ?>
  <?= kpi('users',    $uniqVis,  'Unique visitors') ?>
  <?= kpi('activity', $totVisits,'Page visits') ?>
  <?= kpi('heart',    $totLikes, 'Total likes') ?>
  <?= kpi('cal',      $vis30,    'Visitors · 30 days') ?>
  <?= kpi('msg',      $pendComm, 'Comments to review', false, $pendComm > 0 ? 'NEW' : '') ?>
</div>

<section class="an-card">
  <div class="an-card-top">
    <h2>Visits — last 14 days</h2>
    <span class="an-tot"><b><?= number_format($total14) ?></b> visits · peak <?= number_format($maxDay) ?>/day</span>
  </div>
  <div class="an-plot">
    <div class="an-yaxis">
      <span><?= number_format($niceMax) ?></span>
      <span><?= number_format((int)round($niceMax/2)) ?></span>
      <span>0</span>
    </div>
    <div class="an-plot-main">
      <div class="an-bars-wrap">
        <div class="an-grid"><i></i><i></i><i></i></div>
        <div class="an-bars">
          <?php foreach ($days as $d => $c): $ph = (int)round($c / $niceMax * 100); ?>
            <div class="an-bar">
              <div class="an-bar-fill" style="height:<?= max($c > 0 ? 4 : 1, $ph) ?>%">
                <span class="an-bar-tip"><b><?= number_format($c) ?></b> visit<?= $c==1?'':'s' ?> · <?= h(date('j M', strtotime($d))) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="an-xaxis">
        <?php foreach ($days as $d => $c): ?><span><?= (int)date('j', strtotime($d)) ?></span><?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<div class="an-two">
  <section class="an-card">
    <h2 class="an-h2">🏆 Top posts</h2>
    <div class="an-top">
      <?php foreach ($top as $n => $t): $bw = (int)round($t['views'] / $maxViews * 100); ?>
        <div class="an-toprow">
          <span class="an-rank"><?= $n + 1 ?></span>
          <div class="an-topmain">
            <a class="an-toptitle" href="../posts/<?= h($t['slug']) ?>.html" target="_blank"><?= h($t['slug']) ?></a>
            <div class="an-topbar"><i style="width:<?= max(3,$bw) ?>%"></i></div>
          </div>
          <div class="an-topnums"><b><?= number_format((int)$t['views']) ?></b><?= (int)$t['likes'] ?> likes</div>
        </div>
      <?php endforeach; ?>
      <?php if (!$top): ?><p class="an-empty">No views recorded yet.</p><?php endif; ?>
    </div>
  </section>

  <section class="an-card">
    <h2 class="an-h2">🕐 Recent visits</h2>
    <div class="an-visits">
      <?php foreach ($recent as $r): $host = $r['referrer'] ? (parse_url($r['referrer'], PHP_URL_HOST) ?: '') : ''; ?>
        <div class="an-vrow">
          <span class="an-vico"><?= $svg['globe'] ?></span>
          <div class="an-vmain">
            <div class="an-vpath"><?= h($r['path']) ?></div>
            <div class="an-vmeta"><?= h(date('j M, H:i', strtotime($r['created_at']))) ?><?= $host ? ' · <span class="an-src">' . h($host) . '</span>' : ' · direct' ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$recent): ?><p class="an-empty">No visits yet.</p><?php endif; ?>
    </div>
  </section>
</div>
<?php admin_foot(); ?>

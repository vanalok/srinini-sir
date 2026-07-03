<?php
require_once __DIR__ . '/lib.php';
require_login();

function count_json($file, $key = null) {
  $d = read_json(SITE_ROOT . '/' . $file);
  if ($key) $d = $d[$key] ?? $d;
  if (isset($d['departments'])) { $n = 0; foreach ($d['departments'] as $arr) $n += count($arr); return $n; }
  return is_array($d) ? count($d) : 0;
}
function count_photos() {
  $f = SITE_ROOT . '/photos.js';
  return is_file($f) ? preg_match_all("/category:\s*'/", file_get_contents($f)) : 0;
}

$cards = [
  ['videos.php',         'Videos',          '🎬', count_json('videos.json')],
  ['audios.php',         'Audios',          '🎧', count_json('audios.json')],
  ['honours.php',        'Honours',         '🏆', count_json('honours.json')],
  ['publications.php',   'Publications',    '📄', count_json('publications.json')],
  ['accomplishments.php','Accomplishments', '🏅', count_json('accomplishments.json')],
  ['photos.php',         'Photos',          '🖼️', count_photos()],
  ['blog.php',           'Blog posts',      '📝', count_json('assets/posts-full.json')],
];
$total = array_sum(array_map(fn($c) => $c[3], $cards));

// engagement cards (DB-backed; show a label instead of a count)
$extra = [
  ['analytics.php', 'Analytics', '📈', 'Visitors & views'],
  ['comments.php',  'Comments',  '💬', 'Moderate comments'],
];

admin_head('Dashboard');
echo render_flash();
?>
<div class="a-welcome">
  <div>
    <h1>Welcome back 👋</h1>
    <p>Manage your website content — changes save straight to the live site's data.</p>
  </div>
  <div class="a-total"><b><?= (int)$total ?></b><span>content items</span></div>
</div>

<div class="a-grid">
  <?php foreach ($cards as [$href, $label, $icon, $count]): ?>
    <a class="a-card" href="<?= h($href) ?>">
      <span class="a-card-cta">Manage →</span>
      <span class="a-card-ico"><?= $icon ?></span>
      <span class="a-card-name"><?= h($label) ?></span>
      <span class="a-card-count"><?= (int)$count ?> item<?= $count == 1 ? '' : 's' ?></span>
    </a>
  <?php endforeach; ?>
  <?php foreach ($extra as [$href, $label, $icon, $desc]): ?>
    <a class="a-card" href="<?= h($href) ?>">
      <span class="a-card-cta">Open →</span>
      <span class="a-card-ico"><?= $icon ?></span>
      <span class="a-card-name"><?= h($label) ?></span>
      <span class="a-card-count"><?= h($desc) ?></span>
    </a>
  <?php endforeach; ?>
</div>
<?php admin_foot(); ?>

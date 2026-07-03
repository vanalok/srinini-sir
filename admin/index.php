<?php
require_once __DIR__ . '/lib.php';
require_login();

// counts from the JSON files
function count_json($file, $key = null) {
  $d = read_json(SITE_ROOT . '/' . $file);
  if ($key) $d = $d[$key] ?? $d;
  if (isset($d['departments'])) { // accomplishments/publications shape
    $n = 0; foreach ($d['departments'] as $arr) $n += count($arr); return $n;
  }
  return is_array($d) ? count($d) : 0;
}

$cards = [
  ['videos.php',        'Videos',         '🎬', count_json('videos.json'),          true],
  ['audios.php',        'Audios',         '🎧', count_json('audios.json'),          true],
  ['honours.php',       'Honours',        '🏆', count_json('honours.json'),         true],
  ['publications.php',  'Publications',   '📄', count_json('publications.json'),    false],
  ['accomplishments.php','Accomplishments','🏅', count_json('accomplishments.json'), false],
  ['photos.php',        'Photos',         '🖼️', null,                                false],
  ['blog.php',          'Blog posts',     '📝', null,                                false],
];

admin_head('Dashboard');
echo render_flash();
?>
<h1 class="a-h1">Dashboard</h1>
<p class="a-sub">Manage your website content. Changes save straight to the site's data files.</p>

<div class="a-grid">
  <?php foreach ($cards as [$href, $label, $icon, $count, $ready]): ?>
    <?php if ($ready): ?>
      <a class="a-card" href="<?= h($href) ?>">
        <span class="a-card-ico"><?= $icon ?></span>
        <span class="a-card-name"><?= h($label) ?></span>
        <?php if ($count !== null): ?><span class="a-card-count"><?= (int)$count ?> items</span><?php endif; ?>
      </a>
    <?php else: ?>
      <div class="a-card a-card-soon">
        <span class="a-card-ico"><?= $icon ?></span>
        <span class="a-card-name"><?= h($label) ?></span>
        <span class="a-card-count"><?= $count !== null ? (int)$count . ' items · ' : '' ?>coming next</span>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
<?php admin_foot(); ?>

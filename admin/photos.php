<?php
require_once __DIR__ . '/lib.php';
require_login();

$FILE = SITE_ROOT . '/photos.js';
$PHOTO_CATS = [
  'ayush-photos'     => 'AYUSH',
  'kspcb-photos'     => 'KSPCB',
  'academics'        => 'Academics',
  'shimoga'          => 'Shimoga',
  'honours'          => 'Honours',
  'adcl'             => 'ADCL',
  'forestdeptphotos' => 'Forest Department',
  'kali'             => 'Kali Tiger Reserve',
  'chitradurga'      => 'Chitradurga',
  'family'           => 'Family',
];

/* ---- photos.js parse / serialize ---- */
function read_photos(string $path): array {
  if (!is_file($path)) return [];
  $raw = file_get_contents($path);
  preg_match_all("/\{\s*category:\s*'([^']*)',\s*src:\s*'([^']*)',\s*alt:\s*'([^']*)'\s*\}/", $raw, $m, PREG_SET_ORDER);
  $out = [];
  foreach ($m as $x) $out[] = ['category' => $x[1], 'src' => $x[2], 'alt' => $x[3]];
  return $out;
}
function write_photos(string $path, array $arr): bool {
  $esc = fn($s) => str_replace(["\\", "'"], ["\\\\", "\\'"], $s);
  $lines = array_map(fn($p) => "{ category: '" . $esc($p['category']) . "', src: '" . $esc($p['src']) . "', alt: '" . $esc($p['alt']) . "' }", $arr);
  $js = "const photos = [\n" . implode(",\n", $lines) . "\n];\n";
  $tmp = $path . '.tmp';
  if (file_put_contents($tmp, $js) === false) return false;
  return rename($tmp, $path);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $photos = read_photos($FILE);
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $i = (int)($_POST['index'] ?? -1);
    if (isset($photos[$i])) { array_splice($photos, $i, 1); write_photos($FILE, $photos); flash('Photo removed from gallery.'); }
  }

  if ($action === 'add') {
    $cat = $_POST['category'] ?? '';
    if (!isset($PHOTO_CATS[$cat])) { flash('Pick a valid album.', 'err'); }
    else {
      $dir = IMAGES_DIR . '/' . $cat;
      $web = handle_upload('imgfile', $dir, ['jpg','jpeg','png','webp','gif'], 'images/' . $cat);
      if ($web) { $photos[] = ['category' => $cat, 'src' => $web, 'alt' => $cat]; write_photos($FILE, $photos); flash('Added photo to ' . $PHOTO_CATS[$cat]); }
    }
  }
  header('Location: photos.php' . (isset($_POST['cat']) ? '?cat=' . urlencode($_POST['cat']) : '')); exit;
}

$photos = read_photos($FILE);
$catFilter = $_GET['cat'] ?? '';
$shown = $catFilter ? array_values(array_filter($photos, fn($p) => $p['category'] === $catFilter)) : $photos;
// map shown -> original index for delete
function orig_index($photos, $p) { foreach ($photos as $i => $x) if ($x === $p) return $i; return -1; }

admin_head('Photos');
echo render_flash();
?>
<a class="a-back" href="index.php">← Dashboard</a>
<h1 class="a-h1">🖼️ Photos <span class="a-count"><?= count($photos) ?></span></h1>

<section class="a-panel">
  <h2>Add a photo</h2>
  <form method="post" enctype="multipart/form-data" class="a-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <?php if ($catFilter): ?><input type="hidden" name="cat" value="<?= h($catFilter) ?>"><?php endif; ?>
    <label>Album / department
      <select name="category" required>
        <option value="">— choose —</option>
        <?php foreach ($PHOTO_CATS as $k => $lbl): ?>
          <option value="<?= h($k) ?>" <?= $catFilter === $k ? 'selected' : '' ?>><?= h($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Image file <input type="file" name="imgfile" accept="image/*" required></label>
    <div class="a-actions"><button class="a-btn a-btn-primary" type="submit">Upload photo</button></div>
  </form>
</section>

<section class="a-panel">
  <h2>Gallery</h2>
  <form method="get" class="a-filter">
    <label>Filter album
      <select name="cat" onchange="this.form.submit()">
        <option value="">All (<?= count($photos) ?>)</option>
        <?php foreach ($PHOTO_CATS as $k => $lbl): $c = count(array_filter($photos, fn($p)=>$p['category']===$k)); ?>
          <option value="<?= h($k) ?>" <?= $catFilter === $k ? 'selected' : '' ?>><?= h($lbl) ?> (<?= $c ?>)</option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>
  <div class="a-photos">
    <?php foreach ($shown as $p): $oi = orig_index($photos, $p); ?>
      <div class="a-photo">
        <img src="../<?= h($p['src']) ?>" alt="" loading="lazy">
        <div class="a-photo-cat"><?= h($PHOTO_CATS[$p['category']] ?? $p['category']) ?></div>
        <form method="post" onsubmit="return confirm('Remove this photo from the gallery?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="index" value="<?= $oi ?>">
          <?php if ($catFilter): ?><input type="hidden" name="cat" value="<?= h($catFilter) ?>"><?php endif; ?>
          <button class="a-photo-del" title="Remove" type="submit">×</button>
        </form>
      </div>
    <?php endforeach; ?>
    <?php if (!$shown): ?><p class="a-empty">No photos in this album.</p><?php endif; ?>
  </div>
</section>
<?php admin_foot(); ?>

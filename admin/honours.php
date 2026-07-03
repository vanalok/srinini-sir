<?php
require_once __DIR__ . '/lib.php';
require_login();

$FILE = SITE_ROOT . '/honours.json';
$CATS = ['international' => 'International', 'national' => 'National', 'state' => 'State', 'academic' => 'Academic'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $awards = read_json($FILE);
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $i = (int)($_POST['index'] ?? -1);
    if (isset($awards[$i])) { $t = $awards[$i]['title'] ?? 'award'; array_splice($awards, $i, 1); write_json($FILE, $awards); flash('Deleted: ' . $t); }
  }

  if ($action === 'save') {
    $year  = (int)($_POST['year'] ?? 0);
    $cat   = $_POST['cat'] ?? 'national';
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    $img   = trim($_POST['img'] ?? '');
    $idx   = $_POST['index'] ?? '';
    $existing = ($idx !== '' && isset($awards[(int)$idx])) ? $awards[(int)$idx] : [];

    $up = handle_upload('imgfile', IMAGES_DIR . '/honours', ['jpg','jpeg','png','webp'], 'images/honours');
    if ($up) $img = $up;
    if ($img === '') $img = $existing['img'] ?? '';

    if ($title === '' || $year < 1900) {
      flash('Title and a valid year are required.', 'err');
    } else {
      $entry = ['year' => $year, 'cat' => $cat, 'title' => $title, 'body' => $body, 'img' => $img];
      if ($idx !== '' && isset($awards[(int)$idx])) { $awards[(int)$idx] = $entry; flash('Updated: ' . $title); }
      else { $awards[] = $entry; flash('Added: ' . $title); }
      write_json($FILE, $awards);
    }
  }
  header('Location: honours.php'); exit;
}

$awards = read_json($FILE);
$editIdx = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$edit = ($editIdx !== null && isset($awards[$editIdx])) ? $awards[$editIdx] : null;

admin_head('Honours');
echo render_flash();
?>
<a class="a-back" href="index.php">← Dashboard</a>
<h1 class="a-h1">🏆 Honours <span class="a-count"><?= count($awards) ?></span></h1>

<section class="a-panel">
  <h2><?= $edit ? 'Edit honour' : 'Add an honour' ?></h2>
  <form method="post" enctype="multipart/form-data" class="a-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($edit): ?><input type="hidden" name="index" value="<?= (int)$editIdx ?>"><?php endif; ?>

    <label>Title <input name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
    <label>Year <input name="year" type="number" min="1900" max="2100" required value="<?= h($edit['year'] ?? date('Y')) ?>"></label>
    <label>Category
      <select name="cat">
        <?php foreach ($CATS as $k => $lbl): ?>
          <option value="<?= h($k) ?>" <?= (($edit['cat'] ?? 'national') === $k) ? 'selected' : '' ?>><?= h($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Description <textarea name="body" rows="3"><?= h($edit['body'] ?? '') ?></textarea></label>
    <label>Image <span class="a-hint">(upload to images/honours)</span><input type="file" name="imgfile" accept="image/*"></label>
    <label>…or image path <input name="img" value="<?= h($edit['img'] ?? '') ?>" placeholder="images/honours/…"></label>
    <div class="a-actions">
      <button class="a-btn a-btn-primary" type="submit"><?= $edit ? 'Save changes' : 'Add honour' ?></button>
      <?php if ($edit): ?><a class="a-btn" href="honours.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="a-panel">
  <h2>All honours</h2>
  <div class="a-list">
    <?php foreach ($awards as $i => $a): ?>
      <div class="a-row">
        <img class="a-thumb" src="<?= asset_src($a['img'] ?? '') ?>" alt="" loading="lazy">
        <div class="a-row-main">
          <div class="a-row-title"><?= h($a['title'] ?? '(untitled)') ?></div>
          <div class="a-row-meta"><?= h($a['year'] ?? '') ?> · <?= h($CATS[$a['cat'] ?? ''] ?? ($a['cat'] ?? '')) ?></div>
        </div>
        <div class="a-row-actions">
          <a class="a-btn a-btn-sm" href="honours.php?edit=<?= $i ?>">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this honour?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="index" value="<?= $i ?>">
            <button class="a-btn a-btn-sm a-btn-danger" type="submit">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$awards): ?><p class="a-empty">No honours yet.</p><?php endif; ?>
  </div>
</section>
<?php admin_foot(); ?>

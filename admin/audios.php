<?php
require_once __DIR__ . '/lib.php';
require_login();

$FILE = SITE_ROOT . '/audios.json';
$KINDS = ['documentary' => 'Documentary', 'talk' => 'Talk', 'interview' => 'Interview'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $audios = read_json($FILE);
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $i = (int)($_POST['index'] ?? -1);
    if (isset($audios[$i])) { $t = $audios[$i]['title'] ?? 'audio'; array_splice($audios, $i, 1); write_json($FILE, $audios); flash('Deleted: ' . $t); }
  }

  if ($action === 'save') {
    $title = trim($_POST['title'] ?? '');
    $kind  = $_POST['kind'] ?? 'documentary';
    $src   = trim($_POST['sourceUrl'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $idx   = $_POST['index'] ?? '';
    $existing = ($idx !== '' && isset($audios[(int)$idx])) ? $audios[(int)$idx] : [];

    // audio file: uploaded takes priority, else keep existing / typed path
    $audioUrl = trim($_POST['audioUrl'] ?? '');
    $up = handle_upload('audiofile', AUDIO_DIR, ['mp3','m4a','wav','ogg'], 'assets/audio');
    if ($up) $audioUrl = $up;
    if ($audioUrl === '') $audioUrl = $existing['audioUrl'] ?? '';

    // optional cover upload
    $coverUp = handle_upload('coverfile', IMAGES_DIR, ['jpg','jpeg','png','webp'], 'images');
    if ($coverUp) $image = $coverUp;
    if ($image === '') $image = $existing['image'] ?? 'images/audio_bg.webp';

    if ($title === '' || $audioUrl === '') {
      flash('Title and an audio file (upload or path) are required.', 'err');
    } else {
      $entry = ['title' => $title, 'audioUrl' => $audioUrl, 'image' => $image, 'kind' => $kind];
      if ($src !== '') $entry['sourceUrl'] = $src;
      if ($idx !== '' && isset($audios[(int)$idx])) { $audios[(int)$idx] = array_merge($existing, $entry); flash('Updated: ' . $title); }
      else { $audios[] = $entry; flash('Added: ' . $title); }
      write_json($FILE, $audios);
    }
  }
  header('Location: audios.php'); exit;
}

$audios = read_json($FILE);
$editIdx = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$edit = ($editIdx !== null && isset($audios[$editIdx])) ? $audios[$editIdx] : null;

admin_head('Audios');
echo render_flash();
?>
<a class="a-back" href="index.php">← Dashboard</a>
<h1 class="a-h1">🎧 Audios <span class="a-count"><?= count($audios) ?></span></h1>

<section class="a-panel">
  <h2><?= $edit ? 'Edit audio' : 'Add an audio' ?></h2>
  <form method="post" enctype="multipart/form-data" class="a-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($edit): ?><input type="hidden" name="index" value="<?= (int)$editIdx ?>"><?php endif; ?>

    <label>Title <input name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
    <label>Audio file <span class="a-hint">(mp3/m4a/wav — uploads to assets/audio)</span>
      <input type="file" name="audiofile" accept="audio/*">
    </label>
    <label>…or audio path <span class="a-hint">(if not uploading)</span>
      <input name="audioUrl" value="<?= h($edit['audioUrl'] ?? '') ?>" placeholder="assets/audio/…">
    </label>
    <label>Category
      <select name="kind">
        <?php foreach ($KINDS as $k => $lbl): ?>
          <option value="<?= h($k) ?>" <?= (($edit['kind'] ?? 'documentary') === $k) ? 'selected' : '' ?>><?= h($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Cover image <span class="a-hint">(optional upload)</span><input type="file" name="coverfile" accept="image/*"></label>
    <label>…or cover path <input name="image" value="<?= h($edit['image'] ?? '') ?>" placeholder="images/audio_bg.webp"></label>
    <label>Source URL <span class="a-hint">(optional — original page)</span><input name="sourceUrl" value="<?= h($edit['sourceUrl'] ?? '') ?>"></label>
    <div class="a-actions">
      <button class="a-btn a-btn-primary" type="submit"><?= $edit ? 'Save changes' : 'Add audio' ?></button>
      <?php if ($edit): ?><a class="a-btn" href="audios.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="a-panel">
  <h2>All audios</h2>
  <div class="a-list">
    <?php foreach ($audios as $i => $a): ?>
      <div class="a-row">
        <img class="a-thumb" src="<?= asset_src($a['image'] ?? 'images/audio_bg.webp') ?>" alt="" loading="lazy">
        <div class="a-row-main">
          <div class="a-row-title"><?= h($a['title'] ?? '(untitled)') ?></div>
          <div class="a-row-meta"><?= h($KINDS[$a['kind'] ?? ''] ?? ($a['kind'] ?? '')) ?> · <code><?= h(basename($a['audioUrl'] ?? '')) ?></code></div>
        </div>
        <div class="a-row-actions">
          <a class="a-btn a-btn-sm" href="audios.php?edit=<?= $i ?>">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this audio?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="index" value="<?= $i ?>">
            <button class="a-btn a-btn-sm a-btn-danger" type="submit">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$audios): ?><p class="a-empty">No audios yet.</p><?php endif; ?>
  </div>
</section>
<?php admin_foot(); ?>

<?php
require_once __DIR__ . '/lib.php';
require_login();

$FILE = SITE_ROOT . '/videos.json';

/* extract an 11-char YouTube id from a URL or raw id */
function yt_id(string $s): string {
  $s = trim($s);
  if (preg_match('~(?:youtu\.be/|v=|embed/|shorts/|live/)([A-Za-z0-9_-]{11})~', $s, $m)) return $m[1];
  if (preg_match('~^[A-Za-z0-9_-]{11}$~', $s)) return $s;
  return '';
}

/* ---- Handle actions ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $videos = read_json($FILE);
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $i = (int)($_POST['index'] ?? -1);
    if (isset($videos[$i])) {
      $t = $videos[$i]['title'] ?? 'video';
      array_splice($videos, $i, 1);
      write_json($FILE, $videos);
      flash('Deleted: ' . $t);
    }
  }

  if ($action === 'save') {
    $title = trim($_POST['title'] ?? '');
    $url   = trim($_POST['url'] ?? '');
    $dept  = $_POST['dept'] ?? '';
    $image = trim($_POST['image'] ?? '');
    $order = trim($_POST['order'] ?? '');
    $vid   = yt_id($url);
    if ($title === '' || $vid === '') {
      flash('Title and a valid YouTube link/ID are required.', 'err');
    } else {
      $entry = [
        'title'   => $title,
        'videoId' => $vid,
        'url'     => $url ?: 'https://www.youtube.com/watch?v=' . $vid,
        'dept'    => $dept,
        'order'   => $order === '' ? 0 : (int)$order,
      ];
      if ($image !== '') $entry['image'] = $image;
      $idx = $_POST['index'] ?? '';
      if ($idx !== '' && isset($videos[(int)$idx])) {
        // preserve any extra existing fields
        $videos[(int)$idx] = array_merge($videos[(int)$idx], $entry);
        flash('Updated: ' . $title);
      } else {
        $videos[] = $entry;
        flash('Added: ' . $title);
      }
      write_json($FILE, $videos);
    }
  }
  header('Location: videos.php'); exit;
}

/* ---- Data for view ---- */
$videos = read_json($FILE);
$editIdx = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$edit = ($editIdx !== null && isset($videos[$editIdx])) ? $videos[$editIdx] : null;

admin_head('Videos');
echo render_flash();
?>
<a class="a-back" href="index.php">← Dashboard</a>
<h1 class="a-h1">🎬 Videos <span class="a-count"><?= count($videos) ?></span></h1>

<section class="a-panel">
  <h2><?= $edit ? 'Edit video' : 'Add a video' ?></h2>
  <form method="post" class="a-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($edit): ?><input type="hidden" name="index" value="<?= (int)$editIdx ?>"><?php endif; ?>

    <label>Title
      <input name="title" required value="<?= h($edit['title'] ?? '') ?>">
    </label>
    <label>YouTube link or video ID
      <input name="url" required placeholder="https://youtu.be/… or 11-char ID"
             value="<?= h($edit['url'] ?? '') ?>">
    </label>
    <label>Department / category
      <select name="dept">
        <option value="">— none —</option>
        <?php foreach (DEPARTMENTS as $slug => $name): ?>
          <option value="<?= h($slug) ?>" <?= (($edit['dept'] ?? '') === $slug) ? 'selected' : '' ?>>
            <?= h($name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Custom cover image URL <span class="a-hint">(optional — blank uses the YouTube thumbnail)</span>
      <input name="image" value="<?= h($edit['image'] ?? '') ?>" placeholder="images/videos/…">
    </label>
    <label>Sort order <span class="a-hint">(lower numbers show first; leave 0 for default)</span>
      <input type="number" name="order" value="<?= h((string)($edit['order'] ?? 0)) ?>">
    </label>
    <div class="a-actions">
      <button class="a-btn a-btn-primary" type="submit"><?= $edit ? 'Save changes' : 'Add video' ?></button>
      <?php if ($edit): ?><a class="a-btn" href="videos.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="a-panel">
  <h2>All videos</h2>
  <div class="a-list">
    <?php foreach ($videos as $i => $v): $vid = $v['videoId'] ?? ''; ?>
      <div class="a-row">
        <img class="a-thumb" src="https://i.ytimg.com/vi/<?= h($vid) ?>/default.jpg" alt="" loading="lazy">
        <div class="a-row-main">
          <div class="a-row-title"><?= h($v['title'] ?? '(untitled)') ?></div>
          <div class="a-row-meta">
            <?= h(DEPARTMENTS[$v['dept'] ?? ''] ?? ($v['dept'] ?? 'no category')) ?>
            · <code><?= h($vid) ?></code>
            · order: <?= (int)($v['order'] ?? 0) ?>
          </div>
        </div>
        <div class="a-row-actions">
          <a class="a-btn a-btn-sm" href="videos.php?edit=<?= $i ?>">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this video?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="index" value="<?= $i ?>">
            <button class="a-btn a-btn-sm a-btn-danger" type="submit">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$videos): ?><p class="a-empty">No videos yet.</p><?php endif; ?>
  </div>
</section>
<?php admin_foot(); ?>

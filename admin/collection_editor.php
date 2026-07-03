<?php
/* Shared editor for department-map collections (publications / accomplishments).
   Expects $CFG = ['file','label','icon','page'] before include. */
require_once __DIR__ . '/lib.php';
require_login();

$FILE = SITE_ROOT . '/' . $CFG['file'];

/* Load {departments:{slug:[items]}} and flatten with locators */
function flatten_items(array $data): array {
  $out = [];
  foreach (($data['departments'] ?? []) as $key => $items) {
    foreach ($items as $idx => $item) {
      $out[] = ['key' => $key, 'idx' => $idx, 'item' => $item];
    }
  }
  // newest-ish: keep insertion order
  return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $data = read_json($FILE);
  $key = $_POST['key'] ?? '';
  $idx = (int)($_POST['idx'] ?? -1);
  $action = $_POST['action'] ?? '';

  if (isset($data['departments'][$key][$idx])) {
    if ($action === 'delete') {
      $t = $data['departments'][$key][$idx]['title'] ?? 'item';
      array_splice($data['departments'][$key], $idx, 1);
      write_json($FILE, $data);
      flash('Deleted: ' . $t);
    }
    if ($action === 'save') {
      $newTitle = trim($_POST['title'] ?? '');
      $newDept  = $_POST['dept'] ?? '';
      $it = &$data['departments'][$key][$idx];
      if ($newTitle !== '') $it['title'] = $newTitle;
      if ($newDept !== '') {
        // dept field is what the site filters on; clear categories so it
        // shows ONLY under the chosen department (no double-listing).
        $it['dept'] = $newDept;
        $it['categories'] = '';
        $it['broadCategory'] = '';
      }
      unset($it);
      write_json($FILE, $data);
      flash('Updated: ' . ($newTitle ?: 'item'));
    }
  }
  header('Location: ' . $CFG['page'] . '.php'); exit;
}

$data = read_json($FILE);
$items = flatten_items($data);
$editKey = $_GET['ekey'] ?? null; $editIdx = isset($_GET['eidx']) ? (int)$_GET['eidx'] : null;

admin_head($CFG['label']);
echo render_flash();
?>
<a class="a-back" href="index.php">← Dashboard</a>
<h1 class="a-h1"><?= $CFG['icon'] ?> <?= h($CFG['label']) ?> <span class="a-count"><?= count($items) ?></span></h1>
<p class="a-sub">Edit titles and move items between departments. <em>Adding brand-new items (with a PDF + post page) comes with the Blog editor.</em></p>

<section class="a-panel">
  <h2>All <?= h(strtolower($CFG['label'])) ?></h2>
  <div class="a-list">
    <?php foreach ($items as $row): $it = $row['item']; $isEdit = ($editKey === $row['key'] && $editIdx === $row['idx']); ?>
      <div class="a-row">
        <div class="a-row-main">
          <?php if ($isEdit): ?>
            <form method="post" class="a-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="save">
              <input type="hidden" name="key" value="<?= h($row['key']) ?>">
              <input type="hidden" name="idx" value="<?= (int)$row['idx'] ?>">
              <input name="title" value="<?= h($it['title'] ?? '') ?>" style="width:100%;margin-bottom:8px;">
              <div class="a-inline-row">
                <select name="dept">
                  <?php foreach (DEPARTMENTS as $slug => $name): if ($slug==='talks') continue; ?>
                    <option value="<?= h($slug) ?>" <?= (($it['dept'] ?? '') === $slug) ? 'selected' : '' ?>><?= h($name) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="a-btn a-btn-sm a-btn-primary" type="submit">Save</button>
                <a class="a-btn a-btn-sm" href="<?= $CFG['page'] ?>.php">Cancel</a>
              </div>
            </form>
          <?php else: ?>
            <div class="a-row-title"><?= h($it['title'] ?? '(untitled)') ?></div>
            <div class="a-row-meta">
              <?= h(DEPARTMENTS[$it['dept'] ?? ''] ?? ($it['dept'] ?? 'no department')) ?>
              <?php if (!empty($it['slug'])): ?> · <a href="../posts/<?= h($it['slug']) ?>.html" target="_blank">view post ↗</a><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php if (!$isEdit): ?>
        <div class="a-row-actions">
          <a class="a-btn a-btn-sm" href="<?= $CFG['page'] ?>.php?ekey=<?= h(urlencode($row['key'])) ?>&eidx=<?= (int)$row['idx'] ?>">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this item?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="key" value="<?= h($row['key']) ?>">
            <input type="hidden" name="idx" value="<?= (int)$row['idx'] ?>">
            <button class="a-btn a-btn-sm a-btn-danger" type="submit">Delete</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$items): ?><p class="a-empty">Nothing yet.</p><?php endif; ?>
  </div>
</section>
<?php admin_foot(); ?>

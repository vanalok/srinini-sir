<?php
/* Shared editor for department-map collections (publications / accomplishments).
   Expects $CFG = ['file','label','icon','page'] before include. */
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/post_generator.php';
require_login();

$FILE = SITE_ROOT . '/' . $CFG['file'];
$POSTS_FULL = SITE_ROOT . '/assets/posts-full.json';
$POSTS_DIR  = SITE_ROOT . '/posts';

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
  $action = $_POST['action'] ?? '';

  if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $dept  = $_POST['dept'] ?? '';
    if ($title === '' || !isset(DEPARTMENTS[$dept])) {
      flash('Title and a valid department are required.', 'err');
      header('Location: ' . $CFG['page'] . '.php'); exit;
    }
    // unique slug
    $pf = read_json($POSTS_FULL);
    $slug = slugify($title); $base = $slug; $n = 2;
    $slugs = array_column($pf, 'slug');
    while (in_array($slug, $slugs, true) || is_file($POSTS_DIR . '/' . $slug . '.html')) $slug = $base . '-' . $n++;

    $cover = trim($_POST['image'] ?? '');
    $cu = handle_upload('coverfile', IMAGES_DIR . '/blog', ['jpg','jpeg','png','webp'], 'images/blog');
    if ($cu) $cover = $cu;
    $pdf = handle_upload('pdffile', PDF_DIR, ['pdf'], 'assets/pdf');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $bodyText = $_POST['body'] ?? '';
    $date = date('c');

    // 1) generate the static post page
    generate_post_page($POSTS_DIR, [
      'slug' => $slug, 'title' => $title,
      'bodyHtml' => post_body_from_text($bodyText),
      'cover' => $cover, 'pdf' => $pdf,
      'pdfName' => $pdf ? basename($pdf) : '', 'date' => $date,
    ]);
    // 2) posts-full.json (cover + date the dept card reads)
    $pf[] = [
      'slug' => $slug, 'title' => $title, 'excerpt' => $excerpt, 'date' => $date,
      'minutesToRead' => max(1, (int)round(str_word_count(strip_tags($bodyText)) / 200)),
      'language' => preg_match('/[\x{0C80}-\x{0CFF}]/u', $title) ? 'kn' : 'en',
      'categories' => [DEPARTMENTS[$dept]], 'image' => $cover,
      'localUrl' => 'posts/' . $slug . '.html',
    ] + ($pdf ? ['pdfUrl' => $pdf] : []) + ['cmsGenerated' => true];
    write_json($POSTS_FULL, $pf);
    // 3) the collection department map
    if (!isset($data['departments'][$dept])) $data['departments'][$dept] = [];
    $data['departments'][$dept][] = [
      'id' => $slug, 'title' => $title, 'slug' => $slug,
      'categories' => '', 'broadCategory' => '',
      'hasYouTube' => false, 'hasPDF' => (bool)$pdf,
      'type' => $CFG['type'], 'dept' => $dept, 'cmsGenerated' => true,
    ];
    write_json($FILE, $data);
    flash('Created: ' . $title . ' (page generated)');
    header('Location: ' . $CFG['page'] . '.php'); exit;
  }

  $key = $_POST['key'] ?? '';
  $idx = (int)($_POST['idx'] ?? -1);
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
<p class="a-sub">Add new items (generates a post page with the PDF), edit titles, move items between departments, or delete.</p>

<section class="a-panel">
  <h2>Add a <?= h(rtrim(strtolower($CFG['label']), 's')) ?></h2>
  <form method="post" enctype="multipart/form-data" class="a-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <label>Title <input name="title" required></label>
    <label>Department
      <select name="dept" required>
        <option value="">— choose —</option>
        <?php foreach (DEPARTMENTS as $slug => $name): if ($slug === 'talks') continue; ?>
          <option value="<?= h($slug) ?>"><?= h($name) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>PDF document <span class="a-hint">(optional — shown with an in-page preview)</span><input type="file" name="pdffile" accept="application/pdf"></label>
    <label>Cover image <span class="a-hint">(upload)</span><input type="file" name="coverfile" accept="image/*"></label>
    <label>…or cover path/URL <input name="image" placeholder="images/… or https://…"></label>
    <label>Excerpt / summary <textarea name="excerpt" rows="2"></textarea></label>
    <label>Body text <span class="a-hint">(optional — blank line = new paragraph)</span><textarea name="body" rows="4"></textarea></label>
    <div class="a-actions"><button class="a-btn a-btn-primary" type="submit">Create</button></div>
  </form>
</section>

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

<?php
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/post_generator.php';
require_login();

$FILE = SITE_ROOT . '/assets/posts-full.json';
$POSTS_DIR = SITE_ROOT . '/posts';
$CATS = ['General','Ayush','KSPCB','Karnataka Forest Department','Kali Tiger Reserve',
  'Nagarhole National Park','Ecology & Environment','ADCL','Academics','Shimoga',
  'Chitradurga','Kalaburagi','KFCSC','EMPRI'];

function find_post(array &$posts, string $slug) {
  foreach ($posts as $i => &$p) if (($p['slug'] ?? '') === $slug) return $i;
  return -1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $posts = read_json($FILE);
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $slug = $_POST['slug'] ?? '';
    $i = find_post($posts, $slug);
    if ($i >= 0) {
      $t = $posts[$i]['title'] ?? $slug;
      array_splice($posts, $i, 1);
      write_json($FILE, $posts);
      @unlink($POSTS_DIR . '/' . $slug . '.html');   // remove the page too
      flash('Deleted: ' . $t);
    }
    header('Location: blog.php'); exit;
  }

  if ($action === 'save') {
    $editSlug = $_POST['slug'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $cat   = $_POST['category'] ?? 'General';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $bodyText = $_POST['body'] ?? '';
    $date  = $_POST['date'] ? date('c', strtotime($_POST['date'])) : date('c');
    $lang  = preg_match('/[\x{0C80}-\x{0CFF}]/u', $title) ? 'kn' : 'en';

    if ($title === '') { flash('Title is required.', 'err'); header('Location: blog.php'); exit; }

    $i = $editSlug ? find_post($posts, $editSlug) : -1;
    $isNew = ($i < 0);
    $slug = $isNew ? slugify($title) : $editSlug;
    // ensure unique slug for new posts
    if ($isNew) { $base = $slug; $n = 2; while (find_post($posts, $slug) >= 0) { $slug = $base . '-' . $n++; } }

    $existing = $isNew ? [] : $posts[$i];

    // uploads
    $cover = trim($_POST['image'] ?? ($existing['image'] ?? ''));
    $cu = handle_upload('coverfile', IMAGES_DIR . '/blog', ['jpg','jpeg','png','webp'], 'images/blog');
    if ($cu) $cover = $cu;
    $pdf = $existing['pdfUrl'] ?? '';
    $pu = handle_upload('pdffile', PDF_DIR, ['pdf'], 'assets/pdf');
    if ($pu) $pdf = $pu;

    $entry = array_merge($existing, [
      'slug' => $slug,
      'title' => $title,
      'excerpt' => $excerpt,
      'date' => $date,
      'minutesToRead' => $existing['minutesToRead'] ?? max(1, (int)round(str_word_count(strip_tags($bodyText)) / 200)),
      'language' => $lang,
      'categories' => [$cat],
      'image' => $cover,
      'localUrl' => 'posts/' . $slug . '.html',
    ]);
    if ($pdf) { $entry['pdfUrl'] = $pdf; }

    // Generate the page ONLY for CMS-created posts (never overwrite imported rich posts)
    $cmsGen = $isNew || !empty($existing['cmsGenerated']);
    if ($cmsGen) {
      $entry['cmsGenerated'] = true;
      generate_post_page($POSTS_DIR, [
        'slug' => $slug, 'title' => $title,
        'bodyHtml' => post_body_from_text($bodyText),
        'cover' => $cover, 'pdf' => $pdf,
        'pdfName' => $pdf ? basename($pdf) : '', 'date' => $date,
      ]);
    }

    if ($isNew) { $posts[] = $entry; flash('Created: ' . $title . ($cmsGen ? ' (page generated)' : '')); }
    else { $posts[$i] = $entry; flash('Updated: ' . $title . ($cmsGen ? ' (page regenerated)' : ' (listing only — imported page left intact)')); }
    write_json($FILE, $posts);
    header('Location: blog.php'); exit;
  }
}

$posts = read_json($FILE);
usort($posts, fn($a,$b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
$q = trim($_GET['q'] ?? '');
$editSlug = $_GET['edit'] ?? null;
$edit = null;
if ($editSlug) foreach ($posts as $p) if (($p['slug'] ?? '') === $editSlug) { $edit = $p; break; }
$list = $q ? array_values(array_filter($posts, fn($p) => stripos($p['title'] ?? '', $q) !== false)) : $posts;

admin_head('Blog posts');
echo render_flash();
?>
<a class="a-back" href="index.php">← Dashboard</a>
<h1 class="a-h1">📝 Blog posts <span class="a-count"><?= count($posts) ?></span></h1>

<section class="a-panel">
  <h2><?= $edit ? 'Edit post' : 'New post' ?></h2>
  <?php if ($edit && empty($edit['cmsGenerated'])): ?>
    <div class="flash flash-ok">This is an <strong>imported post</strong> — editing here updates its blog-listing card (title, cover, category, date, excerpt). The post page's rich content is left untouched.</div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="a-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($edit): ?><input type="hidden" name="slug" value="<?= h($edit['slug']) ?>"><?php endif; ?>

    <label>Title <input name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
    <label>Category
      <select name="category">
        <?php $ecat = $edit['categories'][0] ?? 'General'; foreach ($CATS as $c): ?>
          <option value="<?= h($c) ?>" <?= $ecat === $c ? 'selected' : '' ?>><?= h($c) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Date <input type="date" name="date" value="<?= h(substr($edit['date'] ?? date('c'), 0, 10)) ?>"></label>
    <label>Excerpt / summary <textarea name="excerpt" rows="2"><?= h($edit['excerpt'] ?? '') ?></textarea></label>
    <?php if (!$edit || !empty($edit['cmsGenerated'])): ?>
    <label>Body <span class="a-hint">(plain text — blank line starts a new paragraph)</span>
      <textarea name="body" rows="8"></textarea></label>
    <label>PDF attachment <span class="a-hint">(optional)</span><input type="file" name="pdffile" accept="application/pdf"></label>
    <?php endif; ?>
    <label>Cover image <span class="a-hint">(upload)</span><input type="file" name="coverfile" accept="image/*"></label>
    <label>…or cover path/URL <input name="image" value="<?= h($edit['image'] ?? '') ?>" placeholder="images/… or https://…"></label>
    <div class="a-actions">
      <button class="a-btn a-btn-primary" type="submit"><?= $edit ? 'Save' : 'Create post' ?></button>
      <?php if ($edit): ?><a class="a-btn" href="blog.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="a-panel">
  <h2>All posts</h2>
  <form method="get" class="a-filter"><label>Search title <input name="q" value="<?= h($q) ?>" placeholder="type & Enter"></label></form>
  <div class="a-list">
    <?php foreach (array_slice($list, 0, 60) as $p): ?>
      <div class="a-row">
        <img class="a-thumb" src="<?= asset_src($p['image'] ?? '') ?>" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
        <div class="a-row-main">
          <div class="a-row-title"><?= h($p['title'] ?? '(untitled)') ?> <?= empty($p['cmsGenerated']) ? '' : '<span class="a-badge">CMS</span>' ?></div>
          <div class="a-row-meta"><?= h($p['categories'][0] ?? '') ?> · <?= h(substr($p['date'] ?? '', 0, 10)) ?>
            · <a href="../posts/<?= h($p['slug']) ?>.html" target="_blank">view ↗</a></div>
        </div>
        <div class="a-row-actions">
          <a class="a-btn a-btn-sm" href="blog.php?edit=<?= h(urlencode($p['slug'])) ?>">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this post and its page?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= h($p['slug']) ?>">
            <button class="a-btn a-btn-sm a-btn-danger" type="submit">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (count($list) > 60): ?><p class="a-empty">Showing first 60 of <?= count($list) ?> — use search to narrow.</p><?php endif; ?>
    <?php if (!$list): ?><p class="a-empty">No posts match.</p><?php endif; ?>
  </div>
</section>
<?php admin_foot(); ?>

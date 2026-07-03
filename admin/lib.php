<?php
/* ============================================================
   Shared helpers: auth, CSRF, JSON I/O, escaping, flash
   ============================================================ */
require_once __DIR__ . '/config.php';
session_start();

/* ---- Auth ---- */
function is_logged_in(): bool {
  return !empty($_SESSION['admin_ok']);
}
function require_login(): void {
  if (!is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}
function attempt_login(string $user, string $pass): bool {
  if (hash_equals(ADMIN_USER, $user) && password_verify($pass, ADMIN_PASS_HASH)) {
    session_regenerate_id(true);
    $_SESSION['admin_ok'] = true;
    return true;
  }
  return false;
}
function logout(): void {
  $_SESSION = [];
  session_destroy();
}

/* ---- CSRF ---- */
function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function csrf_field(): string {
  return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}
function csrf_check(): void {
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '_')) {
    http_response_code(400);
    exit('Invalid CSRF token — reload the page and try again.');
  }
}

/* ---- Escaping ---- */
function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* Admin thumbnail src: absolute URLs used as-is; local paths get ../ prefix
   (admin lives in /admin, content is one level up). */
function asset_src($p): string {
  $p = trim((string)$p);
  if ($p === '') return '';
  if (preg_match('#^(https?://|//|data:)#i', $p)) return h($p);
  $p = preg_replace('#^\./#', '', ltrim($p, '/'));
  return '../' . h($p);
}

/* ---- JSON read/write (Kannada-safe, pretty) ---- */
function read_json(string $path): array {
  if (!is_file($path)) return [];
  $raw = file_get_contents($path);
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function write_json(string $path, $data): bool {
  $json = json_encode($data,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($json === false) return false;
  // atomic-ish write
  $tmp = $path . '.tmp';
  if (file_put_contents($tmp, $json . "\n") === false) return false;
  return rename($tmp, $path);
}

/* ---- Flash messages ---- */
function flash(string $msg, string $type = 'ok'): void {
  $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
}
function render_flash(): string {
  $out = '';
  foreach ($_SESSION['flash'] ?? [] as $f) {
    $out .= '<div class="flash flash-' . h($f['type']) . '">' . h($f['msg']) . '</div>';
  }
  unset($_SESSION['flash']);
  return $out;
}

/* ---- Slug + upload helpers ---- */
function slugify(string $s): string {
  $s = strtolower(trim($s));
  $s = preg_replace('/[^a-z0-9]+/', '-', $s);
  return trim($s, '-') ?: 'item';
}
/* Handle an uploaded file into $destDir; returns web path (relative to site root) or null. */
function handle_upload(string $field, string $destDir, array $allowedExt, string $webPrefix): ?string {
  if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
  $f = $_FILES[$field];
  if ($f['error'] !== UPLOAD_ERR_OK) { flash('Upload failed (error ' . $f['error'] . ')', 'err'); return null; }
  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExt, true)) { flash('File type .' . h($ext) . ' not allowed', 'err'); return null; }
  if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
  $base = slugify(pathinfo($f['name'], PATHINFO_FILENAME));
  $name = $base . '-' . date('YmdHis') . '.' . $ext;
  $dest = $destDir . '/' . $name;
  if (!move_uploaded_file($f['tmp_name'], $dest)) { flash('Could not save uploaded file', 'err'); return null; }
  return $webPrefix . '/' . $name;
}

/* ---- Navigation items (shared by sidebar + dashboard) ---- */
function nav_items(): array {
  return [
    ['index.php',          'Dashboard',       '🏠'],
    ['videos.php',         'Videos',          '🎬'],
    ['audios.php',         'Audios',          '🎧'],
    ['honours.php',        'Honours',         '🏆'],
    ['publications.php',   'Publications',    '📄'],
    ['accomplishments.php','Accomplishments', '🏅'],
    ['photos.php',         'Photos',          '🖼️'],
    ['blog.php',           'Blog',            '📝'],
  ];
}

/* ---- Page chrome (sidebar layout) ---- */
function render_sidebar(): string {
  $cur = basename($_SERVER['SCRIPT_NAME'] ?? '');
  $h = '<aside class="a-sidebar" id="aSidebar">'
     . '<a class="a-side-brand" href="index.php"><span class="a-side-mark">S</span>'
     . '<span><strong>Srinivasulu IFS</strong><small>Content Manager</small></span></a>'
     . '<nav class="a-side-nav">';
  foreach (nav_items() as [$href, $label, $icon]) {
    $active = ($cur === $href) ? ' is-active' : '';
    $h .= '<a class="a-side-link' . $active . '" href="' . $href . '">'
        . '<span class="a-side-ico">' . $icon . '</span><span>' . h($label) . '</span></a>';
  }
  $h .= '</nav><div class="a-side-foot">'
      . '<a href="../index.html" target="_blank">↗ View site</a>'
      . '<a href="logout.php" class="a-side-logout">⏻ Log out</a></div></aside>';
  return $h;
}
function admin_head(string $title): void {
  echo '<!doctype html><html lang="en"><head><meta charset="UTF-8">'
     . '<meta name="viewport" content="width=device-width,initial-scale=1">'
     . '<title>' . h($title) . ' · Admin</title>'
     . '<link rel="stylesheet" href="admin.css"></head><body>';
  if (is_logged_in()) {
    echo '<div class="a-shell">' . render_sidebar() . '<div class="a-content">'
       . '<header class="a-topbar">'
       . '<button class="a-menu-btn" onclick="document.getElementById(\'aSidebar\').classList.toggle(\'is-open\')" aria-label="Menu">&#9776;</button>'
       . '<span class="a-topbar-title">' . h($title) . '</span>'
       . '<input class="a-topbar-search" id="aSearch" type="search" placeholder="Search this page…" autocomplete="off" style="display:none">'
       . '<a class="a-topbar-view" href="../index.html" target="_blank">View site &#8599;</a>'
       . '</header><main class="a-main">';
  }
  // when logged out, the page (login.php) renders its own full-screen layout
}
function admin_foot(): void {
  if (is_logged_in()) {
    echo '</main></div><div class="a-scrim" onclick="document.getElementById(\'aSidebar\').classList.remove(\'is-open\')"></div></div>';
    // Live client-side filter of the current page's list (rows or photos)
    echo <<<'JS'
<script>
(function(){
  var box = document.getElementById('aSearch');
  if(!box) return;
  var rows = Array.prototype.slice.call(document.querySelectorAll('.a-list .a-row'));
  var photos = Array.prototype.slice.call(document.querySelectorAll('.a-photos .a-photo'));
  var items = rows.length ? rows : photos;
  if(!items.length){ box.style.display='none'; return; }
  box.style.display = '';
  function textOf(el){
    var t = el.querySelector('.a-row-title') || el.querySelector('.a-photo-cat');
    return (t ? t.textContent : el.textContent || '').toLowerCase();
  }
  var empty = document.createElement('p');
  empty.className = 'a-empty'; empty.textContent = 'No matches.'; empty.style.display='none';
  (items[0].parentNode).appendChild(empty);
  box.addEventListener('input', function(){
    var q = this.value.toLowerCase().trim(), shown = 0;
    items.forEach(function(el){
      var ok = !q || textOf(el).indexOf(q) > -1;
      el.style.display = ok ? '' : 'none';
      if(ok) shown++;
    });
    empty.style.display = shown ? 'none' : '';
  });
})();
</script>
JS;
  }
  echo '</body></html>';
}

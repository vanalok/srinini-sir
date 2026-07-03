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

/* ---- Page chrome ---- */
function admin_head(string $title): void {
  echo '<!doctype html><html lang="en"><head><meta charset="UTF-8">'
     . '<meta name="viewport" content="width=device-width,initial-scale=1">'
     . '<title>' . h($title) . ' · Admin</title>'
     . '<link rel="stylesheet" href="admin.css"></head><body>';
  if (is_logged_in()) {
    echo '<header class="a-top"><a class="a-brand" href="index.php">Srinivasulu IFS · Admin</a>'
       . '<nav><a href="../index.html" target="_blank">View site ↗</a>'
       . '<a href="logout.php" class="a-logout">Log out</a></nav></header>';
  }
  echo '<main class="a-main">';
}
function admin_foot(): void { echo '</main></body></html>'; }

<?php
require_once __DIR__ . '/lib.php';
require_login();

$pdo = null; $dbErr = '';
try { require_once __DIR__ . '/../api/db.php'; $pdo = db(); }
catch (Throwable $e) { $dbErr = 'Database not connected. Set credentials in api/db.php and import api/schema.sql.'; }

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $id = (int)($_POST['id'] ?? 0);
  $act = $_POST['action'] ?? '';
  if ($id && in_array($act, ['approve','spam','delete'], true)) {
    if ($act === 'delete') { $pdo->prepare('DELETE FROM comments WHERE id=?')->execute([$id]); flash('Comment deleted.'); }
    else { $pdo->prepare('UPDATE comments SET status=? WHERE id=?')->execute([$act === 'approve' ? 'approved' : 'spam', $id]); flash($act === 'approve' ? 'Comment approved.' : 'Marked as spam.'); }
  }
  header('Location: comments.php' . (isset($_POST['f']) ? '?f=' . urlencode($_POST['f']) : '')); exit;
}

admin_head('Comments');
echo render_flash();
echo '<h1 class="a-h1">💬 Comments</h1>';
if (!$pdo) { echo '<div class="flash flash-err">' . h($dbErr) . '</div>'; admin_foot(); exit; }

$filter = $_GET['f'] ?? 'pending';
$counts = [];
foreach ($pdo->query("SELECT status, COUNT(*) c FROM comments GROUP BY status") as $r) $counts[$r['status']] = (int)$r['c'];
$where = in_array($filter, ['pending','approved','spam'], true) ? "WHERE status='" . $filter . "'" : '';
$list = $pdo->query("SELECT id, slug, name, body, status, created_at FROM comments $where ORDER BY created_at DESC LIMIT 300")->fetchAll();
?>
<div class="a-tabs">
  <?php foreach (['pending'=>'Pending','approved'=>'Approved','spam'=>'Spam','all'=>'All'] as $k=>$lbl): ?>
    <a class="a-tab<?= $filter===$k?' is-on':'' ?>" href="comments.php?f=<?= $k ?>"><?= $lbl ?><?php if(isset($counts[$k])): ?> <span><?= $counts[$k] ?></span><?php endif; ?></a>
  <?php endforeach; ?>
</div>

<section class="a-panel">
  <div class="a-list">
    <?php foreach ($list as $c): ?>
      <div class="a-row" style="align-items:flex-start">
        <div class="a-row-main">
          <div class="a-row-title"><?= h($c['name']) ?>
            <span class="a-badge" style="background:<?= $c['status']==='approved'?'#2e9460':($c['status']==='spam'?'#c0392b':'#b8923c') ?>"><?= h($c['status']) ?></span></div>
          <p style="margin:6px 0; font-size:14px; white-space:pre-wrap;"><?= h($c['body']) ?></p>
          <div class="a-row-meta">on <a href="../posts/<?= h($c['slug']) ?>.html" target="_blank"><?= h($c['slug']) ?></a> · <?= h(date('j M Y, H:i', strtotime($c['created_at']))) ?></div>
        </div>
        <div class="a-row-actions">
          <?php foreach ([['approve','Approve','a-btn-primary'],['spam','Spam',''],['delete','Delete','a-btn-danger']] as [$a,$lbl,$cls]): ?>
            <?php if ($a==='approve' && $c['status']==='approved') continue; ?>
            <form method="post" <?= $a==='delete'?'onsubmit="return confirm(\'Delete this comment?\')"':'' ?>>
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="action" value="<?= $a ?>"><input type="hidden" name="f" value="<?= h($filter) ?>">
              <button class="a-btn a-btn-sm <?= $cls ?>" type="submit"><?= $lbl ?></button>
            </form>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$list): ?><p class="a-empty">No <?= h($filter) ?> comments.</p><?php endif; ?>
  </div>
</section>
<?php admin_foot(); ?>

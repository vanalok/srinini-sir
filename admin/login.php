<?php
require_once __DIR__ . '/lib.php';
if (is_logged_in()) { header('Location: index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (attempt_login($_POST['user'] ?? '', $_POST['pass'] ?? '')) {
    header('Location: index.php'); exit;
  }
  $err = 'Wrong username or password.';
}
admin_head('Login');
?>
<div class="a-login">
  <h1>Admin Login</h1>
  <p class="a-sub">Srinivasulu IFS — Content Manager</p>
  <?php if ($err): ?><div class="flash flash-err"><?= h($err) ?></div><?php endif; ?>
  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <label>Username<input name="user" required autofocus></label>
    <label>Password<input name="pass" type="password" required></label>
    <button class="a-btn a-btn-primary" type="submit">Log in</button>
  </form>
</div>
<?php admin_foot(); ?>

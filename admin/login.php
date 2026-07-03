<?php
require_once __DIR__ . '/lib.php';
if (is_logged_in()) { header('Location: index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (attempt_login($_POST['user'] ?? '', $_POST['pass'] ?? '')) {
    header('Location: index.php'); exit;
  }
  $err = 'Incorrect username or password.';
}
admin_head('Login');
?>
<div class="a-auth">
  <!-- Brand panel -->
  <aside class="a-auth-brand">
    <div class="a-auth-brand-top">
      <div class="a-auth-logo"><span class="a-side-mark">S</span>
        <span><strong>Srinivasulu IFS</strong><small>Content Management System</small></span>
      </div>
    </div>
    <div class="a-auth-brand-mid">
      <h2>Manage your website,<br>beautifully.</h2>
      <ul class="a-auth-feats">
        <li><span>🎬</span> Videos, audios &amp; photos</li>
        <li><span>📄</span> Publications &amp; accomplishments</li>
        <li><span>🏆</span> Honours &amp; awards</li>
        <li><span>📝</span> Blog posts with instant publishing</li>
      </ul>
    </div>
    <div class="a-auth-brand-foot">Indian Forest Service · Government of Karnataka</div>
    <span class="a-auth-orb a-auth-orb-1"></span>
    <span class="a-auth-orb a-auth-orb-2"></span>
  </aside>

  <!-- Form panel -->
  <section class="a-auth-form">
    <div class="a-auth-card">
      <h1>Welcome back</h1>
      <p class="a-auth-sub">Sign in to the content manager.</p>
      <?php if ($err): ?><div class="flash flash-err"><?= h($err) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <label class="a-field">
          <span>Username</span>
          <span class="a-field-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
            <input name="user" required autofocus placeholder="admin">
          </span>
        </label>
        <label class="a-field">
          <span>Password</span>
          <span class="a-field-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
            <input name="pass" type="password" id="pass" required placeholder="••••••••">
            <button type="button" class="a-eye" onclick="var p=document.getElementById('pass');p.type=p.type==='password'?'text':'password';this.classList.toggle('is-on')" aria-label="Show password">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </span>
        </label>
        <button class="a-auth-btn" type="submit">Sign in →</button>
      </form>
      <p class="a-auth-note">Protected area · authorised staff only</p>
    </div>
  </section>
</div>
<?php admin_foot(); ?>

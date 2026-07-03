<?php
/* Generates a static posts/<slug>.html page consistent with the site.
   Works on both Cloudflare and GoDaddy (plain static file). */

function post_body_from_text(string $text): string {
  // Convert plain-text (double newline = paragraph) to safe HTML paragraphs.
  $text = trim($text);
  if ($text === '') return '';
  $blocks = preg_split('/\n\s*\n/', $text);
  $out = '';
  foreach ($blocks as $b) {
    $b = trim($b);
    if ($b === '') continue;
    $b = htmlspecialchars($b, ENT_QUOTES, 'UTF-8');
    $b = nl2br($b);
    $out .= "<p>$b</p>\n";
  }
  return $out;
}

/* $post: slug,title,bodyHtml,cover,pdf,pdfName,date  → writes file, returns bool */
function generate_post_page(string $postsDir, array $post): bool {
  $slug  = $post['slug'];
  $title = htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8');
  $rawTitle = $post['title'] ?? '';
  $body  = $post['bodyHtml'] ?? '';
  $cover = $post['cover'] ?? '';
  $pdf   = $post['pdf'] ?? '';
  $pdfName = $post['pdfName'] ?? 'Document.pdf';
  $dateISO = $post['date'] ?? date('c');
  $dateNice = date('j M Y', strtotime($dateISO));

  $coverHtml = $cover
    ? '<section class="post-cover"><div class="container"><img src="/' . ltrim(htmlspecialchars($cover), '/') . '" alt="' . $title . '"></div></section>'
    : '';
  $pdfHtml = $pdf
    ? '<a class="post-file" href="/' . ltrim(htmlspecialchars($pdf), '/') . '" download="' . htmlspecialchars($pdfName) . '" target="_blank">📄 ' . htmlspecialchars($pdfName) . '</a>'
    : '';

  $html = <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{$title} — Srinivasulu IFS</title>
  <meta name="description" content="{$title}">
  <meta property="og:title" content="{$title}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=Noto+Sans+Kannada:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../design-system.css">
  <style>
    .post-meta-strip{ border-bottom:1px solid var(--line); }
    .post-meta-strip .container{ display:flex; align-items:center; gap:16px; height:52px; }
    .post-meta-strip a{ display:inline-flex; align-items:center; gap:8px; color:var(--v-emerald,#14532d); font-weight:600; font-size:14px; text-decoration:none; }
    .post-cover{ padding:var(--s-6) 0 0; }
    .post-cover img{ width:100%; max-height:460px; object-fit:cover; border-radius:12px; }
    .post-header{ padding:var(--s-6) 0 var(--s-2); }
    .post-header h1{ font-size:clamp(28px,4vw,44px); line-height:1.15; }
    .post-date{ color:var(--ink-3); font-size:14px; margin-top:8px; }
    .post-body{ padding:var(--s-4) 0 var(--s-10); }
    .post-body-inner{ max-width:760px; margin:0 auto; }
    .post-body p{ font-size:17px; line-height:1.75; margin:0 0 16px; }
    .post-body .post-file{ display:inline-flex; align-items:center; gap:10px; padding:12px 18px; margin:8px 0; background:var(--paper-2); border:1px solid rgba(14,46,31,.1); border-radius:10px; color:var(--v-emerald,#14532d); font-weight:600; text-decoration:none; }
    .post-body .post-file:hover{ border-color:var(--v-gold,#c9a84c); }
  </style>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div id="sr-chrome-top"></div>

<main id="main">
  <div class="post-meta-strip"><div class="container"><a href="../blog.html">← Back to Blog</a></div></div>
  {$coverHtml}
  <header class="post-header"><div class="container"><h1>{$title}</h1><div class="post-date">{$dateNice}</div></div></header>
  <section class="post-body"><div class="container"><div class="post-body-inner">
    {$body}
    {$pdfHtml}
  </div></div></section>
</main>

<div id="sr-chrome-footer"></div>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="../chrome.js"></script>
<script src="../app.js"></script>
</body>
</html>

HTML;

  if (!is_dir($postsDir)) @mkdir($postsDir, 0775, true);
  $tmp = $postsDir . '/' . $slug . '.html.tmp';
  if (file_put_contents($tmp, $html) === false) return false;
  return rename($tmp, $postsDir . '/' . $slug . '.html');
}

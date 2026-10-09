<?php
/**
 * OpenBlog - 空白布局（登录页等）
 *
 * @var string $content
 * @var string $title
 */
$siteName = $siteName ?? setting('site_name', 'OpenBlog');
$flashError = \App\Core\Session::getFlash('error');
$flashSuccess = \App\Core\Session::getFlash('success');
?>
<!doctype html>
<html lang="zh-CN" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · <?= e($siteName) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
<style>:root{--accent:<?= e(setting('accent_color', '#4f46e5')) ?>}</style>
</head>
<body class="blank-body">
  <div class="auth-shell">
    <div class="auth-side">
      <span class="brand-mark brand-mark--lg">OB</span>
      <h1><?= e($siteName) ?></h1>
      <p>零框架依赖的纯 PHP 博客系统</p>
      <ul class="auth-features">
        <li>轻量、快速、无需 Composer</li>
        <li>Markdown 写作与代码高亮</li>
        <li>完整的后台与评论审核</li>
      </ul>
    </div>
    <div class="auth-main">
      <?php if ($flashError): ?><div class="flash flash--error"><?= e($flashError) ?></div><?php endif; ?>
      <?php if ($flashSuccess): ?><div class="flash flash--success"><?= e($flashSuccess) ?></div><?php endif; ?>
      <?= $content ?>
    </div>
  </div>
</body>
</html>

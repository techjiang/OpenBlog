<?php
/**
 * OpenBlog - 前台主布局
 *
 * @var string $content
 * @var string $title
 */
$siteName    = $siteName    ?? setting('site_name', 'OpenBlog');
$siteTagline = $siteTagline ?? setting('site_tagline', '');
$description = $description ?? setting('site_description', '');
$keywords    = $keywords    ?? setting('site_keywords', '');
$logo        = setting('site_logo', '');
$accent      = setting('accent_color', '#4f46e5');
$categories  = $categories  ?? \App\Models\Category::withCount();
$tagsCloud   = $tags        ?? \App\Models\Tag::cloud(24);
$popularList = $popular     ?? \App\Models\Post::popular(5);
$latestList  = $latest      ?? \App\Models\Post::latest(5);
$archives    = \App\Models\Post::archives();
$flashSuccess = \App\Core\Session::getFlash('success');
$flashError   = \App\Core\Session::getFlash('error');
$flashInfo    = \App\Core\Session::getFlash('info');
$errorsBag    = \App\Core\Session::getFlash('errors') ?? [];
?>
<!doctype html>
<html lang="zh-CN" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · <?= e($siteName) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="keywords" content="<?= e($keywords) ?>">
<meta name="generator" content="OpenBlog">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:type" content="website">
<link rel="alternate" type="application/rss+xml" title="<?= e($siteName) ?> RSS" href="<?= e(url('feed.xml')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<style>:root{--accent:<?= e($accent) ?>;--accent-soft:<?= e($accent) ?>1a}</style>
<?= \App\Core\Security::meta() ?>
<script>window.OB_BASE = <?= json_encode(rtrim(url('/'), '/'), JSON_UNESCAPED_SLASHES) ?>;</script>
<script>
  (function () {
    var t = localStorage.getItem('ob-theme');
    if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  })();
</script>
</head>
<body>
<a class="skip-link" href="#main">跳到主要内容</a>

<header class="site-header" id="siteHeader">
  <div class="container header-inner">
    <a class="brand" href="<?= e(url('/')) ?>">
      <?php if ($logo !== ''): ?>
        <img src="<?= e(url($logo)) ?>" alt="<?= e($siteName) ?>" class="brand-logo">
      <?php else: ?>
        <span class="brand-mark">OB</span>
      <?php endif; ?>
      <span class="brand-text">
        <strong><?= e($siteName) ?></strong>
        <?php if ($siteTagline !== ''): ?><em><?= e($siteTagline) ?></em><?php endif; ?>
      </span>
    </a>

    <nav class="main-nav" id="mainNav">
      <a href="<?= e(url('/')) ?>" class="<?= is_active('/') ?>">首页</a>
      <a href="<?= e(url('archive')) ?>" class="<?= is_active('/archive') ?>">归档</a>
      <a href="<?= e(url('topics')) ?>" class="<?= is_active('/topics') ?>">分类</a>
      <a href="<?= e(url('search')) ?>" class="<?= is_active('/search') ?>">搜索</a>
      <?php foreach ($categories as $cat): ?>
        <?php if ((int)$cat['real_count'] > 0): ?>
          <a href="<?= e(url('category/' . $cat['slug'])) ?>" class="<?= is_active('/category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

    <div class="header-actions">
      <form class="header-search" action="<?= e(url('search')) ?>" method="get" role="search">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
        <input type="search" name="q" placeholder="搜索文章…" aria-label="搜索文章" value="<?= e((string)($_GET['q'] ?? '')) ?>">
      </form>
      <button class="icon-btn" id="themeToggle" type="button" aria-label="切换主题" title="切换深浅色">
        <svg class="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M19.1 4.9l-1.8 1.8M6.7 17.3l-1.8 1.8"/></svg>
        <svg class="icon-moon" viewBox="0 0 24 24"><path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a6.8 6.8 0 0 0 10.5 10.5z"/></svg>
      </button>
      <button class="icon-btn nav-toggle" id="navToggle" type="button" aria-label="展开菜单">
        <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<?php if ($flashSuccess || $flashError || $flashInfo): ?>
<div class="container flash-wrap">
  <?php if ($flashSuccess): ?><div class="flash flash--success"><?= e($flashSuccess) ?></div><?php endif; ?>
  <?php if ($flashError): ?><div class="flash flash--error"><?= e($flashError) ?></div><?php endif; ?>
  <?php if ($flashInfo): ?><div class="flash flash--info"><?= e($flashInfo) ?></div><?php endif; ?>
</div>
<?php endif; ?>

<main id="main" class="site-main">
  <?= $content ?>
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-col">
      <a class="brand" href="<?= e(url('/')) ?>">
        <span class="brand-mark">OB</span>
        <span class="brand-text"><strong><?= e($siteName) ?></strong></span>
      </a>
      <p class="footer-desc"><?= e($siteTagline) ?></p>
      <div class="social-links">
        <?php if (($g = setting('social_github')) !== ''): ?>
          <a href="<?= e($g) ?>" target="_blank" rel="noopener" aria-label="GitHub"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-3.16 19.49c.5.09.68-.22.68-.48v-1.7c-2.78.6-3.37-1.34-3.37-1.34-.45-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.61.07-.61 1 .07 1.53 1.03 1.53 1.03.89 1.53 2.34 1.09 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.56-1.11-4.56-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.65 0 0 .84-.27 2.75 1.02a9.5 9.5 0 0 1 5 0c1.91-1.29 2.75-1.02 2.75-1.02.55 1.38.2 2.4.1 2.65.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.69-4.57 4.94.36.31.68.92.68 1.85v2.74c0 .27.18.58.69.48A10 10 0 0 0 12 2z"/></svg></a>
        <?php endif; ?>
        <?php if (($t = setting('social_twitter')) !== ''): ?>
          <a href="<?= e($t) ?>" target="_blank" rel="noopener" aria-label="Twitter"><svg viewBox="0 0 24 24"><path d="M22 5.9c-.7.3-1.5.5-2.3.6a4 4 0 0 0 1.8-2.2c-.8.5-1.7.8-2.6 1a4 4 0 0 0-6.9 3.7A11.4 11.4 0 0 1 3.4 4.6a4 4 0 0 0 1.2 5.4c-.7 0-1.3-.2-1.9-.5a4 4 0 0 0 3.2 4 4 4 0 0 1-1.8.1 4 4 0 0 0 3.7 2.8A8 8 0 0 1 2 18a11.3 11.3 0 0 0 6.1 1.8c7.5 0 11.7-6.3 11.4-12 .8-.6 1.5-1.3 2-2.1z"/></svg></a>
        <?php endif; ?>
        <?php if (($em = setting('social_email')) !== ''): ?>
          <a href="mailto:<?= e($em) ?>" aria-label="Email"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></a>
        <?php endif; ?>
        <a href="<?= e(url('feed.xml')) ?>" aria-label="RSS"><svg viewBox="0 0 24 24"><circle cx="6.5" cy="17.5" r="2"/><path d="M4 11a9 9 0 0 1 9 9M4 5a15 15 0 0 1 15 15"/></svg></a>
      </div>
    </div>

    <div class="footer-col">
      <h4>分类</h4>
      <ul class="footer-list">
        <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
          <li><a href="<?= e(url('category/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="footer-col">
      <h4>最新文章</h4>
      <ul class="footer-list">
        <?php foreach ($latestList as $lp): ?>
          <li><a href="<?= e(url('post/' . $lp['slug'])) ?>"><?= e(str_limit((string)$lp['title'], 26)) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="footer-col">
      <h4>订阅更新</h4>
      <p class="footer-desc">新文章发布时第一时间通知你。</p>
      <form class="subscribe-form" action="<?= e(url('newsletter')) ?>" method="post">
        <?= csrf_field() ?>
        <input type="email" name="email" placeholder="you@example.com" required aria-label="邮箱">
        <button type="submit">订阅</button>
      </form>
    </div>
  </div>

  <div class="container footer-bottom">
    <span><?= e(setting('footer_text', 'Powered by OpenBlog')) ?> · © <?= date('Y') ?> <?= e($siteName) ?></span>
    <?php if (($icp = setting('site_icp')) !== ''): ?><span><?= e($icp) ?></span><?php endif; ?>
    <span><a href="<?= e(url('sitemap.xml')) ?>">站点地图</a> · <a href="<?= e(url('feed.xml')) ?>">RSS</a></span>
  </div>
</footer>

<?php if (($stat = setting('stat_code')) !== ''): ?><?= $stat ?><?php endif; ?>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>

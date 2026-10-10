<?php
/**
 * OpenBlog - 后台布局
 *
 * @var string $content
 * @var string $title
 * @var array $user
 */
$user       = $user ?? \App\Core\Auth::user() ?? [];
$siteName   = $siteName ?? setting('site_name', 'OpenBlog');
$stats      = $stats ?? [];
$pending    = $pendingCount ?? 0;
$currentUri = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

$menu = [
    ['group' => '概览'],
    ['url' => 'admin',            'label' => '仪表盘',  'icon' => 'dashboard'],
    ['group' => '内容'],
    ['url' => 'admin/posts',      'label' => '文章',    'icon' => 'post'],
    ['url' => 'admin/pages',      'label' => '页面',    'icon' => 'page'],
    ['url' => 'admin/categories', 'label' => '分类',    'icon' => 'folder'],
    ['url' => 'admin/tags',       'label' => '标签',    'icon' => 'tag'],
    ['url' => 'admin/comments',   'label' => '评论',    'icon' => 'comment', 'badge' => $pending],
    ['url' => 'admin/media',      'label' => '媒体库',  'icon' => 'image'],
    ['group' => '系统'],
    ['url' => 'admin/users',      'label' => '用户',    'icon' => 'user'],
    ['url' => 'admin/settings',   'label' => '设置',    'icon' => 'setting'],
];

$icons = [
    'dashboard' => '<path d="M4 13h6V4H4zM14 20h6v-9h-6zM4 20h6v-4H4zM14 8h6V4h-6z"/>',
    'post'      => '<path d="M5 4h11l3 3v13H5z"/><path d="M8 12h8M8 16h8M8 8h5"/>',
    'page'      => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/>',
    'folder'    => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
    'tag'       => '<path d="M3 11V5a2 2 0 0 1 2-2h6l9 9-8 8z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
    'comment'   => '<path d="M20 12a8 8 0 0 1-8 8H4l2-3a8 8 0 1 1 14-5z"/>',
    'image'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.6"/><path d="M21 16l-5-5-9 9"/>',
    'user'      => '<circle cx="12" cy="8" r="3.6"/><path d="M5 20c1.5-3.5 4-5 7-5s5.5 1.5 7 5"/>',
    'setting'   => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9L17 7M7 17l-2.1 2.1"/>',
];

$flashSuccess = \App\Core\Session::getFlash('success');
$flashError   = \App\Core\Session::getFlash('error');
?>
<!doctype html>
<html lang="zh-CN" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · <?= e($siteName) ?> 后台</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
<style>:root{--accent:<?= e(setting('accent_color', '#4f46e5')) ?>}</style>
<?= \App\Core\Security::meta() ?>
<script>
  window.OB_BASE = <?= json_encode(rtrim(url('/'), '/'), JSON_UNESCAPED_SLASHES) ?>;
  window.OB_ADMIN = <?= json_encode(rtrim(url('/'), '/'), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script>
  (function () {
    var t = localStorage.getItem('ob-theme');
    if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  })();
</script>
</head>
<body class="admin-body">

<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-brand">
    <span class="brand-mark">OB</span>
    <div>
      <strong><?= e($siteName) ?></strong>
      <em>管理后台</em>
    </div>
  </div>

  <nav class="admin-nav">
    <?php foreach ($menu as $item): ?>
      <?php if (isset($item['group'])): ?>
        <div class="admin-nav__group"><?= e($item['group']) ?></div>
      <?php else:
        $href = url($item['url']);
        $active = str_starts_with($currentUri, '/' . $item['url']) && ($item['url'] !== 'admin' || $currentUri === '/admin' || $currentUri === '/admin/');
        if ($item['url'] === 'admin') {
            $active = rtrim($currentUri, '/') === '/admin';
        }
      ?>
        <a href="<?= e($href) ?>" class="admin-nav__item <?= $active ? 'is-active' : '' ?>">
          <svg viewBox="0 0 24 24"><?= $icons[$item['icon']] ?? '' ?></svg>
          <span><?= e($item['label']) ?></span>
          <?php if (!empty($item['badge'])): ?><i class="badge"><?= (int)$item['badge'] ?></i><?php endif; ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="admin-sidebar__foot">
    <a href="<?= e(url('/')) ?>" target="_blank" class="admin-nav__item">
      <svg viewBox="0 0 24 24"><path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
      <span>查看站点</span>
    </a>
  </div>
</aside>

<div class="admin-main">
<header class="admin-topbar">
  <button class="icon-btn admin-burger" id="adminBurger" type="button" aria-label="菜单">
    <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
  </button>

  <div class="admin-topbar__title"><?= e($title) ?></div>

  <form class="topbar-search" action="<?= e(url('admin/posts')) ?>" method="get" role="search" style="margin-left:auto;margin-right:16px;max-width:320px;">
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
    <input type="search" name="q" placeholder="搜索文章…" aria-label="搜索文章" value="<?= e((string)($_GET['q'] ?? '')) ?>">
  </form>

  <div class="admin-topbar__actions">
    <button id="themeToggle" class="icon-btn" type="button" aria-label="切换明暗主题" title="切换明暗主题">
      <svg class="icon-moon" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
      <svg class="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4.5"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
    </button>
    <a class="btn btn--primary btn--sm" href="<?= e(url('admin/posts/create')) ?>">
      <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> 写文章
    </a>

    <div class="admin-user" id="adminUserMenu">
      <button class="admin-user__btn" type="button">
        <span class="avatar-fallback"><?= e(mb_substr((string)($user['display_name'] ?: $user['username'] ?? 'A'), 0, 1)) ?></span>
        <span class="admin-user__name"><?= e($user['display_name'] ?: $user['username'] ?? '') ?></span>
        <svg viewBox="0 0 24 24" class="caret"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <div class="admin-dropdown">
        <a href="<?= e(url('/')) ?>" target="_blank">查看站点</a>
        <a href="<?= e(url('admin/settings')) ?>">站点设置</a>
        <a href="<?= e(url('admin/logout')) ?>" class="is-danger">退出登录</a>
      </div>
    </div>
  </div>
</header>

  <div class="admin-content">
    <?php if ($flashSuccess): ?><div class="flash flash--success"><?= e($flashSuccess) ?></div><?php endif; ?>
    <?php if ($flashError): ?><div class="flash flash--error"><?= e($flashError) ?></div><?php endif; ?>
    <?php $bag = \App\Core\Session::getFlash('errors') ?? []; ?>
    <?php if ($bag !== []): ?>
      <div class="flash flash--error">
        <?php foreach ($bag as $field => $msgs): ?>
          <?php foreach ($msgs as $msg): ?><div><?= e($msg) ?></div><?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </div>
</div>

<div class="admin-backdrop" id="adminBackdrop"></div>

<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>

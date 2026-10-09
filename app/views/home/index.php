<?php
/**
 * OpenBlog - 首页
 *
 * @var array $posts
 * @var \App\Core\Paginator|null $paginator
 * @var array|null $hero
 */
$totalPosts = $paginator?->total ?? count($posts);
?>
<section class="hero">
  <div class="container">
    <div class="hero-inner">
      <div class="hero-copy">
        <span class="hero-badge">OpenBlog · 纯 PHP 博客系统</span>
        <h1><?= e(setting('site_name', 'OpenBlog')) ?></h1>
        <p><?= e(setting('site_tagline', '记录、分享与沉淀')) ?></p>
        <div class="hero-meta">
          <span><b><?= (int)$totalPosts ?></b> 篇文章</span>
          <span><b><?= count($categories ?? []) ?></b> 个分类</span>
          <span><b><?= count($tags ?? []) ?></b> 个标签</span>
        </div>
        <div class="hero-actions">
          <a class="btn btn--primary" href="<?= e(url('archive')) ?>">浏览全部文章</a>
          <a class="btn btn--ghost" href="<?= e(url('feed.xml')) ?>">订阅 RSS</a>
        </div>
      </div>
      <div class="hero-art" aria-hidden="true">
        <div class="orb orb--1"></div>
        <div class="orb orb--2"></div>
        <div class="orb orb--3"></div>
      </div>
    </div>
  </div>
</section>

<?php if ($hero !== null): ?>
<section class="container">
  <a class="featured-card" href="<?= e(url('post/' . $hero['slug'])) ?>">
    <?php if (!empty($hero['cover_image'])): ?>
      <div class="featured-cover" style="background-image:url('<?= e(url($hero['cover_image'])) ?>')"></div>
    <?php else: ?>
      <div class="featured-cover featured-cover--placeholder">
        <span><?= e(mb_substr((string)$hero['title'], 0, 2)) ?></span>
      </div>
    <?php endif; ?>
    <div class="featured-body">
      <span class="tag-pill">精选</span>
      <h2><?= e($hero['title']) ?></h2>
      <p><?= e(str_limit((string)($hero['excerpt'] ?? ''), 120)) ?></p>
      <div class="card-meta">
        <span><?= e($hero['author_name'] ?? $hero['author_username'] ?? '匿名') ?></span>
        <span>·</span>
        <span><?= e(human_date($hero['published_at'], 'Y 年 m 月 d 日')) ?></span>
        <span>·</span>
        <span><?= (int)$hero['reading_time'] ?> 分钟阅读</span>
      </div>
    </div>
  </a>
</section>
<?php endif; ?>

<section class="container main-layout">
  <div class="content-column">
    <div class="section-head">
      <h2>最新文章</h2>
      <?php if (($page ?? 1) > 1): ?><span class="section-sub">第 <?= (int)$page ?> 页</span><?php endif; ?>
    </div>

    <?php if ($posts === []): ?>
      <div class="empty-state">
        <h3>还没有文章</h3>
        <p>前往后台创建第一篇文章吧。</p>
        <a class="btn btn--primary" href="<?= e(url('admin/posts/create')) ?>">写文章</a>
      </div>
    <?php endif; ?>

    <div class="post-list">
      <?php foreach ($posts as $post): ?>
        <article class="post-card">
          <a class="post-card__cover" href="<?= e(url('post/' . $post['slug'])) ?>" aria-hidden="true" tabindex="-1">
            <?php if (!empty($post['cover_image'])): ?>
              <img src="<?= e(url($post['cover_image'])) ?>" alt="" loading="lazy">
            <?php else: ?>
              <span class="cover-initial"><?= e(mb_substr((string)$post['title'], 0, 1)) ?></span>
            <?php endif; ?>
          </a>
          <div class="post-card__body">
            <div class="post-card__top">
              <?php if (!empty($post['category_name'])): ?>
                <a class="chip chip--cat" style="--chip:<?= e($post['category_color'] ?: '#4f46e5') ?>"
                   href="<?= e(url('category/' . $post['category_slug'])) ?>"><?= e($post['category_name']) ?></a>
              <?php endif; ?>
              <time datetime="<?= e((string)$post['published_at']) ?>"><?= e(human_date($post['published_at'])) ?></time>
            </div>

            <h3 class="post-card__title">
              <a href="<?= e(url('post/' . $post['slug'])) ?>"><?= e($post['title']) ?></a>
            </h3>

            <p class="post-card__excerpt"><?= e(str_limit((string)($post['excerpt'] ?? ''), 110)) ?></p>

            <div class="post-card__foot">
              <span class="author">
                <?php if (!empty($post['author_avatar'])): ?>
                  <img src="<?= e(url($post['author_avatar'])) ?>" alt="" loading="lazy">
                <?php else: ?>
                  <span class="avatar-fallback"><?= e(mb_substr((string)($post['author_name'] ?: $post['author_username'] ?: 'A'), 0, 1)) ?></span>
                <?php endif; ?>
                <?= e($post['author_name'] ?: $post['author_username'] ?: '匿名') ?>
              </span>
              <span class="dot">·</span>
              <span><?= (int)$post['reading_time'] ?> 分钟</span>
              <span class="dot">·</span>
              <span><?= number_short((int)$post['view_count']) ?> 阅读</span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <?= $paginator?->render() ?? '' ?>
  </div>

  <?= partial('partials/sidebar', [
      'categories' => $categories ?? [],
      'tags'       => $tags ?? [],
      'popular'    => $popular ?? [],
      'latest'     => $latest ?? [],
  ]) ?>
</section>

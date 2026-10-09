<?php
/**
 * OpenBlog - 搜索结果
 *
 * @var string $keyword
 * @var array $posts
 * @var int $total
 */
?>
<section class="container main-layout">
  <div class="content-column">
    <header class="page-header">
      <h1>搜索</h1>
      <p class="page-sub">输入关键词查找文章标题与正文。</p>
    </header>

    <form class="search-box" action="<?= e(url('search')) ?>" method="get">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
      <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="例如：PHP、设计、部署…" autofocus>
      <button type="submit" class="btn btn--primary">搜索</button>
    </form>

    <?php if ($keyword !== ''): ?>
      <p class="search-result-count">
        <?php if ($total > 0): ?>
          找到 <b><?= (int)$total ?></b> 篇与 “<?= e($keyword) ?>” 相关的文章
        <?php else: ?>
          没有找到与 “<?= e($keyword) ?>” 相关的文章
        <?php endif; ?>
      </p>

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
                <time><?= e(human_date($post['published_at'])) ?></time>
              </div>
              <h3 class="post-card__title"><a href="<?= e(url('post/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
              <p class="post-card__excerpt"><?= e(str_limit((string)($post['excerpt'] ?? ''), 120)) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?= $paginator?->render() ?? '' ?>
    <?php endif; ?>
  </div>

  <?= partial('partials/sidebar', [
      'categories' => $categories ?? [],
      'tags'       => $tags ?? [],
      'popular'    => $popular ?? [],
      'latest'     => $latest ?? [],
  ]) ?>
</section>

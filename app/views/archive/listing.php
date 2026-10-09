<?php
/**
 * OpenBlog - 通用列表页（分类 / 标签 / 作者 / 归档）
 *
 * @var string $heading
 * @var string $subtitle
 * @var array $posts
 * @var \App\Core\Paginator|null $paginator
 */
?>
<section class="container main-layout">
  <div class="content-column">
    <header class="page-header">
      <nav class="breadcrumb">
        <a href="<?= e(url('/')) ?>">首页</a><span>/</span><span><?= e($heading) ?></span>
      </nav>
      <h1><?= e($heading) ?></h1>
      <?php if (!empty($subtitle)): ?><p class="page-sub"><?= e($subtitle) ?></p><?php endif; ?>
    </header>

    <?php if ($posts === []): ?>
      <div class="empty-state">
        <h3>这里还没有文章</h3>
        <p>换一个分类或标签看看吧。</p>
        <a class="btn btn--primary" href="<?= e(url('archive')) ?>">查看全部归档</a>
      </div>
    <?php else: ?>
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
              <h3 class="post-card__title"><a href="<?= e(url('post/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
              <p class="post-card__excerpt"><?= e(str_limit((string)($post['excerpt'] ?? ''), 110)) ?></p>
              <div class="post-card__foot">
                <span><?= (int)$post['reading_time'] ?> 分钟</span>
                <span class="dot">·</span>
                <span><?= number_short((int)$post['view_count']) ?> 阅读</span>
              </div>
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

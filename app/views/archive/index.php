<?php
/**
 * OpenBlog - 归档总览
 *
 * @var array $grouped
 * @var int $total
 */
?>
<section class="container main-layout">
  <div class="content-column">
    <header class="page-header">
      <nav class="breadcrumb"><a href="<?= e(url('/')) ?>">首页</a><span>/</span><span>归档</span></nav>
      <h1>文章归档</h1>
      <p class="page-sub">共 <?= (int)$total ?> 篇文章，按时间倒序排列。</p>
    </header>

    <?php if ($grouped === []): ?>
      <div class="empty-state"><h3>还没有发布任何文章</h3></div>
    <?php endif; ?>

    <?php foreach ($grouped as $year => $months): ?>
      <div class="archive-group">
        <h2 class="archive-group__year"><?= e($year) ?></h2>
        <?php foreach ($months as $m): ?>
          <?php
          $list = \App\Models\Post::paginateByMonth($year . '-' . $m['month'], 1, 50);
          ?>
          <div class="archive-month">
            <h3>
              <a href="<?= e(url('archive/' . $year . '/' . $m['month'])) ?>"><?= (int)$m['month'] ?> 月</a>
              <span class="count"><?= (int)$m['total'] ?> 篇</span>
            </h3>
            <ul class="archive-posts">
              <?php foreach ($list['items'] as $p): ?>
                <li>
                  <time><?= e(human_date($p['published_at'], 'm-d')) ?></time>
                  <a href="<?= e(url('post/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?= partial('partials/sidebar', [
      'categories' => $categories ?? [],
      'tags'       => $tags ?? [],
      'popular'    => $popular ?? [],
      'latest'     => $latest ?? [],
  ]) ?>
</section>

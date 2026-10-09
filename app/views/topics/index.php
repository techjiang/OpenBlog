<?php
/**
 * OpenBlog - 分类与标签总览
 *
 * @var array $categoryList
 * @var array $tagList
 */
?>
<section class="container main-layout">
  <div class="content-column">
    <header class="page-header">
      <nav class="breadcrumb"><a href="<?= e(url('/')) ?>">首页</a><span>/</span><span>分类与标签</span></nav>
      <h1>全部分类与标签</h1>
      <p class="page-sub">共 <?= count($categoryList) ?> 个分类、<?= count($tagList) ?> 个标签。</p>
    </header>

    <h2 class="block-title">分类</h2>
    <div class="topic-grid">
      <?php foreach ($categoryList as $cat): ?>
        <a class="topic-card" href="<?= e(url('category/' . $cat['slug'])) ?>" style="--chip:<?= e($cat['color'] ?: '#4f46e5') ?>">
          <span class="topic-card__icon"><?= e(mb_substr((string)$cat['name'], 0, 1)) ?></span>
          <span class="topic-card__name"><?= e($cat['name']) ?></span>
          <span class="topic-card__desc"><?= e($cat['description'] ?? '') ?></span>
          <span class="topic-card__count"><?= (int)$cat['real_count'] ?> 篇</span>
        </a>
      <?php endforeach; ?>
      <?php if ($categoryList === []): ?><p class="widget-empty">暂无分类</p><?php endif; ?>
    </div>

    <h2 class="block-title">标签</h2>
    <div class="tag-cloud tag-cloud--lg">
      <?php foreach ($tagList as $tag): ?>
        <a href="<?= e(url('tag/' . $tag['slug'])) ?>" style="--sz:<?= min(1.5, 0.9 + ((int)$tag['real_count'] * 0.08)) ?>em">
          #<?= e($tag['name']) ?><i><?= (int)$tag['real_count'] ?></i>
        </a>
      <?php endforeach; ?>
      <?php if ($tagList === []): ?><p class="widget-empty">暂无标签</p><?php endif; ?>
    </div>
  </div>

  <?= partial('partials/sidebar', [
      'categories' => $categories ?? [],
      'tags'       => $tags ?? [],
      'popular'    => $popular ?? [],
      'latest'     => $latest ?? [],
  ]) ?>
</section>

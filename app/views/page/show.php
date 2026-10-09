<?php
/**
 * OpenBlog - 独立页面
 *
 * @var array $page
 * @var string $html
 */
?>
<section class="container page-single">
  <header class="page-header">
    <nav class="breadcrumb"><a href="<?= e(url('/')) ?>">首页</a><span>/</span><span><?= e($page['title']) ?></span></nav>
    <h1><?= e($page['title']) ?></h1>
  </header>

  <?php if (!empty($page['cover_image'])): ?>
    <img class="page-cover" src="<?= e(url($page['cover_image'])) ?>" alt="<?= e($page['title']) ?>">
  <?php endif; ?>

  <article class="prose">
    <?= $html ?>
  </article>
</section>

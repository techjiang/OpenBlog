<?php
/**
 * OpenBlog - 404 页面
 *
 * @var string $title
 * @var string $message
 */
?>
<section class="container error-page">
  <div class="error-code">404</div>
  <h1><?= e($title ?? '页面不存在') ?></h1>
  <p><?= e($message ?? '你访问的地址可能已被移动或删除。') ?></p>
  <div class="error-actions">
    <a class="btn btn--primary" href="<?= e(url('/')) ?>">回到首页</a>
    <a class="btn btn--ghost" href="<?= e(url('archive')) ?>">浏览归档</a>
  </div>
</section>

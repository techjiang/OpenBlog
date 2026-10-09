<?php
/**
 * OpenBlog - 前台侧边栏
 */
$archives = \App\Models\Post::archives();

$byYear = [];
foreach ($archives as $row) {
    $byYear[$row['year']][] = $row;
}
?>
<aside class="sidebar">
  <?php if (\App\Core\Auth::check()): ?>
  <div class="widget widget--quick">
    <h4>管理</h4>
    <a class="btn btn--primary btn--block" href="<?= e(url('admin/posts/create')) ?>">写新文章</a>
    <a class="btn btn--ghost btn--block" href="<?= e(url('admin')) ?>">进入后台</a>
  </div>
  <?php endif; ?>

  <div class="widget">
    <h4>分类</h4>
    <ul class="widget-cats">
      <?php foreach (($categories ?? []) as $cat): ?>
        <li>
          <a href="<?= e(url('category/' . $cat['slug'])) ?>">
            <span class="cat-dot" style="background:<?= e($cat['color'] ?: '#4f46e5') ?>"></span>
            <span class="cat-name"><?= e($cat['name']) ?></span>
            <span class="cat-count"><?= (int)$cat['real_count'] ?></span>
          </a>
        </li>
      <?php endforeach; ?>
      <?php if (($categories ?? []) === []): ?><li class="widget-empty">暂无分类</li><?php endif; ?>
    </ul>
  </div>

  <div class="widget">
    <h4>标签云</h4>
    <div class="tag-cloud">
      <?php foreach (($tags ?? []) as $tag): ?>
        <a href="<?= e(url('tag/' . $tag['slug'])) ?>" style="--sz:<?= min(1.4, 0.85 + ((int)$tag['real_count'] * 0.06)) ?>em">#<?= e($tag['name']) ?></a>
      <?php endforeach; ?>
      <?php if (($tags ?? []) === []): ?><span class="widget-empty">暂无标签</span><?php endif; ?>
    </div>
  </div>

  <div class="widget">
    <h4>热门文章</h4>
    <ol class="widget-list">
      <?php foreach (($popular ?? []) as $i => $p): ?>
        <li>
          <span class="rank"><?= $i + 1 ?></span>
          <a href="<?= e(url('post/' . $p['slug'])) ?>"><?= e(str_limit((string)$p['title'], 30)) ?></a>
          <span class="views"><?= number_short((int)$p['view_count']) ?></span>
        </li>
      <?php endforeach; ?>
      <?php if (($popular ?? []) === []): ?><li class="widget-empty">暂无数据</li><?php endif; ?>
    </ol>
  </div>

  <?php if ($byYear !== []): ?>
  <div class="widget">
    <h4>归档</h4>
    <div class="widget-archive">
      <?php foreach ($byYear as $year => $months): ?>
        <div class="archive-year">
          <span class="year"><?= e($year) ?></span>
          <div class="months">
            <?php foreach ($months as $m): ?>
              <a href="<?= e(url('archive/' . $year . '/' . $m['month'])) ?>"><?= (int)$m['month'] ?>月<span>(<?= (int)$m['total'] ?>)</span></a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</aside>

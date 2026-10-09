<?php
/**
 * OpenBlog - 仪表盘
 *
 * @var array $stats
 * @var array $recentPosts
 * @var array $recentComments
 * @var array $monthly
 * @var array $popular
 */
$maxMonthly = max(1, max(array_column($monthly ?: [['total' => 1]], 'total')));
?>
<div class="stat-grid">
  <div class="stat-card">
    <span class="stat-card__label">文章总数</span>
    <b class="stat-card__value"><?= (int)$stats['posts'] ?></b>
    <span class="stat-card__hint"><?= (int)$stats['drafts'] ?> 篇草稿</span>
  </div>
  <div class="stat-card">
    <span class="stat-card__label">评论</span>
    <b class="stat-card__value"><?= (int)$stats['comments'] ?></b>
    <span class="stat-card__hint"><?= (int)$stats['pending'] ?> 条待审</span>
  </div>
  <div class="stat-card">
    <span class="stat-card__label">总浏览量</span>
    <b class="stat-card__value"><?= number_short((int)$stats['views']) ?></b>
    <span class="stat-card__hint">页面 <?= (int)$stats['pages'] ?> 个</span>
  </div>
  <div class="stat-card">
    <span class="stat-card__label">获赞</span>
    <b class="stat-card__value"><?= number_short((int)$stats['likes']) ?></b>
    <span class="stat-card__hint">累计点赞</span>
  </div>
</div>

<div class="panel-grid">
  <section class="panel">
    <div class="panel__head">
      <h3>最近文章</h3>
      <a class="link-more" href="<?= e(url('admin/posts')) ?>">全部 →</a>
    </div>
    <table class="table">
      <thead><tr><th>标题</th><th>状态</th><th>浏览</th><th>时间</th></tr></thead>
      <tbody>
      <?php foreach ($recentPosts as $p): ?>
        <tr>
          <td><a href="<?= e(url('admin/posts/edit/' . $p['id'])) ?>"><?= e(str_limit((string)$p['title'], 40)) ?></a></td>
          <td><span class="status-badge status--<?= e($p['status']) ?>"><?= e(status_label((string)$p['status'])) ?></span></td>
          <td><?= (int)$p['view_count'] ?></td>
          <td><?= e(human_date($p['created_at'], 'm-d H:i')) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($recentPosts === []): ?><tr><td colspan="4" class="table-empty">暂无文章</td></tr><?php endif; ?>
      </tbody>
    </table>
  </section>

  <section class="panel">
    <div class="panel__head">
      <h3>最近评论</h3>
      <a class="link-more" href="<?= e(url('admin/comments')) ?>">全部 →</a>
    </div>
    <ul class="mini-list">
      <?php foreach ($recentComments as $c): ?>
        <li>
          <div class="mini-list__top">
            <b><?= e($c['author_name']) ?></b>
            <span class="status-badge status--<?= e($c['status']) ?>"><?= e(comment_status_label((string)$c['status'])) ?></span>
            <time><?= e(time_ago($c['created_at'])) ?></time>
          </div>
          <p><?= e(str_limit((string)$c['content'], 70)) ?></p>
          <?php if (!empty($c['post_title'])): ?>
            <span class="mini-list__ref">来自：<?= e(str_limit((string)$c['post_title'], 30)) ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
      <?php if ($recentComments === []): ?><li class="table-empty">暂无评论</li><?php endif; ?>
    </ul>
  </section>
</div>

<section class="panel">
  <div class="panel__head"><h3>发布趋势（近 12 个月）</h3></div>
  <div class="chart">
    <?php foreach ($monthly as $m): ?>
      <div class="chart__col" title="<?= e($m['ym']) ?>：<?= (int)$m['total'] ?> 篇">
        <div class="chart__bar" style="height:<?= round(((int)$m['total'] / $maxMonthly) * 100) ?>%"></div>
        <span><?= e(substr((string)$m['ym'], 5)) ?></span>
      </div>
    <?php endforeach; ?>
    <?php if ($monthly === []): ?><p class="table-empty">暂无数据</p><?php endif; ?>
  </div>
</section>

<section class="panel">
  <div class="panel__head"><h3>热门文章</h3></div>
  <div class="bar-list">
    <?php $maxView = max(1, max(array_column($popular ?: [['view_count' => 1]], 'view_count'))); ?>
    <?php foreach ($popular as $p): ?>
      <div class="bar-row">
        <a href="<?= e(url('post/' . $p['slug'])) ?>" target="_blank"><?= e(str_limit((string)$p['title'], 44)) ?></a>
        <div class="bar-track"><div class="bar-fill" style="width:<?= round(((int)$p['view_count'] / $maxView) * 100) ?>%"></div></div>
        <span><?= number_short((int)$p['view_count']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</section>

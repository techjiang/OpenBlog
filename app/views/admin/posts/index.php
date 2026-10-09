<?php
/**
 * OpenBlog - 文章列表
 *
 * @var array $posts
 * @var int $total
 * @var \App\Core\Paginator $paginator
 * @var array $filters
 * @var array $categoryList
 */
?>
<div class="page-toolbar">
  <form class="filter-bar" method="get" action="<?= e(url('admin/posts')) ?>">
    <input type="search" name="keyword" placeholder="搜索标题或正文…" value="<?= e($filters['keyword'] ?? '') ?>">
    <select name="status">
      <option value="">全部状态</option>
      <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>已发布</option>
      <option value="draft"     <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>草稿</option>
      <option value="private"   <?= ($filters['status'] ?? '') === 'private' ? 'selected' : '' ?>>私密</option>
    </select>
    <select name="category_id">
      <option value="">全部分类</option>
      <?php foreach ($categoryList as $cat): ?>
        <option value="<?= (int)$cat['id'] ?>" <?= (string)($filters['category_id'] ?? '') === (string)$cat['id'] ? 'selected' : '' ?>>
          <?= e($cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn--ghost btn--sm" type="submit">筛选</button>
    <a class="btn btn--sm" href="<?= e(url('admin/posts')) ?>">重置</a>
  </form>

  <a class="btn btn--primary" href="<?= e(url('admin/posts/create')) ?>">
    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> 写文章
  </a>
</div>

<section class="panel">
  <div class="panel__head"><h3>共 <?= (int)$total ?> 篇文章</h3></div>

  <table class="table">
    <thead>
      <tr>
        <th style="width:38%">标题</th>
        <th>分类</th>
        <th>作者</th>
        <th>状态</th>
        <th>浏览</th>
        <th>评论</th>
        <th>更新时间</th>
        <th style="width:120px">操作</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
      <tr>
        <td>
          <a class="cell-title" href="<?= e(url('admin/posts/edit/' . $p['id'])) ?>"><?= e($p['title']) ?></a>
          <?php if ((int)$p['featured'] === 1): ?><span class="mini-tag">精选</span><?php endif; ?>
          <div class="cell-slug">/post/<?= e($p['slug']) ?></div>
        </td>
        <td>
          <?php if (!empty($p['category_name'])): ?>
            <span class="chip chip--cat" style="--chip:<?= e($p['category_color'] ?: '#4f46e5') ?>"><?= e($p['category_name']) ?></span>
          <?php else: ?><span class="muted">未分类</span><?php endif; ?>
        </td>
        <td><?= e($p['author_name'] ?: $p['author_username'] ?? '—') ?></td>
        <td><span class="status-badge status--<?= e($p['status']) ?>"><?= e(status_label((string)$p['status'])) ?></span></td>
        <td><?= (int)$p['view_count'] ?></td>
        <td><?= (int)$p['comment_count'] ?></td>
        <td><?= e(human_date($p['updated_at'], 'Y-m-d H:i')) ?></td>
        <td class="cell-actions">
          <a href="<?= e(url('admin/posts/edit/' . $p['id'])) ?>">编辑</a>
          <?php if ($p['status'] === 'published'): ?>
            <a href="<?= e(url('post/' . $p['slug'])) ?>" target="_blank">查看</a>
          <?php endif; ?>
          <form method="post" action="<?= e(url('admin/posts/delete/' . $p['id'])) ?>" class="inline-form"
                onsubmit="return confirm('确定删除《<?= e($p['title']) ?>》？此操作不可恢复。')">
            <?= csrf_field() ?>
            <button type="submit" class="link-danger">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if ($posts === []): ?><tr><td colspan="8" class="table-empty">没有符合条件的文章</td></tr><?php endif; ?>
    </tbody>
  </table>

  <?= $paginator->render() ?>
</section>

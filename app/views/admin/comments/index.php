<?php
/**
 * OpenBlog - 评论管理
 *
 * @var array $comments
 * @var int $total
 * @var \App\Core\Paginator $paginator
 * @var array $filters
 */
?>
<div class="page-toolbar">
  <form class="filter-bar" method="get" action="<?= e(url('admin/comments')) ?>">
    <select name="status">
      <option value="">全部状态</option>
      <option value="pending"  <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>待审核</option>
      <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>已通过</option>
      <option value="spam"     <?= ($filters['status'] ?? '') === 'spam' ? 'selected' : '' ?>>垃圾</option>
      <option value="trash"    <?= ($filters['status'] ?? '') === 'trash' ? 'selected' : '' ?>>回收站</option>
    </select>
    <input type="search" name="keyword" placeholder="搜索内容或昵称…" value="<?= e($filters['keyword'] ?? '') ?>">
    <button class="btn btn--ghost btn--sm" type="submit">筛选</button>
  </form>
</div>

<section class="panel">
  <div class="panel__head"><h3>共 <?= (int)$total ?> 条评论</h3></div>

  <?php if ($comments === []): ?>
    <p class="table-empty">没有符合条件的评论</p>
  <?php endif; ?>

  <ul class="comment-admin-list">
  <?php foreach ($comments as $c): ?>
    <li class="comment-admin">
      <div class="comment-admin__avatar">
        <img src="<?= e(gravatar((string)($c['author_email'] ?? ''), 80)) ?>" alt="" loading="lazy">
      </div>
      <div class="comment-admin__body">
        <div class="comment-admin__head">
          <b><?= e($c['author_name']) ?></b>
          <?php if (!empty($c['author_email'])): ?><span class="muted"><?= e($c['author_email']) ?></span><?php endif; ?>
          <span class="status-badge status--<?= e($c['status']) ?>"><?= e(comment_status_label((string)$c['status'])) ?></span>
          <time><?= e(human_date($c['created_at'], 'Y-m-d H:i')) ?></time>
        </div>

        <p class="comment-admin__content"><?= nl2br(e($c['content'])) ?></p>

        <div class="comment-admin__meta">
          来自：<a href="<?= e(url('post/' . ($c['post_slug'] ?? ''))) ?>" target="_blank"><?= e($c['post_title'] ?? '（已删除）') ?></a>
          <?php if (!empty($c['ip'])): ?>· IP <?= e($c['ip']) ?><?php endif; ?>
        </div>

        <div class="comment-admin__actions">
          <?php if ($c['status'] !== 'approved'): ?>
            <form method="post" action="<?= e(url('admin/comments/status/' . $c['id'])) ?>" class="inline-form">
              <?= csrf_field() ?><input type="hidden" name="status" value="approved">
              <button type="submit" class="btn btn--sm btn--primary">通过</button>
            </form>
          <?php endif; ?>
          <?php if ($c['status'] !== 'spam'): ?>
            <form method="post" action="<?= e(url('admin/comments/status/' . $c['id'])) ?>" class="inline-form">
              <?= csrf_field() ?><input type="hidden" name="status" value="spam">
              <button type="submit" class="btn btn--sm">标记垃圾</button>
            </form>
          <?php endif; ?>
          <button type="button" class="btn btn--sm" data-reply="<?= (int)$c['id'] ?>">回复</button>
          <form method="post" action="<?= e(url('admin/comments/delete/' . $c['id'])) ?>" class="inline-form"
                onsubmit="return confirm('确定删除该评论？')">
            <?= csrf_field() ?><button type="submit" class="btn btn--sm btn--danger">删除</button>
          </form>
        </div>

        <form method="post" action="<?= e(url('admin/comments/reply/' . $c['id'])) ?>" class="reply-form" id="reply-<?= (int)$c['id'] ?>" hidden>
          <?= csrf_field() ?>
          <textarea name="content" rows="3" placeholder="写下回复…"></textarea>
          <button class="btn btn--primary btn--sm" type="submit">发送回复</button>
        </form>
      </div>
    </li>
  <?php endforeach; ?>
  </ul>

  <?= $paginator->render() ?>
</section>

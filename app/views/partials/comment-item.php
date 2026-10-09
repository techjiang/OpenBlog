<?php
/**
 * OpenBlog - 评论列表项（递归）
 *
 * @var array $comments
 */
?>
<?php foreach ($comments as $comment): ?>
  <li class="comment" id="comment-<?= (int)$comment['id'] ?>">
    <div class="comment__avatar">
      <img src="<?= e(gravatar((string)($comment['author_email'] ?? ''), 72)) ?>" alt="" loading="lazy">
    </div>
    <div class="comment__body">
      <div class="comment__head">
        <b><?= e($comment['author_name']) ?></b>
        <?php if (!empty($comment['author_website'])): ?>
          <a class="comment__site" href="<?= e($comment['author_website']) ?>" target="_blank" rel="noopener nofollow">访问站点</a>
        <?php endif; ?>
        <time><?= e(time_ago($comment['created_at'])) ?></time>
      </div>
      <div class="comment__content"><?= nl2br(e($comment['content'])) ?></div>
      <?php if (!empty($comment['children'])): ?>
        <ul class="comment-children">
          <?= partial('partials/comment-item', ['comments' => $comment['children']]) ?>
        </ul>
      <?php endif; ?>
    </div>
  </li>
<?php endforeach; ?>

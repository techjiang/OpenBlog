<?php
/**
 * OpenBlog - 文章详情
 *
 * @var array $post
 * @var string $html
 * @var array $toc
 * @var array $comments
 * @var int $commentCount
 * @var array|null $prev
 * @var array|null $next
 * @var array $related
 */
$authorName = $post['author_name'] ?: $post['author_username'] ?: '匿名';
$flash = \App\Core\Session::getFlash('error');
?>
<article class="post-single">
  <?php if (!empty($post['cover_image'])): ?>
    <div class="post-hero" style="background-image:url('<?= e(url($post['cover_image'])) ?>')">
      <div class="post-hero__mask"></div>
    </div>
  <?php endif; ?>

  <div class="container main-layout main-layout--post">
    <div class="content-column">
      <header class="post-head">
        <nav class="breadcrumb">
          <a href="<?= e(url('/')) ?>">首页</a>
          <?php if (!empty($post['category_name'])): ?>
            <span>/</span>
            <a href="<?= e(url('category/' . $post['category_slug'])) ?>"><?= e($post['category_name']) ?></a>
          <?php endif; ?>
        </nav>

        <h1 class="post-title"><?= e($post['title']) ?></h1>

        <div class="post-meta">
          <span class="author">
            <?php if (!empty($post['author_avatar'])): ?>
              <img src="<?= e(url($post['author_avatar'])) ?>" alt="" loading="lazy">
            <?php else: ?>
              <span class="avatar-fallback"><?= e(mb_substr((string)$authorName, 0, 1)) ?></span>
            <?php endif; ?>
            <a href="<?= e(url('author/' . ($post['author_username'] ?? ''))) ?>"><?= e($authorName) ?></a>
          </span>
          <span class="dot">·</span>
          <time datetime="<?= e((string)$post['published_at']) ?>"><?= e(human_date($post['published_at'], 'Y 年 m 月 d 日')) ?></time>
          <span class="dot">·</span>
          <span><?= (int)$post['reading_time'] ?> 分钟阅读</span>
          <span class="dot">·</span>
          <span><?= number_short((int)$post['view_count']) ?> 次浏览</span>
        </div>

        <?php if (($post['tags'] ?? []) !== []): ?>
          <div class="post-tags">
            <?php foreach ($post['tags'] as $tag): ?>
              <a class="chip" href="<?= e(url('tag/' . $tag['slug'])) ?>">#<?= e($tag['name']) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </header>

      <div class="prose">
        <?= $html ?>
      </div>

      <div class="post-actions">
        <button class="like-btn" id="likeBtn" data-id="<?= (int)$post['id'] ?>" type="button">
          <svg viewBox="0 0 24 24"><path d="M12 20.5l-1.4-1.3C6 15.3 3 12.6 3 9.2 3 6.5 5.1 4.4 7.8 4.4c1.5 0 3 .7 4.2 2 1.2-1.3 2.7-2 4.2-2 2.7 0 4.8 2.1 4.8 4.8 0 3.4-3 6.1-7.6 10l-1.4 1.3z"/></svg>
          <span id="likeCount"><?= (int)$post['like_count'] ?></span>
        </button>
        <button class="share-btn" id="shareBtn" type="button" data-url="<?= e(url('post/' . $post['slug'])) ?>">
          <svg viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 10.5l6.8-4M8.6 13.5l6.8 4"/></svg>
          分享
        </button>
      </div>

      <?php if ($related !== []): ?>
      <section class="related">
        <h3>相关阅读</h3>
        <div class="related-grid">
          <?php foreach ($related as $r): ?>
            <a class="related-card" href="<?= e(url('post/' . $r['slug'])) ?>">
              <span class="related-card__title"><?= e($r['title']) ?></span>
              <span class="related-card__meta"><?= e(human_date($r['published_at'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <nav class="post-nav">
        <?php if ($prev !== null): ?>
          <a class="post-nav__item" href="<?= e(url('post/' . $prev['slug'])) ?>">
            <span>← 上一篇</span><b><?= e(str_limit((string)$prev['title'], 40)) ?></b>
          </a>
        <?php else: ?><span class="post-nav__item is-empty"></span><?php endif; ?>

        <?php if ($next !== null): ?>
          <a class="post-nav__item post-nav__item--next" href="<?= e(url('post/' . $next['slug'])) ?>">
            <span>下一篇 →</span><b><?= e(str_limit((string)$next['title'], 40)) ?></b>
          </a>
        <?php else: ?><span class="post-nav__item is-empty"></span><?php endif; ?>
      </nav>

      <section class="comments" id="comments">
        <h3>评论 <span class="comment-count"><?= (int)$commentCount ?></span></h3>

        <?php if ($flash): ?><div class="flash flash--error"><?= e($flash) ?></div><?php endif; ?>

        <?php if (setting('comment_enabled', '1') === '1'): ?>
          <form class="comment-form" action="<?= e(url('comments/' . (int)$post['id'])) ?>" method="post">
            <?= csrf_field() ?>
            <?php if (\App\Core\Auth::guest()): ?>
              <div class="comment-form__row">
                <input name="author_name" placeholder="昵称 *" required value="<?= e(old('author_name')) ?>">
                <input name="author_email" type="email" placeholder="邮箱 *（不会公开）" required value="<?= e(old('author_email')) ?>">
                <input name="author_website" placeholder="网址（可选）" value="<?= e(old('author_website')) ?>">
              </div>
            <?php else: ?>
              <p class="comment-as">以 <b><?= e(\App\Core\Auth::user()['display_name'] ?? \App\Core\Auth::user()['username']) ?></b> 身份评论</p>
            <?php endif; ?>
            <textarea name="content" rows="4" placeholder="写下你的想法…（支持 Markdown 基础语法）" required><?= e(old('content')) ?></textarea>
            <div class="comment-form__foot">
              <span class="hint"><?= setting('comment_review', '1') === '1' ? '评论需经审核后公开' : '评论将立即公开' ?></span>
              <button class="btn btn--primary" type="submit">发布评论</button>
            </div>
          </form>
        <?php else: ?>
          <p class="widget-empty">本站已关闭评论功能。</p>
        <?php endif; ?>

        <?php if ($comments === []): ?>
          <p class="widget-empty">还没有评论，来说点什么吧。</p>
        <?php else: ?>
          <ul class="comment-list">
            <?= partial('partials/comment-item', ['comments' => $comments]) ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>

    <?php if ($toc !== []): ?>
      <aside class="toc" id="toc">
        <h4>目录</h4>
        <ul>
          <?php foreach ($toc as $item): ?>
            <li class="toc-lv<?= (int)$item['level'] ?>"><a href="#<?= e($item['id']) ?>"><?= e($item['text']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </aside>
    <?php endif; ?>
  </div>
</article>

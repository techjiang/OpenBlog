<?php
/**
 * OpenBlog - 文章编辑表单
 *
 * @var array|null $post
 * @var array $categoryOpts
 * @var string $tagsValue
 */
$isEdit = $post !== null;
$action = $isEdit ? url('admin/posts/update/' . $post['id']) : url('admin/posts');
?>
<form method="post" action="<?= e($action) ?>" id="postForm" class="post-editor">
  <?= csrf_field() ?>

  <div class="editor-grid">
    <div class="editor-main">
      <div class="panel">
        <input class="title-input" name="title" placeholder="文章标题" required
               value="<?= e($post['title'] ?? '') ?>" id="titleInput">

        <div class="slug-row">
          <span class="slug-prefix"><?= e(url('/post/')) ?></span>
          <input name="slug" id="slugInput" value="<?= e($post['slug'] ?? '') ?>" placeholder="url-slug">
          <button type="button" class="btn btn--ghost btn--sm" id="slugBtn">生成</button>
        </div>

        <div class="editor-tabs">
          <button type="button" class="tab is-active" data-tab="write">编写</button>
          <button type="button" class="tab" data-tab="preview">预览</button>
        </div>

        <textarea name="content" id="contentInput" class="md-editor" placeholder="使用 Markdown 书写…"><?= e($post['content'] ?? '') ?></textarea>
        <div class="md-preview prose" id="previewBox" hidden></div>
      </div>

      <div class="panel">
        <h3 class="panel__title">摘要</h3>
        <textarea name="excerpt" rows="3" class="form-control" placeholder="留空则自动从正文提取"><?= e($post['excerpt'] ?? '') ?></textarea>
      </div>
    </div>

    <aside class="editor-side">
      <div class="panel">
        <h3 class="panel__title">发布</h3>
        <label class="field">
          <span>状态</span>
          <select name="status">
            <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>已发布</option>
            <option value="draft"     <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>草稿</option>
            <option value="private"   <?= ($post['status'] ?? '') === 'private' ? 'selected' : '' ?>>私密</option>
          </select>
        </label>

        <label class="field">
          <span>发布时间</span>
          <input type="datetime-local" name="published_at"
                 value="<?= e($post['published_at'] ? date('Y-m-d\TH:i', strtotime((string)$post['published_at'])) : '') ?>">
        </label>

        <label class="checkbox"><input type="checkbox" name="featured" value="1" <?= !empty($post['featured']) ? 'checked' : '' ?>> 设为精选</label>
        <label class="checkbox"><input type="checkbox" name="allow_comment" value="1" <?= ($post['allow_comment'] ?? 1) ? 'checked' : '' ?>> 允许评论</label>

        <div class="side-actions">
          <button class="btn btn--primary btn--block" type="submit">保存</button>
          <?php if ($isEdit): ?>
            <a class="btn btn--block" href="<?= e(url('post/' . $post['slug'])) ?>" target="_blank">前台查看</a>
          <?php endif; ?>
          <a class="btn btn--block" href="<?= e(url('admin/posts')) ?>">返回列表</a>
        </div>
      </div>

      <div class="panel">
        <h3 class="panel__title">分类</h3>
        <select name="category_id" class="form-control">
          <option value="">未分类</option>
          <?php foreach ($categoryOpts as $id => $name): ?>
            <option value="<?= (int)$id ?>" <?= (string)($post['category_id'] ?? '') === (string)$id ? 'selected' : '' ?>><?= e($name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="panel">
        <h3 class="panel__title">标签</h3>
        <input name="tags" class="form-control" value="<?= e($tagsValue ?? '') ?>" placeholder="用逗号分隔，如：PHP, 设计">
      </div>

      <div class="panel">
        <h3 class="panel__title">封面图</h3>
        <div class="cover-picker">
          <input name="cover_image" id="coverInput" class="form-control" value="<?= e($post['cover_image'] ?? '') ?>" placeholder="图片地址">
          <button type="button" class="btn btn--ghost btn--sm" id="coverBtn">从媒体库选择</button>
          <div class="cover-preview" id="coverPreview">
            <?php if (!empty($post['cover_image'])): ?>
              <img src="<?= e(url($post['cover_image'])) ?>" alt="">
            <?php endif; ?>
          </div>
        </div>
      </div>
    </aside>
  </div>
</form>

<div class="modal" id="mediaModal" hidden>
  <div class="modal__box">
    <div class="modal__head">
      <h3>媒体库</h3>
      <button type="button" class="icon-btn" id="mediaClose">✕</button>
    </div>
    <div class="modal__body" id="mediaBody"><p class="table-empty">加载中…</p></div>
  </div>
</div>

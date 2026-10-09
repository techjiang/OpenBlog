<?php
/**
 * OpenBlog - 页面编辑表单
 *
 * @var array|null $page
 */
$isEdit = $page !== null;
$action = $isEdit ? url('admin/pages/update/' . $page['id']) : url('admin/pages');
?>
<form method="post" action="<?= e($action) ?>" class="post-editor">
  <?= csrf_field() ?>

  <div class="editor-grid">
    <div class="editor-main">
      <div class="panel">
        <input class="title-input" name="title" placeholder="页面标题" required value="<?= e($page['title'] ?? '') ?>">

        <div class="slug-row">
          <span class="slug-prefix"><?= e(url('/page/')) ?></span>
          <input name="slug" value="<?= e($page['slug'] ?? '') ?>" placeholder="about">
        </div>

        <div class="editor-tabs">
          <button type="button" class="tab is-active" data-tab="write">编写</button>
          <button type="button" class="tab" data-tab="preview">预览</button>
        </div>

        <textarea name="content" class="md-editor" placeholder="使用 Markdown 书写…"><?= e($page['content'] ?? '') ?></textarea>
        <div class="md-preview prose" hidden></div>
      </div>
    </div>

    <aside class="editor-side">
      <div class="panel">
        <h3 class="panel__title">发布</h3>
        <label class="field">
          <span>状态</span>
          <select name="status">
            <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>已发布</option>
            <option value="draft"     <?= ($page['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>草稿</option>
          </select>
        </label>
        <label class="checkbox"><input type="checkbox" name="allow_comment" value="1" <?= ($page['allow_comment'] ?? 0) ? 'checked' : '' ?>> 允许评论</label>

        <div class="side-actions">
          <button class="btn btn--primary btn--block" type="submit">保存</button>
          <?php if ($isEdit): ?>
            <a class="btn btn--block" href="<?= e(url('page/' . $page['slug'])) ?>" target="_blank">前台查看</a>
          <?php endif; ?>
          <a class="btn btn--block" href="<?= e(url('admin/pages')) ?>">返回列表</a>
        </div>
      </div>

      <div class="panel">
        <h3 class="panel__title">封面图</h3>
        <input name="cover_image" class="form-control" value="<?= e($page['cover_image'] ?? '') ?>" placeholder="图片地址">
      </div>
    </aside>
  </div>
</form>

<?php
/**
 * OpenBlog - 标签管理
 *
 * @var array $tags
 */
?>
<div class="editor-grid">
  <div class="editor-main">
    <section class="panel">
      <div class="panel__head"><h3>共 <?= count($tags) ?> 个标签</h3></div>
      <table class="table">
        <thead><tr><th>名称</th><th>别名</th><th>关联文章</th><th style="width:130px">操作</th></tr></thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
          <tr>
            <td><b>#<?= e($tag['name']) ?></b></td>
            <td class="muted">/tag/<?= e($tag['slug']) ?></td>
            <td><?= (int)$tag['real_count'] ?></td>
            <td class="cell-actions">
              <button type="button" class="link-edit"
                      data-id="<?= (int)$tag['id'] ?>"
                      data-name="<?= e($tag['name']) ?>"
                      data-slug="<?= e($tag['slug']) ?>">编辑</button>
              <form method="post" action="<?= e(url('admin/tags/delete/' . $tag['id'])) ?>" class="inline-form"
                    onsubmit="return confirm('删除标签「<?= e($tag['name']) ?>」？')">
                <?= csrf_field() ?><button type="submit" class="link-danger">删除</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($tags === []): ?><tr><td colspan="4" class="table-empty">还没有标签</td></tr><?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>

  <aside class="editor-side">
    <section class="panel">
      <h3 class="panel__title" id="tagFormTitle">新建标签</h3>
      <form method="post" action="<?= e(url('admin/tags')) ?>" id="tagForm">
        <?= csrf_field() ?>
        <input type="hidden" name="__id" id="tagId" value="">
        <label class="field"><span>名称 *</span><input name="name" id="tagName" required></label>
        <label class="field"><span>别名</span><input name="slug" id="tagSlug" placeholder="留空自动生成"></label>
        <button class="btn btn--primary btn--block" type="submit">保存标签</button>
      </form>
    </section>
  </aside>
</div>

<?php
/**
 * OpenBlog - 分类管理
 *
 * @var array $categories
 * @var array $options
 */
?>
<div class="editor-grid">
  <div class="editor-main">
    <section class="panel">
      <div class="panel__head"><h3>共 <?= count($categories) ?> 个分类</h3></div>
      <table class="table">
        <thead><tr><th>名称</th><th>别名</th><th>描述</th><th>文章</th><th style="width:130px">操作</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td>
              <span class="cat-dot" style="background:<?= e($cat['color'] ?: '#4f46e5') ?>"></span>
              <b><?= e($cat['name']) ?></b>
            </td>
            <td class="muted">/<?= e($cat['slug']) ?></td>
            <td class="muted"><?= e(str_limit((string)($cat['description'] ?? ''), 30)) ?></td>
            <td><?= (int)$cat['real_count'] ?></td>
            <td class="cell-actions">
              <button type="button" class="link-edit"
                      data-id="<?= (int)$cat['id'] ?>"
                      data-name="<?= e($cat['name']) ?>"
                      data-slug="<?= e($cat['slug']) ?>"
                      data-desc="<?= e((string)($cat['description'] ?? '')) ?>"
                      data-color="<?= e($cat['color'] ?? '') ?>"
                      data-order="<?= (int)$cat['sort_order'] ?>"
                      data-parent="<?= e((string)($cat['parent_id'] ?? '')) ?>">编辑</button>
              <form method="post" action="<?= e(url('admin/categories/delete/' . $cat['id'])) ?>" class="inline-form"
                    onsubmit="return confirm('删除分类「<?= e($cat['name']) ?>」？其下文章将变为未分类。')">
                <?= csrf_field() ?><button type="submit" class="link-danger">删除</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($categories === []): ?><tr><td colspan="5" class="table-empty">还没有分类</td></tr><?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>

  <aside class="editor-side">
    <section class="panel">
      <h3 class="panel__title" id="formTitle">新建分类</h3>
      <form method="post" action="<?= e(url('admin/categories')) ?>" id="catForm">
        <?= csrf_field() ?>
        <input type="hidden" name="__id" id="catId" value="">
        <label class="field"><span>名称 *</span><input name="name" id="catName" required></label>
        <label class="field"><span>别名</span><input name="slug" id="catSlug" placeholder="留空自动生成"></label>
        <label class="field"><span>描述</span><textarea name="description" id="catDesc" rows="2"></textarea></label>
        <label class="field"><span>颜色</span><input type="color" name="color" id="catColor" value="#4f46e5"></label>
        <label class="field">
          <span>父分类</span>
          <select name="parent_id" id="catParent">
            <option value="">无</option>
            <?php foreach ($options as $id => $name): ?>
              <option value="<?= (int)$id ?>"><?= e($name) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field"><span>排序</span><input type="number" name="sort_order" id="catOrder" value="0"></label>
        <button class="btn btn--primary btn--block" type="submit">保存分类</button>
      </form>
    </section>
  </aside>
</div>

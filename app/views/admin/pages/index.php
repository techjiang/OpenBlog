<?php
/**
 * OpenBlog - 页面列表
 *
 * @var array $pages
 * @var int $total
 * @var \App\Core\Paginator $paginator
 */
?>
<div class="page-toolbar">
  <p class="muted">独立页面用于「关于」「友链」等非文章类内容。</p>
  <a class="btn btn--primary" href="<?= e(url('admin/pages/create')) ?>">
    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> 新建页面
  </a>
</div>

<section class="panel">
  <div class="panel__head"><h3>共 <?= (int)$total ?> 个页面</h3></div>

  <table class="table">
    <thead>
      <tr><th>标题</th><th>状态</th><th>更新时间</th><th style="width:150px">操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
      <tr>
        <td>
          <a class="cell-title" href="<?= e(url('admin/pages/edit/' . $p['id'])) ?>"><?= e($p['title']) ?></a>
          <div class="cell-slug">/page/<?= e($p['slug']) ?></div>
        </td>
        <td><span class="status-badge status--<?= e($p['status']) ?>"><?= e(status_label((string)$p['status'])) ?></span></td>
        <td><?= e(human_date($p['updated_at'], 'Y-m-d H:i')) ?></td>
        <td class="cell-actions">
          <a href="<?= e(url('admin/pages/edit/' . $p['id'])) ?>">编辑</a>
          <?php if ($p['status'] === 'published'): ?>
            <a href="<?= e(url('page/' . $p['slug'])) ?>" target="_blank">查看</a>
          <?php endif; ?>
          <form method="post" action="<?= e(url('admin/pages/delete/' . $p['id'])) ?>" class="inline-form"
                onsubmit="return confirm('确定删除该页面？')">
            <?= csrf_field() ?><button type="submit" class="link-danger">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if ($pages === []): ?><tr><td colspan="4" class="table-empty">还没有页面</td></tr><?php endif; ?>
    </tbody>
  </table>

  <?= $paginator->render() ?>
</section>

<?php
/**
 * OpenBlog - 用户管理
 *
 * @var array $users
 */
?>
<div class="editor-grid">
  <div class="editor-main">
    <section class="panel">
      <div class="panel__head"><h3>共 <?= count($users) ?> 位用户</h3></div>
      <table class="table">
        <thead><tr><th>用户</th><th>邮箱</th><th>角色</th><th>文章</th><th>状态</th><th>最后登录</th><th style="width:120px">操作</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><b><?= e($u['display_name'] ?: $u['username']) ?></b><div class="cell-slug">@<?= e($u['username']) ?></div></td>
            <td class="muted"><?= e($u['email']) ?></td>
            <td><span class="chip role--<?= e($u['role']) ?>"><?= e(role_label((string)$u['role'])) ?></span></td>
            <td><?= (int)$u['post_count'] ?></td>
            <td><?= (int)$u['status'] === 1 ? '<span class="status-badge status--published">启用</span>' : '<span class="status-badge status--draft">停用</span>' ?></td>
            <td class="muted"><?= e(human_date($u['last_login_at'], 'Y-m-d H:i')) ?></td>
            <td class="cell-actions">
              <a href="<?= e(url('admin/users/edit/' . $u['id'])) ?>">编辑</a>
              <?php if ((int)$u['id'] !== (int)(\App\Core\Auth::id())): ?>
                <form method="post" action="<?= e(url('admin/users/delete/' . $u['id'])) ?>" class="inline-form"
                      onsubmit="return confirm('确定删除该用户？')">
                  <?= csrf_field() ?><button type="submit" class="link-danger">删除</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </div>

  <aside class="editor-side">
    <section class="panel">
      <h3 class="panel__title">新建用户</h3>
      <form method="post" action="<?= e(url('admin/users')) ?>">
        <?= csrf_field() ?>
        <label class="field"><span>账号 *</span><input name="username" required minlength="3"></label>
        <label class="field"><span>显示名</span><input name="display_name"></label>
        <label class="field"><span>邮箱 *</span><input type="email" name="email" required></label>
        <label class="field"><span>密码 *</span><input type="password" name="password" required minlength="6"></label>
        <label class="field">
          <span>角色</span>
          <select name="role">
            <option value="author">作者</option>
            <option value="editor">编辑</option>
            <option value="admin">管理员</option>
          </select>
        </label>
        <button class="btn btn--primary btn--block" type="submit">创建用户</button>
      </form>
    </section>
  </aside>
</div>

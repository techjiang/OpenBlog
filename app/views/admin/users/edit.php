<?php
/**
 * OpenBlog - 编辑用户
 *
 * @var array $editUser
 */
?>
<section class="panel panel--narrow">
  <div class="panel__head">
    <h3>编辑用户：<?= e($editUser['display_name'] ?: $editUser['username']) ?></h3>
    <a class="link-more" href="<?= e(url('admin/users')) ?>">← 返回</a>
  </div>

  <form method="post" action="<?= e(url('admin/users/update/' . $editUser['id'])) ?>" class="form-grid">
    <?= csrf_field() ?>

    <label class="field"><span>账号 *</span><input name="username" value="<?= e($editUser['username']) ?>" required></label>
    <label class="field"><span>显示名</span><input name="display_name" value="<?= e((string)$editUser['display_name']) ?>"></label>
    <label class="field"><span>邮箱 *</span><input type="email" name="email" value="<?= e($editUser['email']) ?>" required></label>
    <label class="field"><span>新密码</span><input type="password" name="password" placeholder="留空则不修改"></label>
    <label class="field">
      <span>角色</span>
      <select name="role">
        <option value="author" <?= $editUser['role'] === 'author' ? 'selected' : '' ?>>作者</option>
        <option value="editor" <?= $editUser['role'] === 'editor' ? 'selected' : '' ?>>编辑</option>
        <option value="admin"  <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>管理员</option>
      </select>
    </label>
    <label class="checkbox"><input type="checkbox" name="status" value="1" <?= (int)$editUser['status'] === 1 ? 'checked' : '' ?>> 启用账号</label>

    <label class="field field--full"><span>个人简介</span><textarea name="bio" rows="3"><?= e((string)$editUser['bio']) ?></textarea></label>
    <label class="field field--full"><span>个人网站</span><input name="website" value="<?= e((string)$editUser['website']) ?>" placeholder="https://"></label>

    <div class="field--full">
      <button class="btn btn--primary" type="submit">保存修改</button>
      <a class="btn" href="<?= e(url('admin/users')) ?>">取消</a>
    </div>
  </form>
</section>

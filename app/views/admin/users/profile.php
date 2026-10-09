<?php
/**
 * OpenBlog - 个人资料
 */
$u = \App\Core\Auth::user() ?? [];
?>
<section class="panel panel--narrow">
  <div class="panel__head"><h3>个人资料</h3></div>

  <form method="post" action="<?= e(url('admin/profile')) ?>" class="form-grid">
    <?= csrf_field() ?>

    <label class="field"><span>账号 *</span><input name="username" value="<?= e($u['username'] ?? '') ?>" required></label>
    <label class="field"><span>显示名</span><input name="display_name" value="<?= e((string)($u['display_name'] ?? '')) ?>"></label>
    <label class="field"><span>邮箱 *</span><input type="email" name="email" value="<?= e($u['email'] ?? '') ?>" required></label>
    <label class="field"><span>新密码</span><input type="password" name="password" placeholder="留空则不修改"></label>
    <label class="field field--full"><span>头像地址</span><input name="avatar" value="<?= e((string)($u['avatar'] ?? '')) ?>" placeholder="https:// 或 /storage/uploads/..."></label>
    <label class="field field--full"><span>个人网站</span><input name="website" value="<?= e((string)($u['website'] ?? '')) ?>"></label>
    <label class="field field--full"><span>简介</span><textarea name="bio" rows="3"><?= e((string)($u['bio'] ?? '')) ?></textarea></label>

    <div class="field--full">
      <button class="btn btn--primary" type="submit">保存资料</button>
    </div>
  </form>
</section>

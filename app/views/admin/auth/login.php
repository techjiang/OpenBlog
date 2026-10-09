<?php
/**
 * OpenBlog - 后台登录
 */
?>
<div class="auth-card">
  <h2>登录后台</h2>
  <p class="auth-sub">使用管理员账号继续</p>

  <form method="post" action="<?= e(url('admin/login')) ?>" class="auth-form">
    <?= csrf_field() ?>

    <label>账号或邮箱</label>
    <input name="account" type="text" required autofocus autocomplete="username" placeholder="admin">

    <label>密码</label>
    <input name="password" type="password" required autocomplete="current-password" placeholder="••••••••">

    <label class="checkbox">
      <input type="checkbox" name="remember" value="1"> 记住我（30 天）
    </label>

    <button class="btn btn--primary btn--block" type="submit">登录</button>
  </form>

  <p class="auth-foot"><a href="<?= e(url('/')) ?>">← 返回站点前台</a></p>
</div>

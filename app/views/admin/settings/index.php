<?php
/**
 * OpenBlog - 站点设置
 *
 * @var array $settings
 * @var array $subscribers
 */
$s = static fn (string $key, string $default = '') => $settings[$key] ?? $default;
?>
<form method="post" action="<?= e(url('admin/settings')) ?>" class="settings-form">
  <?= csrf_field() ?>

  <div class="panel">
    <div class="panel__head"><h3>基本信息</h3></div>
    <div class="form-grid">
      <label class="field"><span>站点名称</span><input name="site_name" value="<?= e($s('site_name', 'OpenBlog')) ?>"></label>
      <label class="field"><span>副标题</span><input name="site_tagline" value="<?= e($s('site_tagline')) ?>"></label>
      <label class="field field--full"><span>站点描述（SEO）</span><textarea name="site_description" rows="2"><?= e($s('site_description')) ?></textarea></label>
      <label class="field field--full"><span>关键词（SEO）</span><input name="site_keywords" value="<?= e($s('site_keywords')) ?>"></label>
      <label class="field"><span>Logo 地址</span><input name="site_logo" value="<?= e($s('site_logo')) ?>" placeholder="/storage/uploads/logo.png"></label>
      <label class="field"><span>ICP 备案号</span><input name="site_icp" value="<?= e($s('site_icp')) ?>"></label>
      <label class="field"><span>管理员邮箱</span><input name="admin_email" value="<?= e($s('admin_email')) ?>"></label>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h3>阅读设置</h3></div>
    <div class="form-grid">
      <label class="field"><span>每页文章数</span><input type="number" name="posts_per_page" min="1" max="50" value="<?= e($s('posts_per_page', '10')) ?>"></label>
      <label class="field"><span>上传大小上限（MB）</span><input type="number" name="upload_max_size" min="1" max="50" value="<?= e($s('upload_max_size', '5')) ?>"></label>
      <label class="checkbox"><input type="checkbox" name="comment_enabled" value="1" <?= $s('comment_enabled', '1') === '1' ? 'checked' : '' ?>> 开启评论</label>
      <label class="checkbox"><input type="checkbox" name="comment_review" value="1" <?= $s('comment_review', '1') === '1' ? 'checked' : '' ?>> 评论需审核</label>
      <label class="checkbox"><input type="checkbox" name="show_toc" value="1" <?= $s('show_toc', '1') === '1' ? 'checked' : '' ?>> 文章显示目录</label>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h3>外观</h3></div>
    <div class="form-grid">
      <label class="field">
        <span>首页布局</span>
        <select name="home_layout">
          <option value="list"  <?= $s('home_layout', 'list') === 'list' ? 'selected' : '' ?>>列表</option>
          <option value="grid"  <?= $s('home_layout') === 'grid' ? 'selected' : '' ?>>网格</option>
        </select>
      </label>
      <label class="field"><span>主题色</span><input type="color" name="accent_color" value="<?= e($s('accent_color', '#4f46e5')) ?>"></label>
      <label class="field field--full"><span>页脚文字</span><input name="footer_text" value="<?= e($s('footer_text')) ?>"></label>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h3>社交链接</h3></div>
    <div class="form-grid">
      <label class="field"><span>GitHub</span><input name="social_github" value="<?= e($s('social_github')) ?>"></label>
      <label class="field"><span>Twitter / X</span><input name="social_twitter" value="<?= e($s('social_twitter')) ?>"></label>
      <label class="field"><span>联系邮箱</span><input name="social_email" value="<?= e($s('social_email')) ?>"></label>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h3>高级</h3></div>
    <label class="field field--full">
      <span>统计代码（将插入到页面底部）</span>
      <textarea name="stat_code" rows="3" placeholder="&lt;script&gt;...&lt;/script&gt;"><?= e($s('stat_code')) ?></textarea>
    </label>
  </div>

  <div class="settings-actions">
    <button class="btn btn--primary btn--lg" type="submit">保存设置</button>
  </div>
</form>

<form method="post" action="<?= e(url('admin/cache/clear')) ?>" class="settings-actions" style="margin-top:-12px">
  <?= csrf_field() ?>
  <button class="btn btn--sm" type="submit">清空缓存</button>
</form>

<section class="panel">
  <div class="panel__head"><h3>订阅者（<?= count($subscribers) ?>）</h3></div>
  <?php if ($subscribers === []): ?>
    <p class="table-empty">还没有订阅者</p>
  <?php else: ?>
    <div class="tag-cloud">
      <?php foreach ($subscribers as $sub): ?>
        <span class="chip"><?= e($sub['email']) ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

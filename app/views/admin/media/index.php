<?php
/**
 * OpenBlog - 媒体库
 *
 * @var array $files
 */
?>
<div class="page-toolbar">
  <form method="post" action="<?= e(url('admin/media/upload')) ?>" enctype="multipart/form-data" class="upload-bar" id="uploadForm">
    <?= csrf_field() ?>
    <input type="file" name="file" accept="image/*,application/pdf" required id="uploadInput">
    <button class="btn btn--primary btn--sm" type="submit">上传</button>
    <span class="muted">支持 JPG / PNG / GIF / WEBP / SVG / PDF，单个文件不超过 <?= e(setting('upload_max_size', '5')) ?>MB</span>
  </form>
</div>

<section class="panel">
  <div class="panel__head"><h3>共 <?= count($files) ?> 个文件</h3></div>

  <?php if ($files === []): ?>
    <p class="table-empty">还没有上传任何文件</p>
  <?php else: ?>
    <div class="media-grid">
      <?php foreach ($files as $f): ?>
        <div class="media-item" data-path="<?= e((string)$f['filename']) ?>">
          <div class="media-item__thumb">
            <?php if (str_starts_with((string)$f['mime_type'], 'image/')): ?>
              <img src="<?= e(url($f['filename'])) ?>" alt="<?= e((string)$f['original_name']) ?>" loading="lazy">
            <?php else: ?>
              <span class="media-item__ext"><?= e(strtoupper(pathinfo((string)$f['filename'], PATHINFO_EXTENSION))) ?></span>
            <?php endif; ?>
          </div>
          <div class="media-item__info">
            <b title="<?= e((string)$f['original_name']) ?>"><?= e(str_limit((string)$f['original_name'], 20)) ?></b>
            <span><?= e(size_format((int)$f['size'])) ?></span>
          </div>
          <div class="media-item__actions">
            <button type="button" class="btn btn--sm copy-btn" data-url="<?= e(url($f['filename'])) ?>">复制链接</button>
            <form method="post" action="<?= e(url('admin/media/delete/' . $f['id'])) ?>" class="inline-form"
                  onsubmit="return confirm('确定删除该文件？')">
              <?= csrf_field() ?><button type="submit" class="btn btn--sm btn--danger">删除</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

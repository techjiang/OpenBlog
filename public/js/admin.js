/* =====================================================================
   OpenBlog - 后台交互
   ===================================================================== */
(function () {
  'use strict';

  var doc = document;

  /* ---------- 主题切换 ---------- */
  function bindThemeToggle(id) {
    var toggle = doc.getElementById(id);
    if (toggle) {
      toggle.addEventListener('click', function () {
        var next = doc.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        doc.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('ob-theme', next); } catch (e) {}
      });
    }
  }
  bindThemeToggle('themeToggle');

  function csrf() {
    var el = doc.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
  }

  /* ---------- 侧边栏（移动端） ---------- */
  var burger = doc.getElementById('adminBurger');
  var sidebar = doc.getElementById('adminSidebar');
  var backdrop = doc.getElementById('adminBackdrop');
  if (burger && sidebar) {
    burger.addEventListener('click', function () {
      sidebar.classList.toggle('is-open');
      if (backdrop) backdrop.classList.toggle('is-open');
    });
    if (backdrop) {
      backdrop.addEventListener('click', function () {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-open');
      });
    }
  }

  /* ---------- 用户菜单 ---------- */
  var userMenu = doc.getElementById('adminUserMenu');
  if (userMenu) {
    userMenu.querySelector('.admin-user__btn').addEventListener('click', function (e) {
      e.stopPropagation();
      userMenu.classList.toggle('is-open');
    });
    doc.addEventListener('click', function () { userMenu.classList.remove('is-open'); });
  }

  /* ---------- Markdown 编写 / 预览切换 ---------- */
  doc.querySelectorAll('.editor-tabs').forEach(function (tabs) {
    var panel = tabs.parentElement;
    var editor = panel.querySelector('.md-editor');
    var preview = panel.querySelector('.md-preview');
    if (!editor || !preview) return;

    tabs.querySelectorAll('.tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('is-active'); });
        tab.classList.add('is-active');

        var mode = tab.dataset.tab;
        if (mode === 'preview') {
          var body = new FormData();
          body.append('_token', csrf());
          body.append('content', editor.value);

          preview.innerHTML = '<p class="muted">渲染中…</p>';

          fetch((window.OB_ADMIN || '') + '/admin/preview', {
            method: 'POST',
            body: body,
            credentials: 'same-origin'
          })
            .then(function (r) { return r.json(); })
            .then(function (data) { preview.innerHTML = data.html || '<p class="muted">渲染失败</p>'; })
            .catch(function () { preview.innerHTML = '<p class="muted">预览服务不可用</p>'; });

          editor.hidden = true;
          preview.hidden = false;
        } else {
          editor.hidden = false;
          preview.hidden = true;
        }
      });
    });
  });

  /* ---------- 编辑器快捷键（Tab 缩进 / Ctrl+S 保存） ---------- */
  var editor = doc.querySelector('.md-editor');
  if (editor) {
    editor.addEventListener('keydown', function (e) {
      if (e.key === 'Tab') {
        e.preventDefault();
        var start = this.selectionStart, end = this.selectionEnd;
        this.value = this.value.substring(0, start) + '  ' + this.value.substring(end);
        this.selectionStart = this.selectionEnd = start + 2;
      }
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        var form = doc.getElementById('postForm') || editor.closest('form');
        if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
      }
    });
  }

  /* ---------- Slug 自动生成 ---------- */
  var slugBtn = doc.getElementById('slugBtn');
  if (slugBtn) {
    slugBtn.addEventListener('click', function () {
      var title = doc.getElementById('titleInput').value;
      var body = new FormData();
      body.append('_token', csrf());
      body.append('title', title);

      fetch((window.OB_ADMIN || '') + '/admin/posts/slug', {
        method: 'POST', body: body, credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (data) { doc.getElementById('slugInput').value = data.slug || ''; })
        .catch(function () {});
    });
  }

  /* ---------- 媒体库弹窗（选封面） ---------- */
  var coverBtn = doc.getElementById('coverBtn');
  var mediaModal = doc.getElementById('mediaModal');
  if (coverBtn && mediaModal) {
    coverBtn.addEventListener('click', function () {
      mediaModal.hidden = false;
      var body = doc.getElementById('mediaBody');
      if (body.dataset.loaded !== '1') {
        fetch((window.OB_ADMIN || '') + '/admin/media', { credentials: 'same-origin' })
          .then(function (r) { return r.text(); })
          .then(function (html) {
            var parsed = new DOMParser().parseFromString(html, 'text/html');
            var grid = parsed.querySelector('.media-grid');
            body.innerHTML = grid ? grid.outerHTML : '<p class="table-empty">媒体库为空</p>';
            body.dataset.loaded = '1';
            bindMediaPick(body);
          })
          .catch(function () { body.innerHTML = '<p class="table-empty">加载失败</p>'; });
      } else {
        bindMediaPick(body);
      }
    });

    doc.getElementById('mediaClose').addEventListener('click', function () { mediaModal.hidden = true; });
    mediaModal.addEventListener('click', function (e) { if (e.target === mediaModal) mediaModal.hidden = true; });

    function bindMediaPick(scope) {
      scope.querySelectorAll('.media-item').forEach(function (item) {
        if (item.dataset.bound === '1') return;
        item.dataset.bound = '1';
        item.addEventListener('click', function () {
          var path = item.dataset.path;
          var input = doc.getElementById('coverInput');
          if (input) {
            input.value = path;
            var box = doc.getElementById('coverPreview');
            if (box) box.innerHTML = '<img src="' + (window.OB_BASE || '') + '/' + path.replace(/^\//, '') + '" alt="">';
          }
          mediaModal.hidden = true;
        });
      });
    }
  }

  /* ---------- 评论回复 ---------- */
  doc.querySelectorAll('[data-reply]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = doc.getElementById('reply-' + btn.dataset.reply);
      if (form) form.hidden = !form.hidden;
    });
  });

  /* ---------- 分类编辑填充 ---------- */
  doc.querySelectorAll('.link-edit[data-name]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var d = btn.dataset;
      if (doc.getElementById('catId')) {
        doc.getElementById('formTitle').textContent = '编辑分类';
        doc.getElementById('catId').value = d.id;
        doc.getElementById('catName').value = d.name;
        doc.getElementById('catSlug').value = d.slug;
        doc.getElementById('catDesc').value = d.desc;
        doc.getElementById('catColor').value = d.color || '#4f46e5';
        doc.getElementById('catOrder').value = d.order;
        doc.getElementById('catParent').value = d.parent;
        doc.getElementById('catForm').action = (window.OB_ADMIN || '') + '/admin/categories/update/' + d.id;
        doc.getElementById('catName').focus();
      }
      if (doc.getElementById('tagId')) {
        doc.getElementById('tagFormTitle').textContent = '编辑标签';
        doc.getElementById('tagId').value = d.id;
        doc.getElementById('tagName').value = d.name;
        doc.getElementById('tagSlug').value = d.slug;
        doc.getElementById('tagForm').action = (window.OB_ADMIN || '') + '/admin/tags/update/' + d.id;
        doc.getElementById('tagName').focus();
      }
    });
  });

  /* ---------- 复制链接 ---------- */
  doc.querySelectorAll('.copy-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = btn.dataset.url;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          btn.textContent = '已复制';
          setTimeout(function () { btn.textContent = '复制链接'; }, 1500);
        });
      }
    });
  });
})();

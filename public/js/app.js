/* =====================================================================
   OpenBlog - 前台交互
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
  bindThemeToggle('themeToggleTop');

  /* ---------- 移动端侧边栏 ---------- */
  var burger = doc.getElementById('appBurger');
  var sidebar = doc.getElementById('appSidebar');
  if (burger && sidebar) {
    burger.addEventListener('click', function () {
      sidebar.classList.toggle('is-open');
    });
    doc.addEventListener('click', function (e) {
      if (window.innerWidth > 1024) return;
      if (!sidebar.contains(e.target) && !burger.contains(e.target)) {
        sidebar.classList.remove('is-open');
      }
    });
  }

  /* ---------- 代码块一键复制 ---------- */
  doc.querySelectorAll('.code-block').forEach(function (block) {
    var btn = doc.createElement('button');
    btn.className = 'code-copy';
    btn.type = 'button';
    btn.textContent = '复制';
    btn.addEventListener('click', function () {
      var code = block.querySelector('code');
      if (!code) return;
      var text = code.textContent || '';
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () {
          btn.textContent = '已复制';
          setTimeout(function () { btn.textContent = '复制'; }, 1600);
        });
      } else {
        var ta = doc.createElement('textarea');
        ta.value = text;
        doc.body.appendChild(ta);
        ta.select();
        try { doc.execCommand('copy'); btn.textContent = '已复制'; } catch (e) {}
        doc.body.removeChild(ta);
        setTimeout(function () { btn.textContent = '复制'; }, 1600);
      }
    });
    block.appendChild(btn);
  });

  /* ---------- 点赞 ---------- */
  var likeBtn = doc.getElementById('likeBtn');
  if (likeBtn) {
    var liked = false;
    try { liked = localStorage.getItem('ob-liked-' + likeBtn.dataset.id) === '1'; } catch (e) {}

    likeBtn.addEventListener('click', function () {
      var id = likeBtn.dataset.id;
      var tokenEl = doc.querySelector('meta[name="csrf-token"]');
      var token = tokenEl ? tokenEl.getAttribute('content') : '';

      var body = new FormData();
      body.append('_token', token);

      fetch((window.OB_BASE || '') + '/posts/' + id + '/like', {
        method: 'POST',
        body: body,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var counter = doc.getElementById('likeCount');
          if (data.count !== undefined && counter) {
            counter.textContent = data.count;
          }
          if (data.liked) {
            likeBtn.classList.add('is-liked');
            try { localStorage.setItem('ob-liked-' + id, '1'); } catch (e) {}
          } else {
            likeBtn.classList.add('is-liked');
          }
        })
        .catch(function () { /* 忽略 */ });
    });
  }

  /* ---------- 分享 ---------- */
  var shareBtn = doc.getElementById('shareBtn');
  if (shareBtn) {
    shareBtn.addEventListener('click', function () {
      var url = shareBtn.dataset.url || location.href;
      var title = doc.title;

      if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(function () {});
        return;
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          var original = shareBtn.innerHTML;
          shareBtn.textContent = '链接已复制';
          setTimeout(function () { shareBtn.innerHTML = original; }, 1800);
        });
      }
    });
  }

  /* ---------- 目录高亮 ---------- */
  var toc = doc.getElementById('toc');
  if (toc) {
    var links = toc.querySelectorAll('a');
    var targets = [];
    links.forEach(function (a) {
      var el = doc.getElementById(a.getAttribute('href').slice(1));
      if (el) targets.push({ link: a, el: el });
    });

    if (targets.length && 'IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          links.forEach(function (l) { l.classList.remove('active'); });
          var hit = targets.find(function (t) { return t.el === entry.target; });
          if (hit) hit.link.classList.add('active');
        });
      }, { rootMargin: '-90px 0px -70% 0px', threshold: 0 });

      targets.forEach(function (t) { observer.observe(t.el); });
    }
  }

  /* ---------- 顶栏阴影 ---------- */
  var topbar = doc.querySelector('.app-topbar');
  if (topbar) {
    var onScroll = function () {
      topbar.style.boxShadow = window.scrollY > 8 ? '0 1px 0 var(--border)' : 'none';
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- 图片灯箱 ---------- */
  doc.querySelectorAll('.prose img').forEach(function (img) {
    img.style.cursor = 'zoom-in';
    img.addEventListener('click', function () {
      var overlay = doc.createElement('div');
      overlay.className = 'lightbox';
      overlay.innerHTML = '<img src="' + img.src + '" alt="">';
      overlay.addEventListener('click', function () { overlay.remove(); });
      doc.body.appendChild(overlay);
    });
  });
})();

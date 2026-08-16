/* ==========================================================================
   Pa-etos AJAX Navigation
   Loads internal pages into the shell's #pt-content without full reloads.
   - hijacks internal page links (sidebar, cards, buttons)
   - shows a skeleton page + transparent spinner while loading
   - re-executes the fetched page's body scripts (data loads, handlers)
   - keeps browser history working (back/forward via popstate)
   - updates the active sidebar item + closes the mobile drawer
   ========================================================================== */
(function () {
  'use strict';

  var LOGIN_PAGES = ['admin_login.html', 'login.html'];
  var MOBILE_BP = 992;

  /* ------------------------------ helpers ------------------------------ */
  function absUrl(href) {
    try { return new URL(href, window.location.href); } catch (e) { return null; }
  }

  function pageName(url) {
    if (!url) return '';
    var seg = url.pathname.split('/').pop().toLowerCase();
    if (!seg || seg.indexOf('.') === -1) return '';
    return seg;
  }

  function isInternalPage(href) {
    var u = absUrl(href);
    if (!u) return false;
    if (u.origin !== window.location.origin) return false;
    var name = pageName(u);
    if (!name) return false;
    if (!/\.(php|html?)$/.test(name)) return false;
    if (LOGIN_PAGES.indexOf(name) !== -1) return false;
    if (name === 'admin_logout.php' || name === 'logout.php') return false;
    return true;
  }

  /* Used by paetos.js so it doesn't fire its full-page overlay for these links */
  function shouldHandle(a) {
    if (!a) return false;
    if (a.hasAttribute('data-ajax')) return false;
    if (a.hasAttribute('download')) return false;
    if (a.getAttribute('target') === '_blank') return false;
    var href = a.getAttribute('href');
    if (!href) return false;
    if (href.charAt(0) === '#') return false;
    if (/^\s*javascript:/i.test(href)) return false;
    if (/^(mailto|tel|data|javascript|ftp|blob):/i.test(href)) return false;
    return isInternalPage(href);
  }

  /* ------------------------------ loading UI ------------------------------ */
  function skeletonHTML() {
    var rows = '';
    for (var i = 0; i < 5; i++) {
      rows += '<tr><td><span class="pt-skel-row"></span></td><td><span class="pt-skel-row"></span></td>' +
              '<td><span class="pt-skel-row sm"></span></td><td><span class="pt-skel-row xs"></span></td></tr>';
    }
    return '' +
      '<div class="row pt-nav-skeleton">' +
      '<div class="col-xl-3 col-md-6"><div class="pt-skel-card"><span class="pt-skel-line w40"></span><span class="pt-skel-line w70"></span></div></div>' +
      '<div class="col-xl-3 col-md-6"><div class="pt-skel-card"><span class="pt-skel-line w40"></span><span class="pt-skel-line w70"></span></div></div>' +
      '<div class="col-xl-3 col-md-6"><div class="pt-skel-card"><span class="pt-skel-line w40"></span><span class="pt-skel-line w70"></span></div></div>' +
      '<div class="col-xl-3 col-md-6"><div class="pt-skel-card"><span class="pt-skel-line w40"></span><span class="pt-skel-line w70"></span></div></div>' +
      '<div class="col-12"><div class="pt-skel-card pt-skel-card-table"><span class="pt-skel-line w30"></span>' +
      '<table class="pt-skel-table"><tbody>' + rows + '</tbody></table></div></div>' +
      '</div>';
  }

  var spinner = null;
  function ensureSpinner() {
    if (spinner) return;
    spinner = document.createElement('div');
    spinner.id = 'pt-nav-spinner';
    spinner.innerHTML = '<span class="pt-spinner"></span>';
    document.body.appendChild(spinner);
  }

  function showLoading() {
    var host = document.getElementById('pt-content');
    if (host) {
      host.innerHTML = skeletonHTML();
      host.classList.add('pt-swapped');
    }
    ensureSpinner();
    spinner.classList.add('pt-active');
  }

  function hideLoading() {
    if (spinner) spinner.classList.remove('pt-active');
  }

  /* ------------------------------ scripts ------------------------------ */
  function loadScript(src) {
    return new Promise(function (resolve) {
      var el = document.createElement('script');
      el.src = src;
      el.async = true;
      el.onload = function () { resolve(); };
      el.onerror = function () { resolve(); };
      document.body.appendChild(el);
    });
  }

  function runScripts(doc, done) {
    var scripts = Array.prototype.slice.call(doc.body.querySelectorAll('script'));
    var existing = new Set();
    document.querySelectorAll('script[src]').forEach(function (s) {
      existing.add(s.src.split('?')[0]);
    });

    var chain = Promise.resolve();
    var inline = [];
    scripts.forEach(function (s) {
      if (s.src) {
        var src = s.src.split('?')[0];
        if (/jquery/i.test(src)) return;              // jQuery already present
        if (existing.has(src)) return;                // already loaded in shell
        chain = chain.then(function () { return loadScript(s.src); });
      } else if (s.textContent && s.textContent.trim()) {
        inline.push(s.textContent);
      }
    });

    chain.then(function () {
      inline.forEach(function (code) {
        var el = document.createElement('script');
        el.textContent = code;
        document.body.appendChild(el);
        el.parentNode.removeChild(el);
      });
    }).then(done, done);
  }

  /* ------------------------------ post-swap ------------------------------ */
  function setActiveMenu(url) {
    var menu = document.getElementById('menu');
    if (!menu) return;
    var path = url.pathname;
    var full = path + url.hash;

    menu.querySelectorAll('a.mm-active, a.active').forEach(function (a) {
      a.classList.remove('mm-active', 'active');
    });
    menu.querySelectorAll('li.mm-active, li.mm-show, li.active').forEach(function (li) {
      li.classList.remove('mm-active', 'mm-show', 'active');
    });

    var best = null;
    var bestLen = -1;
    menu.querySelectorAll('a[href]').forEach(function (a) {
      var u = absUrl(a.getAttribute('href'));
      if (!u || !u.pathname) return;
      var linkPath = u.pathname;
      var linkFull = linkPath + u.hash;
      var len = 0;
      if (full === linkFull) len = linkFull.length + 1000;      // exact (incl. hash) wins
      else if (path.indexOf(linkPath) === 0) len = linkPath.length;
      if (len > bestLen) { best = a; bestLen = len; }
    });

    if (best) {
      best.classList.add('mm-active', 'active');
      var p = best.parentElement;
      while (p && p.tagName.toLowerCase() !== 'ul') {
        if (p.tagName.toLowerCase() === 'li') p.classList.add('mm-active', 'active');
        p = p.parentElement;
      }
    }
  }

  function closeMobileDrawer() {
    if (window.innerWidth >= MOBILE_BP) return;
    var wrap = document.getElementById('main-wrapper');
    if (wrap && wrap.classList.contains('menu-toggle')) wrap.classList.remove('menu-toggle');
    var hb = document.querySelector('.hamburger');
    if (hb && hb.classList.contains('is-active')) hb.classList.remove('is-active');
  }

  function scrollToHash(hash) {
    if (!hash) return;
    var target = null;
    try { target = document.getElementById(hash.replace('#', '')); } catch (e) { target = null; }
    if (!target) return;
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function refreshPlugins() {
    try {
      if (window.jQuery && jQuery.fn && jQuery.fn.selectpicker) {
        jQuery('.default-select, select.selectpicker').selectpicker('refresh');
      }
    } catch (e) { /* non-fatal */ }
    if (window.PT && PT.initSkeletons) PT.initSkeletons();
  }

  /* Copy page-specific stylesheets into the shell head (e.g. DataTables css) */
  function syncStyles(doc) {
    var head = document.head;
    if (!head) return;
    var existing = new Set();
    head.querySelectorAll('link[rel="stylesheet"]').forEach(function (l) {
      var href = l.getAttribute('href');
      if (href) existing.add(href.split('?')[0]);
    });
    Array.prototype.slice.call(doc.head.querySelectorAll('link[rel="stylesheet"]')).forEach(function (l) {
      var href = l.getAttribute('href');
      if (!href) return;
      var base = href.split('?')[0];
      if (existing.has(base)) return;
      var el = document.createElement('link');
      el.rel = 'stylesheet';
      el.href = l.href;
      head.appendChild(el);
      existing.add(base);
    });
  }

  function swapContent(content, doc, href) {
    var host = document.getElementById('pt-content');
    if (!host) { window.location.href = href; return; }

    var url = new URL(href, window.location.href);
    syncStyles(doc);
    host.innerHTML = content.innerHTML;
    host.classList.remove('pt-swapped');
    void host.offsetWidth;
    host.classList.add('pt-swapped');

    var titleEl = doc.querySelector('title');
    if (titleEl && titleEl.textContent) document.title = titleEl.textContent;

    runScripts(doc, function () {
      setActiveMenu(url);
      closeMobileDrawer();
      refreshPlugins();
      scrollToHash(url.hash);
      if (url.hash) {
        history.replaceState({ pt: url.href }, '', url.href);
      }
      window.scrollTo(0, 0);
      // Dispatch on document so both document- and window-level listeners fire
      // (window.dispatchEvent never reaches document listeners).
      document.dispatchEvent(new Event('pt:content-loaded'));
    });
  }

  /* ------------------------------ loading ------------------------------ */
  var inFlight = false;

  function loadPage(href) {
    if (inFlight) return;
    inFlight = true;
    showLoading();

    fetch(href, { credentials: 'same-origin' })
      .then(function (res) {
        var finalUrl = new URL(res.url || href, window.location.href);
        var name = pageName(finalUrl);
        if (!res.ok || LOGIN_PAGES.indexOf(name) !== -1) {
          window.location.href = finalUrl.href;
          return null;
        }
        return res.text();
      })
      .then(function (html) {
        if (html === null) return;
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var content = doc.querySelector('.content-body');
        if (!content) {
          window.location.href = href;
          return;
        }
        swapContent(content, doc, href);
      })
      .catch(function () {
        window.location.href = href;
      })
      .finally(function () {
        inFlight = false;
        hideLoading();
      });
  }

  function navigate(href, push) {
    var url = absUrl(href);
    if (!url) return;
    var cur = absUrl(window.location.href);

    if (url.href === cur.href) return;

    if (url.pathname === cur.pathname) {
      if (url.hash && url.hash !== cur.hash) {
        if (push) history.pushState({ pt: url.href }, '', url.href);
        scrollToHash(url.hash);
      }
      return;
    }

    if (push) history.pushState({ pt: url.href }, '', url.href);
    loadPage(url.href);
  }

  /* ------------------------------ events ------------------------------ */
  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target && e.target.closest ? e.target.closest('a') : null;
    if (!a) return;
    if (!shouldHandle(a)) return;
    e.preventDefault();
    navigate(a.href, true);
  }, true);

  window.addEventListener('popstate', function () {
    var st = history.state && history.state.pt;
    if (st) loadPage(st);
    else window.location.reload();
  });

  window.PTNav = {
    shouldHandle: shouldHandle,
    navigate: function (href, push) { navigate(href, push !== false); },
    refresh: function () { loadPage(window.location.href); }
  };
})();

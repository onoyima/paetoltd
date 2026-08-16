/* ==========================================================================
   Pa-etos Portal Polish - shared JS
   - Branded page-transition overlay (no blank flash between pages)
   - Top progress bar on navigation
   - Themed skeleton loaders for tables and stat cards
   - Light/dark theme persistence
   - Global toast / confirm / spinner helpers (window.PT)
   ========================================================================== */
(function () {
  'use strict';

  var LOGO = 'images/paetoa.png';

  /* ------------------------------ Theme ------------------------------ */
  var THEME_KEY = 'pt_theme';

  function readTheme() {
    try {
      var t = localStorage.getItem(THEME_KEY);
      if (t === 'dark' || t === 'light') return t;
    } catch (e) { /* ignore */ }
    if (typeof getCookie === 'function') {
      return getCookie('version') === 'dark' ? 'dark' : 'light';
    }
    return 'light';
  }

  function writeTheme(t) {
    try { localStorage.setItem(THEME_KEY, t); } catch (e) { /* ignore */ }
    if (typeof setCookie === 'function') { setCookie('version', t); }
  }

  function applyTheme(t) {
    t = (t === 'dark') ? 'dark' : 'light';
    document.documentElement.setAttribute('data-pt-theme', t);
    document.body.setAttribute('data-theme-version', t);
    var bell = document.querySelector('.dlab-theme-mode');
    if (bell) { bell.classList.toggle('active', t === 'dark'); }
    var sw = document.getElementById('pt-theme-switch');
    if (sw) { sw.checked = (t === 'dark'); }
  }

  function initTheme() {
    var saved = readTheme();
    applyTheme(saved);
    writeTheme(saved);
    /* Persist when the built-in bell toggle is used */
    document.addEventListener('click', function (e) {
      var t = e.target && e.target.closest ? e.target.closest('.dlab-theme-mode') : null;
      if (t) {
        setTimeout(function () {
          var cur = document.body.getAttribute('data-theme-version');
          if (cur) { writeTheme(cur); document.documentElement.setAttribute('data-pt-theme', cur); }
        }, 10);
      }
    }, true);
    /* Sidebar bottom dark-mode switch */
    var sw = document.getElementById('pt-theme-switch');
    if (sw) {
      sw.addEventListener('change', function () {
        var cur = document.body.getAttribute('data-theme-version');
        PT.theme.set(cur === 'dark' ? 'light' : 'dark');
      });
    }
  }

  /* ------------------------------ Build overlay DOM ------------------------------ */
  var overlay = document.createElement('div');
  overlay.id = 'pt-overlay';
  overlay.innerHTML =
    '<div class="pt-logo">' +
      '<img src="' + LOGO + '" alt="Pa-etos Hostel">' +
      '<div class="pt-spinner"></div>' +
    '</div>';
  document.documentElement.appendChild(overlay);

  var progress = document.createElement('div');
  progress.id = 'pt-progress';
  document.documentElement.appendChild(progress);

  var progressTimer = null;
  var progressStep = null;

  function showOverlay() {
    document.body.style.cursor = 'progress';
    overlay.classList.add('pt-active');
    progress.classList.add('pt-active');
    progress.style.width = '0%';
    clearInterval(progressTimer);
    var w = 0;
    progressTimer = setInterval(function () {
      w = Math.min(w + Math.random() * 22, 88);
      progress.style.width = w + '%';
    }, 110);
  }

  function hideOverlay() {
    document.body.style.cursor = '';
    clearInterval(progressTimer);
    clearTimeout(progressStep);
    progress.style.width = '100%';
    progressStep = setTimeout(function () {
      progress.style.width = '0%';
      progress.classList.remove('pt-active');
      overlay.classList.remove('pt-active');
    }, 320);
  }

  /* Brand the template preloader with logo + load bar */
  function brandPreloader() {
    var pre = document.getElementById('preloader');
    if (!pre || pre.querySelector('.pt-pre-brand')) return;
    var brand = document.createElement('div');
    brand.className = 'pt-pre-brand';
    brand.innerHTML =
      '<img src="' + LOGO + '" alt="Pa-etos Hostel">' +
      '<div class="pt-spinner"></div>' +
      '<div class="pt-load-line"></div>';
    pre.appendChild(brand);
  }

  function forceHidePreloader() {
    var pre = document.getElementById('preloader');
    if (pre && !pre.classList.contains('pt-done')) {
      pre.classList.add('pt-done');
    }
  }

  function isSameOrigin(href) {
    try {
      var url = new URL(href, window.location.href);
      return url.origin === window.location.origin;
    } catch (e) {
      return false;
    }
  }

  /* ------------------------------ Link click interceptor ------------------------------ */
  document.addEventListener('click', function (e) {
    if (e.defaultPrevented) return;
    if (e.button !== 0) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    var a = e.target && e.target.closest ? e.target.closest('a') : null;
    if (!a) return;
    if (window.PTNav && PTNav.shouldHandle(a)) return;

    var href = a.getAttribute('href');
    if (!href) return;
    if (a.hasAttribute('download')) return;
    if (a.getAttribute('target') === '_blank') return;
    if (href.charAt(0) === '#') return;
    if (/^\s*javascript:/.test(href)) return;
    if (!isSameOrigin(href)) return;
    if (a.hasAttribute('data-ajax')) return;

    showOverlay();
  }, true);

  /* ------------------------------ Native form submit interceptor ------------------------------ */
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.hasAttribute('data-ajax')) return;
    if (form.getAttribute('onsubmit') || form.getAttribute('data-pt-no-overlay')) return;
    showOverlay();
  }, true);

  /* ------------------------------ Hide on arrival ------------------------------ */
  window.addEventListener('pageshow', function (e) {
    hideOverlay();
    forceHidePreloader();
  });

  window.addEventListener('load', function () {
    setTimeout(forceHidePreloader, 350);
    setTimeout(hideOverlay, 350);
  });

  /* ==========================================================================
     window.PT helpers
     ========================================================================== */
  window.PT = {
    theme: {
      get: readTheme,
      set: function (t) { writeTheme(t); applyTheme(t); },
      toggle: function () { this.set(this.get() === 'dark' ? 'light' : 'dark'); }
    },

    toast: function (type, message, title) {
      if (window.toastr && typeof toastr[type] === 'function') {
        toastr[type](message, title || '', {
          closeButton: true,
          progressBar: true,
          positionClass: 'toast-top-right',
          timeOut: 5000,
          extendedTimeOut: 1000,
          escapeHtml: false
        });
      } else {
        (type === 'error' ? console.error : console.log)(title || '', message);
      }
    },
    success: function (m, t) { this.toast('success', m, t); },
    error: function (m, t) { this.toast('error', m, t); },
    info: function (m, t) { this.toast('info', m, t); },
    warning: function (m, t) { this.toast('warning', m, t); },

    /* Swal2-based confirm (falls back to native confirm) */
    confirm: function (message, title, yesLabel) {
      return new Promise(function (resolve) {
        if (window.Swal) {
          Swal.fire({
            title: title || 'Are you sure?',
            text: message || '',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: yesLabel || 'Yes',
            confirmButtonColor: '#F93A0B',
            cancelButtonText: 'Cancel',
            buttonsStyling: true
          }).then(function (result) {
            resolve(!!result.value);
          });
        } else {
          resolve(window.confirm(message));
        }
      });
    },

    /* Button spinner: PT.btnLoading(btnElement, true/false) */
    btnLoading: function (btn, on) {
      if (!btn) return;
      if (on) {
        btn.classList.add('pt-loading');
        btn.setAttribute('disabled', 'disabled');
      } else {
        btn.classList.remove('pt-loading');
        btn.removeAttribute('disabled');
      }
    },

    /* Skeleton rows inside a table while its data loads.
       Pass the wrapper element (or the table). */
    tableLoading: function (wrap, on) {
      if (!wrap) return;
      var table = wrap.querySelector ? wrap.querySelector('table') : (wrap.tagName === 'TABLE' ? wrap : null);
      if (!table) return;
      var tbody = table.tBodies[0];
      if (!tbody) return;

      if (on) {
        if (!wrap.__ptOrigBody) wrap.__ptOrigBody = tbody.innerHTML;
        var head = table.tHead;
        var cols = head && head.rows.length ? head.rows[0].cells.length : 6;
        var rows = '';
        for (var r = 0; r < 5; r++) {
          rows += '<tr class="pt-skel-row">';
          for (var c = 0; c < cols; c++) {
            var cls = (c === cols - 1) ? ' sm' : (c === 0 ? ' xs' : '');
            rows += '<td><span class="pt-skel-cell' + cls + '"></span></td>';
          }
          rows += '</tr>';
        }
        tbody.innerHTML = rows;
        tbody.setAttribute('data-pt-skeleton', '1');
      } else {
        // Only restore the pre-load body if the skeleton is still showing.
        // If the caller already replaced the content (rows appended), leave it alone
        // so we never wipe freshly-loaded data.
        var skeletonStill = tbody.getAttribute('data-pt-skeleton') === '1' &&
                            tbody.querySelector('.pt-skel-cell') !== null;
        if (skeletonStill && wrap.__ptOrigBody !== null && wrap.__ptOrigBody !== undefined) {
          tbody.innerHTML = wrap.__ptOrigBody;
        }
        wrap.__ptOrigBody = null;
        tbody.removeAttribute('data-pt-skeleton');
      }
    },

    /* Skeleton on stat counts: PT.skeleton(id) then auto-removes on update */
    skeleton: function (id) {
      var el = typeof id === 'string' ? document.getElementById(id) : id;
      if (!el) return;
      el.classList.add('pt-skeleton');
      var initial = el.textContent;
      var remove = function () { el.classList.remove('pt-skeleton'); };
      var obs = new MutationObserver(function (muts) {
        var changed = muts.some(function (m) {
          return m.type === 'characterData' ||
                 (m.type === 'childList' && m.target.textContent !== initial);
        });
        if (changed) {
          obs.disconnect();
          remove();
        }
      });
      obs.observe(el, { childList: true, characterData: true, subtree: true });
      setTimeout(function () { obs.disconnect(); remove(); }, 6000);
    },

    /* Auto-skeleton every [data-pt-count] element present in the page */
    initSkeletons: function () {
      var els = document.querySelectorAll('[data-pt-count]');
      for (var i = 0; i < els.length; i++) {
        this.skeleton(els[i]);
      }
    }
  };

  /* ------------------------------ Auto init ------------------------------ */
  function init() {
    initTheme();
    brandPreloader();
    window.PT.initSkeletons();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

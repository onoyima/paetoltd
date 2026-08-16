/* ============ PA-ETOS shared site scripts ============ */
(function () {
    'use strict';

    // ---------- Preloader ----------
    var pre = document.getElementById('preloader');
    var preShownAt = Date.now();
    function hidePreloader() {
        var wait = Math.max(0, 600 - (Date.now() - preShownAt));
        setTimeout(function () {
            if (pre && !pre.classList.contains('loaded')) {
                pre.classList.add('loaded');
            }
        }, wait);
    }
    if (pre) {
        var rs = document.readyState;
        if (rs === 'complete' || rs === 'interactive') {
            hidePreloader();
        } else {
            document.addEventListener('DOMContentLoaded', hidePreloader);
            window.addEventListener('load', hidePreloader);
            setTimeout(hidePreloader, 3000);
        }
    }

    // ---------- Scroll reveal ----------
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    en.target.classList.add('revealed');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('revealed'); });
    }

    // ---------- Animated counters ----------
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length && 'IntersectionObserver' in window) {
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                cio.unobserve(en.target);
                var el = en.target;
                var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                var suffix = el.getAttribute('data-suffix') || '';
                var dur = 1500;
                var start = null;
                function step(ts) {
                    if (!start) start = ts;
                    var p = Math.min((ts - start) / dur, 1);
                    el.textContent = Math.floor(p * target) + suffix;
                    if (p < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (c) { cio.observe(c); });
    }

    // ---------- Gallery filters ----------
    var filterBtns = document.querySelectorAll('[data-filter]');
    var galleryItems = document.querySelectorAll('.gallery-item');
    if (filterBtns.length) {
        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) { b.classList.remove('gallery-filter-active'); });
                btn.classList.add('gallery-filter-active');
                var f = btn.getAttribute('data-filter');
                galleryItems.forEach(function (item) {
                    var show = (f === 'all') || (item.getAttribute('data-category') === f);
                    item.classList.toggle('gallery-hidden', !show);
                    if (show) {
                        item.classList.remove('revealed');
                        void item.offsetWidth;
                        item.classList.add('revealed');
                    }
                });
            });
        });
    }

    // ---------- Lightbox ----------
    var lightbox = document.getElementById('lightbox');
    if (lightbox) {
        var lbImg = lightbox.querySelector('.lightbox-img');
        var lbCap = lightbox.querySelector('.lightbox-caption');
        var lbItems = Array.prototype.slice.call(galleryItems);
        var currentIdx = 0;

        function visibleItems() {
            return lbItems.filter(function (it) {
                return !it.classList.contains('gallery-hidden');
            });
        }

        function showLb(i) {
            var vis = visibleItems();
            if (!vis.length) return;
            currentIdx = (i + vis.length) % vis.length;
            var img = vis[currentIdx].querySelector('img');
            lbImg.src = img.getAttribute('src');
            lbImg.alt = img.alt;
            lbCap.textContent = img.alt;
        }

        galleryItems.forEach(function (item, idx) {
            item.addEventListener('click', function () {
                var vis = visibleItems();
                var img = item.querySelector('img');
                lbImg.src = img.getAttribute('src');
                lbImg.alt = img.alt;
                lbCap.textContent = img.alt;
                currentIdx = vis.indexOf(item);
                lightbox.classList.add('open');
                document.body.style.overflow = 'hidden';
            });
        });

        function closeLb() {
            lightbox.classList.remove('open');
            document.body.style.overflow = '';
        }

        var lbClose = document.getElementById('lightbox-close');
        var lbPrev = document.getElementById('lightbox-prev');
        var lbNext = document.getElementById('lightbox-next');
        if (lbClose) lbClose.addEventListener('click', closeLb);
        if (lbPrev) lbPrev.addEventListener('click', function (e) { e.stopPropagation(); showLb(currentIdx - 1); });
        if (lbNext) lbNext.addEventListener('click', function (e) { e.stopPropagation(); showLb(currentIdx + 1); });
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) closeLb();
        });
        document.addEventListener('keydown', function (e) {
            if (!lightbox.classList.contains('open')) return;
            if (e.key === 'Escape') closeLb();
            else if (e.key === 'ArrowLeft') showLb(currentIdx - 1);
            else if (e.key === 'ArrowRight') showLb(currentIdx + 1);
        });
    }
})();

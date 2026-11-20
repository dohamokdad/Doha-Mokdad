/* Design 9: menu, sticky header, scroll reveal, and live screenshots on the laptop photos. No libraries. */
(function () {
	'use strict';
	var d = document, root = d.documentElement;
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* Mobile menu */
	var btn = d.querySelector('.dp9-menu'), nav = d.getElementById('dp9-nav');
	if (btn && nav) {
		var close = function () { nav.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); };
		btn.addEventListener('click', function () {
			var open = !nav.classList.contains('is-open');
			nav.classList.toggle('is-open', open);
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		nav.addEventListener('click', function (e) { if (e.target.closest('a')) { close(); } });
		d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); } });
	}

	/* Header line once the page scrolls */
	var head = d.querySelector('.dp9-header');
	if (head) {
		var onScroll = function () { head.classList.toggle('is-stuck', window.scrollY > 8); };
		window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
	}

	/* Scroll reveal: only elements below the first screen, so nothing visible ever blinks. */
	if ('IntersectionObserver' in window && !reduce) {
		var io = new IntersectionObserver(function (es) {
			es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-seen'); io.unobserve(e.target); } });
		}, { rootMargin: '0px 0px -8% 0px' });
		d.querySelectorAll('.dp9-section .dp9-head, .dp9-section .dp9-split > *, .dp9-svc > *, .dp9-project, .dp9-case, .dp9-timeline li, .dp9-end-inner, .dp9-form-wrap, .dp9-price').forEach(function (el) {
			if (el.getBoundingClientRect().top > window.innerHeight) { el.classList.add('dp9-rv'); io.observe(el); }
		});
	}

	/* ---------- Laptop screens ----------
	 * A 1000×625 "screen" is mapped onto the photo's screen corners with a CSS matrix3d (a homography),
	 * so a real screenshot sits in perspective on the laptop. Each slide scrolls from the top, then the next one fades in. */
	var SW = 1000, SH = 625;

	function solve(A, b) { // Gaussian elimination, 8×8
		var n = 8, i, j, k;
		for (i = 0; i < n; i++) {
			var m = i;
			for (k = i + 1; k < n; k++) { if (Math.abs(A[k][i]) > Math.abs(A[m][i])) { m = k; } }
			var t = A[i]; A[i] = A[m]; A[m] = t; t = b[i]; b[i] = b[m]; b[m] = t;
			for (k = i + 1; k < n; k++) {
				var f = A[k][i] / A[i][i];
				for (j = i; j < n; j++) { A[k][j] -= f * A[i][j]; }
				b[k] -= f * b[i];
			}
		}
		var x = new Array(n);
		for (i = n - 1; i >= 0; i--) {
			var s = b[i];
			for (j = i + 1; j < n; j++) { s -= A[i][j] * x[j]; }
			x[i] = s / A[i][i];
		}
		return x;
	}

	function homography(dst) {
		var src = [[0, 0], [SW, 0], [SW, SH], [0, SH]], A = [], b = [];
		for (var i = 0; i < 4; i++) {
			var x = src[i][0], y = src[i][1], u = dst[i][0], v = dst[i][1];
			A.push([x, y, 1, 0, 0, 0, -u * x, -u * y]); b.push(u);
			A.push([0, 0, 0, x, y, 1, -v * x, -v * y]); b.push(v);
		}
		var h = solve(A, b);
		return 'matrix3d(' + [h[0], h[3], 0, h[6], h[1], h[4], 0, h[7], 0, 0, 1, 0, h[2], h[5], 0, 1].join(',') + ')';
	}

	d.querySelectorAll('.dp9-scene').forEach(function (scene) {
		var screen = scene.querySelector('.dp9-screen');
		if (!screen) { return; }
		var quad = (scene.getAttribute('data-quad') || '').split(' ').map(function (p) { return p.split(',').map(parseFloat); });
		if (quad.length !== 4) { return; }
		var slides = [].slice.call(screen.querySelectorAll('.dp9-slide'));
		var now = d.querySelector('[data-now]');
		var cur = 0, timer = 0, visible = false;

		function place() {
			var w = scene.clientWidth, h = scene.clientHeight;
			if (!w || !h) { return; }
			screen.style.transform = homography(quad.map(function (p) { return [p[0] / 100 * w, p[1] / 100 * h]; }));
			screen.classList.add('is-ready');
		}
		function measure(slide) {
			var img = slide.querySelector('img');
			if (!img || !img.naturalWidth) { return 0; }
			var hh = SW * img.naturalHeight / img.naturalWidth;
			var dist = Math.max(0, hh - SH);
			slide.style.setProperty('--dist', (-dist) + 'px');
			var dur = Math.min(14, Math.max(4, dist / 260));
			slide.style.setProperty('--dur', dur + 's');
			return dur;
		}
		function show(i) {
			slides.forEach(function (s, k) { s.classList.toggle('is-on', k === i); });
			var s = slides[i], img = s.querySelector('img');
			if (now && scene.classList.contains('is-hero')) { now.textContent = s.getAttribute('data-title') || ''; }
			var go = function () {
				var dur = reduce ? 5 : measure(s) + 2.6;
				clearTimeout(timer);
				if (slides.length > 1) { timer = setTimeout(next, dur * 1000); }
			};
			if (img && !img.complete) { img.loading = 'eager'; img.addEventListener('load', go, { once: true }); } else { go(); }
		}
		function next() {
			if (!visible || d.hidden) { timer = setTimeout(next, 1500); return; }
			cur = (cur + 1) % slides.length;
			var n = slides[(cur + 1) % slides.length].querySelector('img');
			if (n) { n.loading = 'eager'; }
			show(cur);
		}

		place();
		var sceneImg = scene.querySelector('.dp9-scene-img');
		if (sceneImg && !sceneImg.complete) { sceneImg.addEventListener('load', place, { once: true }); }
		if ('ResizeObserver' in window) { new ResizeObserver(place).observe(scene); } else { window.addEventListener('resize', place); }

		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (es) { visible = es[0].isIntersecting; }, { threshold: .15 }).observe(scene);
		} else { visible = true; }
		if (slides.length) { show(0); }
	});
})();

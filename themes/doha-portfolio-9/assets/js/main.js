/* Doha Portfolio v6: motion with one authored moment (the hero), purposeful reveals elsewhere.
   GSAP + ScrollTrigger + Flip. Everything is visible without JS; reduced motion skips movement. */
(function () {
	'use strict';

	var G = typeof window.gsap !== 'undefined';
	var ST = G && window.ScrollTrigger;
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var editor = document.body.classList.contains('elementor-editor-active') || /elementor-preview/.test(location.search);
	var animate = G && !reduce && !editor;
	var EASE_OUT = 'expo.out';

	if (G) { if (ST) gsap.registerPlugin(ScrollTrigger); if (window.Flip) gsap.registerPlugin(Flip); }
	if (animate) document.documentElement.classList.add('dp-anim');

	function all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
	function once(el, key) { if (el.dataset[key]) return false; el.dataset[key] = '1'; return true; }

	function splitChars(el) {
		var text = el.textContent.trim(), out = [];
		// Arabic letters join, so right-to-left titles animate word by word.
		var rtl = document.documentElement.dir === 'rtl' || /[\u0600-\u06FF]/.test(text);
		if (rtl) {
			el.setAttribute('aria-label', text); el.textContent = '';
			text.split(/\s+/).forEach(function (word, i) {
				if (i) el.appendChild(document.createTextNode(' '));
				var w = document.createElement('span'); w.className = 'sw sc'; w.setAttribute('aria-hidden', 'true'); w.textContent = word; el.appendChild(w); out.push(w);
			});
			return out;
		}
		el.setAttribute('aria-label', text);
		el.textContent = '';
		text.split(/(\s+)/).forEach(function (word) {
			if (/^\s+$/.test(word)) { el.appendChild(document.createTextNode(' ')); return; }
			var w = document.createElement('span'); w.className = 'sw'; w.setAttribute('aria-hidden', 'true');
			word.split('').forEach(function (ch) { var c = document.createElement('span'); c.className = 'sc'; c.textContent = ch; w.appendChild(c); out.push(c); });
			el.appendChild(w);
		});
		return out;
	}

	/* ---------- Screenshot loading (mShots retry, tall detection) ---------- */
	function initShots(root) {
		all('.shot img', root).forEach(function (img) {
			if (!once(img, 'shot')) return;
			if (img.parentNode.classList.contains('is-logo')) return;
			var box = img.parentNode, tries = 0;
			function done() {
				if (img.naturalWidth && img.naturalWidth < 420 && img.src.indexOf('mshots') > -1 && tries < 5) {
					tries++; setTimeout(function () { img.src = img.src.replace(/&r=\d+$/, '') + '&r=' + tries; }, 3500 * tries); return;
				}
				box.classList.add('loaded');
				if (img.naturalHeight / img.naturalWidth > 0.9) {
					box.classList.add('is-tall');
					if (box.closest('[data-scroll-shot]')) box.classList.add('is-scroll');
				}
			}
			if (img.complete && img.naturalWidth) done(); else img.addEventListener('load', done);
			img.addEventListener('error', function () { box.classList.add('failed'); });
		});
	}

	/* ---------- The authored moment: hero ---------- */
	function initHero(root) {
		all('[data-anim="hero"]', root).forEach(function (hero) {
			if (!once(hero, 'hero') || !animate) return;
			var title = hero.querySelector('.hero-title');
			var chars = title ? splitChars(title) : [];
			var cards = all('.show-card', hero);
			hero.classList.add('intro');
			var tl = gsap.timeline({ defaults: { ease: EASE_OUT }, onComplete: function () { hero.classList.remove('intro'); } });
			tl.from(chars, { yPercent: 105, rotateX: -60, opacity: 0, duration: 1.1, stagger: 0.03, transformOrigin: '50% 100%' }, 0.1)
			  .from(hero.querySelectorAll('[data-a="fade"]'), { y: 18, opacity: 0, duration: 0.9, stagger: 0.08 }, 0.55);
			if (cards.length) {
				// Cards rise from below in a fan; the front card lands last.
				tl.from(cards.slice().reverse(), { y: 140, rotate: function (i) { return [14, -14, 0][i] || 0; }, opacity: 0, duration: 1.3, stagger: 0.12, clearProps: 'transform,opacity' }, 0.25);
				if (ST) {
					// On scroll the fan gently folds together and drifts up.
					var wrap = hero.querySelector('.showcase');
					gsap.to(wrap, { yPercent: -10, rotateX: 8, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.6 } });
				}
			}
			if (ST) gsap.to(hero.querySelector('.hero-bg'), { yPercent: 18, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
		});
	}

	/* ---------- Project cards: image reveal, tilt, hover-scroll ---------- */
	function initCards(root) {
		var cards = all('.card', root).filter(function (c) { return once(c, 'card'); });
		if (animate && ST && cards.length) {
			cards.forEach(function (c) {
				gsap.set(c, { opacity: 0, y: 28 });
				gsap.set(c.querySelector('.shot'), { clipPath: 'inset(0 0 100% 0)' });
			});
			ScrollTrigger.batch(cards, {
				start: 'top 90%', once: true,
				onEnter: function (batch) {
					gsap.to(batch, { opacity: 1, y: 0, duration: 0.8, ease: EASE_OUT, stagger: 0.07, overwrite: true });
					gsap.to(batch.map(function (c) { return c.querySelector('.shot'); }), { clipPath: 'inset(0 0 0% 0)', duration: 1.1, ease: 'expo.inOut', stagger: 0.07, delay: 0.05 });
				}
			});
		}
		// Hover-scroll works on its own (plain CSS transition), so it never depends on GSAP loading.
		if (fine && !reduce) cards.forEach(hoverScroll);
		if (!fine || reduce || !G) return;
		cards.forEach(function (card) {
			var frame = card.querySelector('.bezel') || card.querySelector('.browser'), glare = card.querySelector('.browser');
			if (!frame) return;
			var rx = gsap.quickTo(frame, 'rotateX', { duration: 0.6, ease: 'power3' });
			var ry = gsap.quickTo(frame, 'rotateY', { duration: 0.6, ease: 'power3' });
			card.addEventListener('pointermove', function (e) {
				var r = card.getBoundingClientRect(), px = (e.clientX - r.left) / r.width - 0.5, py = (e.clientY - r.top) / r.height - 0.5;
				ry(px * 6); rx(-py * 4);
				glare.style.setProperty('--gx', ((px + 0.5) * 100) + '%');
				glare.style.setProperty('--gy', ((py + 0.5) * 100) + '%');
			});
			card.addEventListener('pointerleave', function () { rx(0); ry(0); });
		});
	}

	/* Long screenshots scroll through the page while the pointer is on the card.
	   The image is measured at hover time, so it also works for images that finished loading late
	   or were swapped in by a lazy-load / optimisation plugin. */
	function hoverScroll(card) {
		var shot = card.querySelector('.shot');
		if (!shot || shot.classList.contains('is-logo')) return;
		card.addEventListener('pointerenter', function () {
			var img = shot.querySelector('img');
			if (!img || !img.naturalWidth || img.naturalHeight / img.naturalWidth <= 0.9) return;
			shot.classList.add('is-tall', 'loaded');
			var dist = img.offsetHeight - shot.clientHeight;
			if (dist <= 2) return;
			var dur = Math.min(7, Math.max(2.5, dist / 240));
			img.style.transition = 'transform ' + dur + 's cubic-bezier(.45,0,.55,1)';
			img.style.transform = 'translate3d(0,' + (-dist) + 'px,0)';
		});
		card.addEventListener('pointerleave', function () {
			var img = shot.querySelector('img');
			if (!img) return;
			img.style.transition = 'transform .7s cubic-bezier(.16,1,.3,1)';
			img.style.transform = '';
		});
	}

	/* ---------- Filters + search (Flip) ---------- */
	function initFilters(root) {
		all('[data-grid]', root).forEach(function (f) {
			if (!once(f, 'filters')) return;
			var grid = f.nextElementSibling, empty = grid.nextElementSibling;
			var cards = all('.card', grid), chips = all('.chip', f), input = f.querySelector('[data-search-input]');
			var current = '*', RHYTHM = [7, 5, 4, 4, 4, 5, 7], bento = grid.classList.contains('is-bento');
			chips.forEach(function (c) { c.setAttribute('aria-pressed', c.classList.contains('is-active') ? 'true' : 'false'); });
			function apply() {
				var q = (input.value || '').trim().toLowerCase(), shown = 0;
				var state = (animate && window.Flip) ? Flip.getState(cards) : null;
				cards.forEach(function (c) {
					var ok = (current === '*' || c.dataset.type === current) && (!q || c.dataset.search.indexOf(q) > -1);
					c.hidden = !ok; if (ok) shown++;
					if (ok && G) { gsap.set(c, { opacity: 1, y: 0 }); gsap.set(c.querySelector('.shot'), { clipPath: 'inset(0 0 0% 0)' }); }
				});
				if (empty) empty.hidden = shown > 0;
				// Keep the bento rhythm intact for whatever is left after filtering.
				if (bento) { var n = 0; cards.forEach(function (c) { if (!c.hidden) c.dataset.span = RHYTHM[n++ % RHYTHM.length]; }); }
				if (state) {
					Flip.from(state, {
						duration: 0.5, ease: 'power3.inOut', absolute: true, nested: true,
						onEnter: function (els) { return gsap.fromTo(els, { opacity: 0, scale: 0.94 }, { opacity: 1, scale: 1, duration: 0.4, ease: EASE_OUT }); },
						onLeave: function (els) { return gsap.to(els, { opacity: 0, scale: 0.94, duration: 0.2, ease: 'power2.out' }); }
					});
				}
				if (ST) setTimeout(function () { ScrollTrigger.refresh(); }, 550);
			}
			chips.forEach(function (ch) {
				ch.addEventListener('click', function () {
					chips.forEach(function (x) { x.classList.remove('is-active'); x.setAttribute('aria-pressed', 'false'); });
					ch.classList.add('is-active'); ch.setAttribute('aria-pressed', 'true');
					current = ch.dataset.filter; apply();
				});
			});
			var t; input.addEventListener('input', function () { clearTimeout(t); t = setTimeout(apply, 160); });
		});
	}

	/* ---------- Section reveals: each section enters its own way ---------- */
	function initReveals(root) {
		if (!animate || !ST) return;
		function on(el, vars, start) {
			if (!el || !once(el, 'rev')) return;
			vars.scrollTrigger = { trigger: el, start: start || 'top 86%', once: true };
			gsap.from(el, vars);
		}
		// Headings resolve out of a soft blur.
		all('.section-head h2, .section-side h2, .company-head h2', root).forEach(function (h) {
			on(h, { y: 36, opacity: 0, filter: 'blur(10px)', duration: 1.1, ease: EASE_OUT, clearProps: 'filter' });
		});
		all('.side-intro, .section-head + .filters', root).forEach(function (el) { on(el, { y: 20, opacity: 0, duration: 0.9, ease: EASE_OUT, delay: 0.12 }); });
		// Process: steps light up one after another, the line draws across.
		all('.steps', root).forEach(function (st) {
			if (!once(st, 'rev')) return;
			var tl = gsap.timeline({ defaults: { ease: EASE_OUT }, scrollTrigger: { trigger: st, start: 'top 80%', once: true } });
			tl.fromTo(st, { '--line': 0 }, { '--line': 1, duration: 1.4 }, 0)
			  .from(st.querySelectorAll('.step'), { y: 30, opacity: 0, duration: 0.9, stagger: 0.18 }, 0.1);
		});
		// Skills: tiles settle in a cascade.
		all('.bento', root).forEach(function (b) {
			if (!once(b, 'rev')) return;
			gsap.from(b.children, { y: 40, scale: 0.97, opacity: 0, duration: 1, ease: EASE_OUT, stagger: 0.08, scrollTrigger: { trigger: b, start: 'top 82%', once: true } });
		});
		// Recognition: frames lift in one after another, slightly rotated, then settle.
		all('.awards', root).forEach(function (w) {
			if (!once(w, 'rev')) return;
			gsap.from(w.querySelectorAll('.award'), { y: 60, rotate: function (i) { return [-3, 2, -2][i % 3]; }, opacity: 0, duration: 1.2, ease: EASE_OUT, stagger: 0.12, scrollTrigger: { trigger: w, start: 'top 80%', once: true } });
		});
		// Contact: the headline wipes up, details follow line by line.
		all('.contact', root).forEach(function (c) {
			if (!once(c, 'rev')) return;
			var tl = gsap.timeline({ defaults: { ease: EASE_OUT }, scrollTrigger: { trigger: c, start: 'top 70%', once: true } });
			tl.from(c.querySelector('h2'), { clipPath: 'inset(0 0 100% 0)', y: 40, duration: 1.1 })
			  .from(c.querySelectorAll('.contact-text, .contact-inner .btn'), { y: 18, opacity: 0, duration: 0.8, stagger: 0.08 }, 0.25)
			  .from(c.querySelectorAll('.contact-list > div'), { x: 24, opacity: 0, duration: 0.8, stagger: 0.07 }, 0.3);
		});
	}

	/* ---------- Certificates: filter, show all, lightbox ---------- */
	function initCerts(root) {
		all('[data-certs]', root).forEach(function (w) {
			if (!once(w, 'certs')) return;
			var list = w.querySelector('.certs'), items = all('.cert', w), chips = all('.chip', w), more = w.querySelector('[data-certs-more]');
			chips.forEach(function (ch) {
				ch.addEventListener('click', function () {
					chips.forEach(function (x) { x.classList.remove('is-active'); x.setAttribute('aria-pressed', 'false'); });
					ch.classList.add('is-active'); ch.setAttribute('aria-pressed', 'true');
					var f = ch.dataset.filter;
					// A specific type shows all of its certificates; "All" returns to the short list.
					if (f !== '*') { list.classList.remove('is-collapsed'); if (more) more.parentNode.hidden = true; }
					var shown = [];
					items.forEach(function (it) { it.hidden = !(f === '*' || it.dataset.type === f); if (!it.hidden) shown.push(it); });
					if (animate) gsap.fromTo(shown, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.5, ease: EASE_OUT, stagger: 0.03, overwrite: true, clearProps: 'transform' });
					if (ST) ScrollTrigger.refresh();
				});
			});
			if (more) more.addEventListener('click', function () {
				var extra = all('.cert[data-extra]', list);
				list.classList.remove('is-collapsed'); more.parentNode.hidden = true;
				if (animate) gsap.from(extra, { opacity: 0, y: 16, duration: 0.6, ease: EASE_OUT, stagger: 0.03, clearProps: 'all' });
				if (ST) ScrollTrigger.refresh();
			});
		});
	}

	var lb = null;
	function initLightbox() {
		var dlg = document.querySelector('dialog.lightbox');
		if (!dlg || lb || typeof dlg.showModal !== 'function') return;
		var img = dlg.querySelector('img'), cap = dlg.querySelector('figcaption'), set = [], idx = 0;
		function show(i) {
			idx = (i + set.length) % set.length;
			var t = set[idx];
			img.src = t.dataset.full || t.getAttribute('href');
			img.alt = t.dataset.caption || t.getAttribute('aria-label') || '';
			cap.textContent = t.dataset.caption || '';
		}
		function visible(el) { return el.offsetParent !== null; }
		document.addEventListener('click', function (e) {
			var t = e.target.closest('.cert-thumb, .award-img');
			if (!t) return;
			e.preventDefault();
			var group = t.classList.contains('cert-thumb') ? '.cert-thumb' : '.award-img';
			set = all(group).filter(visible);
			show(set.indexOf(t));
			dlg.showModal();
		});
		dlg.querySelector('.lb-close').addEventListener('click', function () { dlg.close(); });
		dlg.querySelector('.lb-prev').addEventListener('click', function () { show(idx - 1); });
		dlg.querySelector('.lb-next').addEventListener('click', function () { show(idx + 1); });
		dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
		dlg.addEventListener('keydown', function (e) {
			var rtl = document.dir === 'rtl';
			if (e.key === 'ArrowRight') show(idx + (rtl ? -1 : 1));
			if (e.key === 'ArrowLeft') show(idx + (rtl ? 1 : -1));
		});
		lb = dlg;
	}

	/* ---------- Hero numbers count up once ---------- */
	function initCounters(root) {
		if (!animate) return;
		all('[data-count]', root).forEach(function (el) {
			if (!once(el, 'cnt')) return;
			var end = parseInt(el.dataset.count, 10) || 0, suf = el.dataset.suffix || '', o = { v: 0 };
			el.textContent = '0' + suf;
			gsap.to(o, { v: end, duration: 1.6, delay: 0.9, ease: 'power3.out', onUpdate: function () { el.textContent = Math.round(o.v) + suf; } });
		});
	}

	/* ---------- "View" cursor over project cards (mouse only) ---------- */
	function initCursor() {
		if (!fine || reduce || !G || document.querySelector('.cursor-dot') || !document.querySelector('.card-link')) return;
		var dot = document.createElement('div'); dot.className = 'cursor-dot'; dot.setAttribute('aria-hidden', 'true');
		dot.textContent = document.documentElement.dir === 'rtl' ? 'عرض' : 'View';
		document.body.appendChild(dot); document.documentElement.classList.add('has-cursor');
		var x = gsap.quickTo(dot, 'x', { duration: 0.35, ease: 'power3' }), y = gsap.quickTo(dot, 'y', { duration: 0.35, ease: 'power3' });
		window.addEventListener('pointermove', function (e) { x(e.clientX); y(e.clientY); }, { passive: true });
		document.addEventListener('pointerover', function (e) { dot.classList.toggle('on', !!e.target.closest('.card-link')); });
	}

	/* ---------- Timeline: progress line follows reading position ---------- */
	function initTimeline(root) {
		if (!animate || !ST) return;
		all('.timeline-wrap', root).forEach(function (w) {
			if (!once(w, 'tl')) return;
			gsap.fromTo(w.querySelector('.timeline-progress'), { scaleY: 0 }, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: w, start: 'top 65%', end: 'bottom 65%', scrub: 0.5 } });
			all('.timeline > li', w).forEach(function (li) {
				ScrollTrigger.create({ trigger: li, start: 'top 65%', onEnter: function () { li.classList.add('is-in'); }, onLeaveBack: function () { li.classList.remove('is-in'); } });
			});
		});
	}

	/* ---------- Single project frame ---------- */
	function initSingle(root) {
		if (!animate || !ST) return;
		all('[data-scroll-shot]', root).forEach(function (b) {
			if (!once(b, 'single')) return;
			gsap.from(b, { y: 60, opacity: 0, rotateX: 8, transformPerspective: 1400, duration: 1.2, ease: EASE_OUT });
		});
	}

	/* ---------- Magnetic buttons (decorative, pointer only) ---------- */
	function initMagnetic(root) {
		if (!fine || reduce || !G) return;
		all('.magnetic', root).forEach(function (b) {
			if (!once(b, 'mag')) return;
			var x = gsap.quickTo(b, 'x', { duration: 0.5, ease: 'power3' }), y = gsap.quickTo(b, 'y', { duration: 0.5, ease: 'power3' });
			b.addEventListener('pointermove', function (e) { var r = b.getBoundingClientRect(); x((e.clientX - r.left - r.width / 2) * 0.2); y((e.clientY - r.top - r.height / 2) * 0.3); });
			b.addEventListener('pointerleave', function () { x(0); y(0); });
		});
	}

	/* ---------- Header + progress bar (ScrollTrigger, no scroll listeners) ---------- */
	/* Light / dark switch: remembers the choice; without one, the system setting decides. */
	function initThemeToggle() {
		var root = document.documentElement, mq = window.matchMedia('(prefers-color-scheme: dark)');
		all('.theme-toggle').forEach(function (b) {
			b.addEventListener('click', function () {
				var cur = root.getAttribute('data-theme') || (mq.matches ? 'dark' : 'light');
				var next = cur === 'dark' ? 'light' : 'dark';
				root.classList.add('theme-anim');
				root.setAttribute('data-theme', next);
				try { localStorage.setItem('dp-theme', next); } catch (e) {}
				setTimeout(function () { root.classList.remove('theme-anim'); }, 400);
			});
		});
	}

	function initChrome() {
		initThemeToggle();
		var header = document.querySelector('.site-header'), bar = document.querySelector('.scroll-progress');
		var toggle = document.querySelector('.nav-toggle'), nav = document.getElementById('primary-nav');
		if (toggle && nav) {
			toggle.addEventListener('click', function () { var open = nav.classList.toggle('open'); toggle.setAttribute('aria-expanded', open ? 'true' : 'false'); });
			nav.addEventListener('click', function (e) { if (e.target.closest('a')) { nav.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); } });
			document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && nav.classList.contains('open')) { nav.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); toggle.focus(); } });
		}
		if (!ST) return;
		ScrollTrigger.create({
			start: 0, end: 'max',
			onUpdate: function (self) {
				if (bar) bar.style.transform = 'scaleX(' + self.progress.toFixed(4) + ')';
				if (!header) return;
				var y = self.scroll();
				header.classList.toggle('is-scrolled', y > 10);
				var hide = !reduce && y > 320 && self.direction === 1 && !(nav && nav.classList.contains('open'));
				header.classList.toggle('is-hidden', hide);
			}
		});
	}

	function initAll(root) { initShots(root); initHero(root); initFilters(root); initCards(root); initReveals(root); initCounters(root); initCerts(root); initLightbox(); initTimeline(root); initSingle(root); initMagnetic(root); }

	function boot() { initChrome(); initAll(document); initCursor(); }
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
	window.addEventListener('load', function () { if (ST) ScrollTrigger.refresh(); });

	/* Elementor editor: re-run when a widget re-renders. */
	window.addEventListener('elementor/frontend/init', function () {
		if (!window.elementorFrontend || !elementorFrontend.hooks) return;
		['dp-hero', 'dp-services', 'dp-projects', 'dp-why', 'dp-process', 'dp-experience', 'dp-faq', 'dp-testimonials', 'dp-service-page', 'dp-skills', 'dp-recognition', 'dp-certificates', 'dp-contact'].forEach(function (name) {
			elementorFrontend.hooks.addAction('frontend/element_ready/' + name + '.default', function ($scope) { var el = $scope && ($scope[0] || $scope); if (el) initAll(el); });
		});
	});
})();

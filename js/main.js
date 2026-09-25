(function () {
  'use strict';

  var reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Sobe este número sempre que uma imagem em assets/ for substituída mantendo o mesmo
  // nome de arquivo, para forçar o navegador/CDN a buscar a versão nova (cache-busting).
  var ASSET_VERSION = '4';

  // ---------- helpers ----------
  function escapeHtml(str) {
    return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function picture(basePath, alt, cls, extraAttrs) {
    extraAttrs = extraAttrs || '';
    return '<picture>' +
      '<source srcset="' + basePath + '.webp?v=' + ASSET_VERSION + '" type="image/webp">' +
      '<img src="' + basePath + '.png?v=' + ASSET_VERSION + '" alt="' + escapeHtml(alt) + '"' + (cls ? ' class="' + cls + '"' : '') + ' ' + extraAttrs + '>' +
      '</picture>';
  }
  function fetchJSON(path) {
    return fetch(path, { cache: 'no-store' }).then(function (r) {
      if (!r.ok) throw new Error('Falha ao carregar ' + path);
      return r.json();
    });
  }

  // ---------- menu mobile ----------
  var burger = document.getElementById('burger');
  var menu = document.getElementById('menu');
  if (burger && menu) {
    burger.addEventListener('click', function () {
      var open = menu.classList.toggle('open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.querySelectorAll('#menu a').forEach(function (a) {
      a.addEventListener('click', function () { menu.classList.remove('open'); burger.setAttribute('aria-expanded', 'false'); });
    });
  }

  // ---------- scrollspy ----------
  var secs = [].slice.call(document.querySelectorAll('section[id]'));
  var links = [].slice.call(document.querySelectorAll('#menu a'));
  addEventListener('scroll', function () {
    var y = scrollY + 140, cur = null;
    secs.forEach(function (s) { if (s.offsetTop <= y) cur = s.id; });
    links.forEach(function (l) { l.classList.toggle('active', l.getAttribute('href') === '#' + cur); });
  }, { passive: true });

  // ---------- reveal on scroll ----------
  function observeReveal(root) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: .15 });
    (root || document).querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
  }

  // ---------- contadores ----------
  function count(el) {
    var t = +el.dataset.count, pre = el.dataset.prefix || '', suf = el.dataset.suffix || '', s = null;
    if (reduceMotion) { el.textContent = pre + t.toLocaleString('pt-BR') + suf; return; }
    function f(ts) {
      if (!s) s = ts;
      var p = Math.min((ts - s) / 1300, 1);
      el.textContent = pre + Math.round(t * p).toLocaleString('pt-BR') + suf;
      if (p < 1) requestAnimationFrame(f);
    }
    requestAnimationFrame(f);
  }
  function observeCounters(root) {
    var io2 = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { count(e.target); io2.unobserve(e.target); } });
    }, { threshold: .6 });
    (root || document).querySelectorAll('[data-count]').forEach(function (el) { io2.observe(el); });
  }

  // ---------- accordions ----------
  function accordion(id) {
    document.querySelectorAll('#' + id + ' .item .head').forEach(function (h) {
      h.addEventListener('click', function () {
        var it = h.parentElement, body = h.nextElementSibling, open = it.classList.contains('open');
        it.parentElement.querySelectorAll('.item').forEach(function (x) {
          x.classList.remove('open');
          x.querySelector('.head').setAttribute('aria-expanded', 'false');
          x.querySelector('.body').style.maxHeight = null;
        });
        if (!open) {
          it.classList.add('open');
          h.setAttribute('aria-expanded', 'true');
          body.style.maxHeight = body.scrollHeight + 'px';
        }
      });
    });
  }
  function openDefaultAccordionItems() {
    document.querySelectorAll('.item.open .body').forEach(function (b) { b.style.maxHeight = b.scrollHeight + 'px'; });
  }
  addEventListener('resize', function () {
    document.querySelectorAll('.item.open .body').forEach(function (b) { b.style.maxHeight = b.scrollHeight + 'px'; });
  });

  // ---------- sliders ----------
  function wireSliders(root) {
    (root || document).querySelectorAll('.snav').forEach(function (n) {
      n.addEventListener('click', function () {
        var t = document.getElementById(n.dataset.t);
        if (!t) return;
        var card = t.querySelector('.card-img, .testi-card');
        var w = (card ? card.offsetWidth : 300) + 22;
        t.scrollBy({ left: n.classList.contains('next') ? w : -w, behavior: reduceMotion ? 'auto' : 'smooth' });
      });
    });
  }
  function updateSliderArrows(trackId) {
    var t = document.getElementById(trackId);
    if (!t) return;
    var hasOverflow = t.scrollWidth > t.clientWidth + 4;
    document.querySelectorAll('.snav[data-t="' + trackId + '"]').forEach(function (n) {
      n.style.display = hasOverflow ? '' : 'none';
    });
  }

  // ---------- toast ----------
  var toastEl = document.getElementById('toast');
  var tm;
  function say(m) {
    toastEl.textContent = m;
    toastEl.classList.add('show');
    clearTimeout(tm);
    tm = setTimeout(function () { toastEl.classList.remove('show'); }, 2600);
  }
  function wireToasts(root) {
    (root || document).querySelectorAll('[data-toast]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        if (el.tagName === 'A' && el.getAttribute('href') && el.getAttribute('href').indexOf('#') === 0) return;
        e.preventDefault();
        say(el.dataset.toast);
      });
    });
  }

  // ---------- acessibilidade ----------
  var a11yBtn = document.getElementById('a11yBtn');
  var a11yPanel = document.getElementById('a11yPanel');
  if (a11yBtn && a11yPanel) {
    a11yBtn.addEventListener('click', function () {
      var open = a11yPanel.classList.toggle('open');
      a11yBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    var sc = 1;
    document.querySelectorAll('[data-a]').forEach(function (b) {
      b.addEventListener('click', function () {
        var k = b.dataset.a;
        if (k === 'contrast') { document.documentElement.classList.toggle('hc'); return; }
        if (k === 'inc') sc = Math.min(sc + .1, 1.4);
        if (k === 'dec') sc = Math.max(sc - .1, .9);
        if (k === 'reset') sc = 1;
        document.body.style.zoom = sc;
      });
    });
  }

  // ---------- LGPD: cookie consent funcional ----------
  var cookieEl = document.getElementById('cookie');
  if (cookieEl) {
    var consent = null;
    try { consent = localStorage.getItem('bcontrol_cookie_consent'); } catch (e) {}
    if (consent) {
      cookieEl.style.display = 'none';
      if (consent === 'accepted') loadAnalytics();
    } else {
      cookieEl.style.display = 'flex';
    }
    var acceptBtn = document.getElementById('cookieAccept');
    var rejectBtn = document.getElementById('cookieReject');
    if (acceptBtn) acceptBtn.addEventListener('click', function () {
      try { localStorage.setItem('bcontrol_cookie_consent', 'accepted'); } catch (e) {}
      cookieEl.style.display = 'none';
      loadAnalytics();
    });
    if (rejectBtn) rejectBtn.addEventListener('click', function () {
      try { localStorage.setItem('bcontrol_cookie_consent', 'rejected'); } catch (e) {}
      cookieEl.style.display = 'none';
    });
  }

  // ---------- GA4 (só carrega com consentimento + ID configurado) ----------
  var siteConfig = null;
  function loadAnalytics() {
    if (!siteConfig || !siteConfig.ga4Id) return;
    if (window.__ga4Loaded) return;
    window.__ga4Loaded = true;
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(siteConfig.ga4Id);
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { dataLayer.push(arguments); };
    gtag('js', new Date());
    gtag('config', siteConfig.ga4Id);
  }
  function trackEvent(name, params) {
    if (window.gtag) gtag('event', name, params || {});
  }
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-ga]');
    if (el) trackEvent(el.dataset.ga, {});
  });

  // ---------- parallax ----------
  function makeParallax(sectionId, layerId, strength) {
    if (reduceMotion) return;
    var sec = document.getElementById(sectionId), lay = document.getElementById(layerId);
    if (!sec || !lay) return;
    var ticking = false;
    function upd() {
      if (innerWidth <= 900) { lay.style.transform = ''; ticking = false; return; }
      var r = sec.getBoundingClientRect();
      if (r.bottom > -100 && r.top < innerHeight + 100) {
        var center = r.top + r.height / 2;
        var prog = (center - innerHeight / 2) / (innerHeight / 2 + r.height / 2);
        lay.style.transform = 'translate3d(0,' + (prog * strength).toFixed(1) + 'px,0)';
      }
      ticking = false;
    }
    function onScroll() { if (!ticking) { requestAnimationFrame(upd); ticking = true; } }
    addEventListener('scroll', onScroll, { passive: true });
    addEventListener('resize', upd);
    upd();
  }

  // ---------- hero video ----------
  var VEL = 1.15;
  var hv = document.getElementById('heroVid');
  if (hv) {
    var setV = function () { try { hv.playbackRate = VEL; } catch (e) {} };
    hv.addEventListener('loadedmetadata', setV);
    hv.addEventListener('play', setV);
    hv.addEventListener('canplay', setV);
    // sem o atributo autoplay no HTML: em mobile/reduced-motion o vídeo nunca
    // chega a baixar o arquivo inteiro (preload="metadata" só pega os metadados)
    if (!reduceMotion && !matchMedia('(max-width:640px)').matches) {
      setV();
      hv.play().catch(function () {});
    }
  }

  // ================= CONTENT RENDERING =================

  function renderStats(stats) {
    var wrap = document.getElementById('statsGrid');
    if (!wrap || !stats) return;
    wrap.innerHTML = stats.map(function (s, i) {
      var d = i === 0 ? '' : ' d' + Math.min(i, 3);
      if (s.text) {
        return '<div class="glass reveal' + d + '"><div class="n" style="font-size:clamp(24px,2.6vw,32px)">' + escapeHtml(s.text) + '</div><div class="l">' + escapeHtml(s.label) + '</div></div>';
      }
      return '<div class="glass reveal' + d + '"><div class="n" data-count="' + s.value + '" data-prefix="' + escapeHtml(s.prefix || '') + '" data-suffix="' + escapeHtml(s.suffix || '') + '">0</div><div class="l">' + escapeHtml(s.label) + '</div></div>';
    }).join('');
  }

  function renderServices(services) {
    var wrap = document.getElementById('accServ');
    if (!wrap || !services) return;
    wrap.innerHTML = services.map(function (s, i) {
      var open = i === 0;
      var items = s.items.map(function (it) {
        return '<li><b>' + escapeHtml(it.label) + '</b> <span class="nm">' + escapeHtml(it.norm) + '</span></li>';
      }).join('');
      var chips = s.chips && s.chips.length ? '<div class="subt">Para quem</div><div class="chips">' + s.chips.map(function (c) { return '<span class="chip sec">' + escapeHtml(c) + '</span>'; }).join('') + '</div>' : '';
      return '<div class="item' + (open ? ' open' : '') + '">' +
        '<button class="head" aria-expanded="' + (open ? 'true' : 'false') + '"><h3>' + escapeHtml(s.title) + '</h3><span class="chev" aria-hidden="true">▾</span></button>' +
        '<div class="body"><div class="inner"><div class="panel">' +
        '<div><p class="intro">' + escapeHtml(s.intro) + '</p><ul class="stlist">' + items + '</ul>' + chips + '</div>' +
        '<div class="servimg">' + picture(s.image, s.imageAlt, '', 'loading="lazy" width="900" height="675"') + '</div>' +
        '</div></div></div></div>';
    }).join('');
    accordion('accServ');
    openDefaultAccordionItems();
    observeReveal(wrap);
  }

  function renderFaq(faq) {
    var wrap = document.getElementById('accFaq');
    if (!wrap || !faq) return;
    wrap.innerHTML = faq.map(function (f, i) {
      var open = i === 0;
      return '<div class="item' + (open ? ' open' : '') + '">' +
        '<button class="head" aria-expanded="' + (open ? 'true' : 'false') + '"><span>' + escapeHtml(f.q) + '</span><span class="chev" aria-hidden="true">▾</span></button>' +
        '<div class="body"><div class="inner">' + escapeHtml(f.a) + '</div></div></div>';
    }).join('');
    accordion('accFaq');
    openDefaultAccordionItems();
  }

  function renderDocuments(docs) {
    var wrap = document.getElementById('docsGrid');
    if (!wrap || !docs) return;
    wrap.innerHTML = docs.map(function (d, i) {
      var delay = i === 0 ? '' : ' d' + Math.min(i, 3);
      if (d.file) {
        return '<div class="doc reveal' + delay + '"><b>' + escapeHtml(d.title) + '</b><a class="dl" href="' + escapeHtml(d.file) + '" download data-ga="download_doc">↓ ' + escapeHtml(d.linkLabel) + '</a></div>';
      }
      return '<div class="doc reveal' + delay + '"><b>' + escapeHtml(d.title) + '</b><button class="dl" aria-disabled="true" data-toast="Documento em atualização — em breve disponível para download">↓ ' + escapeHtml(d.linkLabel) + '</button></div>';
    }).join('');
    observeReveal(wrap);
    wireToasts(wrap);
  }

  function renderTeam(team) {
    var wrap = document.getElementById('teamTrack');
    if (!wrap || !team) return;
    wrap.innerHTML = team.map(function (m) {
      return '<figure class="card-img">' + picture(m.image, m.name, '', 'loading="lazy" width="700" height="680"') +
        '<figcaption>' + escapeHtml(m.name) + (m.role ? '<span>' + escapeHtml(m.role) + '</span>' : '') + '</figcaption></figure>';
    }).join('');
  }

  function renderClients(clients) {
    var wrap = document.getElementById('logorow');
    if (!wrap || !clients) return;
    var imgs = clients.map(function (c) { return picture(c.logo, c.name, '', 'loading="lazy" width="200" height="90"'); });
    wrap.innerHTML = imgs.concat(imgs).join('');
  }

  function renderTestimonials(testis) {
    var wrap = document.getElementById('testiTrack');
    if (!wrap || !testis) return;
    wrap.innerHTML = testis.map(function (t) {
      return '<div class="testi-card"><span class="quote-mark" aria-hidden="true">“</span><p>' + escapeHtml(t.quote) + '</p>' +
        '<div class="who">' + escapeHtml(t.name) + (t.company ? '<span>' + escapeHtml(t.company) + '</span>' : '') + '</div></div>';
    }).join('');
  }

  function applySiteConfig(cfg) {
    siteConfig = cfg;
    var waHref = 'https://wa.me/' + String(cfg.whatsappNumber || '').replace(/\D/g, '') + '?text=' + encodeURIComponent(cfg.whatsappMessage || '');
    var hasRealNumber = !/^TODO/.test(cfg.whatsappNumber || '');
    document.querySelectorAll('[data-wa-link]').forEach(function (a) {
      if (hasRealNumber) {
        a.setAttribute('href', waHref);
        a.setAttribute('target', '_blank');
        a.setAttribute('rel', 'noopener');
        a.removeAttribute('data-toast');
      } else {
        a.setAttribute('href', '#contato');
      }
    });

    var cadastroLink = document.getElementById('cadastroLink');
    if (cadastroLink) {
      var hasCadastro = !/^TODO/.test(cfg.cadastroUrl || '');
      if (hasCadastro) { cadastroLink.setAttribute('href', cfg.cadastroUrl); cadastroLink.removeAttribute('data-toast'); }
    }

    var kitBtn = document.querySelector('.kit');
    // kit de coleta continua como aviso até termos o PDF/formulário definitivo (ver RELATORIO.md)

    document.querySelectorAll('[data-phone-display]').forEach(function (el) { el.textContent = cfg.phoneDisplay || ''; });
    document.querySelectorAll('[data-address]').forEach(function (el) { el.textContent = cfg.address || ''; });

    if (cfg.socials) {
      var ig = document.getElementById('socialInstagram');
      var fb = document.getElementById('socialFacebook');
      if (ig && !/^TODO/.test(cfg.socials.instagram || '')) { ig.href = cfg.socials.instagram; ig.removeAttribute('data-toast'); }
      if (fb && !/^TODO/.test(cfg.socials.facebook || '')) { fb.href = cfg.socials.facebook; fb.removeAttribute('data-toast'); }
    }

    wireOrcamentoForm(cfg);

    var consent = null;
    try { consent = localStorage.getItem('bcontrol_cookie_consent'); } catch (e) {}
    if (consent === 'accepted') loadAnalytics();
  }

  function wireOrcamentoForm(cfg) {
    var form = document.getElementById('formOrc');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var msgEl = document.getElementById('formOrcMsg');
      var honeypot = form.querySelector('[name="empresa_site"]');
      if (honeypot && honeypot.value) return; // bot detectado, ignora silenciosamente

      var data = new FormData(form);
      var setor = data.get('setor');
      var targetEmail = (cfg.sectorEmails && cfg.sectorEmails[setor]) || 'bcontrol@bcontrol.com.br';
      data.set('setor_email', targetEmail);
      data.set('_subject', 'Novo pedido de orçamento — bcontrol (' + setor + ')');
      if (cfg.testCcEmail) data.set('_cc', cfg.testCcEmail);
      var endpoint = 'https://formsubmit.co/ajax/' + encodeURIComponent(targetEmail);

      var btn = form.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
      fetch(endpoint, { method: 'POST', body: data, headers: { Accept: 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('erro'); return r.json().catch(function () { return {}; }); })
        .then(function () {
          if (msgEl) { msgEl.textContent = 'Pedido enviado! Em breve nossa equipe entrará em contato.'; msgEl.className = 'field-msg ok'; }
          trackEvent('form_orcamento_enviado', { setor: setor });
          form.reset();
        })
        .catch(function () {
          if (msgEl) { msgEl.textContent = 'Não foi possível enviar agora. Tente novamente ou fale pelo WhatsApp.'; msgEl.className = 'field-msg err'; }
        })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }

  // ================= BOOT =================
  document.addEventListener('DOMContentLoaded', function () {
    observeReveal(document);
    observeCounters(document);
    wireToasts(document);
    wireSliders(document);
    makeParallax('dif', 'difWater', 180);
    makeParallax('cta', 'ctaBg', 150);

    Promise.all([
      fetchJSON('content/site.json'),
      fetchJSON('content/services.json'),
      fetchJSON('content/faq.json'),
      fetchJSON('content/documents.json'),
      fetchJSON('content/team.json'),
      fetchJSON('content/clients.json'),
      fetchJSON('content/testimonials.json')
    ]).then(function (results) {
      var cfg = results[0], services = results[1], faq = results[2], docs = results[3], team = results[4], clients = results[5], testis = results[6];
      renderStats(cfg.stats);
      renderServices(services.items);
      renderFaq(faq.items);
      renderDocuments(docs.items);
      renderTeam(team.items);
      renderClients(clients.items);
      renderTestimonials(testis.items);
      applySiteConfig(cfg);
      observeReveal(document);
      observeCounters(document);
      wireToasts(document);
      wireSliders(document);
      updateSliderArrows('teamTrack');
      updateSliderArrows('testiTrack');
      addEventListener('resize', function () {
        updateSliderArrows('teamTrack');
        updateSliderArrows('testiTrack');
      });
    }).catch(function (err) {
      console.error('Erro ao carregar conteúdo dinâmico:', err);
    });
  });
})();

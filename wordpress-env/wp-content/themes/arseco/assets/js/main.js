(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  // Menu móvel
  const toggle = $('.menu-toggle'), nav = $('#menu-principal');
  if (toggle && nav) {
    const set = (open) => {
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open);
      toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    };
    toggle.addEventListener('click', () => set(!nav.classList.contains('is-open')));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && set(false));
    document.addEventListener('click', (e) => !nav.contains(e.target) && !toggle.contains(e.target) && set(false));
  }

  // Acordeão da FAQ: um item aberto de cada vez
  $$('.acc__btn').forEach((btn) => btn.addEventListener('click', () => {
    const open = btn.getAttribute('aria-expanded') === 'true';
    $$('.acc__btn').forEach((b) => {
      b.setAttribute('aria-expanded', 'false');
      $('#' + b.getAttribute('aria-controls')).hidden = true;
    });
    if (!open) {
      btn.setAttribute('aria-expanded', 'true');
      $('#' + btn.getAttribute('aria-controls')).hidden = false;
    }
  }));

  // Separadores das características
  const tabs = $('[data-tabs]');
  if (tabs) {
    const data = JSON.parse($('[data-tab-data]', tabs).textContent);
    $$('[role=tab]', tabs).forEach((tab, i) => tab.addEventListener('click', () => {
      $$('[role=tab]', tabs).forEach((t) => t.setAttribute('aria-selected', t === tab));
      $('[data-tab-title]', tabs).textContent = data[i][0];
      $('[data-tab-text]', tabs).textContent = data[i][1];
    }));
  }

  // Consentimento de cookies: guarda a escolha; nada de análise/marketing é carregado sem ela
  const KEY = 'arseco_consent', box = $('.consent');
  const read = () => { try { return JSON.parse(localStorage.getItem(KEY)); } catch { return null; } };
  const save = (v) => { try { localStorage.setItem(KEY, JSON.stringify({ ...v, t: Date.now() })); } catch { /* sem armazenamento */ } box.hidden = true; };
  if (box) {
    if (!read()) box.hidden = false;
    $$('[data-consent]', box).forEach((b) => b.addEventListener('click', () => {
      const a = b.dataset.consent;
      if (a === 'accept') return save({ analytics: true, marketing: true });
      if (a === 'reject') return save({ analytics: false, marketing: false });
      const opts = $('.consent__opts', box);
      if (opts.hidden) { opts.hidden = false; b.textContent = 'Guardar escolha'; return; }
      save({ analytics: $('[name=analytics]', box).checked, marketing: $('[name=marketing]', box).checked });
    }));
    $$('[data-consent-open]').forEach((b) => b.addEventListener('click', () => { box.hidden = false; $('button', box).focus(); }));
  }
})();

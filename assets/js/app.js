(() => {
  'use strict';

  const API = 'api/reports.php';
  const NET_API = 'api/network.php';
  const VERIFY_API = 'api/verify.php';
  const ASK_API = 'api/ask.php';
  const FREEZE_API = 'api/freeze.php';
  const AI_API = 'api/ai.php';
  const POLL_MS = 20000;
  const NET_POLL_MS = 15000;
  const LOCALES = { en: 'en-MY', ms: 'ms-MY', zh: 'zh-CN' };
  const HTML_LANG = { en: 'en', ms: 'ms', zh: 'zh-Hans' };

  const CAT = {
    internship: { color: 'var(--internship)', hex: '#7C3AED', icon: '<svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2M2 13h20"/></svg>' },
    rental: { color: 'var(--rental)', hex: '#F59E0B', icon: '<svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>' },
    phishing: { color: 'var(--phishing)', hex: '#E11D48', icon: '<svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4M12 15v2"/></svg>' },
    other: { color: 'var(--other)', hex: '#94A3B8', icon: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/></svg>' },
  };
  const ICON = {
    up: '<svg viewBox="0 0 24 24"><path d="M7 17 17 7M8 7h9v9"/></svg>',
    down: '<svg viewBox="0 0 24 24"><path d="M7 7l10 10M17 8v9H8"/></svg>',
    spark: '<svg viewBox="0 0 24 24"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/></svg>',
    flag: '<svg viewBox="0 0 24 24"><path d="M4 22V4a1 1 0 0 1 1-1h12l-2 4 2 4H5"/></svg>',
    check: '<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
    chevron: '<svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>',
    back: '<svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>',
    globe: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>',
    layers: '<svg viewBox="0 0 24 24"><path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></svg>',
    pin: '<svg viewBox="0 0 24 24"><path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>',
    shieldOk: '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
    shieldX: '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9.5 9.5 5 5M14.5 9.5l-5 5"/></svg>',
    warn: '<svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/></svg>',
    help: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/></svg>',
    phone: '<svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>',
  };
  const STATUS_ICON = { rising: ICON.up, new: ICON.spark, steady: '', fading: ICON.down };
  const FLAG_PATTERNS = [
    [/^Urgency pressure$/, 'urgency'], [/^Upfront payment request$/, 'payment'], [/^Asks for login/, 'credentials'],
    [/^Too-good/, 'too_good'], [/^Moves chat/, 'off_platform'], [/^Avoids in-person/, 'no_viewing'], [/^Official-sounding/, 'free_mail'],
    [/^High-risk domain ending \(\.(\w+)\)/, 'bad_tld'], [/^Shortened link/, 'shortener'], [/^Link uses raw IP/, 'ip_url'],
    [/^Look-alike of (.+) domain$/, 'lookalike'], [/^Suspicious domain pattern/, 'odd_domain'], [/^Link is not HTTPS/, 'no_https'],
  ];
  const SAMPLES = [
    { message: 'Hi! Shopee is offering you a paid internship (RM2,800/month). To confirm, pay RM50 training fee to our HR account by today.', sender: 'hr-team@shopee-hiring.online', url: 'https://shopee-hiring.online/confirm', channel: 'WhatsApp', location: 'block-e' },
    { message: 'Your APSpace password will expire today. Log in here to keep your account active and avoid losing your timetable.', sender: 'it@apu-helpdesk.site', url: 'http://apu-helpdesk.site/login', channel: 'Email', location: 'block-d' },
    { message: 'Room near APU only RM420, fully furnished. Landlord is overseas, transfer RM300 booking fee first and I will courier the keys.', sender: 'rooms4students@gmail.com', url: '', channel: 'Facebook', location: 'block-j' },
    { message: 'Earn RM150 per day with Grab food review tasks! Pay RM40 activation fee to start. Limited slots, WhatsApp me now.', sender: 'grab.tasks.my@gmail.com', url: 'https://wa.me/60123456789', channel: 'Telegram', location: 'spine' },
  ];

  let data = { reports: [], trends: [], stats: null, zones: [] };
  let net = null;
  let directory = null;
  const ui = { view: 'overview', filter: 'all', search: '', trendId: null, shown: 8, sort: 'week', allTrends: false, mapLayer: 'reports', zone: null };
  const settings = { lang: 'en', size: '1', contrast: false, motion: false, links: false };
  let knownIds = null;
  let knownNet = null;
  let lastUpdated = 0;
  let aiRequested = false;
  const drawerStack = [];

  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const cat = (c) => CAT[c] || CAT.other;
  const store = {
    get(k) { try { return localStorage.getItem('byescam.' + k); } catch (e) { return null; } },
    set(k, v) { try { localStorage.setItem('byescam.' + k, v); } catch (e) { return; } },
  };

  function t(key, vars) {
    const dict = window.I18N[settings.lang] || window.I18N.en;
    let s = dict[key] ?? window.I18N.en[key] ?? key;
    if (Array.isArray(s)) return s;
    if (vars) s = s.replace(/\{(\w+)\}/g, (m, k) => (vars[k] ?? ''));
    return s;
  }
  const nReports = (n) => t(n === 1 ? 'n.report.one' : 'n.report.other', { n });
  const catLabel = (c) => t('cat.' + (CAT[c] ? c : 'other'));
  const locale = () => LOCALES[settings.lang] || 'en-MY';

  function flagText(label) {
    if (settings.lang === 'en') return label;
    for (const [re, key] of FLAG_PATTERNS) {
      const m = label.match(re);
      if (m) return t('flag.' + key, { p: m[1] || '' });
    }
    return label;
  }

  function trendTitle(tr) {
    const ai = tr.ai && tr.ai.title && tr.ai.title[settings.lang];
    if (ai) return ai;
    if (settings.lang === 'en' || !tr.parts) return tr.title;
    const p = tr.parts;
    const zh = settings.lang === 'zh';
    const brand = p.brand === 'APU student portal' ? t('brand.apu') : (p.brand || t('brand.generic'));
    const askLbl = p.ask ? t('ask.' + p.ask) : '';
    const ask = zh ? `${p.amount || ''}${askLbl}` : `${askLbl}${p.amount ? ' ' + p.amount : ''}`;
    switch (p.category) {
      case 'internship': {
        if (!askLbl) return tr.title;
        const b = p.brand || '';
        const what = t('what.' + p.kind, { brand: p.kind === 'like_follow' && !zh && b ? b + ' ' : b });
        return t('title.internship', { what, ask });
      }
      case 'rental':
        return askLbl ? t('title.rental', { near: p.near_apu ? t('near') : '', ask, before: p.before_viewing ? t('before') : '' }) : tr.title;
      case 'phishing':
        if (['password', 'otp', 'bank_ic'].includes(p.kind)) return t('title.' + p.kind, { brand });
        return askLbl ? t('title.message', { brand, ask }) : tr.title;
      default:
        return askLbl ? t('title.other', { ask }) : tr.title;
    }
  }

  function timeAgo(iso) {
    const s = Math.max(1, Math.round((Date.now() - new Date(iso)) / 1000));
    if (s < 60) return t('justNow');
    const m = Math.round(s / 60); if (m < 60) return t('minAgo', { n: m });
    const h = Math.round(m / 60); if (h < 24) return t('hAgo', { n: h });
    const d = Math.round(h / 24); return d === 1 ? t('yesterday') : t('daysAgo', { n: d });
  }
  const fmtDate = (iso) => new Date(iso).toLocaleString(locale(), { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
  const fmtTime = (iso) => new Date(iso).toLocaleTimeString(locale(), { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  const zoneOf = (id) => data.zones.find((z) => z.id === id);

  function toast(html, ms = 3600) {
    const el = $('#toast');
    el.innerHTML = html; el.hidden = false;
    clearTimeout(toast._t); toast._t = setTimeout(() => (el.hidden = true), ms);
  }

  async function api(url, opts) {
    const res = await fetch(url, opts);
    const body = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(body.error || `Request failed (${res.status})`);
    return body;
  }

  function applyI18n() {
    document.documentElement.lang = HTML_LANG[settings.lang] || 'en';
    $$('[data-i18n]').forEach((el) => { el.textContent = t(el.dataset.i18n); });
    $$('[data-i18n-ph]').forEach((el) => { el.placeholder = t(el.dataset.i18nPh); });
    $$('[data-i18n-title]').forEach((el) => { el.title = t(el.dataset.i18nTitle); });
    $$('[data-i18n-aria]').forEach((el) => { el.setAttribute('aria-label', t(el.dataset.i18nAria)); });
    $$('[data-i18n-alt]').forEach((el) => { el.alt = t(el.dataset.i18nAlt); });
    const vt = { overview: 'ov.title', map: 'mp.title', network: 'nw.title', verify: 'vf.title', freeze: 'fz.title' }[ui.view];
    document.title = `${t(vt)} · ByeScam`;
    renderLocationOptions();
  }

  function applySettings() {
    const b = document.body;
    b.classList.toggle('hc', settings.contrast);
    b.classList.toggle('rm', settings.motion);
    b.classList.toggle('ul', settings.links);
    document.documentElement.style.setProperty('--ui-zoom', settings.size);
    $$('#langSeg button').forEach((x) => { const on = x.dataset.lang === settings.lang; x.classList.toggle('active', on); x.setAttribute('aria-pressed', on); });
    $$('#sizeSeg button').forEach((x) => { const on = x.dataset.size === settings.size; x.classList.toggle('active', on); x.setAttribute('aria-pressed', on); });
    $('#contrastToggle').checked = settings.contrast;
    $('#motionToggle').checked = settings.motion;
    $('#linksToggle').checked = settings.links;
    Object.entries(settings).forEach(([k, v]) => store.set('set.' + k, String(v)));
  }

  function loadSettings() {
    const lang = store.get('set.lang');
    if (lang && window.I18N[lang]) settings.lang = lang;
    else {
      const nav = (navigator.language || 'en').toLowerCase();
      if (nav.startsWith('ms')) settings.lang = 'ms';
      else if (nav.startsWith('zh')) settings.lang = 'zh';
    }
    settings.size = store.get('set.size') || '1';
    settings.contrast = store.get('set.contrast') === 'true';
    settings.motion = store.get('set.motion') === 'true' || window.matchMedia('(prefers-reduced-motion: reduce)').matches && store.get('set.motion') !== 'false';
    settings.links = store.get('set.links') === 'true';
  }

  function setView(view, focus) {
    if (view === 'ask') { openChat(); view = ui.view || 'overview'; history.replaceState(null, '', '#' + view); }
    if (!['overview', 'map', 'network', 'verify', 'freeze'].includes(view)) view = 'overview';
    ui.view = view;
    $$('[data-view]').forEach((s) => (s.hidden = s.dataset.view !== view));
    $$('[data-view-link]').forEach((a) => {
      const on = a.dataset.viewLink === view;
      a.classList.toggle('active', on);
      if (on) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
    });
    closeSidebar();
    applyI18n();
    renderCurrent();
    if (view === 'verify' && !directory) loadDirectory();
    if (view === 'freeze') loadFreeze();
    if (focus) { window.scrollTo(0, 0); const h = $(`[data-view="${view}"] h1`); if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); } }
  }
  window.addEventListener('hashchange', () => { if (window.matchMedia('(max-width: 560px)').matches && typeof closeChat === 'function') closeChat(); setView(location.hash.slice(1), true); });

  async function load() {
    try {
      const d = await api(`${API}?t=${Date.now()}`);
      const fresh = knownIds ? d.reports.filter((r) => !knownIds.has(r.id)).map((r) => r.id) : [];
      knownIds = new Set(d.reports.map((r) => r.id));
      data = d;
      lastUpdated = Date.now();
      renderCurrent(fresh);
      maybeRunAi();
    } catch (e) {
      $('#updatedText').textContent = t('offline');
    }
  }

  async function loadNetwork() {
    try {
      const n = await api(`${NET_API}?t=${Date.now()}`);
      const prev = knownNet;
      knownNet = new Set(n.recent.map((e) => e.time + e.device));
      n.freshKeys = prev ? n.recent.filter((e) => !prev.has(e.time + e.device)).map((e) => e.time + e.device) : [];
      net = n;
      renderShield();
      if (ui.view === 'network') renderNetwork();
      if (ui.view === 'map') renderMap();
    } catch (e) { return; }
  }

  async function maybeRunAi() {
    if (!data.stats || !data.stats.ai_enabled || aiRequested) return;
    aiRequested = true;
    try {
      const r = await api(AI_API, { method: 'POST' });
      if (r.updated > 0) load();
      if (r.errors && r.errors.length) console.warn('AI summaries:', r.errors.join('; '));
    } catch (e) { console.warn(e); }
  }

  setInterval(load, POLL_MS);
  setInterval(loadNetwork, NET_POLL_MS);
  setInterval(() => {
    if (!lastUpdated) return;
    const s = Math.round((Date.now() - lastUpdated) / 1000);
    $('#updatedText').textContent = s < 5 ? t('live.now') : t('live.ago', { n: s });
  }, 1000);

  function renderCurrent(fresh = []) {
    if (!data.stats) return;
    if (ui.view === 'overview') renderOverview(fresh);
    else if (ui.view === 'map') renderMap();
    else if (ui.view === 'network') renderNetwork();
    else if (ui.view === 'verify') renderDirectory();
    else if (ui.view === 'freeze') renderFreeze();
    if (!$('#chatPanel').hidden) renderAsk();
  }

  function renderOverview(fresh = []) {
    const s = data.stats;
    $('#statTotal').textContent = s.total;
    $('#statWeek').textContent = s.this_week;
    $('#statHigh').textContent = s.high;
    $('#statTrends').textContent = s.active_trends;
    $('#aiBadgeText').textContent = s.ai_enabled ? t('tr.badgeAi') : t('tr.badge');
    renderAlert();
    renderShield();
    renderTrends();
    renderChart();
    renderDonut();
    renderFlags();
    renderDomains();
    renderFeed(fresh);
  }

  function renderAlert() {
    const tr = data.trends.find((x) => (x.status === 'rising' || x.status === 'new') && x.count >= 3);
    const box = $('#alertBanner');
    if (!tr) { box.hidden = true; return; }
    box.hidden = false;
    $('#alertTitle').textContent = t('alert.title', { title: trendTitle(tr) });
    $('#alertText').textContent = t('alert.text', { n: nReports(tr.this_week), channels: tr.channels.join(', ') });
    $('#alertAction').onclick = () => openTrend(tr.id);
  }

  function renderShield() {
    const el = $('#shieldStrip');
    if (!net) { el.hidden = true; return; }
    el.hidden = false;
    $('#shieldTitle').textContent = t('shield.title', { n: net.blocked_today });
    $('#shieldSub').textContent = t('shield.sub', { d: net.devices_today, b: net.status.blocklist_size });
  }

  function sparkSvg(values, color, w = 120, h = 40) {
    const max = Math.max(1, ...values);
    const bw = w / values.length;
    return `<svg viewBox="0 0 ${w} ${h}" preserveAspectRatio="none" aria-hidden="true">${values.map((v, i) => {
      const bh = v ? Math.max(3, (v / max) * (h - 2)) : 2;
      return `<rect x="${i * bw + 1}" y="${h - bh}" width="${bw - 2}" height="${bh}" rx="1.5" fill="${v ? color : '#E6E9F2'}" opacity="${v ? (0.45 + 0.55 * (i / values.length)) : 1}"/>`;
    }).join('')}</svg>`;
  }
  const statusBadge = (st) => `<span class="status ${st}">${STATUS_ICON[st] || ''}${esc(t('status.' + st))}</span>`;

  function renderTrends() {
    let list = [...data.trends];
    if (ui.sort === 'all') list.sort((a, b) => b.count - a.count || b.this_week - a.this_week);
    const grouped = list.filter((x) => x.count >= 2);
    const shown = ui.allTrends ? list : grouped.slice(0, 5);
    const more = $('#moreTrends');
    more.hidden = ui.allTrends || list.length <= shown.length;
    more.textContent = t('tr.showAll', { n: list.length });

    $('#trendGrid').innerHTML = shown.map((tr, i) => {
      const c = cat(tr.category);
      const n = ui.sort === 'week' ? tr.this_week : tr.count;
      const nLabel = ui.sort === 'week' ? t('tc.thisWeek') : t('tc.inTotal');
      const sub = ui.sort === 'week'
        ? t('tc.subWeek', { total: nReports(tr.count), ago: timeAgo(tr.last_seen) })
        : t('tc.subAll', { week: tr.this_week, ago: timeAgo(tr.first_seen) });
      return `
      <button class="tcard ${i === 0 ? 'featured' : ''}" style="--c:${c.color}" data-trend="${tr.id}">
        <div class="tcard-top">
          ${statusBadge(tr.status)}
          <span class="cat-pill" style="--c:${c.color}">${esc(catLabel(tr.category))}</span>
          <span class="risk-pill ${tr.level}">${esc(t('risk', { n: tr.risk }))}</span>
          ${tr.ai ? `<span class="ai-mini" title="${esc(t('dr.aiBy'))}">${ICON.spark}AI</span>` : ''}
        </div>
        <div class="tcard-body">
          <div class="tcount"><b>${n}</b><small>${esc(nLabel)}</small></div>
          <div><p class="ttitle">${esc(trendTitle(tr))}</p><p class="tsub">${esc(sub)}</p></div>
          <div class="spark" title="${esc(t('tc.spark'))}">${sparkSvg(tr.spark, c.hex)}</div>
        </div>
        <div class="tcard-foot">
          ${tr.flags.slice(0, 3).map((f) => `<span class="flag-tag">${esc(flagText(f.label))}</span>`).join('')}
          <span class="more">${esc(t('tc.view', { n: nReports(tr.count) }))}${ICON.chevron}</span>
        </div>
      </button>`;
    }).join('') || `<p class="empty">${esc(t('tr.empty'))}</p>`;
  }

  function renderChart() {
    const el = $('#trendChart');
    if (!el.offsetParent) return;
    const days = data.stats.daily;
    const cats = ['internship', 'rental', 'phishing', 'other'];
    const W = Math.max(320, el.clientWidth), H = 240, pl = 28, pb = 26, pt = 8;
    const max = Math.max(4, ...days.map((d) => cats.reduce((s, c) => s + d[c], 0)));
    const step = Math.ceil(max / 4);
    const top = step * 4;
    const cw = (W - pl) / days.length;
    const bw = Math.min(26, cw * 0.56);
    const y = (v) => pt + (H - pt - pb) * (1 - v / top);

    let svg = '';
    for (let i = 0; i <= 4; i++) {
      const v = step * i;
      svg += `<line class="grid-line" x1="${pl}" x2="${W}" y1="${y(v)}" y2="${y(v)}"/><text class="axis" x="${pl - 8}" y="${y(v) + 4}" text-anchor="end">${v}</text>`;
    }
    days.forEach((d, i) => {
      const x = pl + i * cw;
      let acc = 0;
      const date = new Date(d.date + 'T00:00:00');
      const label = i === days.length - 1 ? t('today') : date.toLocaleDateString(locale(), { day: 'numeric', month: 'short' });
      const bars = cats.map((c) => {
        if (!d[c]) return '';
        const y1 = y(acc + d[c]), y0 = y(acc);
        acc += d[c];
        return `<rect class="bar" x="${x + (cw - bw) / 2}" y="${y1}" width="${bw}" height="${Math.max(0, y0 - y1 - 1.5)}" rx="3" fill="${cat(c).hex}"/>`;
      }).join('');
      svg += `<g class="col" data-i="${i}"><rect class="hover-bg" x="${x + 2}" y="${pt}" width="${cw - 4}" height="${H - pt - pb}" rx="6"/>${bars}
        ${(i % 2 === days.length % 2 || cw > 46) ? `<text class="axis" x="${x + cw / 2}" y="${H - 6}" text-anchor="middle">${esc(label)}</text>` : ''}</g>`;
    });
    el.innerHTML = `<svg viewBox="0 0 ${W} ${H}">${svg}</svg><div class="tooltip" hidden></div>`;
    $('#chartLegend').innerHTML = cats.filter((c) => c !== 'other' || data.stats.categories.other)
      .map((c) => `<span><i class="dot ${c}"></i>${esc(catLabel(c))}</span>`).join('');

    const tip = $('.tooltip', el);
    $$('g.col', el).forEach((g) => {
      g.addEventListener('mouseenter', () => {
        const d = days[g.dataset.i];
        const total = cats.reduce((s, c) => s + d[c], 0);
        const date = new Date(d.date + 'T00:00:00').toLocaleDateString(locale(), { weekday: 'short', day: 'numeric', month: 'short' });
        tip.innerHTML = `<b>${esc(date)} · ${esc(nReports(total))}</b>` + cats.filter((c) => d[c]).map((c) => `<span><i class="dot ${c}"></i>${esc(catLabel(c))}: ${d[c]}</span>`).join('');
        const r = g.getBoundingClientRect(), p = el.getBoundingClientRect();
        tip.style.left = `${r.left - p.left + r.width / 2}px`;
        tip.style.top = `${Math.max(60, y(total) * (p.height / H))}px`;
        tip.hidden = false;
      });
      g.addEventListener('mouseleave', () => (tip.hidden = true));
    });
  }

  function renderBarChart(el, values, labels, color, tipFn) {
    if (!el.offsetParent) return;
    const W = Math.max(320, el.clientWidth), H = 220, pl = 28, pb = 26, pt = 8;
    const max = Math.max(4, ...values);
    const step = Math.ceil(max / 4), top = step * 4;
    const cw = (W - pl) / values.length, bw = Math.min(22, cw * 0.62);
    const y = (v) => pt + (H - pt - pb) * (1 - v / top);
    let svg = '';
    for (let i = 0; i <= 4; i++) svg += `<line class="grid-line" x1="${pl}" x2="${W}" y1="${y(step * i)}" y2="${y(step * i)}"/><text class="axis" x="${pl - 8}" y="${y(step * i) + 4}" text-anchor="end">${step * i}</text>`;
    values.forEach((v, i) => {
      const x = pl + i * cw;
      svg += `<g class="col" data-i="${i}"><rect class="hover-bg" x="${x + 1}" y="${pt}" width="${cw - 2}" height="${H - pt - pb}" rx="5"/>
        ${v ? `<rect class="bar" x="${x + (cw - bw) / 2}" y="${y(v)}" width="${bw}" height="${y(0) - y(v)}" rx="3" fill="${color}"/>` : ''}
        ${labels[i] ? `<text class="axis" x="${x + cw / 2}" y="${H - 6}" text-anchor="middle">${esc(labels[i])}</text>` : ''}</g>`;
    });
    el.innerHTML = `<svg viewBox="0 0 ${W} ${H}">${svg}</svg><div class="tooltip" hidden></div>`;
    const tip = $('.tooltip', el);
    $$('g.col', el).forEach((g) => {
      g.addEventListener('mouseenter', () => {
        const i = +g.dataset.i;
        tip.innerHTML = tipFn(i, values[i]);
        const r = g.getBoundingClientRect(), p = el.getBoundingClientRect();
        tip.style.left = `${r.left - p.left + r.width / 2}px`;
        tip.style.top = `${Math.max(56, y(values[i]) * (p.height / H))}px`;
        tip.hidden = false;
      });
      g.addEventListener('mouseleave', () => (tip.hidden = true));
    });
  }

  function renderDonut() {
    const counts = data.stats.categories;
    const total = Object.values(counts).reduce((a, b) => a + b, 0) || 1;
    const r = 15.9155, C = 100;
    let offset = 0;
    const order = ['phishing', 'internship', 'rental', 'other'].filter((c) => counts[c]);
    const arcs = order.map((c) => {
      const pct = (counts[c] / total) * C;
      const a = `<circle r="${r}" cx="21" cy="21" fill="none" stroke="${cat(c).hex}" stroke-width="5.5" stroke-dasharray="${Math.max(0, pct - 0.8)} ${C - pct + 0.8}" stroke-dashoffset="${-offset}"/>`;
      offset += pct;
      return a;
    }).join('');
    $('#donut').innerHTML = `
      <div class="donut"><svg viewBox="0 0 42 42" aria-hidden="true"><circle r="${r}" cx="21" cy="21" fill="none" stroke="#EEF1F7" stroke-width="5.5"/>${arcs}</svg>
        <div class="donut-center"><b>${data.stats.total}</b><small>${esc(t('dn.reports'))}</small></div></div>
      <ul class="donut-legend">${order.map((c) => `<li><i class="dot ${c}"></i>${esc(catLabel(c))}<b>${counts[c]}</b><em>${Math.round((counts[c] / total) * 100)}%</em></li>`).join('')}</ul>`;
  }

  function renderBars(el, items) {
    const max = Math.max(1, ...items.map((f) => f.count));
    el.innerHTML = items.map((f) => `
      <li><span>${f.html || esc(f.label)}</span><b>${f.count}</b><div class="track"><div class="fill" style="${f.color ? `background:${f.color}` : ''}" data-w="${(f.count / max) * 100}"></div></div></li>`).join('');
    requestAnimationFrame(() => $$('.fill', el).forEach((x) => (x.style.width = x.dataset.w + '%')));
  }

  function renderFlags() {
    renderBars($('#flagBars'), data.stats.flags.map((f) => ({ label: flagText(f.label), count: f.count })));
  }

  function renderDomains() {
    const cutoff = Date.now() - 14 * 864e5;
    const map = new Map();
    data.reports.forEach((r) => {
      if (!r.domain || new Date(r.created_at) < cutoff) return;
      const d = map.get(r.domain) || { domain: r.domain, count: 0, risk: 0, category: r.category, last: r.created_at };
      d.count++; d.risk = Math.max(d.risk, r.risk);
      if (r.created_at > d.last) d.last = r.created_at;
      map.set(r.domain, d);
    });
    const list = [...map.values()].sort((a, b) => b.count - a.count || b.risk - a.risk || b.last.localeCompare(a.last)).slice(0, 6);
    $('#domainList').innerHTML = list.map((d) => `
      <li>
        <span class="domain-ico" aria-hidden="true">${ICON.globe}</span>
        <div><p class="domain-name">${esc(d.domain)}</p><p class="domain-meta">${esc(catLabel(d.category))} · ${esc(nReports(d.count))} · ${esc(timeAgo(d.last))}</p></div>
        <span class="risk-pill ${d.risk >= 60 ? 'high' : d.risk >= 30 ? 'medium' : 'low'}">${esc(t('risk', { n: d.risk }))}</span>
      </li>`).join('') || `<li class="empty">${esc(t('wl.empty'))}</li>`;
  }

  function filteredReports() {
    const q = ui.search.trim().toLowerCase();
    return data.reports.filter((r) => {
      if (ui.trendId && r.trend?.id !== ui.trendId) return false;
      if (ui.filter === 'high' && r.level !== 'high') return false;
      if (['internship', 'rental', 'phishing'].includes(ui.filter) && r.category !== ui.filter) return false;
      if (q && !`${r.message} ${r.sender} ${r.url} ${r.domain} ${r.trend?.title || ''}`.toLowerCase().includes(q)) return false;
      return true;
    });
  }

  function renderFeed(fresh = []) {
    const list = filteredReports();
    const shown = list.slice(0, ui.shown);
    $('#feedCount').textContent = list.length !== data.reports.length
      ? t('fd.countOf', { n: nReports(list.length), total: data.reports.length })
      : t('fd.count', { n: nReports(list.length) });
    const tf = $('#trendFilter');
    const tr = ui.trendId && data.trends.find((x) => x.id === ui.trendId);
    tf.hidden = !tr;
    if (tr) $('#trendFilterText').textContent = t('fd.trend', { title: trendTitle(tr) });

    $('#feed').innerHTML = shown.map((r) => {
      const c = cat(r.category);
      const z = zoneOf(r.location);
      const trn = r.trend && data.trends.find((x) => x.id === r.trend.id);
      return `
      <article class="report ${fresh.includes(r.id) ? 'new' : ''}" style="--c:${c.color}" data-report="${r.id}" tabindex="0" role="button">
        <div class="report-ico" aria-hidden="true">${c.icon}</div>
        <div>
          <div class="report-meta"><span class="cat-pill" style="--c:${c.color}">${esc(catLabel(r.category))}</span><span>${esc(r.channel)}</span>·<span>${esc(timeAgo(r.created_at))}</span>${z ? `·<span class="loc">${ICON.pin}${esc(z.short)}</span>` : ''}${r.domain ? `·<span class="domain-name" style="font-size:12px">${esc(r.domain)}</span>` : ''}</div>
          <p class="report-msg">${esc(r.message)}</p>
          ${trn && !ui.trendId ? `<span class="report-trend">${ICON.layers}${esc(trendTitle(trn))}</span>` : ''}
        </div>
        <div class="report-right"><span class="score ${r.level}">${r.risk}</span><span class="risk-pill ${r.level}">${esc(t('level.' + r.level))}</span></div>
      </article>`;
    }).join('') || `<div class="empty">${esc(t('fd.empty'))}</div>`;
    $('#loadMore').hidden = list.length <= ui.shown;
  }

  function zoneReportCounts() {
    const cutoff = Date.now() - 14 * 864e5;
    const counts = {};
    data.zones.forEach((z) => (counts[z.id] = { total: 0, cats: {} }));
    data.reports.forEach((r) => {
      if (!counts[r.location] || new Date(r.created_at) < cutoff) return;
      counts[r.location].total++;
      counts[r.location].cats[r.category] = (counts[r.location].cats[r.category] || 0) + 1;
    });
    return counts;
  }

  function renderMap() {
    if (!data.zones.length) return;
    const layer = ui.mapLayer;
    $$('#mapLayer button').forEach((b) => { const on = b.dataset.layer === layer; b.classList.toggle('active', on); b.setAttribute('aria-pressed', on); });
    const rc = zoneReportCounts();
    const wifi = net ? net.zones_week : {};
    const values = {};
    data.zones.forEach((z) => (values[z.id] = layer === 'reports' ? rc[z.id].total : (wifi[z.id] || 0)));
    const max = Math.max(1, ...Object.values(values));

    $('#mapDots').innerHTML = data.zones.map((z) => {
      const v = values[z.id];
      const size = v ? 34 + 70 * Math.sqrt(v / max) : 18;
      let color = '#DC2626';
      if (layer === 'reports') {
        const top = Object.entries(rc[z.id].cats).sort((a, b) => b[1] - a[1])[0];
        color = top ? cat(top[0]).hex : '#94A3B8';
      }
      const label = `${z.name}: ${layer === 'reports' ? t('mp.nReports', { n: v }) : t('mp.nBlocked', { n: v })}`;
      return `<button class="hotspot ${v ? '' : 'zero'} ${ui.zone === z.id ? 'selected' : ''}" style="left:${z.x}%;top:${z.y}%;--s:${size}px;--hc:${color}" data-zone="${z.id}" aria-label="${esc(label)}">
        <span class="hs-ring" aria-hidden="true"></span><span class="hs-core" aria-hidden="true">${v}</span>
        <span class="hs-label" aria-hidden="true">${esc(z.short)}</span></button>`;
    }).join('');
    $('#mapLegend').innerHTML = layer === 'reports'
      ? `<span>${esc(t('mp.legendReports'))}</span><span class="legend">${['internship', 'rental', 'phishing'].map((c) => `<span><i class="dot ${c}"></i>${esc(catLabel(c))}</span>`).join('')}</span>`
      : `<span>${esc(t('mp.legendWifi'))}</span>`;
    renderZonePanel(rc, values);
  }

  function renderZonePanel(rc, values) {
    const panel = $('#zonePanel');
    const layer = ui.mapLayer;
    if (!ui.zone) {
      $('#zoneTitle').textContent = t('mp.byBuilding');
      $('#zoneSub').textContent = layer === 'reports' ? t('mp.reportsWeek') : t('mp.blockedWeek');
      const rows = [...data.zones].sort((a, b) => values[b.id] - values[a.id]);
      const max = Math.max(1, ...Object.values(values));
      panel.innerHTML = `<ul class="zone-list">${rows.map((z) => `
        <li><button data-zone="${z.id}">
          <span class="zl-name">${esc(z.name)}</span><b>${values[z.id]}</b>
          <span class="track"><span class="fill" style="width:${(values[z.id] / max) * 100}%;${layer === 'wifi' ? 'background:linear-gradient(90deg,#F87171,#DC2626)' : ''}"></span></span>
        </button></li>`).join('')}</ul>`;
      return;
    }
    const z = zoneOf(ui.zone);
    $('#zoneTitle').textContent = z.name;
    $('#zoneSub').textContent = '';
    const reports = data.reports.filter((r) => r.location === z.id);
    const byTrend = {};
    reports.forEach((r) => { if (r.trend) byTrend[r.trend.id] = (byTrend[r.trend.id] || 0) + 1; });
    const topTrends = Object.entries(byTrend).sort((a, b) => b[1] - a[1]).slice(0, 4).map(([id, n]) => ({ tr: data.trends.find((x) => x.id === id), n })).filter((x) => x.tr);
    const domains = net && net.zone_domains[z.id] ? Object.entries(net.zone_domains[z.id]) : [];
    panel.innerHTML = `
      <button class="back-link" data-zone-back>${ICON.back}${esc(t('mp.back'))}</button>
      <div class="mini-stats" style="grid-template-columns:1fr 1fr">
        <div><b>${rc[z.id].total}</b><small>${esc(t('mp.reportsWeek'))}</small></div>
        <div><b class="score high">${net ? net.zones_week[z.id] : '–'}</b><small>${esc(t('mp.blockedWeek'))}</small></div>
      </div>
      <p class="section-label">${esc(t('mp.top'))}</p>
      ${topTrends.length ? `<div class="mini-list">${topTrends.map(({ tr, n }) => `
        <button class="mini-report" data-trend="${tr.id}" style="text-align:left"><p>${esc(trendTitle(tr))}</p><small>${esc(catLabel(tr.category))} · ${esc(nReports(n))}</small></button>`).join('')}</div>`
        : `<p class="muted">${esc(t('mp.nothing'))}</p>`}
      <p class="section-label">${esc(t('mp.topDomains'))}</p>
      ${domains.length ? `<ul class="bars small" id="zoneDomains"></ul>` : `<p class="muted">–</p>`}`;
    if (domains.length) renderBars($('#zoneDomains'), domains.map(([d, n]) => ({ html: `<span class="domain-name">${esc(d)}</span>`, count: n, color: 'linear-gradient(90deg,#F87171,#DC2626)' })));
  }

  function renderNetwork() {
    if (!net) { loadNetwork(); return; }
    const st = net.status;
    $('#netStatus').innerHTML = `
      <span class="ns-dot" aria-hidden="true"></span>
      <div><b>${esc(t('nw.status'))}</b><small>${esc(t('nw.statusSub', { r: st.resolver, ago: timeAgo(st.last_sync), n: st.access_points }))}</small></div>
      <span class="ns-badge">${ICON.shieldOk}DNS</span>`;
    $('#nwToday').textContent = net.blocked_today;
    $('#nwDevices').textContent = net.devices_today;
    $('#nwWeek').textContent = net.blocked_week;
    $('#nwList').textContent = st.blocklist_size;

    const nowH = new Date().getHours();
    const labels = net.hourly.map((v, h) => (h % 3 === 0 ? `${String(h).padStart(2, '0')}:00` : ''));
    renderBarChart($('#hourChart'), net.hourly.map((v, h) => (h > nowH ? 0 : v)), labels, '#DC2626',
      (h, v) => `<b>${String(h).padStart(2, '0')}:00–${String(h).padStart(2, '0')}:59</b><span>${esc(t('nw.blocked'))}: ${v}</span>`);

    const top = $('#nwTop');
    if (net.top_today.length) renderBars(top, net.top_today.map((d) => ({ html: `<span class="domain-name">${esc(d.domain)}</span> <span class="muted">· ${esc(catLabel(d.category))}</span>`, count: d.count, color: 'linear-gradient(90deg,#F87171,#DC2626)' })));
    else top.innerHTML = `<li class="empty">${esc(t('nw.emptyTop'))}</li>`;

    const fresh = net.freshKeys || [];
    $('#netLog').innerHTML = `
      <thead><tr><th>${esc(t('nw.th.time'))}</th><th>${esc(t('nw.th.where'))}</th><th>${esc(t('nw.th.ap'))}</th><th>${esc(t('nw.th.device'))}</th><th>${esc(t('nw.th.domain'))}</th><th>${esc(t('nw.th.action'))}</th></tr></thead>
      <tbody>${net.recent.map((e) => {
        const z = zoneOf(e.zone);
        return `<tr class="${fresh.includes(e.time + e.device) ? 'new' : ''}">
          <td class="mono">${esc(fmtTime(e.time))}</td><td>${esc(z ? z.short : e.zone)}</td><td class="mono">${esc(e.ap)}</td>
          <td class="mono muted">${esc(e.device)}</td><td><span class="domain-name">${esc(e.domain)}</span></td>
          <td><span class="risk-pill high">${esc(t('nw.blocked'))}</span></td></tr>`;
      }).join('')}</tbody>`;

    $('#blockTable').innerHTML = `
      <thead><tr><th>${esc(t('nw.th.domain'))}</th><th>${esc(t('nw.th.type'))}</th><th class="num">${esc(t('nw.th.reports'))}</th><th class="num">${esc(t('nw.th.blocked'))}</th><th>${esc(t('nw.th.added'))}</th></tr></thead>
      <tbody>${net.blocklist.map((b) => `<tr>
        <td><span class="domain-name">${esc(b.domain)}</span></td>
        <td><span class="cat-pill" style="--c:${cat(b.category).color}">${esc(catLabel(b.category))}</span></td>
        <td class="num">${b.reports}</td><td class="num"><b>${b.blocked_week}</b></td><td class="muted">${esc(timeAgo(b.added_at))}</td></tr>`).join('')}</tbody>`;
  }

  async function loadDirectory() {
    try { const d = await api(VERIFY_API); directory = d.directory; renderDirectory(); } catch (e) { return; }
  }

  function renderDirectory() {
    if (!directory) return;
    $('#directory').innerHTML = `
      <div class="dir-grid">${directory.official.map((e) => `
        <div class="dir-item"><span class="dir-ico" aria-hidden="true">${ICON.shieldOk}</span>
          <div><b>${esc(e.name)}</b><small>${esc(e.type)}</small>
          <div class="chip-row">${e.domains.map((d) => `<span class="ok-chip">${esc(d)}</span>`).join('')}</div>
          ${e.note ? `<p class="muted dir-note">${esc(e.note)}</p>` : ''}</div></div>`).join('')}</div>
      <p class="section-label" style="margin-top:18px">${esc(t('vf.hotlines'))}</p>
      ${directory.hotlines.map((h) => `<div class="dir-item hotline"><span class="dir-ico" aria-hidden="true">${ICON.phone}</span><div><b>${esc(h.name)} — <a href="tel:${esc(h.number)}">${esc(h.number)}</a></b><p class="muted dir-note">${esc(h.note)}</p></div></div>`).join('')}`;
  }

  async function runVerify(q) {
    const box = $('#verifyResult');
    q = q.trim();
    if (!q) { box.innerHTML = `<div class="vres unknown">${ICON.help}<div><b>${esc(t('vf.invalid'))}</b></div></div>`; return; }
    try {
      const d = await api(`${VERIFY_API}?q=${encodeURIComponent(q)}`);
      directory = d.directory; renderDirectory();
      const r = d.result;
      const host = r.host || r.query;
      let cls = 'unknown', icon = ICON.help, title = '', text = '';
      switch (r.verdict) {
        case 'official':
          cls = 'ok'; icon = ICON.shieldOk;
          title = r.kind === 'phone' ? t('vf.hotline', { name: r.entity.name }) : t('vf.official', { name: r.entity.name });
          text = r.kind === 'phone' ? r.entity.note : `${t('vf.officialText', { host, type: r.entity.type.toLowerCase() })} ${r.entity.note || ''}`;
          break;
        case 'lookalike':
          cls = 'bad'; icon = ICON.shieldX;
          title = t('vf.lookalike', { name: r.similar_to.name });
          text = t('vf.lookalikeText', { domain: r.similar_to.domain, host }) + (r.reports ? ' ' + t('vf.also', { n: nReports(r.reports) }) : '');
          break;
        case 'reported':
          cls = 'bad'; icon = ICON.shieldX; title = t('vf.reported'); text = t('vf.reportedText', { n: nReports(r.reports) }); break;
        case 'suspicious':
          cls = 'warn'; icon = ICON.warn; title = t('vf.suspicious'); text = t('vf.suspiciousText'); break;
        case 'invalid':
          title = t('vf.invalid'); break;
        default:
          title = t('vf.unknown'); text = t('vf.unknownText');
      }
      const trends = (r.trend_ids || []).map((id) => data.trends.find((x) => x.id === id)).filter(Boolean);
      box.innerHTML = `
        <div class="vres ${cls}">
          <span class="vres-ico" aria-hidden="true">${icon}</span>
          <div>
            <p class="vres-q mono">${esc(r.query)}</p>
            <b class="vres-title">${esc(title)}</b>
            ${text ? `<p>${esc(text)}</p>` : ''}
            ${r.flags && r.flags.length ? `<div class="chip-row" style="margin-top:8px">${r.flags.map((f) => `<span class="flag-tag">${esc(flagText(f.label))}</span>`).join('')}</div>` : ''}
            ${trends.length ? `<p class="section-label" style="margin-top:12px">${esc(t('vf.related'))}</p><div class="mini-list">${trends.map((tr) => `<button class="mini-report" data-trend="${tr.id}" style="text-align:left"><p>${esc(trendTitle(tr))}</p><small>${esc(nReports(tr.count))}</small></button>`).join('')}</div>` : ''}
          </div>
        </div>`;
    } catch (e) { box.textContent = e.message; }
  }

  function showDrawer(title, html) {
    $('#drawerTitle').textContent = title;
    $('#drawerBody').innerHTML = html;
    $('#drawer').hidden = false;
    $('#drawerBody').scrollTop = 0;
    $('#drawer [data-close]').focus();
  }

  function adviceBlock(category, list) {
    const items = list && list.length ? list : t('adv.' + (CAT[category] ? category : 'other'));
    return `<div class="advice"><strong>${esc(t('dr.stay'))}</strong><ul>${items.map((a) => `<li>${esc(a)}</li>`).join('')}</ul></div>`;
  }

  function openTrend(id, push = true) {
    const tr = data.trends.find((x) => x.id === id);
    if (!tr) return;
    if (push) drawerStack.length = 0;
    drawerStack.push({ type: 'trend', id });
    const c = cat(tr.category);
    const members = tr.report_ids.map((rid) => data.reports.find((r) => r.id === rid)).filter(Boolean);
    const L = settings.lang;
    const ai = tr.ai;
    const zones = {};
    members.forEach((r) => { if (r.location) zones[r.location] = (zones[r.location] || 0) + 1; });
    const zoneList = Object.entries(zones).sort((a, b) => b[1] - a[1]).map(([zid, n]) => { const z = zoneOf(zid); return z ? `<span class="loc-chip">${ICON.pin}${esc(z.short)} · ${n}</span>` : ''; }).join('');
    showDrawer(t('dr.trend'), `
      <div class="trend-hero" style="--c:${c.color}">
        <div class="tcard-top">${statusBadge(tr.status)}<span class="cat-pill" style="--c:${c.color}">${esc(catLabel(tr.category))}</span></div>
        <h3>${esc(trendTitle(tr))}</h3>
        <p class="muted">${esc(t('dr.desc', { week: nReports(tr.this_week), first: timeAgo(tr.first_seen), last: timeAgo(tr.last_seen), channels: tr.channels.join(', ') }))}</p>
        <div class="mini-stats">
          <div><b>${tr.this_week}</b><small>${esc(t('dr.thisWeek'))}</small></div>
          <div><b>${tr.count}</b><small>${esc(t('dr.total'))}</small></div>
          <div><b class="score ${tr.level}">${tr.risk}</b><small>${esc(t('dr.avgRisk'))}</small></div>
        </div>
      </div>
      ${ai && ai.summary && ai.summary[L] ? `<div class="ai-box"><p class="section-label">${ICON.spark}${esc(t('dr.ai'))}</p><p>${esc(ai.summary[L])}</p><small class="muted">${esc(t('dr.aiBy'))}</small></div>` : ''}
      <div><p class="section-label">${esc(t('dr.perDay'))}</p><div class="spark-lg">${sparkSvg(tr.spark, c.hex, 420, 56)}</div></div>
      <div><p class="section-label">${esc(t('dr.shared'))}</p>
        <ul class="flag-list" style="margin:0">${tr.flags.map((f) => `<li>${ICON.flag}<div>${esc(flagText(f.label))}<small>${esc(t('dr.seenIn', { n: f.count, t: tr.count }))}</small></div></li>`).join('')}</ul></div>
      ${tr.domains.length ? `<div><p class="section-label">${esc(t('dr.domains'))}</p><div class="chip-row">${tr.domains.map((d) => `<span class="mono-chip">${esc(d)}</span>`).join('')}</div></div>` : ''}
      ${zoneList ? `<div><p class="section-label">${esc(t('dr.where'))}</p><div class="chip-row">${zoneList}</div></div>` : ''}
      ${adviceBlock(tr.category, ai && ai.advice ? ai.advice[L] : null)}
      <button class="btn btn-primary" data-got-this="${tr.id}">${ICON.check} ${esc(t('dr.gotThis'))}</button>
      <div><p class="section-label">${esc(t('dr.all', { n: nReports(tr.count) }))}</p>
        <div class="mini-list">${members.map((r) => `
          <button class="mini-report" data-report="${r.id}" style="text-align:left"><p>${esc(r.message)}</p><small>${esc(r.channel)} · ${esc(timeAgo(r.created_at))} · ${esc(t('risk', { n: r.risk }))}</small></button>`).join('')}</div></div>
      <button class="btn btn-ghost" data-filter-trend="${tr.id}">${esc(t('dr.showFeed'))}</button>`);
  }

  function openReport(id) {
    const r = data.reports.find((x) => x.id === id);
    if (!r) return;
    const prev = drawerStack[drawerStack.length - 1];
    if (!prev || prev.type !== 'trend') drawerStack.length = 0;
    drawerStack.push({ type: 'report', id });
    const c = cat(r.category);
    const z = zoneOf(r.location);
    const trn = r.trend && data.trends.find((x) => x.id === r.trend.id);
    const back = prev && prev.type === 'trend' ? `<button class="back-link" data-back>${ICON.back}${esc(t('dr.back'))}</button>` : '';
    const school = r.faculty === 'Not stated' ? t('sc.none') : t('sc.' + r.faculty.toLowerCase());
    showDrawer(t('dr.report'), `
      ${back}
      <div class="drawer-top">
        <div class="gauge" style="--p:${r.risk};--gc:var(--${r.level})"><span>${r.risk}</span><small>/100</small></div>
        <div>
          <p class="preview-level" style="margin:0;color:var(--${r.level})">${esc(t('level.' + r.level))}</p>
          <p class="muted"><span class="cat-pill" style="--c:${c.color}">${esc(catLabel(r.category))}</span> · ${esc(t('dr.reported', { ago: timeAgo(r.created_at) }))}</p>
          ${trn ? `<button class="back-link" style="margin-top:6px" data-trend="${trn.id}">${ICON.layers}${esc(trendTitle(trn))}</button>` : ''}
        </div>
      </div>
      <div><p class="section-label">${esc(t('dr.message'))}</p><div class="quote" style="--c:${c.color}">${esc(r.message)}</div></div>
      <dl class="kv">
        <dt>${esc(t('dr.sender'))}</dt><dd>${r.sender ? esc(r.sender) : `<span class="muted">${esc(t('dr.notProvided'))}</span>`}</dd>
        <dt>${esc(t('dr.link'))}</dt><dd>${r.url ? `<code>${esc(r.url)}</code>` : `<span class="muted">${esc(t('dr.none'))}</span>`}</dd>
        <dt>${esc(t('dr.via'))}</dt><dd>${esc(r.channel)}</dd>
        <dt>${esc(t('dr.location'))}</dt><dd>${z ? esc(z.name) : `<span class="muted">${esc(t('loc.none'))}</span>`}</dd>
        <dt>${esc(t('dr.by'))}</dt><dd>${esc(t('dr.byVal', { school }))}</dd>
        <dt>${esc(t('dr.date'))}</dt><dd>${esc(fmtDate(r.created_at))}</dd>
      </dl>
      <div><p class="section-label">${esc(t('dr.flags', { n: r.flags.length }))}</p>
        <ul class="flag-list" style="margin:0">${r.flags.map((f) => `<li>${ICON.flag}<div>${esc(flagText(f.label))}<small>${esc(f.evidence)}</small></div></li>`).join('') || `<li class="ok">${ICON.check}<div>${esc(t('dr.noFlags'))}</div></li>`}</ul></div>
      ${adviceBlock(r.category)}`);
  }

  let lastFocus = null;
  function openModal(id) { lastFocus = document.activeElement; $(id).hidden = false; const f = $(`${id} input, ${id} textarea, ${id} button`); if (f) setTimeout(() => f.focus(), 30); }
  function closeAll() {
    $$('.modal, .drawer').forEach((m) => (m.hidden = true));
    drawerStack.length = 0;
    if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
  }

  function renderLocationOptions() {
    const sel = $('#locationSelect');
    if (!sel || !data.zones.length) return;
    const cur = sel.value;
    sel.innerHTML = `<option value="">${esc(t('loc.none'))}</option>` + data.zones.map((z) => `<option value="${z.id}">${esc(z.name)}</option>`).join('');
    sel.value = cur;
  }

  function openReportForm(prefill) {
    const f = $('#reportForm');
    f.reset();
    renderLocationOptions();
    if (prefill) Object.entries(prefill).forEach(([k, v]) => { if (f.elements[k]) f.elements[k].value = v; });
    $('#msgError').textContent = '';
    f.querySelector('.field').classList.remove('invalid');
    $('#drawer').hidden = true;
    lastFocus = document.activeElement;
    $('#reportModal').hidden = false;
    preview();
    setTimeout(() => f.elements.message.focus(), 50);
  }

  let previewTimer, previewSeq = 0;
  function preview() {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(async () => {
      const f = $('#reportForm');
      const body = { message: f.elements.message.value, sender: f.elements.sender.value, url: f.elements.url.value };
      if (body.message.trim().length < 10 && !body.url.trim()) { $('#previewEmpty').hidden = false; $('#previewBody').hidden = true; return; }
      const seq = ++previewSeq;
      try {
        const a = await api(`${API}?preview=1`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        if (seq !== previewSeq) return;
        $('#previewEmpty').hidden = true; $('#previewBody').hidden = false;
        const g = $('#gauge');
        g.style.setProperty('--p', a.risk); g.style.setProperty('--gc', `var(--${a.level})`);
        $('#gaugeValue').textContent = a.risk;
        const lv = $('#previewLevel');
        lv.textContent = t('pv.' + a.level);
        lv.style.color = `var(--${a.level})`;
        $('#previewCat').textContent = catLabel(a.category);
        const m = $('#previewMatch');
        m.hidden = false;
        if (a.match) {
          const tr = data.trends.find((x) => x.id === a.match.id);
          m.className = 'match';
          m.innerHTML = `${ICON.layers.replace('<svg', '<svg style="width:13px;height:13px;vertical-align:-2px"')} ${esc(t('rp.match', { n: nReports(a.match.count) }))}<b>${esc(tr ? trendTitle(tr) : a.match.title)}</b>`;
        } else { m.className = 'match new'; m.innerHTML = t('rp.newTrend'); }
        $('#previewFlags').innerHTML = a.flags.map((fl) => `<li>${ICON.flag}<div>${esc(flagText(fl.label))}<small>${esc(fl.evidence)}</small></div></li>`).join('')
          || `<li class="ok">${ICON.check}<div>${esc(t('rp.noFlags'))}</div></li>`;
      } catch (e) { return; }
    }, 350);
  }

  async function submitReport() {
    const f = $('#reportForm');
    const body = Object.fromEntries(new FormData(f).entries());
    if ((body.message || '').trim().length < 10) {
      $('#msgError').textContent = t('rp.err');
      f.querySelector('.field').classList.add('invalid');
      f.elements.message.focus();
      return;
    }
    const btn = $('#submitReport');
    btn.disabled = true; btn.textContent = t('rp.submitting');
    try {
      const res = await api(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
      closeAll();
      aiRequested = false;
      await load();
      const tr = res.trend && data.trends.find((x) => x.id === res.trend.id);
      toast(res.trend && !res.trend.is_new
        ? `${ICON.check}<span>${t('toast.grouped', { title: esc(tr ? trendTitle(tr) : res.trend.title), n: esc(nReports(res.trend.count)) })}</span>`
        : `${ICON.check}<span>${esc(t('toast.new'))}</span>`, 5000);
      ui.filter = 'all'; ui.trendId = null; ui.search = ''; $('#searchInput').value = '';
      $$('#filterChips .chip').forEach((c) => c.classList.toggle('active', c.dataset.filter === 'all'));
      if (ui.view === 'overview') renderFeed([res.report.id]);
      loadNetwork();
    } catch (e) {
      $('#msgError').textContent = e.message;
    } finally {
      btn.disabled = false; btn.textContent = t('rp.submit');
    }
  }

  function renderTips() {
    $('#tipsBody').innerHTML = ['internship', 'rental', 'phishing'].map((k) => `
      <div class="tip"><div class="report-ico" style="--c:${cat(k).color}" aria-hidden="true">${cat(k).icon}</div>
        <div><h3>${esc(t('tips.cat', { cat: catLabel(k) }))}</h3><ul>${t('adv.' + k).map((a) => `<li>${esc(a)}</li>`).join('')}</ul></div></div>`).join('')
      + `<div class="tip"><div class="report-ico" style="--c:var(--primary)" aria-hidden="true">${ICON.flag}</div><div><h3>${esc(t('tips.paid'))}</h3><ul>${t('tips.paidList').map((a) => `<li>${a}</li>`).join('')}</ul></div></div>`;
  }


  const FZ_SCENARIOS = {
    paid: [{ key: 'fz.paid.1', go: 'fzBanks', btn: 'fz.goBanks' }, { key: 'fz.paid.2', tel: '997', btn: 'fz.call997' }, { key: 'fz.paid.3' }, { key: 'fz.paid.4' }],
    creds: [{ key: 'fz.creds.1', go: 'fzLock', btn: 'fz.goLock' }, { key: 'fz.creds.2', go: 'fzBanks', btn: 'fz.goBanks' }, { key: 'fz.creds.3' }, { key: 'fz.creds.4', tel: '+60389925050', btn: 'fz.callApu' }],
    click: [{ key: 'fz.click.1' }, { key: 'fz.click.2' }, { key: 'fz.click.3', go: 'fzLock', btn: 'fz.goLock' }, { key: 'fz.click.4', report: true, btn: 'report' }],
  };
  const freeze = { data: null, scenario: null, last: null };

  async function loadFreeze() {
    try { freeze.data = await api(`${FREEZE_API}?t=${Date.now()}`); if (ui.view === 'freeze') renderFreeze(); } catch (e) { return; }
  }

  async function copyText(text) {
    let ok = false;
    try { await navigator.clipboard.writeText(text); ok = true; } catch (e) {
      const ta = document.createElement('textarea'); ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
      document.body.appendChild(ta); ta.select();
      try { ok = document.execCommand('copy'); } catch (e2) { ok = false; }
      ta.remove();
    }
    toast(ok ? `${ICON.check}<span>${esc(t('fz.copied', { n: text }))}</span>` : `<span>${esc(text)}</span>`);
  }

  function renderTriage() {
    $$('#triageTabs .triage-tab').forEach((b) => { const on = b.dataset.scenario === freeze.scenario; b.classList.toggle('active', on); b.setAttribute('aria-selected', on); });
    const panel = $('#triagePanel');
    if (!freeze.scenario) { panel.innerHTML = `<p class="muted triage-hint">${esc(t('fz.pick'))}</p>`; return; }
    panel.innerHTML = `<ol class="triage-steps">${FZ_SCENARIOS[freeze.scenario].map((st, i) => `
      <li><span class="ts-n">${i + 1}</span><span class="ts-text">${esc(t(st.key))}</span>
        ${st.go ? `<button class="btn btn-sm btn-ghost" data-goto="${st.go}">${esc(t(st.btn))}${ICON.chevron}</button>` : ''}
        ${st.tel ? `<a class="btn btn-sm btn-danger" href="tel:${st.tel}">${ICON.phone}${esc(t(st.btn))}</a>` : ''}
        ${st.report ? `<button class="btn btn-sm btn-ghost" data-open-report>${esc(t(st.btn))}</button>` : ''}
      </li>`).join('')}</ol>`;
  }

  function renderBanks() {
    if (!freeze.data) return;
    const q = ($('#bankSearch').value || '').trim().toLowerCase();
    const list = freeze.data.directory.filter((b) => b.id !== 'nsrc' && (!q || `${b.name} ${b.display}`.toLowerCase().includes(q)));
    $('#bankGrid').innerHTML = list.map((b) => {
      const sentence = b.type === 'campus';
      const steps = sentence ? t('fz.steps.' + b.id) : b.steps;
      const hours = b.hours === '24/7' ? t('fz.h24') : b.id === 'apu' ? t('fz.hApu') : t('fz.hCheck');
      return `
      <article class="bank-card ${b.type}">
        <div class="bank-top">
          <div><b class="bank-name">${esc(b.name)}</b><span class="bank-meta"><span class="type-pill ${b.type}">${esc(t('fz.type.' + b.type))}</span><span class="hours ${b.hours === '24/7' ? 'h24' : ''}">${esc(hours)}</span></span></div>
        </div>
        <p class="bank-num mono">${esc(b.display)}</p>
        <div class="bank-actions">
          <a class="btn btn-sm btn-danger" href="tel:${esc(b.tel)}" aria-label="${esc(t('fz.callX', { name: b.name }))}">${ICON.phone}${esc(t('fz.call'))}</a>
          <button class="btn btn-sm btn-ghost" data-copy="${esc(b.display.replace(/\s*\(.*\)$/, ''))}">${esc(t('fz.copy'))}</button>
          ${b.email ? `<a class="btn btn-sm btn-ghost" href="mailto:${esc(b.email)}">${esc(b.email)}</a>` : ''}
        </div>
        <div class="ks">
          <p class="ks-label">${esc(sentence ? t('fz.whatToDo') : t('fz.ks'))}</p>
          ${sentence ? `<ol class="ks-list">${steps.map((x) => `<li>${esc(x)}</li>`).join('')}</ol>`
            : `<div class="ks-path">${steps.map((x, i) => `<span class="ks-seg">${i ? '<span class="ks-arrow" aria-hidden="true">›</span>' : ''}<span class="ks-step">${esc(x)}</span></span>`).join('')}</div>`}
        </div>
      </article>`;
    }).join('') || `<p class="empty">${esc(t('fz.noBank'))}</p>`;
  }

  const fzAction = (type) => t('fz.fd.' + (['apspace', 'email', 'moodle', 'wifi'].includes(type) ? type : 'other'));

  function renderFreezeFeed() {
    if (!freeze.data) return;
    const st = freeze.data.stats;
    $('#fzStats').textContent = t('fz.stats', { today: st.today, domains: st.domains });
    $('#fzFeed').innerHTML = freeze.data.recent.map((f) => {
      const z = zoneOf(f.location);
      return `<li class="${freeze.last && freeze.last === f.id ? 'new' : ''}">
        <span class="fz-dot" aria-hidden="true">${ICON.shieldOk}</span>
        <div><p><b class="mono">${esc(f.anon)}</b> ${esc(fzAction(f.type))}${f.domain ? ` ${esc(t('fz.fd.andBlocked'))} <span class="domain-name">${esc(f.domain)}</span>` : ''}</p>
        <small class="muted">${esc(timeAgo(f.created_at))}${z ? ` · ${esc(z.short)}` : ''} · ${esc(f.ref)}</small></div></li>`;
    }).join('');
  }

  function renderFreeze() {
    renderTriage();
    renderBanks();
    renderFreezeFeed();
    const sel = $('#fzLocation');
    if (sel && data.zones.length) {
      const cur = sel.value;
      sel.innerHTML = `<option value="">${esc(t('loc.none'))}</option>` + data.zones.map((z) => `<option value="${z.id}">${esc(z.name)}</option>`).join('');
      sel.value = cur;
    }
    if (!freeze.data) loadFreeze();
  }

  function freezeResultHtml(r, url) {
    const until = new Date(r.lock_until).toLocaleTimeString(locale(), { hour: '2-digit', minute: '2-digit' });
    const dns = r.actions.find((a) => a.key === 'dns');
    let dnsLine, dnsCls = 'done';
    if (dns.done) dnsLine = t(dns.already ? 'fz.r.dnsAlready' : 'fz.r.dns', { domain: dns.domain });
    else if (dns.official) { dnsLine = t('fz.r.dnsOfficial'); dnsCls = 'info'; }
    else if (dns.given) { dnsLine = t('fz.r.dnsShared'); dnsCls = 'info'; }
    else { dnsLine = t('fz.r.dnsNone'); dnsCls = 'skip'; }
    const items = [
      { cls: 'done', text: t('fz.r.sessions') },
      { cls: 'done', text: t('fz.r.lock', { time: until }) },
      { cls: dnsCls, text: dnsLine },
      { cls: 'done', text: t('fz.r.ticket', { ref: r.ref }) },
    ];
    return `
      <div class="freeze-result">
        <div class="fr-head"><span class="fr-ico" aria-hidden="true">${ICON.shieldOk}</span><div><b>${esc(t('fz.r.title'))}</b><small class="muted">${esc(t('fz.r.sub', { anon: r.anon, ref: r.ref }))}</small></div></div>
        <ul class="fr-list">${items.map((it, i) => `<li class="${it.cls}" style="--d:${i * 380}ms"><span class="fr-check" aria-hidden="true">${it.cls === 'done' ? ICON.check : it.cls === 'info' ? ICON.help : '–'}</span><span>${esc(it.text)}</span></li>`).join('')}</ul>
        <div class="fr-note"><b>${esc(t('fz.r.stillTitle'))}</b><p>${esc(t('fz.r.still'))}</p></div>
        <div class="fr-actions">
          <button class="btn btn-primary btn-sm" data-fz-share="${esc(url)}">${ICON.layers} ${esc(t('fz.r.share'))}</button>
          <a class="btn btn-ghost btn-sm" href="tel:+60389925050">${ICON.phone} ${esc(t('fz.callApu'))}</a>
        </div>
      </div>`;
  }

  async function submitFreeze(e) {
    e.preventDefault();
    const f = $('#freezeForm');
    const body = Object.fromEntries(new FormData(f).entries());
    const tp = (body.tp_number || '').replace(/\s+/g, '').toUpperCase();
    const err = $('#tpError');
    if (!/^TP\d{6}$/.test(tp) && !/^[A-Z]{2,4}\d{5,8}$/.test(tp)) {
      err.textContent = t('fz.tpErr'); f.elements.tp_number.closest('.field').classList.add('invalid'); f.elements.tp_number.focus(); return;
    }
    err.textContent = ''; f.elements.tp_number.closest('.field').classList.remove('invalid');
    const btn = $('#freezeBtn');
    btn.disabled = true; btn.classList.add('busy');
    try {
      const r = await api(FREEZE_API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ...body, tp_number: tp }) });
      freeze.last = r.event.id;
      $('#freezeResult').innerHTML = freezeResultHtml(r, body.url_or_sender || '');
      f.reset();
      await loadFreeze();
      loadNetwork();
      $('#freezeResult').scrollIntoView({ behavior: settings.motion ? 'auto' : 'smooth', block: 'nearest' });
    } catch (ex) {
      err.textContent = t('fz.tpErr');
    } finally {
      btn.disabled = false; btn.classList.remove('busy');
    }
  }

  const ask = { started: false, topic: '', story: '', answers: {}, messages: [], log: [], quick: null, busy: false, done: false, mode: null };

  function openChat() {
    const panel = $('#chatPanel');
    if (!panel.hidden) return;
    panel.hidden = false;
    $('#chatFab').classList.add('open');
    $('#chatFab').setAttribute('aria-expanded', 'true');
    renderAsk();
    setTimeout(() => $('#chatInput').focus(), 60);
  }
  function closeChat() {
    $('#chatPanel').hidden = true;
    $('#chatFab').classList.remove('open');
    $('#chatFab').setAttribute('aria-expanded', 'false');
  }

  function askReset() {
    Object.assign(ask, { started: true, topic: '', story: '', answers: {}, messages: [], log: [], quick: null, busy: false, done: false });
    ask.log.push({ who: 'bot', key: 'ask.hello' });
    ask.quick = { kind: 'topic', options: ['internship', 'rental', 'phishing', 'other'] };
    renderAsk();
  }

  function botText(m) {
    if (m.key) return t(m.key, m.vars);
    return m.text || '';
  }

  function verdictHtml(v, res) {
    const lvlClass = { scam: 'bad', suspicious: 'warn', safe: 'ok' }[v.level] || 'warn';
    const color = { scam: 'var(--high)', suspicious: 'var(--medium)', safe: 'var(--low)' }[v.level];
    const icon = v.level === 'safe' ? ICON.shieldOk : v.level === 'scam' ? ICON.shieldX : ICON.warn;
    const tr = res.trend && data.trends.find((x) => x.id === res.trend.id);
    const reasons = (v.reasons || []).map((r) => {
      if (r.type === 'text') return esc(r.text);
      if (r.type === 'flag') return esc(flagText(r.label));
      if (r.type === 'answer') return esc(t('r.' + r.key));
      if (r.type === 'unsure') return esc(t('r.unsure', { q: t('q.' + r.key) }));
      if (r.type === 'trend' && res.trend) return esc(t('r.trend', { n: nReports(res.trend.count), title: tr ? trendTitle(tr) : res.trend.title }));
      return '';
    }).filter(Boolean);
    const steps = v.steps && v.steps.length ? v.steps : t('adv.' + (CAT[v.category] ? v.category : 'other'));
    const domain = res.analysis && res.analysis.domain;
    return `
      <div class="verdict ${lvlClass}">
        <div class="verdict-top">
          <span class="vres-ico" aria-hidden="true">${icon}</span>
          <div class="verdict-head"><b>${esc(v.title || t('vd.' + v.level + '.title'))}</b><p>${esc(v.summary || t('vd.' + v.level + '.text'))}</p></div>
          <div class="gauge sm" style="--p:${v.score};--gc:${color}" title="${esc(t('vd.score'))}"><span>${v.score}</span><small>%</small></div>
        </div>
        ${reasons.length ? `<p class="section-label">${esc(t('vd.why'))}</p><ul class="reason-list">${reasons.map((r) => `<li>${ICON.flag}<span>${r}</span></li>`).join('')}</ul>` : ''}
        <p class="section-label">${esc(t('vd.next'))}</p>
        <ul class="step-list">${steps.map((x) => `<li>${ICON.check}<span>${esc(x)}</span></li>`).join('')}</ul>
        ${v.level !== 'safe' ? `<p class="paid-line">${ICON.phone}<span>${esc(t('vd.paid'))}</span></p>` : ''}
        <div class="verdict-actions">
          ${v.level !== 'safe' ? `<button class="btn btn-primary btn-sm" data-ask-action="report">${esc(t('vd.report'))}</button><a class="btn btn-danger btn-sm" href="#freeze">${esc(t('nav.freeze'))}</a>` : ''}
          ${domain ? `<button class="btn btn-ghost btn-sm" data-ask-action="verify" data-domain="${esc(domain)}">${esc(t('vd.check'))}</button>` : ''}
          ${tr ? `<button class="btn btn-ghost btn-sm" data-trend="${tr.id}">${ICON.layers} ${esc(t('vd.trend'))}</button>` : ''}
        </div>
      </div>`;
  }

  function renderAsk() {
    if (!ask.started) askReset();
    const modeEl = $('#askMode');
    const aiOn = ask.mode ? ask.mode === 'ai' : !!(data.stats && data.stats.ai_enabled);
    modeEl.innerHTML = aiOn ? `${ICON.spark}${esc(t('ask.modeAi'))}` : esc(t('ask.modeRules'));
    modeEl.className = 'mode-badge ' + (aiOn ? 'ai' : '');
    const log = $('#chatLog');
    log.innerHTML = ask.log.map((m) => {
      if (m.who === 'user') return `<div class="msg user"><div class="bubble">${esc(m.labelKey ? t(m.labelKey) : m.text)}</div></div>`;
      if (m.verdict) return `<div class="msg bot wide">${verdictHtml(m.verdict, m.res)}</div>`;
      return `<div class="msg bot"><div class="bubble">${m.step ? `<small class="q-step">${esc(t('ask.step', { n: m.step, t: m.total }))}</small>` : ''}${esc(botText(m))}</div></div>`;
    }).join('') + (ask.busy ? '<div class="msg bot"><div class="bubble typing" aria-label="…"><i></i><i></i><i></i></div></div>' : '');
    const q = $('#chatQuick');
    if (ask.busy || !ask.quick) q.innerHTML = '';
    else if (ask.quick.kind === 'topic') q.innerHTML = ask.quick.options.map((o) => `<button class="chip" data-ask-topic="${o}">${o !== 'other' ? `<i class="dot ${o}"></i>` : ''}${esc(t('ask.topic.' + o))}</button>`).join('');
    else if (ask.quick.kind === 'answer') q.innerHTML = ['yes', 'no', 'unsure'].map((o) => `<button class="chip" data-ask-answer="${o}">${esc(t('ans.' + o))}</button>`).join('');
    else if (ask.quick.kind === 'options') q.innerHTML = ask.quick.options.map((o, i) => `<button class="chip" data-ask-option="${i}">${esc(o)}</button>`).join('');
    else if (ask.quick.kind === 'done') q.innerHTML = `<button class="chip" data-ask-action="restart">${esc(t('ask.restart'))}</button>`;
    $('#chatInput').disabled = ask.busy;
    const last = log.lastElementChild;
    log.scrollTop = last && last.classList.contains('wide') ? last.offsetTop - 12 : log.scrollHeight;
  }

  async function askSend() {
    ask.busy = true; ask.quick = null; renderAsk();
    try {
      const res = await api(ASK_API, { method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lang: settings.lang, topic: ask.topic, story: ask.story, answers: ask.answers, messages: ask.messages }) });
      ask.mode = res.mode;
      if (res.type === 'more') {
        ask.log.push({ who: 'bot', key: 'ask.more' });
      } else if (res.type === 'question') {
        if (res.mode === 'ai') {
          ask.log.push({ who: 'bot', text: res.question.text });
          ask.messages.push({ role: 'assistant', content: JSON.stringify({ type: 'question', text: res.question.text, options: res.question.options }) });
          ask.quick = res.question.options.length ? { kind: 'options', options: res.question.options } : null;
        } else {
          ask.log.push({ who: 'bot', key: 'q.' + res.question.key, step: res.step, total: res.total });
          ask.messages.push({ role: 'assistant', content: t('q.' + res.question.key) });
          ask.pending = res.question.key;
          ask.quick = { kind: 'answer' };
        }
      } else if (res.type === 'verdict') {
        ask.log.push({ who: 'bot', verdict: res.verdict, res });
        ask.done = true;
        ask.quick = { kind: 'done' };
      }
    } catch (e) {
      ask.log.push({ who: 'bot', key: 'ask.error' });
    } finally {
      ask.busy = false; renderAsk();
      if (!ask.done) $('#chatInput').focus();
    }
  }

  function askUserText(text) {
    text = text.trim();
    if (!text || ask.busy) return;
    if (ask.done) askReset();
    ask.log.push({ who: 'user', text });
    ask.messages.push({ role: 'user', content: text });
    if (ask.pending) {
      const v = /^(y|ya|yes|是|ye)/i.test(text) ? 'yes' : /^(n|no|tidak|tak|否|不)/i.test(text) ? 'no' : null;
      if (v) ask.answers[ask.pending] = v; else ask.story += '\n' + text;
      ask.pending = null;
    } else {
      ask.story = ask.story ? ask.story + '\n' + text : text;
    }
    askSend();
  }

  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-copy],[data-scenario],[data-goto],[data-fz-share],[data-trend],[data-report],[data-open-report],[data-close],[data-got-this],[data-filter-trend],[data-back],[data-soon],[data-zone],[data-zone-back],[data-open-settings],[data-example],[data-ask-topic],[data-ask-answer],[data-ask-option],[data-ask-action]');
    if (!el) {
      if (e.target.classList.contains('modal') || e.target.classList.contains('drawer')) closeAll();
      return;
    }
    if (el.dataset.copy) { copyText(el.dataset.copy); return; }
    if (el.dataset.scenario) { freeze.scenario = el.dataset.scenario; renderTriage(); return; }
    if (el.dataset.goto) { const tgt = document.getElementById(el.dataset.goto); if (tgt) { tgt.scrollIntoView({ behavior: settings.motion ? 'auto' : 'smooth', block: 'start' }); const fi = tgt.querySelector('input'); if (fi) setTimeout(() => fi.focus({ preventScroll: true }), 400); } return; }
    if (el.hasAttribute('data-fz-share')) { const u = el.dataset.fzShare; openReportForm({ message: t('fz.shareMsg') + (u ? ' ' + u : ''), url: /^https?:\/\//i.test(u) ? u : '', sender: u.includes('@') ? u : '', channel: u.includes('@') ? 'Email' : 'Other' }); return; }
    if (el.dataset.askTopic) {
      ask.topic = el.dataset.askTopic;
      ask.log.push({ who: 'user', labelKey: 'ask.topic.' + ask.topic });
      ask.log.push({ who: 'bot', key: 'ask.describe' });
      ask.quick = null; renderAsk(); $('#chatInput').focus();
      return;
    }
    if (el.dataset.askAnswer) {
      const v = el.dataset.askAnswer;
      if (ask.pending) ask.answers[ask.pending] = v;
      ask.pending = null;
      ask.log.push({ who: 'user', labelKey: 'ans.' + v });
      ask.messages.push({ role: 'user', content: t('ans.' + v) });
      askSend();
      return;
    }
    if (el.dataset.askOption) { askUserText(ask.quick.options[+el.dataset.askOption]); return; }
    if (el.dataset.askAction) {
      const a = el.dataset.askAction;
      if (a === 'restart') askReset();
      if (a === 'report') openReportForm({ message: ask.story.trim() });
      if (a === 'verify') { if (window.matchMedia('(max-width: 560px)').matches) closeChat(); location.hash = 'verify'; setTimeout(() => { $('#verifyInput').value = el.dataset.domain; runVerify(el.dataset.domain); }, 60); }
      return;
    }
    if (el.dataset.soon) { e.preventDefault(); toast(`<span>${t('toast.soon', { name: esc(t('nav.' + el.dataset.soon)) })}</span>`); return; }
    if (el.hasAttribute('data-open-settings')) { e.preventDefault(); closeSidebar(); openModal('#settingsModal'); return; }
    if (el.hasAttribute('data-open-report')) { closeSidebar(); openReportForm(); return; }
    if (el.hasAttribute('data-close')) { closeAll(); return; }
    if (el.hasAttribute('data-back')) { drawerStack.pop(); const tr = drawerStack.pop(); if (tr) openTrend(tr.id, false); return; }
    if (el.hasAttribute('data-zone-back')) { ui.zone = null; renderMap(); return; }
    if (el.dataset.example) { $('#verifyInput').value = el.dataset.example; runVerify(el.dataset.example); return; }
    if (el.dataset.zone) { ui.zone = ui.zone === el.dataset.zone && el.classList.contains('hotspot') ? null : el.dataset.zone; renderMap(); return; }
    if (el.dataset.gotThis) {
      const tr = data.trends.find((x) => x.id === el.dataset.gotThis);
      const latest = tr && data.reports.find((r) => r.id === tr.report_ids[0]);
      openReportForm(latest ? { message: latest.message, sender: '', url: '', channel: latest.channel } : null);
      return;
    }
    if (el.dataset.filterTrend) {
      ui.trendId = el.dataset.filterTrend; ui.shown = 50;
      closeAll();
      if (ui.view !== 'overview') location.hash = 'overview'; else renderFeed();
      setTimeout(() => $('#feedSection').scrollIntoView({ behavior: settings.motion ? 'auto' : 'smooth', block: 'start' }), 50);
      return;
    }
    if (el.dataset.trend) { openTrend(el.dataset.trend); return; }
    if (el.dataset.report) openReport(el.dataset.report);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !e.target.closest('#chatPanel')) {
      if ($$('.modal, .drawer').some((m) => !m.hidden)) closeAll();
      else if (!$('#chatPanel').hidden) closeChat();
    }
    if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('.report')) { e.preventDefault(); openReport(e.target.dataset.report); }
  });

  $('#filterChips').addEventListener('click', (e) => {
    const chip = e.target.closest('.chip'); if (!chip) return;
    ui.filter = chip.dataset.filter; ui.shown = 8;
    $$('#filterChips .chip').forEach((c) => { c.classList.toggle('active', c === chip); c.setAttribute('aria-pressed', c === chip); });
    renderFeed();
  });
  $('#clearTrendFilter').addEventListener('click', () => { ui.trendId = null; ui.shown = 8; renderFeed(); });
  $('#loadMore').addEventListener('click', () => { ui.shown += 10; renderFeed(); });
  $('#searchInput').addEventListener('input', (e) => {
    ui.search = e.target.value; ui.shown = 8;
    if (ui.view !== 'overview') { location.hash = 'overview'; }
    else renderFeed();
    if (e.target.value.length === 1) setTimeout(() => $('#feedSection').scrollIntoView({ behavior: settings.motion ? 'auto' : 'smooth', block: 'start' }), 30);
  });
  $('#trendSort').addEventListener('click', (e) => {
    const b = e.target.closest('button'); if (!b) return;
    ui.sort = b.dataset.sort;
    $$('#trendSort button').forEach((x) => x.classList.toggle('active', x === b));
    renderTrends();
  });
  $('#moreTrends').addEventListener('click', () => { ui.allTrends = true; renderTrends(); });
  $('#mapLayer').addEventListener('click', (e) => { const b = e.target.closest('button'); if (!b) return; ui.mapLayer = b.dataset.layer; renderMap(); });
  $('#freezeForm').addEventListener('submit', submitFreeze);
  $('#bankSearch').addEventListener('input', renderBanks);
  $('#tpInput').addEventListener('input', () => { $('#tpError').textContent = ''; $('#tpInput').closest('.field').classList.remove('invalid'); });
  $('#chatForm').addEventListener('submit', (e) => { e.preventDefault(); const i = $('#chatInput'); askUserText(i.value); i.value = ''; });
  $('#chatInput').addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); $('#chatForm').requestSubmit(); } });
  $('#askRestart').addEventListener('click', askReset);
  $('#chatFab').addEventListener('click', () => ($('#chatPanel').hidden ? openChat() : closeChat()));
  $('#chatClose').addEventListener('click', () => { closeChat(); $('#chatFab').focus(); });
  $('#chatPanel').addEventListener('keydown', (e) => { if (e.key === 'Escape') { e.stopPropagation(); closeChat(); $('#chatFab').focus(); } });
  $('#verifyForm').addEventListener('submit', (e) => { e.preventDefault(); runVerify($('#verifyInput').value); });

  $('#reportForm').addEventListener('input', preview);
  $('#reportForm').addEventListener('submit', (e) => { e.preventDefault(); submitReport(); });
  $('#submitReport').addEventListener('click', submitReport);
  let sampleIdx = -1;
  $('#fillSample').addEventListener('click', () => {
    sampleIdx = (sampleIdx + 1) % SAMPLES.length;
    const f = $('#reportForm');
    Object.entries(SAMPLES[sampleIdx]).forEach(([k, v]) => { if (f.elements[k]) f.elements[k].value = v; });
    preview();
  });
  $('#openTips').addEventListener('click', (e) => { e.preventDefault(); closeSidebar(); renderTips(); openModal('#tipsModal'); });

  $('#langSeg').addEventListener('click', (e) => {
    const b = e.target.closest('button'); if (!b) return;
    settings.lang = b.dataset.lang; applySettings(); applyI18n(); renderCurrent();
    if ($('#verifyResult').innerHTML && $('#verifyInput').value) runVerify($('#verifyInput').value);
  });
  $('#sizeSeg').addEventListener('click', (e) => {
    const b = e.target.closest('button'); if (!b) return;
    settings.size = b.dataset.size; applySettings(); setTimeout(() => renderCurrent(), 60);
  });
  $('#contrastToggle').addEventListener('change', (e) => { settings.contrast = e.target.checked; applySettings(); });
  $('#motionToggle').addEventListener('change', (e) => { settings.motion = e.target.checked; applySettings(); });
  $('#linksToggle').addEventListener('change', (e) => { settings.links = e.target.checked; applySettings(); });

  const closeSidebar = () => $('#sidebar').classList.remove('open');
  const isMobile = () => window.matchMedia('(max-width: 900px)').matches;
  function setCollapsed(on) {
    $('.app').classList.toggle('collapsed', on);
    const btn = $('#collapseBtn');
    btn.title = on ? t('expand') : t('collapse');
    btn.setAttribute('aria-label', btn.title);
    store.set('sidebarCollapsed', on ? '1' : '0');
    setTimeout(() => renderCurrent(), 240);
  }
  if (store.get('sidebarCollapsed') === '1') $('.app').classList.add('collapsed');
  $('#collapseBtn').addEventListener('click', () => setCollapsed(!$('.app').classList.contains('collapsed')));
  $('#menuBtn').addEventListener('click', () => {
    if (isMobile()) $('#sidebar').classList.add('open');
    else setCollapsed(!$('.app').classList.contains('collapsed'));
  });
  $('#sidebarBackdrop').addEventListener('click', closeSidebar);

  let resizeT;
  window.addEventListener('resize', () => { clearTimeout(resizeT); resizeT = setTimeout(() => renderCurrent(), 150); });

  loadSettings();
  applySettings();
  setView(location.hash.slice(1) || 'overview', false);
  load();
  loadNetwork();
})();

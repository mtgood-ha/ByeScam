(() => {
  'use strict';

  const API = 'api/reports.php';
  const POLL_MS = 20000;
  const CAT = {
    internship: { label: 'Internship / job', color: 'var(--internship)', hex: '#7C3AED',
      icon: '<svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2M2 13h20"/></svg>' },
    rental: { label: 'Rental', color: 'var(--rental)', hex: '#F59E0B',
      icon: '<svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>' },
    phishing: { label: 'Phishing', color: 'var(--phishing)', hex: '#E11D48',
      icon: '<svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4M12 15v2"/></svg>' },
    other: { label: 'Other', color: 'var(--other)', hex: '#94A3B8',
      icon: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/></svg>' },
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
  };
  const STATUS = {
    rising: { label: 'Rising', icon: ICON.up },
    new: { label: 'New', icon: ICON.spark },
    steady: { label: 'Steady', icon: '' },
    fading: { label: 'Fading', icon: ICON.down },
  };
  const ADVICE = {
    internship: ['Real companies never charge you a training, registration or "kit" fee.', 'Check the job on the company\'s official careers page or with APU Career Centre.', 'Be wary of offers that skip interviews or only talk on WhatsApp/Telegram.'],
    rental: ['Never pay a booking fee or deposit before viewing the room in person.', 'Ask to meet the landlord and see the tenancy agreement and their IC.', 'Prices far below the area average are a common bait.'],
    phishing: ['Type the official address yourself (e.g. apspace.apu.edu.my) instead of clicking links.', 'No bank, university or PTPTN will ever ask for your password, OTP or TAC.', 'Check the sender domain carefully: apu-support.xyz is not apu.edu.my.'],
    other: ['Don\'t click links or pay anything until you have verified the sender.', 'Ask a friend or APU IT/Student Services if unsure.'],
  };
  const SAMPLES = [
    { message: 'Hi! Shopee is offering you a paid internship (RM2,800/month). To confirm, pay RM50 training fee to our HR account by today.', sender: 'hr-team@shopee-hiring.online', url: 'https://shopee-hiring.online/confirm', channel: 'WhatsApp' },
    { message: 'Your APSpace password will expire today. Log in here to keep your account active and avoid losing your timetable.', sender: 'it@apu-helpdesk.site', url: 'http://apu-helpdesk.site/login', channel: 'Email' },
    { message: 'Room near APU only RM420, fully furnished. Landlord is overseas, transfer RM300 booking fee first and I will courier the keys.', sender: 'rooms4students@gmail.com', url: '', channel: 'Facebook' },
    { message: 'Earn RM150 per day with Grab food review tasks! Pay RM40 activation fee to start. Limited slots, WhatsApp me now.', sender: 'grab.tasks.my@gmail.com', url: 'https://wa.me/60123456789', channel: 'Telegram' },
  ];

  let data = { reports: [], trends: [], stats: null };
  const ui = { filter: 'all', search: '', trendId: null, shown: 8, sort: 'week', allTrends: false };
  let knownIds = null;
  let lastUpdated = 0;
  const drawerStack = [];

  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const cat = (c) => CAT[c] || CAT.other;
  const cap = (s) => s.charAt(0).toUpperCase() + s.slice(1);
  const plural = (n, w) => `${n} ${w}${n === 1 ? '' : 's'}`;

  function timeAgo(iso) {
    const s = Math.max(1, Math.round((Date.now() - new Date(iso)) / 1000));
    if (s < 60) return 'just now';
    const m = Math.round(s / 60); if (m < 60) return `${m} min ago`;
    const h = Math.round(m / 60); if (h < 24) return `${h} h ago`;
    const d = Math.round(h / 24); return d === 1 ? 'yesterday' : `${d} days ago`;
  }
  const fmtDate = (iso) => new Date(iso).toLocaleString(undefined, { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

  function toast(html, ms = 3600) {
    const t = $('#toast');
    t.innerHTML = html; t.hidden = false;
    clearTimeout(toast._t); toast._t = setTimeout(() => (t.hidden = true), ms);
  }

  async function api(url, opts) {
    const res = await fetch(url, opts);
    const body = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(body.error || `Request failed (${res.status})`);
    return body;
  }

  async function load() {
    try {
      const d = await api(`${API}?t=${Date.now()}`);
      const fresh = knownIds ? d.reports.filter((r) => !knownIds.has(r.id)).map((r) => r.id) : [];
      knownIds = new Set(d.reports.map((r) => r.id));
      data = d;
      lastUpdated = Date.now();
      render(fresh);
    } catch (e) {
      $('#updatedText').textContent = 'Offline – retrying';
      console.error(e);
    }
  }
  setInterval(load, POLL_MS);
  setInterval(() => {
    if (!lastUpdated) return;
    const s = Math.round((Date.now() - lastUpdated) / 1000);
    $('#updatedText').textContent = s < 5 ? 'Live · updated just now' : `Live · updated ${s}s ago`;
  }, 1000);

  function render(fresh = []) {
    renderStats();
    renderAlert();
    renderTrends();
    renderChart();
    renderDonut();
    renderFlags();
    renderDomains();
    renderFeed(fresh);
  }

  function renderStats() {
    const s = data.stats;
    $('#statTotal').textContent = s.total;
    $('#statWeek').textContent = s.this_week;
    $('#statHigh').textContent = s.high;
    $('#statTrends').textContent = s.active_trends;
  }

  function renderAlert() {
    const t = data.trends.find((x) => (x.status === 'rising' || x.status === 'new') && x.count >= 3);
    const box = $('#alertBanner');
    if (!t) { box.hidden = true; return; }
    box.hidden = false;
    $('#alertTitle').textContent = `Trending now: ${t.title}`;
    $('#alertText').textContent = `${plural(t.this_week, 'report')} this week via ${t.channels.join(', ')}. Don't pay or log in — check the red flags before you respond.`;
    $('#alertAction').onclick = () => openTrend(t.id);
  }

  function sparkSvg(values, color, w = 120, h = 40) {
    const max = Math.max(1, ...values);
    const bw = w / values.length;
    return `<svg viewBox="0 0 ${w} ${h}" preserveAspectRatio="none">${values.map((v, i) => {
      const bh = v ? Math.max(3, (v / max) * (h - 2)) : 2;
      return `<rect x="${i * bw + 1}" y="${h - bh}" width="${bw - 2}" height="${bh}" rx="1.5" fill="${v ? color : '#E6E9F2'}" opacity="${v ? (0.45 + 0.55 * (i / values.length)) : 1}"/>`;
    }).join('')}</svg>`;
  }

  function statusBadge(status) {
    const s = STATUS[status] || STATUS.steady;
    return `<span class="status ${status}">${s.icon}${s.label}</span>`;
  }

  function renderTrends() {
    let list = [...data.trends];
    if (ui.sort === 'all') list.sort((a, b) => b.count - a.count || b.this_week - a.this_week);
    const grouped = list.filter((t) => t.count >= 2);
    const shown = ui.allTrends ? list : grouped.slice(0, 5);
    const more = $('#moreTrends');
    more.hidden = ui.allTrends || list.length <= shown.length;
    more.textContent = `Show all ${list.length} trends`;

    $('#trendGrid').innerHTML = shown.map((t, i) => {
      const c = cat(t.category);
      const n = ui.sort === 'week' ? t.this_week : t.count;
      const nLabel = ui.sort === 'week' ? `report${t.this_week === 1 ? '' : 's'}<br>this week` : `report${t.count === 1 ? '' : 's'}<br>in total`;
      const sub = ui.sort === 'week'
        ? `${plural(t.count, 'report')} in total · last seen ${timeAgo(t.last_seen)}`
        : `${t.this_week} this week · first seen ${timeAgo(t.first_seen)}`;
      return `
      <button class="tcard ${i === 0 ? 'featured' : ''}" style="--c:${c.color}" data-trend="${t.id}">
        <div class="tcard-top">
          ${statusBadge(t.status)}
          <span class="cat-pill" style="--c:${c.color}">${c.label}</span>
          <span class="risk-pill ${t.level}">Risk ${t.risk}</span>
        </div>
        <div class="tcard-body">
          <div class="tcount"><b>${n}</b><small>${nLabel}</small></div>
          <div><p class="ttitle">${esc(t.title)}</p><p class="tsub">${sub}</p></div>
          <div class="spark" title="Reports per day, last 14 days">${sparkSvg(t.spark, c.hex)}</div>
        </div>
        <div class="tcard-foot">
          ${t.flags.slice(0, 3).map((f) => `<span class="flag-tag">${esc(f.label)}</span>`).join('')}
          <span class="more">View ${plural(t.count, 'report')}${ICON.chevron}</span>
        </div>
      </button>`;
    }).join('') || '<p class="empty">No trends yet. Be the first to report a scam!</p>';
  }

  function renderChart() {
    const el = $('#trendChart');
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
      const label = i === days.length - 1 ? 'Today' : date.toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
      const bars = cats.map((c) => {
        if (!d[c]) return '';
        const y1 = y(acc + d[c]), y0 = y(acc);
        acc += d[c];
        return `<rect class="bar" x="${x + (cw - bw) / 2}" y="${y1}" width="${bw}" height="${Math.max(0, y0 - y1 - 1.5)}" rx="3" fill="${cat(c).hex}"/>`;
      }).join('');
      svg += `<g class="col" data-i="${i}"><rect class="hover-bg" x="${x + 2}" y="${pt}" width="${cw - 4}" height="${H - pt - pb}" rx="6"/>${bars}
        ${(i % 2 === days.length % 2 || cw > 46) ? `<text class="axis" x="${x + cw / 2}" y="${H - 6}" text-anchor="middle">${label}</text>` : ''}</g>`;
    });
    el.innerHTML = `<svg viewBox="0 0 ${W} ${H}">${svg}</svg><div class="tooltip" hidden></div>`;

    $('#chartLegend').innerHTML = cats.filter((c) => c !== 'other' || data.stats.categories.other)
      .map((c) => `<span><i class="dot ${c}"></i>${cat(c).label}</span>`).join('');

    const tip = $('.tooltip', el);
    $$('g.col', el).forEach((g) => {
      g.addEventListener('mouseenter', () => {
        const d = days[g.dataset.i];
        const total = cats.reduce((s, c) => s + d[c], 0);
        const date = new Date(d.date + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short' });
        tip.innerHTML = `<b>${date} · ${plural(total, 'report')}</b>` + cats.filter((c) => d[c]).map((c) => `<span><i class="dot ${c}"></i>${cat(c).label}: ${d[c]}</span>`).join('');
        const r = g.getBoundingClientRect(), p = el.getBoundingClientRect();
        tip.style.left = `${r.left - p.left + r.width / 2}px`;
        tip.style.top = `${Math.max(60, y(total) * (p.height / H))}px`;
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
      <div class="donut"><svg viewBox="0 0 42 42"><circle r="${r}" cx="21" cy="21" fill="none" stroke="#EEF1F7" stroke-width="5.5"/>${arcs}</svg>
        <div class="donut-center"><b>${data.stats.total}</b><small>reports</small></div></div>
      <ul class="donut-legend">${order.map((c) => `<li><i class="dot ${c}"></i>${cat(c).label}<b>${counts[c]}</b><em>${Math.round((counts[c] / total) * 100)}%</em></li>`).join('')}</ul>`;
  }

  function renderFlags() {
    const flags = data.stats.flags;
    const max = Math.max(1, ...flags.map((f) => f.count));
    $('#flagBars').innerHTML = flags.map((f) => `
      <li><span>${esc(f.label)}</span><b>${f.count}</b><div class="track"><div class="fill" data-w="${(f.count / max) * 100}"></div></div></li>`).join('');
    requestAnimationFrame(() => $$('#flagBars .fill').forEach((el) => (el.style.width = el.dataset.w + '%')));
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
        <span class="domain-ico">${ICON.globe}</span>
        <div><p class="domain-name">${esc(d.domain)}</p><p class="domain-meta">${cat(d.category).label} · ${plural(d.count, 'report')} · ${timeAgo(d.last)}</p></div>
        <span class="risk-pill ${d.risk >= 60 ? 'high' : d.risk >= 30 ? 'medium' : 'low'}">Risk ${d.risk}</span>
      </li>`).join('') || '<li class="empty">No suspicious domains yet.</li>';
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
    $('#feedCount').textContent = `${plural(list.length, 'report')}${list.length !== data.reports.length ? ` (of ${data.reports.length})` : ''} · newest first`;
    const tf = $('#trendFilter');
    const t = ui.trendId && data.trends.find((x) => x.id === ui.trendId);
    tf.hidden = !t;
    if (t) $('#trendFilterText').textContent = `Trend: ${t.title}`;

    $('#feed').innerHTML = shown.map((r) => {
      const c = cat(r.category);
      return `
      <article class="report ${fresh.includes(r.id) ? 'new' : ''}" style="--c:${c.color}" data-report="${r.id}" tabindex="0">
        <div class="report-ico">${c.icon}</div>
        <div>
          <div class="report-meta"><span class="cat-pill" style="--c:${c.color}">${c.label}</span><span>${esc(r.channel)}</span>·<span>${timeAgo(r.created_at)}</span>${r.domain ? `·<span class="domain-name" style="font-size:12px">${esc(r.domain)}</span>` : ''}</div>
          <p class="report-msg">${esc(r.message)}</p>
          ${r.trend && !ui.trendId ? `<span class="report-trend">${ICON.layers}${esc(r.trend.title)}</span>` : ''}
        </div>
        <div class="report-right"><span class="score ${r.level}">${r.risk}</span><span class="risk-pill ${r.level}">${cap(r.level)} risk</span></div>
      </article>`;
    }).join('') || `<div class="empty">No reports match your filters.</div>`;
    $('#loadMore').hidden = list.length <= ui.shown;
  }

  function showDrawer(html) {
    $('#drawerBody').innerHTML = html;
    $('#drawer').hidden = false;
    $('#drawerBody').scrollTop = 0;
  }

  function adviceBlock(category) {
    return `<div class="advice"><strong>How to stay safe</strong><ul>${(ADVICE[category] || ADVICE.other).map((a) => `<li>${esc(a)}</li>`).join('')}</ul></div>`;
  }

  function openTrend(id, push = true) {
    const t = data.trends.find((x) => x.id === id);
    if (!t) return;
    if (push) drawerStack.length = 0;
    drawerStack.push({ type: 'trend', id });
    const c = cat(t.category);
    const members = t.report_ids.map((rid) => data.reports.find((r) => r.id === rid)).filter(Boolean);
    $('#drawer h2').textContent = 'Scam trend';
    showDrawer(`
      <div class="trend-hero" style="--c:${c.color}">
        <div class="tcard-top">${statusBadge(t.status)}<span class="cat-pill" style="--c:${c.color}">${c.label}</span></div>
        <h3>${esc(t.title)}</h3>
        <p class="muted">${plural(t.this_week, 'report')} this week. First reported ${timeAgo(t.first_seen)}, most recently ${timeAgo(t.last_seen)} via ${esc(t.channels.join(', '))}.</p>
        <div class="mini-stats">
          <div><b>${t.this_week}</b><small>this week</small></div>
          <div><b>${t.count}</b><small>total reports</small></div>
          <div><b class="score ${t.level}">${t.risk}</b><small>avg risk</small></div>
        </div>
      </div>
      <div><p class="section-label">Reports per day (last 14 days)</p><div class="spark-lg">${sparkSvg(t.spark, c.hex, 420, 56)}</div></div>
      <div><p class="section-label">Shared red flags</p>
        <ul class="flag-list" style="margin:0">${t.flags.map((f) => `<li>${ICON.flag}<div>${esc(f.label)}<small>seen in ${f.count} of ${t.count} reports</small></div></li>`).join('')}</ul></div>
      ${t.domains.length ? `<div><p class="section-label">Domains used</p><div class="chip-row">${t.domains.map((d) => `<span class="mono-chip">${esc(d)}</span>`).join('')}</div></div>` : ''}
      ${adviceBlock(t.category)}
      <button class="btn btn-primary" data-got-this="${t.id}">${ICON.check} I got this too — report it</button>
      <div><p class="section-label">All ${plural(t.count, 'report')} in this trend</p>
        <div class="mini-list">${members.map((r) => `
          <div class="mini-report" data-report="${r.id}"><p>${esc(r.message)}</p><small>${esc(r.channel)} · ${timeAgo(r.created_at)} · risk ${r.risk}</small></div>`).join('')}</div></div>
      <button class="btn btn-ghost" data-filter-trend="${t.id}">Show these in the report feed</button>`);
  }

  function openReport(id) {
    const r = data.reports.find((x) => x.id === id);
    if (!r) return;
    const prev = drawerStack[drawerStack.length - 1];
    if (!prev || prev.type !== 'trend') drawerStack.length = 0;
    drawerStack.push({ type: 'report', id });
    const c = cat(r.category);
    const back = prev && prev.type === 'trend' ? `<button class="back-link" data-back>${ICON.back}Back to trend</button>` : '';
    $('#drawer h2').textContent = 'Report details';
    showDrawer(`
      ${back}
      <div class="drawer-top">
        <div class="gauge" style="--p:${r.risk};--gc:var(--${r.level})"><span>${r.risk}</span><small>/100</small></div>
        <div>
          <p class="preview-level" style="margin:0;color:var(--${r.level})">${cap(r.level)} risk</p>
          <p class="muted"><span class="cat-pill" style="--c:${c.color}">${c.label}</span> · reported ${timeAgo(r.created_at)}</p>
          ${r.trend ? `<button class="back-link" style="margin-top:6px" data-trend="${r.trend.id}">${ICON.layers}${esc(r.trend.title)}</button>` : ''}
        </div>
      </div>
      <div><p class="section-label">Message</p><div class="quote" style="--c:${c.color}">${esc(r.message)}</div></div>
      <dl class="kv">
        <dt>Sender</dt><dd>${r.sender ? esc(r.sender) : '<span class="muted">Not provided</span>'}</dd>
        <dt>Link</dt><dd>${r.url ? `<code>${esc(r.url)}</code>` : '<span class="muted">None</span>'}</dd>
        <dt>Received via</dt><dd>${esc(r.channel)}</dd>
        <dt>Reported by</dt><dd>${esc(r.faculty)} student</dd>
        <dt>Date</dt><dd>${fmtDate(r.created_at)}</dd>
      </dl>
      <div><p class="section-label">Red flags found (${r.flags.length})</p>
        <ul class="flag-list" style="margin:0">${r.flags.map((f) => `<li>${ICON.flag}<div>${esc(f.label)}<small>${esc(f.evidence)}</small></div></li>`).join('') || `<li class="ok">${ICON.check}<div>No obvious red flags</div></li>`}</ul></div>
      ${adviceBlock(r.category)}`);
  }

  function openModal(id) { $(id).hidden = false; }
  function closeAll() {
    $$('.modal, .drawer').forEach((m) => (m.hidden = true));
    drawerStack.length = 0;
  }

  function openReportForm(prefill) {
    const f = $('#reportForm');
    f.reset();
    if (prefill) Object.entries(prefill).forEach(([k, v]) => { if (f.elements[k]) f.elements[k].value = v; });
    $('#msgError').textContent = '';
    f.querySelector('.field').classList.remove('invalid');
    $('#drawer').hidden = true;
    openModal('#reportModal');
    preview();
    setTimeout(() => f.elements.message.focus(), 50);
  }

  let previewTimer, previewSeq = 0;
  function preview() {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(async () => {
      const f = $('#reportForm');
      const body = { message: f.elements.message.value, sender: f.elements.sender.value, url: f.elements.url.value };
      if (body.message.trim().length < 10 && !body.url.trim()) {
        $('#previewEmpty').hidden = false; $('#previewBody').hidden = true; return;
      }
      const seq = ++previewSeq;
      try {
        const a = await api(`${API}?preview=1`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        if (seq !== previewSeq) return;
        $('#previewEmpty').hidden = true; $('#previewBody').hidden = false;
        const g = $('#gauge');
        g.style.setProperty('--p', a.risk); g.style.setProperty('--gc', `var(--${a.level})`);
        $('#gaugeValue').textContent = a.risk;
        const lv = $('#previewLevel');
        lv.textContent = { high: 'High risk — likely a scam', medium: 'Suspicious', low: 'Low risk' }[a.level];
        lv.style.color = `var(--${a.level})`;
        $('#previewCat').textContent = cat(a.category).label;
        const m = $('#previewMatch');
        m.hidden = false;
        if (a.match) {
          m.className = 'match';
          m.innerHTML = `${ICON.layers.replace('<svg', '<svg style="width:13px;height:13px;vertical-align:-2px"')} Matches an existing trend (${plural(a.match.count, 'report')}):<b>${esc(a.match.title)}</b>`;
        } else {
          m.className = 'match new';
          m.innerHTML = `Doesn't match any trend yet — your report will <b>start a new trend</b>`;
        }
        $('#previewFlags').innerHTML = a.flags.map((fl) => `<li>${ICON.flag}<div>${esc(fl.label)}<small>${esc(fl.evidence)}</small></div></li>`).join('')
          || `<li class="ok">${ICON.check}<div>No obvious red flags yet</div></li>`;
      } catch (e) { console.error(e); }
    }, 350);
  }

  async function submitReport() {
    const f = $('#reportForm');
    const body = Object.fromEntries(new FormData(f).entries());
    if ((body.message || '').trim().length < 10) {
      $('#msgError').textContent = 'Please paste the scam message (at least 10 characters).';
      f.querySelector('.field').classList.add('invalid');
      f.elements.message.focus();
      return;
    }
    const btn = $('#submitReport');
    btn.disabled = true; btn.textContent = 'Submitting…';
    try {
      const res = await api(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
      closeAll();
      await load();
      const t = res.trend;
      toast(t && !t.is_new
        ? `${ICON.check}<span>Thanks! Your report was grouped into <b>${esc(t.title)}</b> (now ${plural(t.count, 'report')}).</span>`
        : `${ICON.check}<span>Thanks! Your report started a new trend. We'll group future reports like it.</span>`, 5000);
      ui.filter = 'all'; ui.trendId = null; ui.search = ''; $('#searchInput').value = '';
      $$('#filterChips .chip').forEach((c) => c.classList.toggle('active', c.dataset.filter === 'all'));
      renderFeed([res.report.id]);
    } catch (e) {
      $('#msgError').textContent = e.message;
    } finally {
      btn.disabled = false; btn.textContent = 'Submit report';
    }
  }

  function renderTips() {
    $('#tipsBody').innerHTML = ['internship', 'rental', 'phishing'].map((k) => `
      <div class="tip"><div class="report-ico" style="--c:${cat(k).color}">${cat(k).icon}</div>
        <div><h3>${cat(k).label} scams</h3><ul>${ADVICE[k].map((a) => `<li>${esc(a)}</li>`).join('')}</ul></div></div>`).join('')
      + `<div class="tip"><div class="report-ico" style="--c:var(--primary)">${ICON.flag}</div><div><h3>Already paid or clicked a link?</h3><ul><li>Call your bank immediately and freeze your card.</li><li>Call the National Scam Response Centre at <b>997</b>.</li><li>Change your APU password and tell APU IT Services.</li></ul></div></div>`;
  }

  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-trend],[data-report],[data-open-report],[data-close],[data-got-this],[data-filter-trend],[data-back],[data-soon]');
    if (!el) {
      if (e.target.classList.contains('modal') || e.target.classList.contains('drawer')) closeAll();
      return;
    }
    if (el.dataset.soon) { e.preventDefault(); toast(`<span><b>${esc(el.dataset.soon)}</b> is coming soon.</span>`); return; }
    if (el.hasAttribute('data-open-report')) { closeSidebar(); openReportForm(); return; }
    if (el.hasAttribute('data-close')) { closeAll(); return; }
    if (el.hasAttribute('data-back')) { drawerStack.pop(); const t = drawerStack.pop(); if (t) openTrend(t.id, false); return; }
    if (el.dataset.gotThis) {
      const t = data.trends.find((x) => x.id === el.dataset.gotThis);
      const latest = t && data.reports.find((r) => r.id === t.report_ids[0]);
      openReportForm(latest ? { message: latest.message, sender: '', url: '', channel: latest.channel } : null);
      return;
    }
    if (el.dataset.filterTrend) {
      ui.trendId = el.dataset.filterTrend; ui.shown = 50;
      closeAll(); renderFeed();
      $('#feedSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
    if (el.dataset.trend) { openTrend(el.dataset.trend); return; }
    if (el.dataset.report) { openReport(el.dataset.report); }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll();
    if (e.key === 'Enter' && e.target.matches('.report')) openReport(e.target.dataset.report);
  });

  $('#filterChips').addEventListener('click', (e) => {
    const chip = e.target.closest('.chip'); if (!chip) return;
    ui.filter = chip.dataset.filter; ui.shown = 8;
    $$('#filterChips .chip').forEach((c) => c.classList.toggle('active', c === chip));
    renderFeed();
  });
  $('#clearTrendFilter').addEventListener('click', () => { ui.trendId = null; ui.shown = 8; renderFeed(); });
  $('#loadMore').addEventListener('click', () => { ui.shown += 10; renderFeed(); });
  $('#searchInput').addEventListener('input', (e) => {
    ui.search = e.target.value; ui.shown = 8; renderFeed();
    if (e.target.value.length === 1) $('#feedSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  $('#trendSort').addEventListener('click', (e) => {
    const b = e.target.closest('button'); if (!b) return;
    ui.sort = b.dataset.sort;
    $$('#trendSort button').forEach((x) => x.classList.toggle('active', x === b));
    renderTrends();
  });
  $('#moreTrends').addEventListener('click', () => { ui.allTrends = true; renderTrends(); });

  $('#reportForm').addEventListener('input', preview);
  $('#reportForm').addEventListener('submit', (e) => { e.preventDefault(); submitReport(); });
  $('#submitReport').addEventListener('click', submitReport);
  $('#fillSample').addEventListener('click', () => {
    const s = SAMPLES[(fillSample.i = ((fillSample.i ?? -1) + 1) % SAMPLES.length)];
    const f = $('#reportForm');
    Object.entries(s).forEach(([k, v]) => (f.elements[k].value = v));
    preview();
  });
  const fillSample = {};

  $('#openTips').addEventListener('click', (e) => { e.preventDefault(); closeSidebar(); renderTips(); openModal('#tipsModal'); });

  const closeSidebar = () => $('#sidebar').classList.remove('open');
  const isMobile = () => window.matchMedia('(max-width: 900px)').matches;
  function setCollapsed(on) {
    $('.app').classList.toggle('collapsed', on);
    const btn = $('#collapseBtn');
    btn.title = on ? 'Expand sidebar' : 'Collapse sidebar';
    btn.setAttribute('aria-label', btn.title);
    try { localStorage.setItem('byescam.sidebarCollapsed', on ? '1' : '0'); } catch (e) {  }
    setTimeout(() => data.stats && renderChart(), 240);
  }
  try { if (localStorage.getItem('byescam.sidebarCollapsed') === '1') $('.app').classList.add('collapsed'); } catch (e) {  }
  $('#collapseBtn').addEventListener('click', () => setCollapsed(!$('.app').classList.contains('collapsed')));
  $('#menuBtn').addEventListener('click', () => {
    if (isMobile()) $('#sidebar').classList.add('open');
    else setCollapsed(!$('.app').classList.contains('collapsed'));
  });
  $('#sidebarBackdrop').addEventListener('click', closeSidebar);

  let resizeT;
  window.addEventListener('resize', () => { clearTimeout(resizeT); resizeT = setTimeout(() => data.stats && renderChart(), 150); });

  load();
})();

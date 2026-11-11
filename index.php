<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Live Campus Scan · ByeScam</title>
  <link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">

  <!-- ============ SIDEBAR ============ -->
  <aside class="sidebar" id="sidebar">
    <a class="brand" href="index.php">
      <img src="assets/img/logo.svg" alt="" width="34" height="34">
      <span>Bye<b>Scam</b></span>
    </a>

    <nav class="nav">
      <p class="nav-label">Protect</p>
      <a class="nav-item active" href="index.php">
        <svg viewBox="0 0 24 24"><path d="M2 12h3l3-8 4 16 3-8h7"/></svg>
        Live Campus Scan
        <span class="live-dot" title="Live"></span>
      </a>
      <a class="nav-item disabled" href="#" data-soon="URL & Links Inspector">
        <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
        URL &amp; Links Inspector
        <span class="badge-soon">Soon</span>
      </a>
      <a class="nav-item disabled" href="#" data-soon="1-Click Emergency Freeze">
        <svg viewBox="0 0 24 24"><path d="M12 2v20M4.9 4.9l14.2 14.2M2 12h20M4.9 19.1 19.1 4.9"/></svg>
        Emergency Freeze
        <span class="badge-soon">Soon</span>
      </a>

      <p class="nav-label">Help</p>
      <a class="nav-item" href="#" id="openTips">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/></svg>
        Safety tips
      </a>
    </nav>

    <div class="sidebar-card">
      <p class="sidebar-card-title">Spotted a scam?</p>
      <p>Share it so ByeScam can warn other students.</p>
      <button class="btn btn-light btn-block" data-open-report>Report a scam</button>
    </div>
  </aside>
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- ============ MAIN ============ -->
  <main class="main">
    <header class="topbar">
      <button class="icon-btn menu-btn" id="menuBtn" aria-label="Open menu">
        <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
      <div class="search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" id="searchInput" placeholder="Search reports, domains or keywords…">
      </div>
      <div class="topbar-right">
        <span class="live-status"><span class="pulse"></span><span id="updatedText">Live</span></span>
        <button class="btn btn-primary" data-open-report>
          <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
          <span class="hide-sm">Report a scam</span>
        </button>
      </div>
    </header>

    <div class="content">
      <div class="page-head">
        <div>
          <h1>Live Campus Scan</h1>
          <p class="muted">Scams currently targeting students, learned from reports submitted by the campus community.</p>
        </div>
      </div>

      <!-- Alert banner -->
      <div class="alert" id="alertBanner" hidden>
        <div class="alert-icon"><svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/></svg></div>
        <div><strong id="alertTitle"></strong><p id="alertText"></p></div>
        <button class="btn btn-sm btn-outline-danger" id="alertAction">View reports</button>
      </div>

      <!-- Stat cards -->
      <section class="stats">
        <div class="stat">
          <div class="stat-icon blue"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg></div>
          <div><p class="stat-label">Total reports</p><p class="stat-value" id="statTotal">–</p><p class="stat-sub">from students on campus</p></div>
        </div>
        <div class="stat">
          <div class="stat-icon teal"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
          <div><p class="stat-label">This week</p><p class="stat-value" id="statWeek">–</p><p class="stat-sub">new reports in 7 days</p></div>
        </div>
        <div class="stat">
          <div class="stat-icon red"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM12 8v4M12 16h.01"/></svg></div>
          <div><p class="stat-label">High-risk scams</p><p class="stat-value" id="statHigh">–</p><p class="stat-sub">risk score 60+</p></div>
        </div>
        <div class="stat">
          <div class="stat-icon amber"><svg viewBox="0 0 24 24"><path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/></svg></div>
          <div><p class="stat-label">Active scam trends</p><p class="stat-value" id="statTrends">–</p><p class="stat-sub">grouped by AI from reports</p></div>
        </div>
      </section>

      <!-- AI-grouped trends -->
      <section class="card" id="trendsSection">
        <div class="card-head">
          <div>
            <h2 class="with-badge">Trending scams on campus <span class="ai-badge"><svg viewBox="0 0 24 24"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/></svg>AI-grouped</span></h2>
            <p class="muted">Similar student reports are automatically grouped into one trend</p>
          </div>
          <div class="seg" id="trendSort">
            <button class="active" data-sort="week">This week</button>
            <button data-sort="all">All time</button>
          </div>
        </div>
        <div class="trend-grid" id="trendGrid"></div>
        <button class="btn btn-ghost btn-block" id="moreTrends" hidden>Show all trends</button>
      </section>

      <!-- Charts row -->
      <section class="grid-2-1">
        <div class="card">
          <div class="card-head">
            <div><h2>Reports over the last 14 days</h2><p class="muted">Daily reports by scam type</p></div>
            <div class="legend" id="chartLegend"></div>
          </div>
          <div class="chart" id="trendChart"></div>
        </div>
        <div class="card">
          <div class="card-head"><div><h2>Scam types</h2><p class="muted">Share of all reports</p></div></div>
          <div class="donut-wrap" id="donut"></div>
        </div>
      </section>

      <!-- Insights row -->
      <section class="grid-2">
        <div class="card">
          <div class="card-head"><div><h2>Most common red flags</h2><p class="muted">What gives these scams away</p></div></div>
          <ul class="bars" id="flagBars"></ul>
        </div>
        <div class="card">
          <div class="card-head"><div><h2>Domain watchlist</h2><p class="muted">Suspicious domains reported in the last 14 days</p></div></div>
          <ul class="domains" id="domainList"></ul>
        </div>
      </section>

      <!-- Live feed -->
      <section class="card" id="feedSection">
        <div class="card-head feed-head">
          <div><h2>Live report feed</h2><p class="muted" id="feedCount"></p></div>
          <div class="trend-filter" id="trendFilter" hidden><span id="trendFilterText"></span><button aria-label="Clear trend filter" id="clearTrendFilter"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button></div>
          <div class="chips" id="filterChips">
            <button class="chip active" data-filter="all">All</button>
            <button class="chip" data-filter="internship"><i class="dot internship"></i>Internship</button>
            <button class="chip" data-filter="rental"><i class="dot rental"></i>Rental</button>
            <button class="chip" data-filter="phishing"><i class="dot phishing"></i>Phishing</button>
            <button class="chip" data-filter="high"><i class="dot high"></i>High risk</button>
          </div>
        </div>
        <div class="feed" id="feed"></div>
        <button class="btn btn-ghost btn-block" id="loadMore" hidden>Show more reports</button>
      </section>

      <footer class="footer">ByeScam · Built to keep university students safe online. Demo data: 30 mock student reports.</footer>
    </div>
  </main>
</div>

<!-- ============ REPORT MODAL ============ -->
<div class="modal" id="reportModal" hidden>
  <div class="modal-box modal-lg" role="dialog" aria-modal="true" aria-labelledby="reportTitle">
    <div class="modal-head">
      <div>
        <h2 id="reportTitle">Report a scam</h2>
        <p class="muted">Paste a suspicious message you or a friend received. ByeScam checks it for red flags and groups it with similar reports from other students.</p>
      </div>
      <button class="icon-btn" data-close aria-label="Close"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <form id="reportForm" class="modal-body report-grid" novalidate>
      <div class="form-col">
        <label class="field">
          <span>Message text <em>*</em></span>
          <textarea name="message" rows="6" placeholder="e.g. Room for rent near APU RM450, landlord overseas, transfer booking fee to secure…" required></textarea>
          <small class="error" id="msgError"></small>
        </label>
        <div class="row-2">
          <label class="field">
            <span>Sender (email / domain / phone)</span>
            <input name="sender" placeholder="hr@company-careers.xyz">
          </label>
          <label class="field">
            <span>Link in the message</span>
            <input name="url" placeholder="https://…">
          </label>
        </div>
        <div class="row-2">
          <label class="field">
            <span>Where did you get it?</span>
            <select name="channel">
              <option>WhatsApp</option><option>Email</option><option>SMS</option><option>Telegram</option>
              <option>Instagram</option><option>Facebook</option><option>Other</option>
            </select>
          </label>
          <label class="field">
            <span>Your school</span>
            <select name="faculty">
              <option>Computing</option><option>Business</option><option>Engineering</option><option>Accounting</option>
              <option>Design</option><option>Psychology</option><option>Not stated</option>
            </select>
          </label>
        </div>
        <button type="button" class="link-btn" id="fillSample">Not sure what to write? Fill in a sample scam</button>
      </div>

      <aside class="preview" id="preview">
        <p class="preview-label">Live analysis</p>
        <div class="preview-empty" id="previewEmpty">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <p>Start typing and ByeScam will check the message for red flags.</p>
        </div>
        <div id="previewBody" hidden>
          <div class="gauge" id="gauge"><span id="gaugeValue">0</span><small>/100</small></div>
          <p class="preview-level" id="previewLevel"></p>
          <p class="preview-cat">Looks like: <b id="previewCat"></b></p>
          <div class="match" id="previewMatch" hidden></div>
          <ul class="flag-list" id="previewFlags"></ul>
        </div>
      </aside>
    </form>
    <div class="modal-foot">
      <button class="btn btn-ghost" data-close>Cancel</button>
      <button class="btn btn-primary" id="submitReport">Submit report</button>
    </div>
  </div>
</div>

<!-- ============ DETAIL DRAWER ============ -->
<div class="drawer" id="drawer" hidden>
  <div class="drawer-box">
    <div class="modal-head">
      <h2>Report details</h2>
      <button class="icon-btn" data-close aria-label="Close"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <div class="drawer-body" id="drawerBody"></div>
  </div>
</div>

<!-- ============ TIPS MODAL ============ -->
<div class="modal" id="tipsModal" hidden>
  <div class="modal-box" role="dialog" aria-modal="true">
    <div class="modal-head">
      <h2>How to spot student scams</h2>
      <button class="icon-btn" data-close aria-label="Close"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <div class="modal-body tips" id="tipsBody"></div>
  </div>
</div>

<div class="toast" id="toast" hidden></div>

<script src="assets/js/app.js"></script>
</body>
</html>

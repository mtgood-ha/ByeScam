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
<a class="skip-link" href="#content" data-i18n="skip">Skip to main content</a>
<div class="app">

  <aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <a class="brand" href="#overview" aria-label="ByeScam home">
      <img src="assets/img/logo.svg" alt="" width="46" height="46">
      <span class="brand-text">Bye<b>Scam</b></span>
    </a>

    <nav class="nav">
      <p class="nav-label" data-i18n="nav.scan">Live Campus Scan</p>
      <a class="nav-item" href="#overview" data-view-link="overview" data-i18n-title="nav.overview">
        <svg viewBox="0 0 24 24"><path d="M2 12h3l3-8 4 16 3-8h7"/></svg>
        <span class="nav-text" data-i18n="nav.overview">Overview</span>
        <span class="live-dot" aria-hidden="true"></span>
      </a>
      <a class="nav-item" href="#map" data-view-link="map" data-i18n-title="nav.map">
        <svg viewBox="0 0 24 24"><path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/></svg>
        <span class="nav-text" data-i18n="nav.map">Campus heatmap</span>
      </a>
      <a class="nav-item" href="#network" data-view-link="network" data-i18n-title="nav.network">
        <svg viewBox="0 0 24 24"><path d="M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0M2 9a15 15 0 0 1 20 0"/><circle cx="12" cy="19.5" r="1"/></svg>
        <span class="nav-text" data-i18n="nav.network">Wi-Fi scam radar</span>
      </a>
      <a class="nav-item" href="#verify" data-view-link="verify" data-i18n-title="nav.verify">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
        <span class="nav-text" data-i18n="nav.verify">Is it really APU?</span>
      </a>

      <a class="nav-item" href="#inspector" data-view-link="inspector" data-i18n-title="nav.url">
        <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
        <span class="nav-text" data-i18n="nav.url">URL &amp; Links Inspector</span>
      </a>
      <a class="nav-item nav-danger" href="#freeze" data-view-link="freeze" data-i18n-title="nav.freeze">
        <svg viewBox="0 0 24 24"><path d="M12 2v20M4.9 4.9l14.2 14.2M2 12h20M4.9 19.1 19.1 4.9"/></svg>
        <span class="nav-text" data-i18n="nav.freeze">Emergency Freeze</span>
      </a>

      <p class="nav-label" data-i18n="nav.help">Help</p>
      <a class="nav-item" href="#" id="openTips" data-i18n-title="nav.tips">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/></svg>
        <span class="nav-text" data-i18n="nav.tips">Safety tips</span>
      </a>
      <a class="nav-item" href="#" data-open-settings data-i18n-title="nav.a11y">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="4.5" r="1.8"/><path d="M5 8.5l7 1.5 7-1.5M12 10v5l-3 6M12 15l3 6"/></svg>
        <span class="nav-text" data-i18n="nav.a11y">Language &amp; accessibility</span>
      </a>
    </nav>

    <div class="sidebar-card">
      <p class="sidebar-card-title" data-i18n="side.title">Spotted a scam?</p>
      <p data-i18n="side.text">Share it so ByeScam can warn other students.</p>
      <button class="btn btn-light btn-block" data-open-report data-i18n="report">Report a scam</button>
    </div>
    <button class="sidebar-mini-report" data-open-report data-i18n-title="report" data-i18n-aria="report">
      <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
    </button>
    <button class="collapse-btn" id="collapseBtn" data-i18n-title="collapse" data-i18n-aria="collapse">
      <svg viewBox="0 0 24 24"><path d="m11 17-5-5 5-5M18 17l-5-5 5-5"/></svg>
      <span class="nav-text" data-i18n="collapse">Collapse</span>
    </button>
  </aside>
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <main class="main">
    <header class="topbar">
      <button class="icon-btn menu-btn" id="menuBtn" data-i18n-aria="menu" data-i18n-title="menu">
        <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
      <div class="search" role="search">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <label for="searchInput" class="sr-only" data-i18n="search">Search</label>
        <input type="search" id="searchInput" data-i18n-ph="searchPh" placeholder="Search reports, domains or keywords…">
      </div>
      <div class="topbar-right">
        <span class="live-status"><span class="pulse" aria-hidden="true"></span><span id="updatedText">Live</span></span>
        <button class="icon-btn settings-btn" data-open-settings data-i18n-title="nav.a11y" data-i18n-aria="nav.a11y">
          <span class="aa" aria-hidden="true">Aa</span>
        </button>
        <button class="btn btn-primary" data-open-report>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
          <span class="hide-sm" data-i18n="report">Report a scam</span>
        </button>
      </div>
    </header>

    <div class="content" id="content" tabindex="-1">

      <section class="view" data-view="overview" aria-labelledby="h-overview">
        <div class="page-head">
          <div>
            <h1 id="h-overview" data-i18n="ov.title">Live Campus Scan</h1>
            <p class="muted" data-i18n="ov.sub">Scams currently targeting students, learned from reports submitted by the campus community.</p>
          </div>
        </div>

        <div class="alert" id="alertBanner" role="alert" hidden>
          <div class="alert-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/></svg></div>
          <div><strong id="alertTitle"></strong><p id="alertText"></p></div>
          <button class="btn btn-sm btn-outline-danger" id="alertAction" data-i18n="viewReports">View reports</button>
        </div>

        <section class="stats" aria-label="Summary">
          <div class="stat">
            <div class="stat-icon blue" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg></div>
            <div><p class="stat-label" data-i18n="st.total">Total reports</p><p class="stat-value" id="statTotal">–</p><p class="stat-sub" data-i18n="st.totalSub">from students on campus</p></div>
          </div>
          <div class="stat">
            <div class="stat-icon teal" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
            <div><p class="stat-label" data-i18n="st.week">This week</p><p class="stat-value" id="statWeek">–</p><p class="stat-sub" data-i18n="st.weekSub">new reports in 7 days</p></div>
          </div>
          <div class="stat">
            <div class="stat-icon red" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM12 8v4M12 16h.01"/></svg></div>
            <div><p class="stat-label" data-i18n="st.high">High-risk scams</p><p class="stat-value" id="statHigh">–</p><p class="stat-sub" data-i18n="st.highSub">risk score 60+</p></div>
          </div>
          <div class="stat">
            <div class="stat-icon amber" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/></svg></div>
            <div><p class="stat-label" data-i18n="st.trends">Active scam trends</p><p class="stat-value" id="statTrends">–</p><p class="stat-sub" data-i18n="st.trendsSub">grouped from reports</p></div>
          </div>
        </section>

        <a class="shield-strip" href="#network" id="shieldStrip">
          <span class="shield-ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0M2 9a15 15 0 0 1 20 0"/><circle cx="12" cy="19.5" r="1"/></svg></span>
          <span><b id="shieldTitle">–</b><small id="shieldSub"></small></span>
          <span class="shield-cta"><span data-i18n="openRadar">Open Wi-Fi radar</span><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></span>
        </a>

        <section class="card" id="trendsSection" aria-labelledby="h-trends">
          <div class="card-head">
            <div>
              <h2 class="with-badge" id="h-trends"><span data-i18n="tr.title">Trending scams on campus</span> <span class="ai-badge" id="aiBadge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/></svg><span id="aiBadgeText">AI-grouped</span></span></h2>
              <p class="muted" data-i18n="tr.sub">Similar student reports are automatically grouped into one trend</p>
            </div>
            <div class="seg" id="trendSort" role="group">
              <button class="active" data-sort="week" data-i18n="tr.week">This week</button>
              <button data-sort="all" data-i18n="tr.all">All time</button>
            </div>
          </div>
          <div class="trend-grid" id="trendGrid"></div>
          <button class="btn btn-ghost btn-block" id="moreTrends" hidden></button>
        </section>

        <section class="grid-2-1">
          <div class="card">
            <div class="card-head">
              <div><h2 data-i18n="ch.title">Reports over the last 14 days</h2><p class="muted" data-i18n="ch.sub">Daily reports by scam type</p></div>
              <div class="legend" id="chartLegend"></div>
            </div>
            <div class="chart" id="trendChart" role="img" data-i18n-aria="ch.title"></div>
          </div>
          <div class="card">
            <div class="card-head"><div><h2 data-i18n="dn.title">Scam types</h2><p class="muted" data-i18n="dn.sub">Share of all reports</p></div></div>
            <div class="donut-wrap" id="donut"></div>
          </div>
        </section>

        <section class="grid-2">
          <div class="card">
            <div class="card-head"><div><h2 data-i18n="fl.title">Most common red flags</h2><p class="muted" data-i18n="fl.sub">What gives these scams away</p></div></div>
            <ul class="bars" id="flagBars"></ul>
          </div>
          <div class="card">
            <div class="card-head"><div><h2 data-i18n="wl.title">Domain watchlist</h2><p class="muted" data-i18n="wl.sub">Suspicious domains reported in the last 14 days</p></div></div>
            <ul class="domains" id="domainList"></ul>
          </div>
        </section>

        <section class="card" id="feedSection" aria-labelledby="h-feed">
          <div class="card-head feed-head">
            <div><h2 id="h-feed" data-i18n="fd.title">Live report feed</h2><p class="muted" id="feedCount" aria-live="polite"></p></div>
            <div class="trend-filter" id="trendFilter" hidden><span id="trendFilterText"></span><button data-i18n-aria="clear" id="clearTrendFilter"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button></div>
            <div class="chips" id="filterChips" role="group">
              <button class="chip active" data-filter="all" data-i18n="fd.all">All</button>
              <button class="chip" data-filter="internship"><i class="dot internship"></i><span data-i18n="cat.internship.short">Internship</span></button>
              <button class="chip" data-filter="rental"><i class="dot rental"></i><span data-i18n="cat.rental">Rental</span></button>
              <button class="chip" data-filter="phishing"><i class="dot phishing"></i><span data-i18n="cat.phishing">Phishing</span></button>
              <button class="chip" data-filter="high"><i class="dot high"></i><span data-i18n="fd.high">High risk</span></button>
            </div>
          </div>
          <div class="feed" id="feed"></div>
          <button class="btn btn-ghost btn-block" id="loadMore" hidden data-i18n="fd.more">Show more reports</button>
        </section>
      </section>

      <section class="view" data-view="map" aria-labelledby="h-map" hidden>
        <div class="page-head">
          <div>
            <h1 id="h-map" data-i18n="mp.title">Campus heatmap</h1>
            <p class="muted" data-i18n="mp.sub">Where on campus students are being targeted, from scam reports and blocked Wi-Fi lookups.</p>
          </div>
          <div class="seg" id="mapLayer" role="group">
            <button class="active" data-layer="reports" data-i18n="mp.reports">Scam reports</button>
            <button data-layer="wifi" data-i18n="mp.wifi">Wi-Fi blocks</button>
          </div>
        </div>
        <section class="map-layout">
          <div class="card map-card">
            <div class="map-wrap" id="mapWrap">
              <img src="assets/img/apu-campus-map.jpg" alt="Map of APU campus showing Blocks B, D, E, J, the Spine, Main Entrance and parking" data-i18n-alt="mp.alt" id="mapImg">
              <div class="map-dots" id="mapDots"></div>
            </div>
            <div class="map-legend" id="mapLegend"></div>
          </div>
          <div class="card zone-card">
            <div class="card-head"><div><h2 id="zoneTitle" data-i18n="mp.byBuilding">By building</h2><p class="muted" id="zoneSub" data-i18n="mp.week">Last 7 days</p></div></div>
            <div id="zonePanel"></div>
          </div>
        </section>
      </section>

      <section class="view" data-view="network" aria-labelledby="h-net" hidden>
        <div class="page-head">
          <div>
            <h1 id="h-net" data-i18n="nw.title">Wi-Fi scam radar</h1>
            <p class="muted" data-i18n="nw.sub">Scam domains reported by students are pushed to the campus DNS, so devices on APU Wi-Fi can't open them.</p>
          </div>
        </div>
        <div class="net-status" id="netStatus"></div>
        <section class="stats" aria-label="Wi-Fi protection summary">
          <div class="stat"><div class="stat-icon red" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg></div>
            <div><p class="stat-label" data-i18n="nw.blockedToday">Blocked today</p><p class="stat-value" id="nwToday">–</p><p class="stat-sub" data-i18n="nw.blockedTodaySub">scam-site lookups stopped</p></div></div>
          <div class="stat"><div class="stat-icon teal" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg></div>
            <div><p class="stat-label" data-i18n="nw.devices">Devices protected</p><p class="stat-value" id="nwDevices">–</p><p class="stat-sub" data-i18n="nw.devicesSub">today, anonymised</p></div></div>
          <div class="stat"><div class="stat-icon blue" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 4 5-5"/></svg></div>
            <div><p class="stat-label" data-i18n="nw.week">This week</p><p class="stat-value" id="nwWeek">–</p><p class="stat-sub" data-i18n="nw.weekSub">lookups blocked in 7 days</p></div></div>
          <div class="stat"><div class="stat-icon amber" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg></div>
            <div><p class="stat-label" data-i18n="nw.list">Blocklist</p><p class="stat-value" id="nwList">–</p><p class="stat-sub" data-i18n="nw.listSub">domains from student reports</p></div></div>
        </section>

        <section class="card">
          <div class="card-head"><div><h2 data-i18n="nw.how">How it works</h2></div></div>
          <ol class="flow" id="flow">
            <li><span class="flow-n">1</span><b data-i18n="nw.f1">Students report</b><small data-i18n="nw.f1s">Scam messages go to ByeScam in the cloud</small></li>
            <li><span class="flow-n">2</span><b data-i18n="nw.f2">ByeScam learns</b><small data-i18n="nw.f2s">High-risk domains join the blocklist</small></li>
            <li><span class="flow-n">3</span><b data-i18n="nw.f3">Campus DNS blocks</b><small data-i18n="nw.f3s">Wi-Fi access points stop the lookup</small></li>
            <li><span class="flow-n">4</span><b data-i18n="nw.f4">Heatmap updates</b><small data-i18n="nw.f4s">Security sees which buildings are hit</small></li>
          </ol>
        </section>

        <section class="grid-2-1">
          <div class="card">
            <div class="card-head"><div><h2 data-i18n="nw.hourly">Blocked lookups today</h2><p class="muted" data-i18n="nw.hourlySub">By hour, all access points</p></div></div>
            <div class="chart" id="hourChart" role="img" data-i18n-aria="nw.hourly"></div>
          </div>
          <div class="card">
            <div class="card-head"><div><h2 data-i18n="nw.top">Most blocked today</h2><p class="muted" data-i18n="nw.topSub">Domains students almost opened</p></div></div>
            <ul class="bars" id="nwTop"></ul>
          </div>
        </section>

        <section class="card">
          <div class="card-head"><div><h2 class="with-badge"><span data-i18n="nw.log">Live DNS log</span> <span class="live-chip"><span class="pulse" aria-hidden="true"></span>LIVE</span></h2><p class="muted" data-i18n="nw.logSub">Latest blocked lookups. Device IDs are anonymised.</p></div></div>
          <div class="table-wrap"><table class="table" id="netLog"></table></div>
        </section>

        <section class="card">
          <div class="card-head"><div><h2 data-i18n="nw.blocklist">Campus blocklist</h2><p class="muted" data-i18n="nw.blocklistSub">Auto-generated from student reports with risk 60+. Link shorteners and free email providers are never blocked.</p></div></div>
          <div class="table-wrap"><table class="table" id="blockTable"></table></div>
        </section>
      </section>

      <section class="view" data-view="verify" aria-labelledby="h-verify" hidden>
        <div class="page-head">
          <div>
            <h1 id="h-verify" data-i18n="vf.title">Is it really APU?</h1>
            <p class="muted" data-i18n="vf.sub">Paste an email address, website link or phone number to check whether it really belongs to APU or another official organisation.</p>
          </div>
        </div>
        <section class="card verify-card">
          <form id="verifyForm" class="verify-form" role="search">
            <label for="verifyInput" class="sr-only" data-i18n="vf.label">Email, link or phone number</label>
            <input id="verifyInput" type="text" autocomplete="off" data-i18n-ph="vf.ph" placeholder="e.g. it-helpdesk@apu-support.xyz">
            <button class="btn btn-primary" type="submit" data-i18n="vf.check">Check</button>
          </form>
          <div class="verify-examples"><span data-i18n="vf.try">Try:</span>
            <button type="button" class="chip" data-example="it-helpdesk@apu-support.xyz">it-helpdesk@apu-support.xyz</button>
            <button type="button" class="chip" data-example="https://apspace.apu.edu.my">https://apspace.apu.edu.my</button>
            <button type="button" class="chip" data-example="shopee-careers.online">shopee-careers.online</button>
            <button type="button" class="chip" data-example="997">997</button>
          </div>
          <div id="verifyResult" aria-live="polite"></div>
        </section>
        <section class="card">
          <div class="card-head"><div><h2 data-i18n="vf.dir">Official directory</h2><p class="muted" data-i18n="vf.dirSub">Real websites and email domains. Anything else claiming to be them is fake.</p></div></div>
          <div class="directory" id="directory"></div>
        </section>
      </section>

      <section class="view" data-view="freeze" aria-labelledby="h-freeze" hidden>
        <div class="page-head">
          <div>
            <h1 id="h-freeze" data-i18n="fz.title">Emergency Freeze</h1>
            <p class="muted" data-i18n="fz.sub">Fell for a scam? The first minutes matter most. Pick what happened and follow the steps — stay calm, you can still stop it.</p>
          </div>
        </div>

        <section class="card" aria-labelledby="h-triage">
          <div class="card-head"><div><h2 id="h-triage" data-i18n="fz.triage">What just happened?</h2></div></div>
          <div class="triage" role="tablist" id="triageTabs">
            <button class="triage-tab" role="tab" data-scenario="paid" aria-selected="false"><span class="tri-ico red" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="13" rx="2"/><path d="M2 10h20M6 15h4"/></svg></span><b data-i18n="fz.sc.paid">I transferred money / paid a fee</b></button>
            <button class="triage-tab" role="tab" data-scenario="creds" aria-selected="false"><span class="tri-ico amber" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg></span><b data-i18n="fz.sc.creds">I clicked a link &amp; entered my password / OTP</b></button>
            <button class="triage-tab" role="tab" data-scenario="click" aria-selected="false"><span class="tri-ico blue" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg></span><b data-i18n="fz.sc.click">I only clicked a suspicious link</b></button>
          </div>
          <div class="triage-panel" id="triagePanel" role="tabpanel" aria-live="polite"></div>
        </section>

        <section class="nsrc-banner" id="fzNsrc" aria-labelledby="h-nsrc">
          <div class="nsrc-num" aria-hidden="true">997</div>
          <div class="nsrc-text">
            <h2 id="h-nsrc" data-i18n="fz.nsrc.title">Lost money? Call NSRC 997 now</h2>
            <p data-i18n="fz.nsrc.text">The National Scam Response Centre works with the police, Bank Negara and your bank to trace and freeze stolen money. Open 24 hours — the call also counts as your police report.</p>
          </div>
          <div class="nsrc-actions">
            <a class="btn btn-primary btn-call" href="tel:997"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg><span data-i18n="fz.call997">Call 997</span></a>
            <button class="btn btn-ghost btn-light-ghost" data-copy="997"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg><span data-i18n="fz.copy">Copy number</span></button>
          </div>
        </section>

        <section class="card" id="fzBanks" aria-labelledby="h-banks">
          <div class="card-head">
            <div><h2 id="h-banks" data-i18n="fz.banks">Bank &amp; e-wallet Kill Switch</h2><p class="muted" data-i18n="fz.banksSub">Freeze your online banking yourself in seconds, then call the fraud hotline.</p></div>
            <div class="search fz-search" role="search">
              <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
              <label for="bankSearch" class="sr-only" data-i18n="fz.searchBank">Find your bank</label>
              <input type="search" id="bankSearch" data-i18n-ph="fz.searchBank" placeholder="Find your bank…">
            </div>
          </div>
          <div class="bank-grid" id="bankGrid"></div>
          <p class="muted fz-disclaimer" data-i18n="fz.menuNote">App menus can change after updates. If you can't find the Kill Switch, call the hotline straight away.</p>
        </section>

        <section class="grid-2-1 fz-lower">
          <div class="card lock-card" id="fzLock" aria-labelledby="h-lock">
            <div class="card-head">
              <div><h2 id="h-lock" class="with-badge"><span data-i18n="fz.lock.title">1-Click APU Account &amp; Wi-Fi Freeze</span> <span class="demo-badge" data-i18n="fz.demo">Demo simulation</span></h2>
              <p class="muted" data-i18n="fz.lock.sub">Entered your APKey on a fake APSpace or Moodle page? Lock your sessions and block the link for everyone on campus Wi-Fi.</p></div>
            </div>
            <form id="freezeForm" novalidate>
              <div class="row-2">
                <label class="field">
                  <span><span data-i18n="fz.tp">TP number</span> <em>*</em></span>
                  <input name="tp_number" id="tpInput" placeholder="TP071234" autocomplete="off" maxlength="12" aria-describedby="tpHelp tpError" required>
                  <small class="muted" id="tpHelp" data-i18n="fz.tpHelp">Only the first 4 characters are kept (e.g. TP07****).</small>
                  <small class="error" id="tpError" role="alert"></small>
                </label>
                <label class="field">
                  <span data-i18n="fz.what">What was compromised?</span>
                  <select name="incident_type">
                    <option value="apspace" data-i18n="fz.it.apspace">APSpace / APKey password</option>
                    <option value="email" data-i18n="fz.it.email">Student email (Office 365)</option>
                    <option value="moodle" data-i18n="fz.it.moodle">Moodle</option>
                    <option value="wifi" data-i18n="fz.it.wifi">Clicked a link on campus Wi-Fi</option>
                    <option value="other" data-i18n="fz.it.other">Something else</option>
                  </select>
                </label>
              </div>
              <div class="row-2">
                <label class="field">
                  <span data-i18n="fz.link">Link or sender you clicked (optional)</span>
                  <input name="url_or_sender" placeholder="http://apspace-verify.xyz/login" autocomplete="off">
                </label>
                <label class="field">
                  <span data-i18n="rp.where">Where were you on campus?</span>
                  <select name="location" id="fzLocation"></select>
                </label>
              </div>
              <button class="btn btn-danger btn-block btn-lg" type="submit" id="freezeBtn"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M4.9 4.9l14.2 14.2M2 12h20M4.9 19.1 19.1 4.9"/></svg><span data-i18n="fz.btn">1-Click Freeze My APU Account &amp; Block Link</span></button>
            </form>
            <div id="freezeResult" aria-live="polite"></div>
          </div>
          <div class="card" aria-labelledby="h-recent">
            <div class="card-head"><div><h2 id="h-recent" data-i18n="fz.recent">Recent campus emergency actions</h2><p class="muted" id="fzStats"></p></div></div>
            <ul class="fz-feed" id="fzFeed"></ul>
          </div>
        </section>
      </section>

      <section class="view" data-view="inspector" aria-labelledby="h-ins" hidden>
        <div class="page-head">
          <div>
            <h1 id="h-ins" data-i18n="nav.url">URL &amp; Links Inspector</h1>
            <p class="muted" data-i18n="ins.sub">The ByeScam Chrome extension checks links in Gmail before you click them.</p>
          </div>
        </div>
        <section class="card ext-card" aria-labelledby="h-ext">
          <div class="ext-head">
            <img src="extension/icon.png" alt="" width="56" height="56" class="ext-icon">
            <div>
              <h2 id="h-ext" data-i18n="ins.extTitle">ByeScam Inspector for Chrome</h2>
              <p class="muted" data-i18n="ins.extSub">Free · Works in Gmail · Install in under a minute</p>
            </div>
            <a class="btn btn-primary btn-lg" href="download-extension.php" download="byescam-extension.zip" id="downloadExtension">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              <span data-i18n="ins.download">Download Extension (.zip)</span>
            </a>
          </div>
          <ol class="ext-steps">
            <li><span class="ts-n">1</span><span data-i18n="ins.s1">Click Download Extension (.zip) and extract the folder.</span></li>
            <li><span class="ts-n">2</span><span><span data-i18n="ins.s2">Open a new tab and go to</span> <code class="ext-code">chrome://extensions/</code> <button type="button" class="btn btn-ghost btn-sm" data-copy="chrome://extensions/" data-i18n="ins.copy">Copy</button></span></li>
            <li><span class="ts-n">3</span><span data-i18n="ins.s3">Turn on Developer mode in the top-right corner.</span></li>
            <li><span class="ts-n">4</span><span data-i18n="ins.s4">Click Load unpacked, select the extracted folder, and refresh Gmail.</span></li>
          </ol>
        </section>
      </section>

      <footer class="footer" data-i18n="footer">ByeScam · Built to keep university students safe online. Demo data: 30 mock student reports and a simulated campus DNS.</footer>
    </div>
  </main>
</div>

<div class="modal" id="reportModal" hidden>
  <div class="modal-box modal-lg" role="dialog" aria-modal="true" aria-labelledby="reportTitle">
    <div class="modal-head">
      <div>
        <h2 id="reportTitle" data-i18n="report">Report a scam</h2>
        <p class="muted" data-i18n="rp.sub">Paste a suspicious message you or a friend received. ByeScam checks it for red flags and groups it with similar reports from other students.</p>
      </div>
      <button class="icon-btn" data-close data-i18n-aria="close"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <form id="reportForm" class="modal-body report-grid" novalidate>
      <div class="form-col">
        <label class="field">
          <span><span data-i18n="rp.msg">Message text</span> <em>*</em></span>
          <textarea name="message" rows="6" data-i18n-ph="rp.msgPh" placeholder="e.g. Room for rent near APU RM450, landlord overseas, transfer booking fee to secure…" required aria-describedby="msgError"></textarea>
          <small class="error" id="msgError" role="alert"></small>
        </label>
        <div class="row-2">
          <label class="field">
            <span data-i18n="rp.sender">Sender (email / domain / phone)</span>
            <input name="sender" placeholder="hr@company-careers.xyz">
          </label>
          <label class="field">
            <span data-i18n="rp.url">Link in the message</span>
            <input name="url" placeholder="https://…">
          </label>
        </div>
        <div class="row-3">
          <label class="field">
            <span data-i18n="rp.channel">Where did you get it?</span>
            <select name="channel">
              <option>WhatsApp</option><option>Email</option><option>SMS</option><option>Telegram</option>
              <option>Instagram</option><option>Facebook</option><option value="Other" data-i18n="other">Other</option>
            </select>
          </label>
          <label class="field">
            <span data-i18n="rp.where">Where were you on campus?</span>
            <select name="location" id="locationSelect"></select>
          </label>
          <label class="field">
            <span data-i18n="rp.school">Your school</span>
            <select name="faculty">
              <option value="Computing" data-i18n="sc.computing">Computing</option><option value="Business" data-i18n="sc.business">Business</option>
              <option value="Engineering" data-i18n="sc.engineering">Engineering</option><option value="Accounting" data-i18n="sc.accounting">Accounting</option>
              <option value="Design" data-i18n="sc.design">Design</option><option value="Psychology" data-i18n="sc.psychology">Psychology</option>
              <option value="Not stated" data-i18n="sc.none">Prefer not to say</option>
            </select>
          </label>
        </div>
        <button type="button" class="link-btn" id="fillSample" data-i18n="rp.sample">Not sure what to write? Fill in a sample scam</button>
      </div>

      <aside class="preview" id="preview" aria-live="polite">
        <p class="preview-label" data-i18n="rp.live">Live analysis</p>
        <div class="preview-empty" id="previewEmpty">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <p data-i18n="rp.empty">Start typing and ByeScam will check the message for red flags.</p>
        </div>
        <div id="previewBody" hidden>
          <div class="gauge" id="gauge"><span id="gaugeValue">0</span><small>/100</small></div>
          <p class="preview-level" id="previewLevel"></p>
          <p class="preview-cat"><span data-i18n="rp.looks">Looks like:</span> <b id="previewCat"></b></p>
          <div class="match" id="previewMatch" hidden></div>
          <ul class="flag-list" id="previewFlags"></ul>
        </div>
      </aside>
    </form>
    <div class="modal-foot">
      <button class="btn btn-ghost" data-close data-i18n="cancel">Cancel</button>
      <button class="btn btn-primary" id="submitReport" data-i18n="rp.submit">Submit report</button>
    </div>
  </div>
</div>

<div class="drawer" id="drawer" hidden>
  <div class="drawer-box" role="dialog" aria-modal="true" aria-labelledby="drawerTitle">
    <div class="modal-head">
      <h2 id="drawerTitle"></h2>
      <button class="icon-btn" data-close data-i18n-aria="close"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <div class="drawer-body" id="drawerBody"></div>
  </div>
</div>

<div class="modal" id="tipsModal" hidden>
  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="tipsTitle">
    <div class="modal-head">
      <h2 id="tipsTitle" data-i18n="tips.title">How to spot student scams</h2>
      <button class="icon-btn" data-close data-i18n-aria="close"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <div class="modal-body tips" id="tipsBody"></div>
  </div>
</div>

<div class="modal" id="settingsModal" hidden>
  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="settingsTitle">
    <div class="modal-head">
      <h2 id="settingsTitle" data-i18n="nav.a11y">Language &amp; accessibility</h2>
      <button class="icon-btn" data-close data-i18n-aria="close"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <div class="modal-body settings">
      <fieldset>
        <legend data-i18n="set.lang">Language</legend>
        <div class="seg seg-lg" id="langSeg">
          <button data-lang="en" lang="en">English</button>
          <button data-lang="ms" lang="ms">Bahasa Melayu</button>
          <button data-lang="zh" lang="zh">中文</button>
        </div>
      </fieldset>
      <fieldset>
        <legend data-i18n="set.size">Text size</legend>
        <div class="seg seg-lg" id="sizeSeg">
          <button data-size="1" data-i18n="set.normal">Normal</button>
          <button data-size="1.15" data-i18n="set.large">Large</button>
          <button data-size="1.3" data-i18n="set.xl">Extra large</button>
        </div>
      </fieldset>
      <label class="switch-row"><span><b data-i18n="set.contrast">High contrast</b><small data-i18n="set.contrastSub">Darker text and stronger borders</small></span><input type="checkbox" id="contrastToggle" role="switch"></label>
      <label class="switch-row"><span><b data-i18n="set.motion">Reduce motion</b><small data-i18n="set.motionSub">Turn off animations and live pulses</small></span><input type="checkbox" id="motionToggle" role="switch"></label>
      <label class="switch-row"><span><b data-i18n="set.links">Underline links</b><small data-i18n="set.linksSub">Make clickable text easier to spot</small></span><input type="checkbox" id="linksToggle" role="switch"></label>
    </div>
  </div>
</div>

<button class="chat-fab" id="chatFab" aria-controls="chatPanel" aria-expanded="false" data-i18n-aria="nav.ask" data-i18n-title="nav.ask">
  <svg class="fab-open" viewBox="0 0 24 24" aria-hidden="true"><g transform="translate(0 0.9)"><path d="M6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H11l-4 3.5V16H6.5A2.5 2.5 0 0 1 4 13.5v-8A2.5 2.5 0 0 1 6.5 3z"/><path d="M10.1 7.7a2 2 0 0 1 3.85.55c0 1.25-1.95 1.6-1.95 2.6" stroke-width="1.8"/><circle cx="12" cy="13" r=".55" fill="currentColor" stroke-width="1"/></g></svg>
  <svg class="fab-close" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
</button>

<section class="chat-panel" id="chatPanel" role="dialog" aria-labelledby="chatTitle" hidden>
  <div class="chat-head">
    <span class="bot-avatar" aria-hidden="true"><img src="assets/img/logo.svg" alt="" width="36" height="36"></span>
    <div class="chat-title"><b id="chatTitle" data-i18n="nav.ask">Ask before you pay</b><span class="mode-badge" id="askMode"></span></div>
    <button class="icon-btn sm" id="askRestart" data-i18n-title="ask.restart" data-i18n-aria="ask.restart"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg></button>
    <button class="icon-btn sm" id="chatClose" data-i18n-title="close" data-i18n-aria="close"><svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></button>
  </div>
  <div class="chat-log" id="chatLog" role="log" aria-live="polite"></div>
  <div class="chat-quick" id="chatQuick"></div>
  <form class="chat-input" id="chatForm">
    <label for="chatInput" class="sr-only" data-i18n="ask.ph">Describe what happened</label>
    <textarea id="chatInput" rows="2" data-i18n-ph="ask.ph" placeholder="Describe what happened, or paste the message…"></textarea>
    <button class="btn btn-primary" type="submit" data-i18n-aria="ask.send"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
  </form>
  <p class="chat-note"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span data-i18n="ask.note">Never share passwords, OTPs, IC or bank numbers — not even here.</span></p>
</section>

<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>

<script src="assets/js/i18n.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>

# ByeScam — Live Campus Scan

Protects APU students from internship, rental and phishing scams. Students crowdsource scam
reports, ByeScam groups similar reports into trends, and the campus network uses those
reports to block scam sites on Wi-Fi.

## Run it (XAMPP)
1. Start **Apache** in the XAMPP Control Panel.
2. Open http://localhost/ByeScam/
3. On first load, 30 mock reports are seeded into `data/reports.json`.
   Reset the demo data: http://localhost/ByeScam/api/reports.php?reset=1

No database needed (PHP 7.4+, the curl extension is used for AI summaries).

## Pages
| Page | What it shows |
|---|---|
| **Overview** `#overview` | Trending scams (grouped reports), stats, charts, red flags, live report feed |
| **Ask before you pay** `#ask` | Chat: a student describes the situation, ByeScam asks follow-up questions and gives a verdict |
| **Campus heatmap** `#map` | APU campus map with hotspots per building — scam reports or blocked Wi-Fi lookups |
| **Wi-Fi scam radar** `#network` | Simulated campus DNS filter: blocklist built from reports, live block log, hourly chart |
| **Emergency Freeze** `#freeze` | "What just happened?" triage, 1-tap NSRC 997, bank & e-wallet Kill Switch directory, simulated 1-click APU account & Wi-Fi freeze, recent campus actions |
| **Is it really APU?** `#verify` | Checks an email, link or phone number against the official directory and student reports |

## AI summaries (optional)
1. Copy `config.example.php` to `config.php`.
2. Put your Anthropic API key in `anthropic_api_key`.
3. Reload the dashboard. Trends with 2+ reports get a Claude-written title, summary and
   safety advice in English, Bahasa Melayu and Chinese (cached in `data/ai_cache.json`).

With a key, **Ask before you pay** is also run by Claude (it asks up to 3 follow-up questions
and returns a verdict in the student's language).

Without a key, trend titles are written by ByeScam's own rule-based grouping, and
**Ask before you pay** uses a built-in smart checklist (topic-specific yes/no questions,
red-flag analysis and matching against campus trends). If an AI call fails, it falls back
to the checklist automatically.
`config.php` is git-ignored so the key is never committed.

## Accessibility
Language & accessibility settings (the **Aa** button): English / Bahasa Melayu / 中文,
text size, high contrast, reduce motion and underlined links. Also: skip link, keyboard
navigation, screen-reader labels and live regions.

## Demo notes
- The Wi-Fi radar uses simulated DNS logs generated from the real blocklist, so it updates
  live without real network hardware. A real deployment would feed the blocklist to the
  campus resolver (e.g. a DNS RPZ zone) and read its query logs.
- The official directory in `includes/campus.php` should be confirmed with APU IT before launch.
- **Emergency Freeze** (`includes/freeze.php`, `api/freeze.php`): the APU account lock and helpdesk ticket are
  **simulated** — the UI labels them "Demo simulation" and still tells students to change their APKey password and
  contact APU IT. Domains submitted in a freeze are added to the real ByeScam Wi-Fi blocklist (official APU
  domains, free email providers and link shorteners are never blocked). Only the first 4 characters of the TP
  number are stored (e.g. `TP07****`). Bank hotlines and Kill Switch steps were checked against the banks'
  own pages in November 2026 — re-check them before a real launch, since app menus change.

## AI assistance
Parts of this project, including code, UI design and documentation, were developed with the help of
[Claude](https://claude.ai) by Anthropic as an AI coding assistant. The team reviewed, tested and
adapted all of the output.

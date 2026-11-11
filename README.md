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
| **Campus heatmap** `#map` | APU campus map with hotspots per building — scam reports or blocked Wi-Fi lookups |
| **Wi-Fi scam radar** `#network` | Simulated campus DNS filter: blocklist built from reports, live block log, hourly chart |
| **Is it really APU?** `#verify` | Checks an email, link or phone number against the official directory and student reports |

## AI summaries (optional)
1. Copy `config.example.php` to `config.php`.
2. Put your Anthropic API key in `anthropic_api_key`.
3. Reload the dashboard. Trends with 2+ reports get a Claude-written title, summary and
   safety advice in English, Bahasa Melayu and Chinese (cached in `data/ai_cache.json`).

Without a key, trend titles are written by ByeScam's own rule-based grouping.
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

## Coming soon
- URL & Links Inspector
- 1-Click Emergency Freeze

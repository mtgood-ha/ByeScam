# ByeScam — Live Campus Scan

Protects university students from internship, rental and phishing scams.
This build focuses on **Live Campus Scan**: students crowdsource scam reports and
ByeScam groups similar reports into trends, e.g.

> 7 reports this week: Fake Shopee internship asking for an RM50 training fee

## Run it (XAMPP)
1. Start **Apache** in the XAMPP Control Panel.
2. Open http://localhost/ByeScam/
3. On first load, 30 mock reports are seeded into `data/reports.json`.
   To reset the demo data: http://localhost/ByeScam/api/reports.php?reset=1

No database needed (PHP 7.4+).

## How it works
- `includes/analyzer.php` reads the **message text, sender domain and URL**, finds red flags
  (upfront fee, urgency, OTP/password requests, look-alike domains, risky TLDs, short links…)
  and gives a 0–100 risk score.
- `includes/trends.php` groups similar reports. Two reports are similar when they share the
  scam type, impersonated brand (Shopee, APU, Maybank…), what's being asked for (training fee,
  booking fee, OTP…), red flags and wording. Each group gets an auto-written headline.
- `api/reports.php` — GET dashboard data, POST a new report, POST `?preview=1` for live analysis.
- `index.php` + `assets/` — the dashboard UI (vanilla JS, no libraries).

## Coming soon
- URL & Links Inspector
- 1-Click Emergency Freeze

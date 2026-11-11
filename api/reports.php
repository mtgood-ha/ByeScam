<?php
/**
 * GET  api/reports.php             -> { reports, trends, stats }
 * GET  api/reports.php?reset=1     -> wipe and re-seed the 30 demo reports
 * POST api/reports.php?preview=1   -> analyse a message + show which trend it matches (nothing saved)
 * POST api/reports.php             -> save a new crowdsourced report
 */
require_once __DIR__ . '/../includes/store.php';
header('Content-Type: application/json; charset=utf-8');

function bs_json($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $in['message'] = mb_substr(trim((string)($in['message'] ?? '')), 0, 2000);
    $in['sender']  = mb_substr(trim((string)($in['sender'] ?? '')), 0, 200);
    $in['url']     = mb_substr(trim((string)($in['url'] ?? '')), 0, 500);

    $reports = bs_load_reports();

    if (isset($_GET['preview'])) {
        $draft = bs_make_report($in);
        bs_json([
            'category' => $draft['category'], 'risk' => $draft['risk'], 'level' => $draft['level'],
            'flags' => $draft['flags'], 'domain' => $draft['domain'],
            'match' => bs_match_trend($draft, bs_trends($reports)),
        ]);
    }

    if (mb_strlen($in['message']) < 10) bs_json(['error' => 'Please paste the scam message (at least 10 characters).'], 422);

    $report = bs_make_report($in);
    array_unshift($reports, $report);
    bs_save_reports($reports);

    // Tell the student which trend their report joined
    $match = null;
    foreach (bs_trends($reports) as $t) {
        if (in_array($report['id'], $t['report_ids'], true)) {
            $match = ['id' => $t['id'], 'title' => $t['title'], 'count' => $t['count'], 'is_new' => $t['count'] === 1];
            break;
        }
    }
    unset($report['features']);
    bs_json(['report' => $report, 'trend' => $match]);
}

if (isset($_GET['reset'])) bs_save_reports(bs_seed_reports());

bs_json(bs_dashboard(bs_load_reports()) + ['server_time' => date('c')]);

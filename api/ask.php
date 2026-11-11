<?php
require_once __DIR__ . '/../includes/ask.php';
header('Content-Type: application/json; charset=utf-8');
@set_time_limit(60);

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$lang = in_array($in['lang'] ?? 'en', ['en', 'ms', 'zh'], true) ? $in['lang'] : 'en';
$messages = [];
foreach ((array)($in['messages'] ?? []) as $m) {
    $role = ($m['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
    $content = mb_substr(trim((string)($m['content'] ?? '')), 0, 2000);
    if ($content !== '') $messages[] = ['role' => $role, 'content' => $content];
}
$messages = array_slice($messages, -16);
$topic = in_array($in['topic'] ?? '', ['internship', 'rental', 'phishing', 'other'], true) ? $in['topic'] : '';
$story = mb_substr(trim((string)($in['story'] ?? '')), 0, 4000);
$answers = [];
foreach ((array)($in['answers'] ?? []) as $k => $v) {
    if (preg_match('/^[a-z_]+$/', (string)$k) && in_array($v, ['yes', 'no', 'unsure'], true)) $answers[$k] = $v;
}

if ($story === '' && !$messages) {
    http_response_code(422);
    echo json_encode(['error' => 'Describe your situation first.']);
    exit;
}

$reports = bs_load_reports();
$res = null;
if (bs_ai_enabled() && $messages && ($messages[0]['role'] ?? '') === 'user') $res = bs_ask_ai($messages, $lang, $reports, $topic);
if (!$res) $res = bs_ask_rules($story !== '' ? $story : implode("\n", array_column(array_filter($messages, fn($m) => $m['role'] === 'user'), 'content')), $answers, $reports, $topic);
$res['ai_enabled'] = bs_ai_enabled();

echo json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

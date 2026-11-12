<?php
define('BS_AI_CACHE_FILE', __DIR__ . '/../data/ai_cache.json');

function bs_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $file = __DIR__ . '/../config.php';
    $cfg = file_exists($file) ? (array)(include $file) : [];
    $cfg['anthropic_api_key'] = $cfg['anthropic_api_key'] ?? '' ?: (getenv('ANTHROPIC_API_KEY') ?: '');
    $cfg['anthropic_model'] = $cfg['anthropic_model'] ?? '' ?: 'claude-haiku-5-5';
    $cfg['timezone'] = $cfg['timezone'] ?? '' ?: 'Asia/Kuala_Lumpur';
    return $cfg;
}

function bs_ai_enabled(): bool {
    return bs_config()['anthropic_api_key'] !== '';
}

function bs_ai_cache(): array {
    if (!file_exists(BS_AI_CACHE_FILE)) return [];
    $d = json_decode(file_get_contents(BS_AI_CACHE_FILE), true);
    return is_array($d) ? $d : [];
}

function bs_ai_save_cache(array $cache): void {
    if (!is_dir(dirname(BS_AI_CACHE_FILE))) mkdir(dirname(BS_AI_CACHE_FILE), 0775, true);
    file_put_contents(BS_AI_CACHE_FILE, json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function bs_ai_prompt(array $trend, array $members): string {
    $lines = [];
    foreach (array_slice($members, 0, 12) as $i => $r) {
        $lines[] = ($i + 1) . '. [' . $r['channel'] . '] from "' . $r['sender'] . '"' . ($r['url'] ? ' link ' . $r['url'] : '') . ': ' . $r['message'];
    }
    $flags = implode(', ', array_column($trend['flags'], 'label'));
    return "You help university students in Malaysia (Asia Pacific University, APU) avoid scams.\n"
        . "These " . count($members) . " crowdsourced reports were grouped as one scam campaign (category: {$trend['category']}).\n"
        . "Common red flags: $flags.\n\nReports:\n" . implode("\n", $lines) . "\n\n"
        . "Return ONLY a JSON object, no markdown, with this shape:\n"
        . '{"title":{"en":"","ms":"","zh":""},"summary":{"en":"","ms":"","zh":""},"advice":{"en":["","",""],"ms":["","",""],"zh":["","",""]}}' . "\n"
        . "- title: max 12 words, starts with \"Fake\" in English, names the brand impersonated and what is asked for (amount if consistent), e.g. \"Fake Shopee internship asking for an RM50 training fee\".\n"
        . "- summary: 2 short sentences in plain language describing how the scam works and who it targets.\n"
        . "- advice: 3 short, specific actions a student should take.\n"
        . "- ms = Bahasa Malaysia, zh = Simplified Chinese. Keep brand names and RM amounts unchanged.";
}

function bs_ai_chat(array $messages, string $system = '', int $maxTokens = 900): array {
    $cfg = bs_config();
    $key = $cfg['gemini_api_key'] ?? '';
    
    $contents = [];
    foreach ($messages as $m) {
        $contents[] = ['role' => $m['role'] === 'assistant' ? 'model' : 'user', 'parts' => [['text' => $m['content']]]];
    }
    
    $body = ['contents' => $contents, 'generationConfig' => ['maxOutputTokens' => $maxTokens]];
    if ($system !== '') $body['systemInstruction'] = ['parts' => [['text' => $system]]];
    
    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/" . ($cfg['gemini_model'] ?? 'gemini-1.5-flash') . ":generateContent?key=" . $key);
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($body), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25]);
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if (!$raw || $code !== 200) return ['error' => 'AI request failed (HTTP ' . $code . ')'];
    $resp = json_decode($raw, true);
    return ['text' => $resp['candidates'][0]['content']['parts'][0]['text'] ?? ''];
}

function bs_ai_json(string $text): ?array {
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start === false || $end === false) return null;
    $j = json_decode(substr($text, $start, $end - $start + 1), true);
    return is_array($j) ? $j : null;
}

function bs_ai_call(string $prompt): ?array {
    $r = bs_ai_chat([['role' => 'user', 'content' => $prompt]]);
    if (isset($r['error'])) return $r;
    return bs_ai_parse($r['text']);
}

function bs_ai_parse(string $text): ?array {
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start === false || $end === false) return ['error' => 'AI returned no JSON'];
    $j = json_decode(substr($text, $start, $end - $start + 1), true);
    if (!is_array($j) || empty($j['title']['en'])) return ['error' => 'AI returned invalid JSON'];
    $out = [];
    foreach (['en', 'ms', 'zh'] as $l) {
        $out['title'][$l] = trim((string)($j['title'][$l] ?? $j['title']['en']));
        $out['summary'][$l] = trim((string)($j['summary'][$l] ?? ($j['summary']['en'] ?? '')));
        $adv = $j['advice'][$l] ?? ($j['advice']['en'] ?? []);
        $out['advice'][$l] = array_values(array_filter(array_map('strval', is_array($adv) ? $adv : []))) ;
    }
    $out['generated_at'] = date('c');
    return $out;
}

function bs_ai_refresh(array $trends, array $reports, int $limit = 4): array {
    if (!bs_ai_enabled()) return ['enabled' => false, 'updated' => 0, 'errors' => []];
    $cache = bs_ai_cache();
    $byId = [];
    foreach ($reports as $r) $byId[$r['id']] = $r;
    $updated = 0; $errors = [];
    foreach ($trends as $t) {
        if ($t['count'] < 2 || $updated >= $limit) continue;
        $key = $t['id'] . ':' . $t['count'];
        if (isset($cache[$key])) continue;
        $members = array_values(array_filter(array_map(fn($id) => $byId[$id] ?? null, $t['report_ids'])));
        $res = bs_ai_call(bs_ai_prompt($t, $members));
        if (!$res || isset($res['error'])) { $errors[] = $res['error'] ?? 'unknown error'; break; }
        foreach (array_keys($cache) as $k) if (strpos($k, $t['id'] . ':') === 0) unset($cache[$k]);
        $cache[$key] = $res;
        $updated++;
    }
    if ($updated) bs_ai_save_cache($cache);
    return ['enabled' => true, 'updated' => $updated, 'errors' => $errors];
}

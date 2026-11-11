<?php
require_once __DIR__ . '/store.php';

const BS_ASK_QUESTIONS = [
    'internship' => [
        ['key' => 'int_fee',       'risky' => 'yes', 'weight' => 35, 'skip_flag' => 'payment'],
        ['key' => 'int_interview', 'risky' => 'no',  'weight' => 15],
        ['key' => 'int_official',  'risky' => 'no',  'weight' => 15],
        ['key' => 'int_chat',      'risky' => 'yes', 'weight' => 10, 'skip_flag' => 'off_platform'],
        ['key' => 'int_rush',      'risky' => 'yes', 'weight' => 10, 'skip_flag' => 'urgency'],
    ],
    'rental' => [
        ['key' => 'rent_viewed',   'risky' => 'no',  'weight' => 30],
        ['key' => 'rent_pay_first','risky' => 'yes', 'weight' => 30, 'skip_flag' => 'payment'],
        ['key' => 'rent_met',      'risky' => 'no',  'weight' => 15],
        ['key' => 'rent_cheap',    'risky' => 'yes', 'weight' => 15, 'skip_flag' => 'too_good'],
        ['key' => 'rent_rush',     'risky' => 'yes', 'weight' => 10, 'skip_flag' => 'urgency'],
    ],
    'phishing' => [
        ['key' => 'ph_secret',     'risky' => 'yes', 'weight' => 40, 'skip_flag' => 'credentials'],
        ['key' => 'ph_link',       'risky' => 'yes', 'weight' => 20],
        ['key' => 'ph_domain',     'risky' => 'no',  'weight' => 20, 'safe_bonus' => 15, 'skip_flag' => 'lookalike'],
        ['key' => 'ph_threat',     'risky' => 'yes', 'weight' => 15, 'skip_flag' => 'urgency'],
    ],
    'other' => [
        ['key' => 'gen_pay',       'risky' => 'yes', 'weight' => 25, 'skip_flag' => 'payment'],
        ['key' => 'gen_secret',    'risky' => 'yes', 'weight' => 30, 'skip_flag' => 'credentials'],
        ['key' => 'gen_know',      'risky' => 'no',  'weight' => 15],
        ['key' => 'gen_rush',      'risky' => 'yes', 'weight' => 15, 'skip_flag' => 'urgency'],
    ],
];

const BS_ASK_MAX_QUESTIONS = 3;

function bs_ask_extract(string $text): array {
    $url = '';
    $sender = '';
    if (preg_match('#\bhttps?://[^\s<>"\']+#i', $text, $m)) $url = rtrim($m[0], '.,);');
    elseif (preg_match('#\b(?:www\.)?[a-z0-9-]+(?:\.[a-z0-9-]+)*\.(?:com|my|net|org|xyz|top|online|site|live|click|info|icu|buzz|shop|cfd|work|support|ly|me|id|gy|at)(?:/[^\s]*)?#i', $text, $m)) $url = rtrim($m[0], '.,);');
    if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $text, $m)) $sender = $m[0];
    if ($sender !== '' && $url !== '' && stripos($sender, $url) !== false) $url = '';
    return [$sender, $url];
}

function bs_ask_analyse(string $text, array $reports): array {
    [$sender, $url] = bs_ask_extract($text);
    $draft = bs_make_report(['message' => $text, 'sender' => $sender, 'url' => $url]);
    $trends = bs_trends($reports);
    $match = bs_match_trend($draft, $trends);
    if ($match) {
        foreach ($trends as $t) if ($t['id'] === $match['id']) { $match['parts'] = $t['parts']; $match['this_week'] = $t['this_week']; }
    }
    return ['draft' => $draft, 'match' => $match, 'sender' => $sender, 'url' => $url];
}

function bs_ask_rules(string $story, array $answers, array $reports, string $topic = ''): array {
    $a = bs_ask_analyse($story, $reports);
    $d = $a['draft'];
    $cat = isset(BS_ASK_QUESTIONS[$topic]) ? $topic : (isset(BS_ASK_QUESTIONS[$d['category']]) ? $d['category'] : 'other');
    $flagKeys = array_column($d['flags'], 'key');

    $base = ['mode' => 'rules', 'analysis' => ['category' => $cat, 'risk' => $d['risk'], 'flags' => $d['flags'], 'domain' => $d['domain']], 'trend' => $a['match']];

    if (mb_strlen(trim($story)) < 20 && !$answers) {
        return $base + ['type' => 'more'];
    }

    $implied = [];
    foreach (BS_ASK_QUESTIONS[$cat] as $q) {
        if (isset($q['skip_flag']) && in_array($q['skip_flag'], $flagKeys, true) && !array_key_exists($q['key'], $answers)) $implied[$q['key']] = $q['key'] === 'ph_domain' ? 'no' : $q['risky'];
    }
    $asked = count(array_intersect_key($answers, array_column(BS_ASK_QUESTIONS[$cat], null, 'key')));
    foreach (BS_ASK_QUESTIONS[$cat] as $q) {
        if (isset($implied[$q['key']]) || array_key_exists($q['key'], $answers)) continue;
        if ($asked >= BS_ASK_MAX_QUESTIONS) break;
        return $base + ['type' => 'question', 'question' => ['key' => $q['key'], 'options' => ['yes', 'no', 'unsure']], 'step' => $asked + 1, 'total' => min(BS_ASK_MAX_QUESTIONS, $asked + count(array_filter(BS_ASK_QUESTIONS[$cat], fn($x) => !isset($implied[$x['key']]) && !array_key_exists($x['key'], $answers))))];
    }
    $answers = $implied + $answers;

    $score = (int)round($d['risk'] * 0.7);
    $reasons = [];
    foreach ($d['flags'] as $f) $reasons[] = ['type' => 'flag', 'label' => $f['label'], 'evidence' => $f['evidence']];
    foreach (BS_ASK_QUESTIONS[$cat] as $q) {
        $ans = $answers[$q['key']] ?? null;
        if ($ans === null) continue;
        if ($ans === $q['risky']) { $score += $q['weight']; if (!isset($implied[$q['key']])) $reasons[] = ['type' => 'answer', 'key' => $q['key']]; }
        elseif ($ans === 'unsure') { $score += (int)round($q['weight'] / 3); $reasons[] = ['type' => 'unsure', 'key' => $q['key']]; }
        elseif (!empty($q['safe_bonus'])) { $score -= $q['safe_bonus']; }
    }
    if ($a['match']) { $score += 15; $reasons[] = ['type' => 'trend']; }
    $score = max(3, min(99, $score));
    $level = $score >= 60 ? 'scam' : ($score >= 30 ? 'suspicious' : 'safe');

    return $base + ['type' => 'verdict', 'verdict' => ['level' => $level, 'score' => $score, 'reasons' => $reasons, 'category' => $cat]];
}

function bs_ask_ai(array $messages, string $lang, array $reports, string $topic = ''): ?array {
    $story = '';
    foreach ($messages as $m) if ($m['role'] === 'user') $story .= $m['content'] . "\n";
    $a = bs_ask_analyse($story, $reports);
    $d = $a['draft'];
    $langName = ['en' => 'English', 'ms' => 'Bahasa Melayu', 'zh' => 'Simplified Chinese'][$lang] ?? 'English';
    $userTurns = count(array_filter($messages, fn($m) => $m['role'] === 'user'));
    $flags = implode('; ', array_map(fn($f) => $f['label'] . ' (' . $f['evidence'] . ')', $d['flags'])) ?: 'none';
    $trend = $a['match'] ? "It closely matches a scam trend already reported by {$a['match']['count']} APU students: \"{$a['match']['title']}\"." : 'It does not match any known campus scam trend yet.';

    $system = "You are ByeScam's \"Ask before you pay\" assistant for university students at Asia Pacific University (APU), Malaysia. "
        . "A student describes a situation (job/internship offer, room rental, a message asking them to log in, pay or share details). "
        . "Your job: ask short follow-up questions ONE AT A TIME (max 3 in total) to understand the risk, then give a clear verdict.\n\n"
        . ($topic ? "The student says this is about: $topic.\n" : '')
        . "ByeScam's automatic analysis of what the student wrote so far: category={$d['category']}, risk score={$d['risk']}/100, red flags: $flags. $trend\n\n"
        . "Rules:\n- Reply ONLY with a JSON object, no markdown.\n"
        . "- To ask a question: {\"type\":\"question\",\"text\":\"<one short question>\",\"options\":[\"<2-4 short tappable answers>\"]}\n"
        . "- To give the verdict: {\"type\":\"verdict\",\"level\":\"scam|suspicious|safe\",\"score\":<0-100 likelihood it is a scam>,\"title\":\"<max 10 words>\",\"summary\":\"<2 sentences, plain language>\",\"reasons\":[\"<2-4 short reasons>\"],\"steps\":[\"<2-4 concrete next steps>\"]}\n"
        . "- Give the verdict as soon as you are confident, and always by the student's 4th message.\n"
        . "- Never ask for passwords, OTPs, IC or bank numbers. Never tell them to pay. If they already paid, tell them to call their bank and the NSRC hotline 997 immediately.\n"
        . "- Real companies never charge students to work; never pay a rental deposit before viewing in person; APU only uses *.apu.edu.my.\n"
        . "- Write everything in $langName. Be kind and brief; students may be stressed.";

    if ($userTurns >= 4) $system .= "\n- The student has now answered enough. Give the verdict now.";

    $r = bs_ai_chat(array_map(fn($m) => ['role' => $m['role'], 'content' => (string)$m['content']], $messages), $system, 700);
    if (isset($r['error'])) return null;
    $j = bs_ai_json($r['text']);
    if (!$j || !in_array($j['type'] ?? '', ['question', 'verdict'], true)) return null;

    $out = ['mode' => 'ai', 'analysis' => ['category' => $d['category'], 'risk' => $d['risk'], 'flags' => $d['flags'], 'domain' => $d['domain']], 'trend' => $a['match']];
    if ($j['type'] === 'question') {
        $out['type'] = 'question';
        $out['question'] = ['text' => (string)($j['text'] ?? ''), 'options' => array_slice(array_values(array_map('strval', (array)($j['options'] ?? []))), 0, 4)];
        return $out;
    }
    $level = in_array($j['level'] ?? '', ['scam', 'suspicious', 'safe'], true) ? $j['level'] : 'suspicious';
    $out['type'] = 'verdict';
    $out['verdict'] = [
        'level' => $level, 'score' => max(0, min(100, (int)($j['score'] ?? 50))), 'category' => $d['category'],
        'title' => (string)($j['title'] ?? ''), 'summary' => (string)($j['summary'] ?? ''),
        'reasons' => array_map(fn($x) => ['type' => 'text', 'text' => (string)$x], array_slice((array)($j['reasons'] ?? []), 0, 5)),
        'steps' => array_slice(array_values(array_map('strval', (array)($j['steps'] ?? []))), 0, 5),
    ];
    return $out;
}

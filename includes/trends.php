<?php
require_once __DIR__ . '/analyzer.php';

const BS_MATCH_THRESHOLD = 0.5;

function bs_jaccard(array $a, array $b): float {
    if (!$a || !$b) return 0.0;
    $inter = count(array_intersect($a, $b));
    $union = count(array_unique(array_merge($a, $b)));
    return $union ? $inter / $union : 0.0;
}

function bs_mode(array $values) {
    $values = array_filter($values, fn($v) => $v !== null && $v !== '');
    if (!$values) return null;
    $c = array_count_values(array_map('strval', $values));
    arsort($c);
    return key($c);
}

function bs_cluster_profile(array $members): array {
    $n = count($members);
    $tokCount = []; $flagCount = [];
    foreach ($members as $r) {
        foreach ($r['features']['tokens'] as $t) $tokCount[$t] = ($tokCount[$t] ?? 0) + 1;
        foreach (array_column($r['flags'], 'key') as $f) $flagCount[$f] = ($flagCount[$f] ?? 0) + 1;
    }
    $min = max(1, (int)ceil($n * 0.34));
    return [
        'category'    => $members[0]['category'],
        'brand'       => bs_mode(array_map(fn($r) => $r['features']['brand'], $members)),
        'ask'         => bs_mode(array_map(fn($r) => $r['features']['ask'], $members)),
        'ask_payment' => (bool)bs_mode(array_map(fn($r) => $r['features']['ask_payment'] ? '1' : '0', $members)),
        'tokens'      => array_keys(array_filter($tokCount, fn($c) => $c >= $min)),
        'flags'       => array_keys(array_filter($flagCount, fn($c) => $c >= $min)),
    ];
}

function bs_similarity(array $report, array $profile): float {
    $f = $report['features'];
    if ($report['category'] !== $profile['category']) return 0.0;
    $s = 0.0;
    if ($f['brand'] && $profile['brand']) $s += $f['brand'] === $profile['brand'] ? 0.45 : -0.30;
    if ($f['ask'] && $f['ask'] === $profile['ask']) $s += 0.25;
    elseif ($f['ask_payment'] && $profile['ask_payment']) $s += 0.15;
    $s += 0.30 * bs_jaccard(array_column($report['flags'], 'key'), $profile['flags']);
    $s += 0.35 * bs_jaccard($f['tokens'], $profile['tokens']);
    return round(min(1.0, $s), 3);
}

function bs_cluster(array $reports): array {
    usort($reports, fn($a, $b) => strcmp($a['created_at'], $b['created_at']));
    $clusters = [];
    foreach ($reports as $r) {
        if (!isset($r['features'])) $r['features'] = bs_features($r['message'], $r['sender'], $r['url']);
        $best = -1; $bestScore = 0;
        foreach ($clusters as $i => $c) {
            $score = bs_similarity($r, $c['profile']);
            if ($score > $bestScore) { $bestScore = $score; $best = $i; }
        }
        if ($best >= 0 && $bestScore >= BS_MATCH_THRESHOLD) {
            $clusters[$best]['members'][] = $r;
            $clusters[$best]['profile'] = bs_cluster_profile($clusters[$best]['members']);
        } else {
            $clusters[] = ['members' => [$r], 'profile' => bs_cluster_profile([$r])];
        }
    }
    return $clusters;
}

function bs_match_trend(array $report, array $trends): ?array {
    $best = null; $bestScore = 0;
    foreach ($trends as $t) {
        $score = bs_similarity($report, $t['profile']);
        if ($score > $bestScore) { $bestScore = $score; $best = $t; }
    }
    if (!$best || $bestScore < BS_MATCH_THRESHOLD) return null;
    return ['id' => $best['id'], 'title' => $best['title'], 'count' => $best['count'], 'score' => $bestScore];
}

function bs_article(string $next): string {
    return preg_match('/^(rm|[aeiou])/i', $next) ? 'an' : 'a';
}

function bs_amount_text(array $amounts): ?string {
    $amounts = array_values(array_filter($amounts, fn($a) => $a !== null));
    if (!$amounts) return null;
    $fmt = fn($v) => 'RM' . (floor((float)$v) == (float)$v ? (int)$v : number_format((float)$v, 2));
    $mode = bs_mode($amounts);
    $share = count(array_filter($amounts, fn($a) => (string)$a === (string)$mode)) / count($amounts);
    if ($share >= 0.6) return $fmt($mode);
    return $fmt(min($amounts)) . '–' . $fmt(max($amounts));
}

function bs_trend_title(array $members, array $profile): string {
    $brandName = bs_mode(array_map(fn($r) => $r['features']['brand_name'], $members));
    $askLabel  = bs_mode(array_map(fn($r) => $r['features']['ask_label'], $members));
    $amount    = bs_amount_text(array_map(fn($r) => $r['features']['ask'] === $profile['ask'] ? $r['features']['amount'] : null, $members));
    $tokens    = $profile['tokens'];
    $flags     = $profile['flags'];

    $askPhrase = '';
    if ($askLabel) {
        $obj = $amount ? "$amount $askLabel" : $askLabel;
        $askPhrase = $profile['ask_payment'] ? ' asking for ' . bs_article($obj) . ' ' . $obj : " asking for your $askLabel";
    }

    switch ($profile['category']) {
        case 'internship':
            if (in_array('like', $tokens) && in_array('follow', $tokens)) $what = ($brandName ? "$brandName " : '') . '"like & follow" job';
            elseif ($brandName) $what = "$brandName internship";
            elseif (in_array('crypto', $tokens) || in_array('usdt', $tokens)) $what = 'crypto trading internship';
            elseif (in_array('internship', $tokens)) $what = 'online internship';
            else $what = 'part-time job';
            return "Fake $what$askPhrase";

        case 'rental':
            $apu = count(array_filter($members, fn($r) => preg_match('/\bapu\b/i', $r['message'])));
            $near = $apu >= count($members) / 2 ? ' near APU' : '';
            $tail = in_array('no_viewing', $flags) ? ' before viewing' : '';
            return "Fake room rental$near$askPhrase$tail";

        case 'phishing':
            $b = $brandName ?: 'official-looking';
            switch ($profile['ask']) {
                case 'password': return "Fake $b login page stealing passwords";
                case 'otp':      return "Fake $b security alert asking for your OTP/TAC";
                case 'bank_ic':  return "Fake $b message asking for bank & IC details";
                default:         return "Fake $b message$askPhrase";
            }

        default:
            return 'Suspicious message' . $askPhrase;
    }
}

function bs_trends(array $reports): array {
    $now = time(); $day = 86400;
    $out = [];
    foreach (bs_cluster($reports) as $c) {
        $m = $c['members'];
        usort($m, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        $p = $c['profile'];

        $week = 0; $prevWeek = 0; $spark = array_fill(0, 14, 0);
        $domains = []; $channels = []; $flagCount = []; $flagLabels = []; $riskSum = 0;
        foreach ($m as $r) {
            $age = $now - strtotime($r['created_at']);
            if ($age <= 7 * $day) $week++; elseif ($age <= 14 * $day) $prevWeek++;
            $d = (int)floor($age / $day);
            if ($d < 14) $spark[13 - $d]++;
            if ($r['domain']) $domains[$r['domain']] = true;
            $channels[$r['channel']] = ($channels[$r['channel']] ?? 0) + 1;
            foreach ($r['flags'] as $f) { $flagCount[$f['key']] = ($flagCount[$f['key']] ?? 0) + 1; $flagLabels[$f['key']] = $f['label']; }
            $riskSum += $r['risk'];
        }
        arsort($flagCount); arsort($channels);

        $first = strtotime(end($m)['created_at']);
        $last  = strtotime($m[0]['created_at']);
        if ($now - $first <= 3 * $day && count($m) >= 2) $status = 'new';
        elseif ($week > $prevWeek) $status = 'rising';
        elseif ($now - $last > 4 * $day) $status = 'fading';
        else $status = 'steady';

        $title = bs_trend_title($m, $p);
        $avgRisk = (int)round($riskSum / count($m));
        $out[] = [
            'id'         => substr(md5($p['category'] . '|' . $title), 0, 10),
            'title'      => $title,
            'headline'   => $week . ' report' . ($week === 1 ? '' : 's') . " this week: $title",
            'category'   => $p['category'],
            'brand'      => bs_mode(array_map(fn($r) => $r['features']['brand_name'], $m)),
            'count'      => count($m),
            'this_week'  => $week,
            'last_week'  => $prevWeek,
            'status'     => $status,
            'risk'       => $avgRisk,
            'level'      => $avgRisk >= 60 ? 'high' : ($avgRisk >= 30 ? 'medium' : 'low'),
            'flags'      => array_map(fn($k) => ['key' => $k, 'label' => $flagLabels[$k], 'count' => $flagCount[$k]], array_slice(array_keys($flagCount), 0, 4)),
            'domains'    => array_slice(array_keys($domains), 0, 5),
            'channels'   => array_slice(array_keys($channels), 0, 3),
            'spark'      => $spark,
            'first_seen' => date('c', $first),
            'last_seen'  => date('c', $last),
            'report_ids' => array_column($m, 'id'),
            'profile'    => $p,
        ];
    }

    usort($out, fn($a, $b) => [$b['this_week'], $b['count'], $b['risk']] <=> [$a['this_week'], $a['count'], $a['risk']]);
    return $out;
}

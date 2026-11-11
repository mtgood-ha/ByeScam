<?php
require_once __DIR__ . '/analyzer.php';

const BS_ZONES = [
    'block-j'       => ['name' => 'On-Campus Accommodation (Block J)', 'short' => 'Block J', 'x' => 23.5, 'y' => 31.8, 'ap' => 'BLKJ', 'aps' => 6],
    'block-e'       => ['name' => 'Block E',                            'short' => 'Block E', 'x' => 33.6, 'y' => 45.3, 'ap' => 'BLKE', 'aps' => 8],
    'block-d'       => ['name' => 'Block D',                            'short' => 'Block D', 'x' => 44.6, 'y' => 52.4, 'ap' => 'BLKD', 'aps' => 8],
    'block-b'       => ['name' => 'Block B',                            'short' => 'Block B', 'x' => 55.9, 'y' => 61.4, 'ap' => 'BLKB', 'aps' => 6],
    'spine'         => ['name' => 'The Spine',                          'short' => 'Spine',   'x' => 62.2, 'y' => 33.7, 'ap' => 'SPN',  'aps' => 10],
    'main-entrance' => ['name' => 'Main Entrance',                      'short' => 'Entrance','x' => 79.8, 'y' => 65.3, 'ap' => 'ENT',  'aps' => 3],
    'parking'       => ['name' => 'Open Space Parking',                 'short' => 'Parking', 'x' => 65.7, 'y' => 89.2, 'ap' => 'PRK',  'aps' => 2],
];

const BS_ZONE_AFFINITY = [
    'internship' => ['block-e' => 4, 'block-b' => 4, 'block-d' => 3, 'spine' => 3, 'block-j' => 2, 'main-entrance' => 1, 'parking' => 1],
    'rental'     => ['block-j' => 8, 'spine' => 2, 'block-e' => 1, 'block-b' => 1, 'block-d' => 1, 'main-entrance' => 1, 'parking' => 1],
    'phishing'   => ['block-d' => 4, 'block-b' => 4, 'spine' => 4, 'block-e' => 3, 'block-j' => 3, 'main-entrance' => 1, 'parking' => 1],
    'other'      => ['spine' => 3, 'block-j' => 3, 'block-b' => 2, 'block-d' => 2, 'block-e' => 2, 'main-entrance' => 1, 'parking' => 1],
];

const BS_OFFICIAL = [
    ['name' => 'Asia Pacific University (APU)', 'type' => 'University', 'domains' => ['apu.edu.my', 'apiit.edu.my'],
     'note' => 'APSpace, Moodle and staff/student email only live on *.apu.edu.my addresses.'],
    ['name' => 'Maybank / Maybank2u', 'type' => 'Bank', 'domains' => ['maybank2u.com.my', 'maybank.com', 'maybank.com.my'],
     'note' => 'Maybank never asks for your TAC or password through links.'],
    ['name' => 'CIMB', 'type' => 'Bank', 'domains' => ['cimbclicks.com.my', 'cimb.com.my', 'cimb.com'], 'note' => ''],
    ['name' => 'PTPTN', 'type' => 'Government', 'domains' => ['ptptn.gov.my'], 'note' => 'Government sites end in .gov.my.'],
    ['name' => "Touch 'n Go eWallet", 'type' => 'E-wallet', 'domains' => ['tngdigital.com.my', 'touchngo.com.my'], 'note' => ''],
    ['name' => 'Pos Malaysia (Pos Laju)', 'type' => 'Courier', 'domains' => ['pos.com.my'], 'note' => ''],
    ['name' => 'Shopee Malaysia', 'type' => 'Company', 'domains' => ['shopee.com.my', 'shopee.com'], 'note' => 'Shopee careers are listed on its official site, never paid.'],
    ['name' => 'JobStreet', 'type' => 'Job portal', 'domains' => ['jobstreet.com.my', 'jobstreet.com'], 'note' => ''],
    ['name' => 'TikTok', 'type' => 'Company', 'domains' => ['tiktok.com'], 'note' => ''],
    ['name' => 'Microsoft 365', 'type' => 'Company', 'domains' => ['microsoft.com', 'office.com', 'microsoftonline.com'], 'note' => ''],
];

const BS_HOTLINES = [
    ['name' => 'National Scam Response Centre (NSRC)', 'number' => '997', 'note' => 'Call immediately if you already transferred money.'],
];

function bs_valid_zone(string $z): string {
    return isset(BS_ZONES[$z]) ? $z : '';
}

function bs_fallback_zone(string $id): string {
    $keys = array_keys(BS_ZONES);
    return $keys[crc32($id) % count($keys)];
}

function bs_zones_public(): array {
    $out = [];
    foreach (BS_ZONES as $id => $z) $out[] = ['id' => $id, 'name' => $z['name'], 'short' => $z['short'], 'x' => $z['x'], 'y' => $z['y']];
    return $out;
}

function bs_weighted_pick(array $weights) {
    $sum = array_sum($weights);
    if ($sum <= 0) return array_key_first($weights);
    $r = mt_rand(1, (int)($sum * 1000)) / 1000;
    foreach ($weights as $k => $w) { $r -= $w; if ($r <= 0) return $k; }
    return array_key_last($weights);
}

function bs_blocklist(array $reports): array {
    $list = [];
    foreach ($reports as $r) {
        $d = $r['domain'] ?? '';
        if ($d === '' || $r['risk'] < 60 || in_array($d, SHORTENERS, true) || in_array($d, FREE_MAIL, true) || in_array($d, CHAT_HOSTS, true)) continue;
        if (!isset($list[$d])) $list[$d] = ['domain' => $d, 'category' => $r['category'], 'reports' => 0, 'added_at' => $r['created_at'], 'risk' => 0];
        $list[$d]['reports']++;
        $list[$d]['risk'] = max($list[$d]['risk'], $r['risk']);
        if ($r['created_at'] < $list[$d]['added_at']) $list[$d]['added_at'] = $r['created_at'];
    }
    return $list;
}

function bs_network(array $reports): array {
    $now = time();
    $bucket = 120;
    $blocklist = bs_blocklist($reports);
    $startToday = strtotime('today', $now);
    $startWeek = $now - 7 * 86400;

    $zoneWeek = array_fill_keys(array_keys(BS_ZONES), 0);
    $zoneToday = array_fill_keys(array_keys(BS_ZONES), 0);
    $hourly = array_fill(0, 24, 0);
    $domainToday = []; $domainWeek = []; $zoneDomains = array_fill_keys(array_keys(BS_ZONES), []);
    $devicesToday = []; $blockedToday = 0; $blockedWeek = 0;
    $recent = [];

    $added = [];
    foreach ($blocklist as $d => $b) $added[$d] = strtotime($b['added_at']);

    for ($t = intdiv($startWeek, $bucket) * $bucket; $t <= $now; $t += $bucket) {
        $active = [];
        foreach ($blocklist as $d => $b) {
            if ($added[$d] > $t) continue;
            $ageDays = ($t - $added[$d]) / 86400;
            $active[$d] = max(0.4, 3 - $ageDays * 0.25) * ($b['reports'] + 1);
        }
        if (!$active) continue;
        mt_srand(crc32('byescam-net-' . $t));
        $hour = (int)date('G', $t);
        $busy = ($hour >= 8 && $hour <= 18) ? 1.0 : (($hour >= 19 && $hour <= 23) ? 0.6 : 0.15);
        $n = 0;
        if (mt_rand(1, 100) <= 30 * $busy) $n++;
        if (mt_rand(1, 100) <= 9 * $busy) $n++;
        for ($i = 0; $i < $n; $i++) {
            $ts = $t + mt_rand(0, $bucket - 1);
            if ($ts > $now) continue;
            $domain = bs_weighted_pick($active);
            $cat = $blocklist[$domain]['category'];
            $zone = bs_weighted_pick(BS_ZONE_AFFINITY[$cat] ?? BS_ZONE_AFFINITY['other']);
            $z = BS_ZONES[$zone];
            $device = substr(md5('dev' . mt_rand(1, 900) . $zone), 0, 6);
            $blockedWeek++;
            $zoneWeek[$zone]++;
            $domainWeek[$domain] = ($domainWeek[$domain] ?? 0) + 1;
            $zoneDomains[$zone][$domain] = ($zoneDomains[$zone][$domain] ?? 0) + 1;
            if ($ts >= $startToday) {
                $blockedToday++;
                $zoneToday[$zone]++;
                $hourly[(int)date('G', $ts)]++;
                $devicesToday[$device] = true;
                $domainToday[$domain] = ($domainToday[$domain] ?? 0) + 1;
            }
            $recent[] = ['time' => date('c', $ts), 'zone' => $zone, 'ap' => sprintf('%s-AP%02d', $z['ap'], mt_rand(1, $z['aps'])),
                         'device' => 'dev-' . $device, 'domain' => $domain, 'category' => $cat, 'action' => 'blocked'];
            if (count($recent) > 40) array_shift($recent);
        }
    }
    mt_srand();

    arsort($domainToday);
    foreach ($zoneDomains as &$zd) { arsort($zd); $zd = array_slice($zd, 0, 4, true); }
    unset($zd);
    $list = array_values($blocklist);
    foreach ($list as &$b) $b['blocked_week'] = $domainWeek[$b['domain']] ?? 0;
    unset($b);
    usort($list, fn($a, $b) => [$b['blocked_week'], $b['risk']] <=> [$a['blocked_week'], $a['risk']]);

    return [
        'status'         => ['filter' => 'active', 'resolver' => 'campus-dns-01', 'last_sync' => date('c', $now - ($now % 300)), 'blocklist_size' => count($list), 'access_points' => array_sum(array_column(BS_ZONES, 'aps'))],
        'blocked_today'  => $blockedToday,
        'blocked_week'   => $blockedWeek,
        'devices_today'  => count($devicesToday),
        'hourly'         => $hourly,
        'zones_today'    => $zoneToday,
        'zones_week'     => $zoneWeek,
        'zone_domains'   => $zoneDomains,
        'top_today'      => array_slice(array_map(fn($d, $c) => ['domain' => $d, 'count' => $c, 'category' => $blocklist[$d]['category']], array_keys($domainToday), $domainToday), 0, 5),
        'recent'         => (function () use ($recent) { usort($recent, fn($a, $b) => strcmp($b['time'], $a['time'])); return array_slice($recent, 0, 15); })(),
        'blocklist'      => $list,
    ];
}

function bs_registrable(string $host): string {
    $parts = explode('.', $host);
    $n = count($parts);
    if ($n >= 3 && in_array($parts[$n - 2], ['com', 'edu', 'gov', 'org', 'net'], true) && strlen($parts[$n - 1]) === 2) {
        return implode('.', array_slice($parts, -3));
    }
    return implode('.', array_slice($parts, -2));
}

function bs_verify(string $q, array $reports): array {
    $q = trim($q);
    $digits = preg_replace('/\D/', '', $q);
    $res = ['query' => $q, 'kind' => 'domain', 'host' => '', 'verdict' => 'unknown', 'entity' => null, 'similar_to' => null,
            'reports' => 0, 'trend_ids' => [], 'flags' => []];

    if ($q === '') return $res;

    if ($digits !== '' && strlen($digits) >= 3 && !preg_match('/[a-z]{2,}\./i', $q)) {
        $res['kind'] = 'phone';
        foreach (BS_HOTLINES as $h) {
            if ($digits === $h['number']) { $res['verdict'] = 'official'; $res['entity'] = ['name' => $h['name'], 'type' => 'Hotline', 'note' => $h['note']]; return $res; }
        }
        $tail = substr($digits, -8);
        foreach ($reports as $r) {
            $hay = preg_replace('/\D/', '', $r['sender'] . ' ' . $r['message']);
            if (strlen($tail) >= 7 && strpos($hay, $tail) !== false) { $res['reports']++; if (!empty($r['trend']['id'])) $res['trend_ids'][] = $r['trend']['id']; }
        }
        $res['verdict'] = $res['reports'] ? 'reported' : 'unknown';
        $res['trend_ids'] = array_values(array_unique($res['trend_ids']));
        return $res;
    }

    $host = bs_host($q);
    if ($host === '') { $res['verdict'] = 'invalid'; return $res; }
    $res['host'] = $host;
    $res['kind'] = strpos($q, '@') !== false ? 'email' : (preg_match('#^https?://#i', $q) ? 'link' : 'domain');

    foreach (BS_OFFICIAL as $e) {
        foreach ($e['domains'] as $d) {
            if (bs_ends_with($host, $d)) {
                $res['verdict'] = 'official';
                $res['entity'] = ['name' => $e['name'], 'type' => $e['type'], 'note' => $e['note'], 'domain' => $d];
                return $res;
            }
        }
    }

    foreach ($reports as $r) {
        $match = ($r['domain'] ?? '') === $host || stripos($r['sender'] . ' ' . $r['url'], $host) !== false;
        if ($match) { $res['reports']++; if (!empty($r['trend']['id'])) $res['trend_ids'][] = $r['trend']['id']; }
    }
    $res['trend_ids'] = array_values(array_unique($res['trend_ids']));

    $best = null; $bestDist = PHP_INT_MAX;
    foreach (BS_OFFICIAL as $e) {
        foreach ($e['domains'] as $d) {
            $dl = explode('.', $d)[0];
            $dist = levenshtein(bs_registrable($host), $d);
            $hostBody = substr($host, 0, (int)strrpos($host, '.'));
            $contains = (strlen($dl) >= 4 || $dl === 'apu') && (strpos($hostBody, $dl) !== false || strpos($hostBody, str_replace('2u', '', $dl)) !== false);
            if ($contains) $dist = min($dist, 1);
            if ($dist < $bestDist) { $bestDist = $dist; $best = ['name' => $e['name'], 'domain' => $d]; }
        }
    }
    if ($best && $bestDist <= 2) $res['similar_to'] = $best;

    $a = bs_analyze('', $host, $res['kind'] === 'link' ? $q : '');
    $res['flags'] = $a['flags'];

    if ($res['similar_to']) $res['verdict'] = 'lookalike';
    elseif ($res['reports']) $res['verdict'] = 'reported';
    elseif ($a['flags']) $res['verdict'] = 'suspicious';
    else $res['verdict'] = 'unknown';
    return $res;
}

function bs_directory(): array {
    return ['official' => BS_OFFICIAL, 'hotlines' => BS_HOTLINES];
}

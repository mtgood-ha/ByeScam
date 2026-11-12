<?php
require_once __DIR__ . '/campus.php';

define('BS_FREEZE_FILE', __DIR__ . '/../data/freezes.json');

const BS_FREEZE_DIRECTORY = [
    ['id' => 'nsrc', 'name' => 'National Scam Response Centre (NSRC)', 'type' => 'national', 'display' => '997', 'tel' => '997', 'hours' => '24/7',
     'steps' => ['Call 997 and say you were scammed', 'Give the time, amount and the account you paid', 'This call also counts as your police report']],
    ['id' => 'maybank', 'name' => 'Maybank (MAE / Maybank2u)', 'type' => 'bank', 'display' => '03-5891 4744', 'tel' => '+60358914744', 'hours' => '24/7',
     'steps' => ['MAE app', 'More', 'Kill Switch', 'Deactivate M2U Access']],
    ['id' => 'cimb', 'name' => 'CIMB (OCTO)', 'type' => 'bank', 'display' => '03-6204 7788', 'tel' => '+60362047788', 'hours' => '24/7',
     'steps' => ['CIMB OCTO app', 'Services', 'Clicks Profile', 'Lock Clicks ID', 'Proceed']],
    ['id' => 'pbb', 'name' => 'Public Bank (MyPB)', 'type' => 'bank', 'display' => '03-2177 3555', 'tel' => '+60321773555', 'hours' => '24/7',
     'steps' => ['MyPB app', 'Kill Switch icon on the home page', 'Kill Switch']],
    ['id' => 'rhb', 'name' => 'RHB', 'type' => 'bank', 'display' => '03-9206 8118', 'tel' => '+60392068118', 'hours' => '24/7',
     'steps' => ['RHB Mobile Banking app', 'Lock Account icon', 'Lock Account Now']],
    ['id' => 'hlb', 'name' => 'Hong Leong Bank (HLB Connect)', 'type' => 'bank', 'display' => '03-7626 8899', 'tel' => '+60376268899', 'hours' => '24/7',
     'steps' => ['HLB Connect app', 'Emergency Lock']],
    ['id' => 'bimb', 'name' => 'Bank Islam (BIMB Mobile)', 'type' => 'bank', 'display' => '03-2690 0900', 'tel' => '+60326900900', 'hours' => '24/7',
     'steps' => ['BIMB Mobile app', 'Kill Switch', 'Start Kill Switch', 'Verify with your ID']],
    ['id' => 'tng', 'name' => "Touch 'n Go eWallet", 'type' => 'ewallet', 'display' => '03-5022 3888 (press *)', 'tel' => '+60350223888', 'hours' => '',
     'steps' => ['TNG eWallet app', 'Kill Switch']],
    ['id' => 'apu', 'name' => 'APU IT Helpdesk', 'type' => 'campus', 'display' => '03-8992 5050', 'tel' => '+60389925050', 'hours' => 'Mon–Fri 8:30am–6pm',
     'email' => 'assist@apu.edu.my', 'place' => 'TechCentre, Room 10, Level 6, Spine',
     'steps' => ['Change your APKey password', 'Email assist@apu.edu.my from your APU email', 'Or visit TechCentre (Spine, Level 6)']],
];

const BS_FREEZE_TYPES = ['apspace', 'email', 'moodle', 'wifi', 'other'];

function bs_freeze_anon(string $id): string {
    return strtoupper(substr($id, 0, 4)) . '****';
}

function bs_freeze_seed(): array {
    $now = time();
    $rows = [
        [2,    'TP06', 'apspace', 'apspace-verify.xyz',     'block-d'],
        [38,   'TP07', 'email',   'office365-apu.cfd',      'block-b'],
        [95,   'TP08', 'moodle',  'apu-moodle.site',        'block-e'],
        [210,  'TP07', 'wifi',    'apu-itservices.online',  'spine'],
        [480,  'TP06', 'apspace', 'apspace-results.top',    'block-j'],
        [900,  'TP08', 'email',   '',                       'block-b'],
        [1650, 'TP07', 'apspace', 'apu-support.xyz',        'block-d'],
        [2700, 'TP06', 'other',   'maybank2u-secure.top',   'block-j'],
    ];
    $out = [];
    foreach ($rows as [$min, $tp, $type, $domain, $zone]) {
        $out[] = ['id' => substr(md5($tp . $min . $domain), 0, 10), 'anon' => $tp . '****', 'type' => $type, 'domain' => $domain,
                  'location' => $zone, 'ref' => 'BS-FRZ-' . (1000 + (crc32($tp . $min) % 9000)),
                  'created_at' => date('c', $now - $min * 60 - 17), 'seed' => true];
    }
    return $out;
}

function bs_freeze_load(): array {
    if (!file_exists(BS_FREEZE_FILE)) {
        $seed = bs_freeze_seed();
        bs_freeze_save($seed);
        return $seed;
    }
    $d = json_decode(file_get_contents(BS_FREEZE_FILE), true);
    return is_array($d) ? $d : [];
}

function bs_freeze_save(array $rows): void {
    if (!is_dir(dirname(BS_FREEZE_FILE))) mkdir(dirname(BS_FREEZE_FILE), 0775, true);
    usort($rows, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
    file_put_contents(BS_FREEZE_FILE, json_encode(array_slice(array_values($rows), 0, 200), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function bs_freeze_is_official(string $host): bool {
    foreach (BS_OFFICIAL as $e) foreach ($e['domains'] as $d) if (bs_ends_with($host, $d)) return true;
    return false;
}

function bs_freeze_domain(string $input): string {
    $host = bs_host($input);
    if ($host === '' || bs_freeze_is_official($host) || in_array($host, SHORTENERS, true)) return '';
    $isEmail = strpos($input, '@') !== false;
    return bs_threat_domain($isEmail ? $host : '', $isEmail ? '' : $host);
}

function bs_freeze_blocklist_domains(): array {
    $out = [];
    foreach (bs_freeze_load() as $f) {
        if (!empty($f['domain']) && !isset($out[$f['domain']])) $out[$f['domain']] = $f['created_at'];
    }
    return $out;
}

function bs_freeze_submit(array $in): array {
    $id = strtoupper(preg_replace('/\s+/', '', (string)($in['tp_number'] ?? '')));
    if (!preg_match('/^TP\d{6}$/', $id) && !preg_match('/^[A-Z]{2,4}\d{5,8}$/', $id)) {
        return ['error' => 'invalid_tp'];
    }
    $type = in_array($in['incident_type'] ?? '', BS_FREEZE_TYPES, true) ? $in['incident_type'] : 'other';
    $raw = mb_substr(trim((string)($in['url_or_sender'] ?? '')), 0, 300);
    $domain = $raw !== '' ? bs_freeze_domain($raw) : '';
    $official = $raw !== '' && $domain === '' && bs_host($raw) !== '' && bs_freeze_is_official(bs_host($raw));
    $zone = bs_valid_zone((string)($in['location'] ?? ''));
    $now = time();
    $ref = 'BS-FRZ-' . random_int(1000, 9999);

    $event = ['id' => bin2hex(random_bytes(5)), 'anon' => bs_freeze_anon($id), 'type' => $type, 'domain' => $domain,
              'location' => $zone, 'ref' => $ref, 'created_at' => date('c', $now)];
    $rows = bs_freeze_load();
    $alreadyBlocked = $domain !== '' && (isset(bs_freeze_blocklist_domains()[$domain]) || isset(bs_blocklist(bs_load_reports())[$domain]));
    array_unshift($rows, $event);
    bs_freeze_save($rows);

    return [
        'ok' => true,
        'simulated' => true,
        'ref' => $ref,
        'anon' => $event['anon'],
        'lock_until' => date('c', $now + 15 * 60),
        'actions' => [
            ['key' => 'sessions', 'done' => true],
            ['key' => 'lock', 'done' => true],
            ['key' => 'dns', 'done' => $domain !== '', 'domain' => $domain, 'already' => $alreadyBlocked, 'official' => $official, 'given' => $raw !== ''],
            ['key' => 'ticket', 'done' => true, 'ref' => $ref],
        ],
        'event' => $event,
    ];
}

function bs_freeze_recent(int $limit = 8): array {
    $rows = bs_freeze_load();
    $today = strtotime('today');
    $todayCount = count(array_filter($rows, fn($r) => strtotime($r['created_at']) >= $today));
    $domains = count(bs_freeze_blocklist_domains());
    return ['recent' => array_slice($rows, 0, $limit), 'stats' => ['today' => $todayCount, 'domains' => $domains, 'total' => count($rows)]];
}

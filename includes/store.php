<?php
require_once __DIR__ . '/analyzer.php';
require_once __DIR__ . '/trends.php';
require_once __DIR__ . '/campus.php';
require_once __DIR__ . '/ai.php';

date_default_timezone_set(bs_config()['timezone']);

define('BS_DATA_FILE', __DIR__ . '/../data/reports.json');

function bs_load_reports(): array {
    if (!file_exists(BS_DATA_FILE)) {
        $seed = bs_seed_reports();
        bs_save_reports($seed);
        return $seed;
    }
    $data = json_decode(file_get_contents(BS_DATA_FILE), true);
    return is_array($data) ? $data : [];
}

function bs_save_reports(array $reports): void {
    if (!is_dir(dirname(BS_DATA_FILE))) mkdir(dirname(BS_DATA_FILE), 0775, true);
    file_put_contents(BS_DATA_FILE, json_encode(array_values($reports), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function bs_make_report(array $in, ?int $timestamp = null): array {
    $message = trim((string)($in['message'] ?? ''));
    $sender  = trim((string)($in['sender'] ?? ''));
    $url     = trim((string)($in['url'] ?? ''));
    $a = bs_analyze($message, $sender, $url);
    return [
        'id'         => bin2hex(random_bytes(6)),
        'message'    => $message,
        'sender'     => $sender,
        'url'        => $url,
        'channel'    => trim((string)($in['channel'] ?? 'Other')) ?: 'Other',
        'faculty'    => trim((string)($in['faculty'] ?? 'Not stated')) ?: 'Not stated',
        'location'   => bs_valid_zone((string)($in['location'] ?? '')),
        'category'   => $a['category'],
        'risk'       => $a['risk'],
        'level'      => $a['level'],
        'flags'      => $a['flags'],
        'domain'     => $a['domain'],
        'features'   => bs_features($message, $sender, $url),
        'created_at' => date('c', $timestamp ?? time()),
    ];
}

function bs_seed_reports(): array {
    $samples = [

        ['Congratulations! You are shortlisted for the Shopee Graduate Internship 2026 (RM2,500 allowance). Pay RM50 training fee to confirm your slot before midnight.', 'hr@shopee-careers.online', 'https://shopee-careers.online/confirm', 'Email', 'Business', 0],
        ['Hi, this is Shopee HR. Your CV was selected for our marketing internship. A one-time RM50 training fee is needed to secure the position, pay via TNG today.', 'shopee.hr.recruit@gmail.com', '', 'WhatsApp', 'Computing', 0],
        ['Shopee Malaysia internship offer! Limited slots. Transfer RM50 for training materials and onboarding kit, then we send your offer letter.', 'talent@shopee-intern.xyz', 'https://shopee-intern.xyz/offer', 'Telegram', 'Business', 1],
        ['Dear student, Shopee is hiring interns for e-commerce operations. Complete registration by paying the RM50 training fee at the link below.', 'careers@shopee-my.site', 'https://bit.ly/shopee-intern26', 'Email', 'Accounting', 2],
        ['Shopee internship interview passed! Please bank in RM50 training fee within 24 hours or your slot will be released.', '+60 11-2345 6789', '', 'SMS', 'Design', 3],
        ['Got this from "Shopee HR" on LinkedIn: remote internship, RM3000/month, but must pay RM60 training fee first.', 'recruit@shopee-careers.online', 'https://shopee-careers.online/pay', 'WhatsApp', 'Engineering', 5],
        ['Shopee internship program - training fee RM50 required before onboarding. Contact HR on Telegram.', 'shopeehr.my@yahoo.com', 'https://t.me/shopeeHRmy', 'Telegram', 'Business', 9],

        ['URGENT: Your APSpace account will be suspended within 24 hours. Verify your account and password here.', 'it-helpdesk@apu-support.xyz', 'http://apspace-verify.xyz/login', 'Email', 'Computing', 0],
        ['APU IT Services: unusual login detected on your student account. Log in to confirm your identity.', 'security@apu-itservices.online', 'http://apu-itservices.online/secure', 'Email', 'Engineering', 2],
        ['Your Moodle email quota is full. Click here to reset your password or your inbox will be deleted.', 'admin@apu-moodle.site', 'http://apu-moodle.site/reset', 'Email', 'Engineering', 4],
        ['APU Scholarship 2026: you are eligible for a 50% scholarship. Login with your student portal password to claim before it expire.', 'scholarship@apu-edu.live', 'http://apu-edu.live/claim', 'Email', 'Computing', 6],
        ['Final reminder: re-verify your APSpace login to access exam results. Enter your TP number and password.', 'noreply@apspace-results.top', 'https://apspace-results.top', 'Email', 'Psychology', 8],
        ['Your APU student email will expire. Log in to keep your account active.', 'support@office365-apu.cfd', 'http://office365-apu.cfd/login', 'Email', 'Design', 11],

        ['Room for rent 5 min walking distance to APU, fully furnished RM450. Landlord is overseas so no viewing, transfer booking fee RM300 and keys will be couriered.', 'sarahrooms@yahoo.com', '', 'Facebook', 'Engineering', 1],
        ["Master room near APU RM480. I'm working overseas now, pay RM300 booking fee and I will mail the key to you.", 'jenny.lim.rent@gmail.com', '', 'WhatsApp', 'Design', 5],
        ['Studio near Bukit Jalil LRT / APU, below market price RM600. Owner outstation, cannot view this week. Booking fee RM300 via DuitNow to hold.', 'propertydeals.kl@gmail.com', '', 'Telegram', 'Business', 8],
        ['Looking for housemate! Room near APU RM500, owner overseas. Transfer RM350 booking fee first then can move in.', 'aimanrooms@hotmail.com', '', 'WhatsApp', 'Psychology', 10],
        ['Cheapest room near APU Technology Park, RM400 incl. Wi-Fi. Booking fee RM300 to secure, viewing not possible because landlord is overseas.', 'kl.studentstay@outlook.com', 'https://tinyurl.com/roomapu', 'Facebook', 'Computing', 12],

        ['Hiring part time! Just like and follow TikTok shops, earn RM300 per day. No experience needed. Registration fee RM30 only, WhatsApp me.', 'recruit.malaysia88@gmail.com', 'https://wa.me/60112345678', 'WhatsApp', 'Business', 0],
        ['TikTok online job: like & follow videos, RM20 per task, daily payout. Pay RM30 registration fee to join our Telegram group.', 'tiktok.jobs.my@gmail.com', 'https://t.me/tiktokjobsMY', 'Telegram', 'Computing', 3],
        ['Easy money for students: like and follow TikTok accounts from home. Pay RM30 registration fee to start earning.', 'hr@tiktok-tasks.click', 'https://tiktok-tasks.click/join', 'Instagram', 'Accounting', 7],
        ['Part-time TikTok task job, like and follow shops, earn up to RM5000 monthly. Registration fee RM35, limited slots.', 'ambassador.my@gmail.com', 'https://s.id/tiktoktask', 'Instagram', 'Design', 10],

        ['Maybank2u security alert: unusual activity detected. Login to confirm your identity and enter the TAC sent to you.', 'alerts@maybank2u-secure.top', 'http://maybank2u-secure.top/m2u', 'SMS', 'Accounting', 1],
        ['Maybank: your account has been temporarily suspended. Verify now and enter the TAC code to restore access.', 'service@maybank-verify.buzz', 'http://maybank-verify.buzz', 'SMS', 'Business', 4],
        ["M2U: RM1,250 transfer pending. If this wasn't you, cancel immediately by entering your TAC here.", 'm2u@maybank2u-alert.icu', 'https://bit.ly/m2u-cancel', 'SMS', 'Computing', 12],

        ['PTPTN: your loan disbursement is on hold. Update your bank details and IC number immediately.', 'noreply@ptptn-gov.info', 'https://bit.ly/ptptn-upd8', 'SMS', 'Computing', 2],
        ['PTPTN loan approved! Please submit your bank details and IC number to receive RM1,500 today.', 'ptptn.disburse@gmail.com', 'https://ptptn-claim.site', 'SMS', 'Engineering', 9],

        ['Your parcel could not be delivered. Pay RM2.50 redelivery fee via the link.', 'poslaju@pos-delivery.icu', 'https://pos-delivery.icu/pay', 'SMS', 'Design', 6],
        ['Pos Laju: delivery failed due to incomplete address. Pay RM2.50 redelivery fee within 24 hours.', 'notify@poslaju-track.top', 'http://poslaju-track.top', 'SMS', 'Psychology', 13],

        ['Internship at crypto trading firm. Earn USDT commission per task, deposit 50 USDT to unlock tasks.', 'hr.dept@cryptoworks.shop', 'https://cryptoworks.shop/task', 'Telegram', 'Computing', 3],
    ];

    $locations = ['block-e', 'block-b', 'block-b', 'block-e', 'block-d', 'spine', 'block-e',
                  'block-d', 'block-b', 'spine', 'block-e', 'block-b', 'block-j',
                  'block-j', 'block-j', 'main-entrance', 'block-j', 'block-j',
                  'spine', 'block-e', 'block-b', 'block-d',
                  'block-j', 'spine', 'block-b',
                  'block-e', 'block-j',
                  'block-j', 'parking',
                  'block-d'];
    $reports = [];
    $now = time();
    foreach ($samples as $i => $s) {
        $ts = $now - $s[5] * 86400 - (($i * 7919) % 50000) - 600;
        $reports[] = bs_make_report(['message' => $s[0], 'sender' => $s[1], 'url' => $s[2], 'channel' => $s[3], 'faculty' => $s[4], 'location' => $locations[$i] ?? ''], $ts);
    }
    usort($reports, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
    return $reports;
}

function bs_dashboard(array $reports): array {
    $now = time(); $day = 86400;
    $cats = ['internship', 'rental', 'phishing', 'other'];
    $trends = bs_trends($reports);

    $trendOf = [];
    foreach ($trends as $t) foreach ($t['report_ids'] as $rid) $trendOf[$rid] = ['id' => $t['id'], 'title' => $t['title']];

    $week = 0; $high = 0; $catCount = array_fill_keys($cats, 0); $flagCount = [];
    $daily = [];
    for ($d = 13; $d >= 0; $d--) {
        $k = date('Y-m-d', $now - $d * $day);
        $daily[$k] = ['date' => $k] + array_fill_keys($cats, 0);
    }
    foreach ($reports as &$r) {
        $ts = strtotime($r['created_at']);
        $cat = in_array($r['category'], $cats, true) ? $r['category'] : 'other';
        $catCount[$cat]++;
        if ($now - $ts <= 7 * $day) $week++;
        if ($r['level'] === 'high') $high++;
        $k = date('Y-m-d', $ts);
        if (isset($daily[$k])) $daily[$k][$cat]++;
        foreach ($r['flags'] as $f) $flagCount[$f['label']] = ($flagCount[$f['label']] ?? 0) + 1;
        $r['trend'] = $trendOf[$r['id']] ?? null;
        if (!array_key_exists('location', $r)) $r['location'] = bs_fallback_zone($r['id']);
        unset($r['features']);
    }
    unset($r);
    arsort($flagCount);

    $active = array_values(array_filter($trends, fn($t) => $t['count'] >= 2));
    $ai = bs_ai_cache();
    foreach ($trends as &$t) {
        unset($t['profile']);
        $t['ai'] = $ai[$t['id'] . ':' . $t['count']] ?? null;
    }
    unset($t);

    return [
        'reports' => $reports,
        'trends'  => $trends,
        'zones'   => bs_zones_public(),
        'stats'   => [
            'total'         => count($reports),
            'this_week'     => $week,
            'high'          => $high,
            'active_trends' => count($active),
            'categories'    => $catCount,
            'daily'         => array_values($daily),
            'ai_enabled'    => bs_ai_enabled(),
            'flags'         => array_slice(array_map(fn($k, $v) => ['label' => $k, 'count' => $v], array_keys($flagCount), $flagCount), 0, 6),
        ],
    ];
}

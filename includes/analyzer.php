<?php
/**
 * ByeScam scam analyzer.
 * Learns nothing magical: it scores a report using keyword patterns on the
 * message text, the sender domain and the URL, then assigns a category,
 * a list of red flags and a 0-100 risk score.
 */

const CATEGORIES = [
    'internship' => ['internship', 'intern ', 'job offer', 'hiring', 'part-time', 'part time', 'commission',
                     'training fee', 'registration fee', 'recruit', 'salary', 'work from home', 'hr department',
                     'vacancy', 'position', 'interview', 'resume', 'cv', 'onboarding', 'like and follow', 'task'],
    'rental'     => ['room', 'rent', 'deposit', 'tenant', 'landlord', 'condo', 'unit', 'viewing', 'booking fee',
                     'roommate', 'housemate', 'accommodation', 'apartment', 'studio', 'utilities', 'lease',
                     'near apu', 'walking distance', 'fully furnished'],
    'phishing'   => ['verify', 'account', 'password', 'login', 'log in', 'suspended', 'apspace', 'student portal',
                     'otp', 'bank', 'update your', 'reset', 'ptptn', 'scholarship', 'moodle', 'email quota',
                     'parcel', 'refund', 'tac', 'security alert', 'unusual activity'],
];

const FLAG_RULES = [
    'urgency'      => ['label' => 'Urgency pressure', 'weight' => 15,
                       'words' => ['urgent', 'immediately', 'within 24', 'today only', 'last chance', 'act now',
                                   'limited slot', 'limited slots', 'expire', 'asap', 'before midnight', 'final notice']],
    'payment'      => ['label' => 'Upfront payment request', 'weight' => 25,
                       'words' => ['deposit', 'fee', 'transfer', 'bank in', 'bank-in', 'pay ', 'payment', 'tng',
                                   "touch 'n go", 'touch n go', 'duitnow', 'crypto', 'usdt']],
    'credentials'  => ['label' => 'Asks for login / OTP / IC', 'weight' => 25,
                       'words' => ['password', 'otp', 'tac', 'ic number', 'mykad', 'login details', 'verify your account',
                                   'confirm your identity', 'card number', 'cvv']],
    'too_good'     => ['label' => 'Too-good-to-be-true offer', 'weight' => 15,
                       'words' => ['easy money', 'no experience', 'guaranteed', 'per day', 'daily payout',
                                   'earn up to', 'below market', 'cheapest', 'free laptop', 'just like']],
    'off_platform' => ['label' => 'Moves chat off-platform', 'weight' => 10,
                       'words' => ['whatsapp me', 'telegram', 'add me', 'dm me', 'pm me', 'contact this number',
                                   'wa.me', 't.me/']],
    'no_viewing'   => ['label' => 'Avoids in-person meeting', 'weight' => 15,
                       'words' => ['overseas', 'no viewing', 'cannot view', 'outstation', 'keys will be couriered',
                                   'mail the key', 'online interview only']],
];

const FREE_MAIL   = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com', 'icloud.com', 'proton.me'];
const BAD_TLDS    = ['xyz', 'top', 'click', 'info', 'live', 'online', 'site', 'icu', 'buzz', 'shop', 'cfd', 'rest', 'work', 'support'];
const CHAT_HOSTS  = ['wa.me', 't.me', 'api.whatsapp.com', 'telegram.me'];
const SHORTENERS  = ['bit.ly', 'tinyurl.com', 't.co', 's.id', 'cutt.ly', 'shorturl.at', 'rb.gy', 'is.gd', 'tiny.cc'];
const BRANDS      = [  // brand keyword => official domains
    'apu'     => ['apu.edu.my', 'apiit.edu.my'],
    'apspace' => ['apu.edu.my'],
    'maybank' => ['maybank2u.com.my', 'maybank.com'],
    'cimb'    => ['cimbclicks.com.my', 'cimb.com.my'],
    'ptptn'   => ['ptptn.gov.my'],
    'shopee'  => ['shopee.com.my'],
    'jobstreet' => ['jobstreet.com.my', 'jobstreet.com'],
];

function bs_host(string $value): string {
    $value = strtolower(trim($value));
    if ($value === '') return '';
    if (strpos($value, '@') !== false) $value = substr($value, strrpos($value, '@') + 1);
    if (!preg_match('#^[a-z]+://#', $value)) $value = 'http://' . $value;
    $host = parse_url($value, PHP_URL_HOST) ?: '';
    $host = preg_replace('/^www\./', '', $host);
    // phone numbers or plain names are not domains
    return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $host) ? $host : '';
}

function bs_ends_with(string $host, string $domain): bool {
    return $host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain;
}

/** Whole-word / whole-phrase match so "tac" does not match "contact". */
function bs_has(string $text, string $phrase): bool {
    return (bool)preg_match('/(?<![a-z0-9])' . preg_quote(trim($phrase), '/') . '(?![a-z0-9])/', $text);
}

function bs_analyze(string $message, string $sender = '', string $url = ''): array {
    $text = ' ' . strtolower($message) . ' ';
    $senderHost = bs_host($sender);
    $urlHost = bs_host($url);
    $flags = [];
    $add = function (string $key, string $label, int $weight, string $evidence) use (&$flags) {
        if (!isset($flags[$key])) $flags[$key] = ['key' => $key, 'label' => $label, 'weight' => $weight, 'evidence' => $evidence];
    };

    // 1. Category by keyword hits
    $scores = [];
    foreach (CATEGORIES as $cat => $words) {
        $scores[$cat] = 0;
        foreach ($words as $w) if (bs_has($text, $w)) $scores[$cat]++;
    }
    arsort($scores);
    $category = key($scores);
    if (reset($scores) === 0) $category = $url !== '' ? 'phishing' : 'other';

    // 2. Message-text red flags
    foreach (FLAG_RULES as $key => $rule) {
        foreach ($rule['words'] as $w) {
            if (bs_has($text, $w)) { $add($key, $rule['label'], $rule['weight'], '"' . trim($w) . '"'); break; }
        }
    }
    if (preg_match('/rm\s?\d{2,5}\s?(\/|per|a)\s?(day|hour|task)/', $text, $m)) {
        $add('too_good', 'Too-good-to-be-true offer', 15, '"' . $m[0] . '"');
    }

    // 3. Sender domain checks
    if ($senderHost !== '') {
        if (in_array($senderHost, FREE_MAIL, true) && in_array($category, ['internship', 'phishing'], true)) {
            $add('free_mail', 'Official-sounding message from free email', 15, $senderHost);
        }
    }

    // 4. URL / domain checks (sender + link)
    foreach (array_filter([$senderHost, $urlHost]) as $host) {
        $tld = substr(strrchr($host, '.') ?: '', 1);
        if (in_array($tld, BAD_TLDS, true)) $add('bad_tld', 'High-risk domain ending (.' . $tld . ')', 15, $host);
        if (in_array($host, SHORTENERS, true)) $add('shortener', 'Shortened link hides destination', 15, $host);
        if (preg_match('/^\d{1,3}(\.\d{1,3}){3}$/', $host)) $add('ip_url', 'Link uses raw IP address', 20, $host);
        foreach (BRANDS as $brand => $official) {
            if (strpos($host, $brand) === false) continue;
            $legit = false;
            foreach ($official as $d) if (bs_ends_with($host, $d)) $legit = true;
            $nice = ['apu' => 'APU', 'apspace' => 'APSpace', 'maybank' => 'Maybank', 'cimb' => 'CIMB', 'ptptn' => 'PTPTN', 'shopee' => 'Shopee', 'jobstreet' => 'JobStreet'][$brand] ?? ucfirst($brand);
            if (!$legit) $add('lookalike', 'Look-alike of ' . $nice . ' domain', 30, $host);
        }
        if (preg_match('/[a-z]+[0-9]+[a-z]+|-{2,}|(login|verify|secure|update)[.-]/', $host)) {
            $add('odd_domain', 'Suspicious domain pattern', 10, $host);
        }
    }
    if ($url !== '' && stripos($url, 'https://') !== 0 && $urlHost !== '') {
        $add('no_https', 'Link is not HTTPS', 5, $urlHost);
    }

    // Classic scam combinations get a bonus
    $combo = 0;
    if (isset($flags['payment'], $flags['no_viewing'])) $combo += 20;   // pay-before-viewing rental
    if (isset($flags['payment'], $flags['too_good']))   $combo += 10;   // pay-to-work job
    if (isset($flags['credentials'], $flags['lookalike'])) $combo += 10; // fake login page
    if (count($flags) >= 3) $combo += 10;
    $risk = min(100, array_sum(array_column($flags, 'weight')) + $combo);
    $level = $risk >= 60 ? 'high' : ($risk >= 30 ? 'medium' : 'low');

    return [
        'category' => $category,
        'risk'     => $risk,
        'level'    => $level,
        'flags'    => array_values($flags),
        'domain'   => bs_threat_domain($senderHost, $urlHost),
    ];
}

/** The domain worth putting on the watchlist (skips gmail.com, wa.me, etc.). */
function bs_threat_domain(string $senderHost, string $urlHost): string {
    foreach ([$urlHost, $senderHost] as $h) {
        if ($h !== '' && !in_array($h, FREE_MAIL, true) && !in_array($h, CHAT_HOSTS, true)) return $h;
    }
    return '';
}

/* ------------------------------------------------------------------
   Features used to group similar reports into trends
   ------------------------------------------------------------------ */

// Impersonated brands: keyword => [group key, display name]. First match wins.
const BRAND_KEYWORDS = [
    'apspace' => ['apu', 'APU student portal'], 'moodle' => ['apu', 'APU student portal'],
    'student portal' => ['apu', 'APU student portal'], 'apu' => ['apu', 'APU student portal'],
    'shopee' => ['shopee', 'Shopee'], 'tiktok' => ['tiktok', 'TikTok'], 'jobstreet' => ['jobstreet', 'JobStreet'],
    'maybank' => ['maybank', 'Maybank2u'], 'm2u' => ['maybank', 'Maybank2u'], 'cimb' => ['cimb', 'CIMB'],
    'ptptn' => ['ptptn', 'PTPTN'], 'touch n go' => ['tng', "Touch 'n Go"], 'tng' => ['tng', "Touch 'n Go"],
    'pos laju' => ['poslaju', 'Pos Laju'], 'poslaju' => ['poslaju', 'Pos Laju'], 'parcel' => ['poslaju', 'Pos Laju'],
    'office365' => ['microsoft', 'Microsoft 365'], 'microsoft' => ['microsoft', 'Microsoft 365'],
];

// What the scammer wants: regex => [key, label, is_payment]
const ASK_PATTERNS = [
    '/training (fee|material|course)/'          => ['training_fee', 'training fee', true],
    '/(registration|joining) fee|register.{0,20}fee/' => ['registration_fee', 'registration fee', true],
    '/starter kit|laptop setup/'                 => ['kit_fee', 'starter kit fee', true],
    '/booking fee/'                              => ['booking_fee', 'booking fee', true],
    '/redelivery/'                               => ['redelivery_fee', 'redelivery fee', true],
    '/deposit/'                                  => ['deposit', 'deposit', true],
    '/(?<![a-z])(tac|otp)(?![a-z])/'             => ['otp', 'OTP/TAC code', false],
    '/bank details|ic number|mykad/'             => ['bank_ic', 'bank & IC details', false],
    '/password|log ?in|verify your account|confirm your identity/' => ['password', 'login password', false],
    '/fee|bank in|transfer|pay /'                => ['fee', 'payment', true],
];

const STOPWORDS = ['this','that','with','your','will','have','from','here','only','then','just','them','they','their',
    'been','were','what','when','which','into','more','also','after','before','about','dear','please','today','hours',
    'link','below','via','need','needed','send','pays','your','there','because','first','within'];

function bs_features(string $message, string $sender = '', string $url = ''): array {
    $text = strtolower($message);
    $all = $text . ' ' . strtolower($sender) . ' ' . strtolower($url);

    $brand = null; $brandName = null;
    foreach (BRAND_KEYWORDS as $kw => [$key, $name]) {
        if (preg_match('/' . preg_quote($kw, '/') . '/', $all)) { $brand = $key; $brandName = $name; break; }
    }

    $ask = null; $askLabel = null; $askPay = false; $askPos = -1;
    foreach (ASK_PATTERNS as $re => [$key, $label, $pay]) {
        if (preg_match($re, $text, $m, PREG_OFFSET_CAPTURE)) {
            [$ask, $askLabel, $askPay, $askPos] = [$key, $label, $pay, $m[0][1]];
            break;
        }
    }

    // Amount: the RM figure closest to what is being asked for (not the fake salary)
    $amount = null;
    if ($askPay && preg_match_all('/rm\s?(\d+(?:[.,]\d+)?)/', $text, $mm, PREG_OFFSET_CAPTURE)) {
        $best = PHP_INT_MAX;
        foreach ($mm[1] as [$val, $pos]) {
            $d = abs($pos - $askPos);
            if ($d < $best) { $best = $d; $amount = str_replace(',', '', $val); }
        }
    }

    preg_match_all('/[a-z]{4,}/', $text, $w);
    $tokens = array_values(array_unique(array_diff($w[0], STOPWORDS)));

    return ['brand' => $brand, 'brand_name' => $brandName, 'ask' => $ask, 'ask_label' => $askLabel,
            'ask_payment' => $askPay, 'amount' => $amount, 'tokens' => $tokens];
}

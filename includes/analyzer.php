<?php
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
const BRANDS      = [
    'apu'       => ['apu.edu.my', 'apiit.edu.my'],
    'apspace'   => ['apu.edu.my'],
    'maybank'   => ['maybank2u.com.my', 'maybank.com'],
    'cimb'      => ['cimbclicks.com.my', 'cimb.com.my'],
    'ptptn'     => ['ptptn.gov.my'],
    'shopee'    => ['shopee.com.my'],
    'jobstreet' => ['jobstreet.com.my', 'jobstreet.com'],
];

function bs_host(string $value): string {
    $value = strtolower(trim($value));
    if ($value === '') return '';
    if (strpos($value, '@') !== false) $value = substr($value, strrpos($value, '@') + 1);
    if (!preg_match('#^[a-z]+://#', $value)) $value = 'http://' . $value;
    $host = parse_url($value, PHP_URL_HOST) ?: '';
    $host = preg_replace('/^www\./', '', $host);

    return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $host) ? $host : '';
}

function bs_ends_with(string $host, string $domain): bool {
    return $host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain;
}

function bs_has(string $text, string $phrase): bool {
    return (bool)preg_match('/(?<![a-z0-9])' . preg_quote(trim($phrase), '/') . '(?![a-z0-9])/', $text);
}

/**
 * Deterministic rule-based scanner (used for technical signal extraction + fallback)
 */
function bs_rule_analyze(string $message, string $sender = '', string $url = ''): array {
    $text = ' ' . strtolower($message) . ' ';
    $senderHost = bs_host($sender);
    $urlHost = bs_host($url);
    $flags = [];
    $add = function (string $key, string $label, int $weight, string $evidence) use (&$flags) {
        if (!isset($flags[$key])) {
            $flags[$key] = ['key' => $key, 'label' => $label, 'weight' => $weight, 'evidence' => $evidence];
        }
    };

    $scores = [];
    foreach (CATEGORIES as $cat => $words) {
        $scores[$cat] = 0;
        foreach ($words as $w) {
            if (bs_has($text, $w)) $scores[$cat]++;
        }
    }
    arsort($scores);
    $category = key($scores);
    if (reset($scores) === 0) $category = $url !== '' ? 'phishing' : 'other';

    foreach (FLAG_RULES as $key => $rule) {
        foreach ($rule['words'] as $w) {
            if (bs_has($text, $w)) {
                $add($key, $rule['label'], $rule['weight'], '"' . trim($w) . '"');
                break;
            }
        }
    }
    if (preg_match('/rm\s?\d{2,5}\s?(\/|per|a)\s?(day|hour|task)/', $text, $m)) {
        $add('too_good', 'Too-good-to-be-true offer', 15, '"' . $m[0] . '"');
    }

    if ($senderHost !== '') {
        if (in_array($senderHost, FREE_MAIL, true) && in_array($category, ['internship', 'phishing'], true)) {
            $add('free_mail', 'Official-sounding message from free email', 15, $senderHost);
        }
    }

    foreach (array_filter([$senderHost, $urlHost]) as $host) {
        $tld = substr(strrchr($host, '.') ?: '', 1);
        if (in_array($tld, BAD_TLDS, true)) $add('bad_tld', 'High-risk domain ending (.' . $tld . ')', 15, $host);
        if (in_array($host, SHORTENERS, true)) $add('shortener', 'Shortened link hides destination', 15, $host);
        if (preg_match('/^\d{1,3}(\.\d{1,3}){3}$/', $host)) $add('ip_url', 'Link uses raw IP address', 20, $host);
        foreach (BRANDS as $brand => $official) {
            if (strpos($host, $brand) === false) continue;
            $legit = false;
            foreach ($official as $d) {
                if (bs_ends_with($host, $d)) $legit = true;
            }
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

    $combo = 0;
    if (isset($flags['payment'], $flags['no_viewing'])) $combo += 20;
    if (isset($flags['payment'], $flags['too_good']))   $combo += 10;
    if (isset($flags['credentials'], $flags['lookalike'])) $combo += 10;
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

/**
 * Hybrid AI + Rule Analyzer
 */
function bs_analyze(string $message, string $sender = '', string $url = ''): array {
    if (empty(trim($message)) && empty(trim($sender)) && empty(trim($url))) {
        return [
            'category' => 'Safe',
            'risk'     => 0,
            'level'    => 'low',
            'flags'    => [],
            'domain'   => '',
        ];
    }

    // 1. Run deterministic technical rules first
    $ruleResult = bs_rule_analyze($message, $sender, $url);

    // 2. If AI chat helper isn't loaded, return rule-based result immediately
    if (!function_exists('bs_ai_chat')) {
        return $ruleResult;
    }

    // 3. Build strict system prompt grounded for APU students
    $systemPrompt = "You are an expert cybersecurity analyzer protecting Asia Pacific University (APU) students from email scams.
Analyze the provided email sender, URL, message body, and automated technical signals.
Return ONLY raw, valid JSON with NO markdown formatting and NO backticks.

Evaluation Guidelines:
- Sender Authenticity: Official APU domains end in '@apu.edu.my', '@mail.apu.edu.my', or '@apiit.edu.my'. Legitimate announcements or automated receipts from verified institutions/services should be scored LOW (0-25) even if they contain words like 'login', 'account', or 'payment'.
- Spoofing & Phishing: Flag mismatched sender domains, look-alike domains, URL shorteners (bit.ly, tinyurl), non-HTTPS links, or requests for passwords/OTP/TAC/MyKad.
- Student Scams: Flag fake internships asking for training/registration fees, rental scams demanding deposits without viewing, or unrealistic 'easy money' tasks.
- Specificity Rule: Every flag in 'flags' MUST quote exact evidence from the sender, URL, or message body. Never output generic filler. If safe, return an empty flags array [].

Required JSON Schema:
{
  \"risk\": <integer 0-100>,
  \"level\": <\"low\" | \"medium\" | \"high\">,
  \"category\": <\"Safe\" | \"Phishing\" | \"Fake Internship\" | \"Rental Scam\" | \"Suspicious Offer\">,
  \"flags\": [
    { \"label\": \"<Specific finding citing exact domain, URL, or quote from email>\" }
  ]
}

Scoring Thresholds:
- 70-100 = \"high\"
- 35-69 = \"medium\"
- 0-34 = \"low\"";

    $payload = json_encode([
        'sender'            => $sender,
        'url'               => $url,
        'message'           => $message,
        'technical_signals' => array_map(fn($f) => $f['label'] . ' (' . $f['evidence'] . ')', $ruleResult['flags']),
    ], JSON_UNESCAPED_SLASHES);

    $messages = [
        ['role' => 'user', 'content' => "Analyze this email:\n" . $payload]
    ];

    // 4. Call Gemini AI
    $aiResponse = bs_ai_chat($messages, $systemPrompt, 800);

    // 5. Fallback to deterministic rules if AI fails
    if (isset($aiResponse['error']) || empty($aiResponse['text'])) {
        return $ruleResult;
    }

    $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($aiResponse['text']));
    $parsed = json_decode($cleanJson, true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['risk'], $parsed['level'])) {
        return $ruleResult;
    }

    $risk = max(0, min(100, (int)$parsed['risk']));
    $level = $risk >= 70 ? 'high' : ($risk >= 35 ? 'medium' : 'low');

    return [
        'category' => (string)($parsed['category'] ?? $ruleResult['category']),
        'risk'     => $risk,
        'level'    => $level,
        'flags'    => is_array($parsed['flags'] ?? null) ? $parsed['flags'] : [],
        'domain'   => $ruleResult['domain'],
    ];
}

function bs_threat_domain(string $senderHost, string $urlHost): string {
    foreach ([$urlHost, $senderHost] as $h) {
        if ($h !== '' && !in_array($h, FREE_MAIL, true) && !in_array($h, CHAT_HOSTS, true)) return $h;
    }
    return '';
}

const BRAND_KEYWORDS = [
    'apspace' => ['apu', 'APU student portal'], 'moodle' => ['apu', 'APU student portal'],
    'student portal' => ['apu', 'APU student portal'], 'apu' => ['apu', 'APU student portal'],
    'shopee' => ['shopee', 'Shopee'], 'tiktok' => ['tiktok', 'TikTok'], 'jobstreet' => ['jobstreet', 'JobStreet'],
    'maybank' => ['maybank', 'Maybank2u'], 'm2u' => ['maybank', 'Maybank2u'], 'cimb' => ['cimb', 'CIMB'],
    'ptptn' => ['ptptn', 'PTPTN'], 'touch n go' => ['tng', "Touch 'n Go"], 'tng' => ['tng', "Touch 'n Go"],
    'pos laju' => ['poslaju', 'Pos Laju'], 'poslaju' => ['poslaju', 'Pos Laju'], 'parcel' => ['poslaju', 'Pos Laju'],
    'office365' => ['microsoft', 'Microsoft 365'], 'microsoft' => ['microsoft', 'Microsoft 365'],
];

const ASK_PATTERNS = [
    '/training (fee|material|course)/'                => ['training_fee', 'training fee', true],
    '/(registration|joining) fee|register.{0,20}fee/' => ['registration_fee', 'registration fee', true],
    '/starter kit|laptop setup/'                      => ['kit_fee', 'starter kit fee', true],
    '/booking fee/'                                   => ['booking_fee', 'booking fee', true],
    '/redelivery/'                                    => ['redelivery_fee', 'redelivery fee', true],
    '/deposit/'                                       => ['deposit', 'deposit', true],
    '/(?<![a-z])(tac|otp)(?![a-z])/'                  => ['otp', 'OTP/TAC code', false],
    '/bank details|ic number|mykad/'                  => ['bank_ic', 'bank & IC details', false],
    '/password|log ?in|verify your account|confirm your identity/' => ['password', 'login password', false],
    '/fee|bank in|transfer|pay /'                     => ['fee', 'payment', true],
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
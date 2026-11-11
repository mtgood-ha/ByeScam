<?php
require_once __DIR__ . '/../includes/store.php';
header('Content-Type: application/json; charset=utf-8');
$q = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 300);
$reports = bs_dashboard(bs_load_reports())['reports'];
$out = ['directory' => bs_directory()];
if ($q !== '') $out['result'] = bs_verify($q, $reports);
echo json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

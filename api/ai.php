<?php
require_once __DIR__ . '/../includes/store.php';
header('Content-Type: application/json; charset=utf-8');
@set_time_limit(120);
$reports = bs_load_reports();
echo json_encode(bs_ai_refresh(bs_trends($reports), $reports), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

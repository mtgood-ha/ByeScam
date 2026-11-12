<?php
require_once __DIR__ . '/../includes/store.php';
require_once __DIR__ . '/../includes/freeze.php';
header('Content-Type: application/json; charset=utf-8');

function bs_freeze_json($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $res = bs_freeze_submit((array)$in);
    if (isset($res['error'])) bs_freeze_json($res, 422);
    bs_freeze_json($res);
}

bs_freeze_json(['directory' => BS_FREEZE_DIRECTORY] + bs_freeze_recent() + ['server_time' => date('c')]);

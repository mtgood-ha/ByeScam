<?php
require_once __DIR__ . '/../includes/store.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(bs_network(bs_load_reports()) + ['server_time' => date('c')], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

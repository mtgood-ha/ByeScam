<?php
// Set headers to allow cross-origin requests from the Chrome extension
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Import the existing analysis engine
require_once __DIR__ . '/../includes/analyzer.php';

// Read the incoming JSON payload from the Chrome extension
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing JSON payload']);
    exit;
}

// Extract the fields submitted by content.js
$message = (string)($data['message'] ?? '');
$sender  = (string)($data['sender'] ?? '');
$url     = (string)($data['url'] ?? '');

// Pass the extracted Gmail data into your existing bs_analyze engine
$result = bs_analyze($message, $sender, $url);

// Output the final risk score and flags back to the extension
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
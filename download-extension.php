<?php
$sourceDir = __DIR__ . DIRECTORY_SEPARATOR . 'extension';
$zipRoot   = 'byescam-extension';
$fileName  = 'byescam-extension.zip';

function bs_extension_fail(string $message): void {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo "ByeScam could not package the extension.\n\n" . $message . "\n";
    exit;
}

if (!is_dir($sourceDir)) {
    bs_extension_fail('The extension/ folder is missing from the ByeScam project.');
}
if (!class_exists('ZipArchive')) {
    bs_extension_fail('PHP ZipArchive is not available. In XAMPP, open php.ini, change ";extension=zip" to "extension=zip", then restart Apache.');
}

$tmpPath = tempnam(sys_get_temp_dir(), 'bsx');
if ($tmpPath === false) {
    bs_extension_fail('Could not create a temporary file for the zip archive.');
}
register_shutdown_function(function () use ($tmpPath) {
    if (is_file($tmpPath)) @unlink($tmpPath);
});

$zip = new ZipArchive();
if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    bs_extension_fail('ZipArchive could not open the temporary archive.');
}

$zip->addEmptyDir($zipRoot);
$added = 0;
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($files as $file) {
    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($sourceDir) + 1));
    if ($file->isDir()) {
        $zip->addEmptyDir($zipRoot . '/' . $relative);
        continue;
    }
    if (!$zip->addFile($file->getPathname(), $zipRoot . '/' . $relative)) {
        $zip->close();
        bs_extension_fail('ZipArchive could not add ' . $relative . ' to the archive.');
    }
    $added++;
}

if ($added === 0) {
    $zip->close();
    bs_extension_fail('The extension/ folder is empty.');
}
if (!$zip->close()) {
    bs_extension_fail('ZipArchive failed while writing the archive.');
}

clearstatcache(true, $tmpPath);
$size = filesize($tmpPath);
if (!$size) {
    bs_extension_fail('The generated archive is empty.');
}

while (ob_get_level() > 0) ob_end_clean();
ignore_user_abort(true);
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $size);
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

readfile($tmpPath);
@unlink($tmpPath);
exit;

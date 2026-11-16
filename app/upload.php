<?php
declare(strict_types=1);
session_start();

const MAX_BYTES = 5 * 1024 * 1024;
const DEST_DIR = '/srv/axapta-exchange/incoming';

function fail(string $message, int $status = 400): never {
    http_response_code($status);
    echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}
$csrf = $_POST['csrf'] ?? '';
if (!is_string($csrf) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    fail('Invalid CSRF token', 403);
}
if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
    fail('No file received');
}
$file = $_FILES['file'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    fail('Upload failed (code ' . (int)$file['error'] . ')');
}
if (!is_uploaded_file($file['tmp_name'])) {
    fail('Invalid upload source');
}
$size = (int)$file['size'];
if ($size < 1 || $size > MAX_BYTES) {
    fail('File size is outside the allowed range');
}
$original = (string)$file['name'];
if (strtolower(pathinfo($original, PATHINFO_EXTENSION)) !== 'xlsx') {
    fail('Only .xlsx files are accepted');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowedMimes = ['application/zip', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
if (!in_array($mime, $allowedMimes, true)) {
    fail('Unexpected file MIME type');
}

/*
 * XLSX is an OOXML ZIP package. Check required package entries.
 * This is not a malware scan and does not fully protect against ZIP bombs.
 */
$zip = new ZipArchive();
if ($zip->open($file['tmp_name']) !== true) {
    fail('File is not a readable ZIP package');
}
$hasContentTypes = $zip->locateName('[Content_Types].xml') !== false;
$hasWorkbook = $zip->locateName('xl/workbook.xml') !== false;
$entryCount = $zip->numFiles;
$zip->close();

if (!$hasContentTypes || !$hasWorkbook || $entryCount < 2 || $entryCount > 2000) {
    fail('File does not look like a valid XLSX package');
}

if (!is_dir(DEST_DIR) || !is_writable(DEST_DIR)) {
    error_log('Upload destination unavailable: ' . DEST_DIR);
    fail('Upload service is temporarily unavailable', 500);
}
$storedName = bin2hex(random_bytes(16)) . '.xlsx';
$destination = DEST_DIR . '/' . $storedName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    fail('Could not store uploaded file', 500);
}
chmod($destination, 0640);
error_log('XLSX uploaded: ' . $storedName . ' bytes=' . $size);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
$_SESSION['flash'] = 'Файл принят. Идентификатор: ' . $storedName;
header('Location: /', true, 303);
exit;

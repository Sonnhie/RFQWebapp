<?php
// download.php
session_start();

// 1️⃣ Validate input
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    echo "❌ No file specified.";
    exit;
}

// 2️⃣ Sanitize filename to prevent directory traversal
$file = basename($_GET['file']);

// 3️⃣ Define the upload directory
$baseDir = "D:/Uploads/Quotation/";

// 4️⃣ Build full absolute path
$path = $baseDir . $file;

// 5️⃣ Check file existence and type safety
if (!file_exists($path)) {
    http_response_code(404);
    echo "❌ File not found.";
    exit;
}

// 6️⃣ Safely detect MIME type (fallback in case mime_content_type fails)
$mimeType = mime_content_type($path) ?: 'application/octet-stream';

// 7️⃣ Whitelist allowed file types
$allowedMimeTypes = [
    'application/pdf',
    'application/zip',
    'application/x-zip-compressed',
    'image/jpeg',
    'image/png',
];

if (!in_array($mimeType, $allowedMimeTypes)) {
    http_response_code(403);
    echo "❌ File type not allowed.";
    exit;
}

// 8️⃣ Send headers for download
header('Content-Description: File Transfer');
header("Content-Type: $mimeType");
header("Content-Disposition: attachment; filename=\"" . $file . "\"");
header("Content-Length: " . filesize($path));
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Expires: 0');

// 9️⃣ Output the file to browser
readfile($path);
exit;

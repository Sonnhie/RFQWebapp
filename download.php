<?php
// download.php
session_start();


// 1️⃣ Check if 'file' parameter is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    echo "No file specified.";
    exit;
}

// 2️⃣ Sanitize file name to prevent path traversal
$file = basename($_GET['file']);

// 3️⃣ Define the base upload folder
$baseDir = "D:/Uploads/Quotation/";

// 4️⃣ Build full path
$path = $baseDir . $file;

// 5️⃣ Check file existence
if (!file_exists($path)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// 6️⃣ Force file download headers
$mimeType = mime_content_type($path);
header("Content-Type: $mimeType");
header("Content-Disposition: attachment; filename=\"" . $file . "\"");
header("Content-Length: " . filesize($path));

// 7️⃣ Output file
readfile($path);
exit;

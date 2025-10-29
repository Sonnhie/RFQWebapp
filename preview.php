<?php
// preview.php
session_start();

if (!isset($_SESSION['user']['id'])) {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

// 1️⃣ Check if 'file' parameter is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    echo "No file specified.";
    exit;
}

// 2️⃣ Sanitize file name to prevent path traversal (e.g., ../../etc/passwd)
$file = basename($_GET['file']);

// 3️⃣ Define the safe base directory (your upload folder)
$baseDir = "D:/Uploads/Attachments/";

// 4️⃣ Construct the absolute file path
$path = $baseDir . $file;

// 5️⃣ Check if file exists
if (!file_exists($path)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// 6️⃣ Set headers for preview
$mimeType = mime_content_type($path);
header("Content-Type: $mimeType");
header("Content-Disposition: inline; filename=\"" . $file . "\"");

// 7️⃣ Output the file contents
readfile($path);
exit;

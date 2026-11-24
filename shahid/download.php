
<?php
require "db.php";

$id = (int)($_GET['id'] ?? 0);
$inline = isset($_GET['inline']) &&
          $_GET['inline'] === '1';

$stmt = $conn->prepare(
    "SELECT original_name, stored_name, file_type
     FROM documents WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$doc = $stmt->get_result()->fetch_assoc();

if (!$doc || empty($doc['stored_name'])) {
    http_response_code(404);
    exit("File not found.");
}

$path = __DIR__ . "/uploads/" . basename($doc['stored_name']);

if (!is_file($path)) {
    http_response_code(404);
    exit("File missing.");
}

$isPdf = $doc['file_type'] === 'PDF';
$showInline = $inline && $isPdf;

$filename = basename($doc['original_name'] ?: 'document');
$filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

header("X-Content-Type-Options: nosniff");
header("Content-Type: " . ($showInline ? "application/pdf" : "application/octet-stream"));
header("Content-Length: " . filesize($path));
header(
    "Content-Disposition: " .
    ($showInline ? "inline" : "attachment") .
    "; filename=\"" . ($showInline ? "document.pdf" : $filename) . "\""
);

readfile($path);
exit;
?>

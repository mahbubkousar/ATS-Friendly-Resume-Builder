<?php
/**
 * Download ATS Guideline API
 * Allows authenticated users to download ATS guideline documents
 */

require_once '../config/session.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(403);
    die('Access denied');
}

$fileName = $_GET['file'] ?? '';

// Validate file name (security check)
if (empty($fileName) || strpos($fileName, '..') !== false || strpos($fileName, '/') !== false) {
    http_response_code(400);
    die('Invalid file name');
}

$guidelineDir = '../external_resources/';
$filePath = $guidelineDir . $fileName;

// Check if file exists
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die('File not found');
}

// Verify it's a PDF
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if ($fileExt !== 'pdf') {
    http_response_code(400);
    die('Invalid file type');
}

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($filePath);
exit;
?>

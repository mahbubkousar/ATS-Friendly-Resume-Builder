<?php
/**
 * Get ATS Guidelines API
 * Returns list of ATS guideline documents from external_resources folder
 * These are the official standards/sources used for ATS score calculation
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');

try {
    require_once '../config/session.php';
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

try {
    $guidelineDir = '../external_resources/';

    // Check if directory exists
    if (!is_dir($guidelineDir)) {
        echo json_encode(['success' => false, 'error' => 'Guidelines directory not found']);
        exit;
    }

    // Get all PDF files
    $pdfFiles = glob($guidelineDir . '*.pdf');

    if (empty($pdfFiles)) {
        echo json_encode(['success' => false, 'error' => 'No guideline documents found']);
        exit;
    }

    $guidelines = [];

    // Get metadata for each guideline document
    foreach ($pdfFiles as $pdfPath) {
        $fileName = basename($pdfPath);
        $fileNameNoExt = pathinfo($fileName, PATHINFO_FILENAME);
        $fileSize = filesize($pdfPath);
        $fileModified = filemtime($pdfPath);

        // Map file names to friendly titles
        $titleMap = [
            'ONU' => 'Ohio Northern University - ATS Guidelines',
            'UCM' => 'University of Central Missouri - ATS Standards',
            'UIC' => 'University of Illinois Chicago - ATS Best Practices'
        ];

        $guidelines[] = [
            'id' => $fileNameNoExt,
            'title' => $titleMap[$fileNameNoExt] ?? $fileNameNoExt . ' - ATS Guidelines',
            'file_name' => $fileName,
            'size' => $fileSize,
            'size_formatted' => formatBytes($fileSize),
            'modified' => date('Y-m-d', $fileModified),
            'download_url' => 'api/download-guideline.php?file=' . urlencode($fileName)
        ];
    }

    echo json_encode([
        'success' => true,
        'count' => count($guidelines),
        'guidelines' => $guidelines,
        'citation_text' => 'ATS scores calculated based on industry-standard guidelines from leading universities and career services.'
    ]);

} catch (Exception $e) {
    error_log("Guideline listing error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to list guidelines: ' . $e->getMessage()
    ]);
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>

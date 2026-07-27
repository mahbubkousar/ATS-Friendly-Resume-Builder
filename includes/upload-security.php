<?php

const MAX_DOCUMENT_UPLOAD_BYTES = 5 * 1024 * 1024;
const MAX_RESUME_TEXT_BYTES = 100000;
const MAX_JOB_DESCRIPTION_TEXT_BYTES = 50000;

class UploadValidationException extends RuntimeException
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 400)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

/**
 * @param array<string, mixed> $file
 * @param string[] $allowedExtensions
 * @return array{tmp_path: string, extension: string, mime_type: string, size: int}
 */
function validateDocumentUpload(array $file, array $allowedExtensions): array
{
    $error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

    if ($error !== UPLOAD_ERR_OK) {
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new UploadValidationException('The uploaded file exceeds the allowed size.', 413);
        }
        throw new UploadValidationException('The document upload failed.');
    }

    $tmpPath = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
    $fileName = isset($file['name']) ? (string) $file['name'] : '';
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new UploadValidationException('Invalid uploaded file.');
    }
    $size = filesize($tmpPath);
    if ($size === false) {
        throw new UploadValidationException('Unable to inspect the uploaded file.');
    }
    if ($size <= 0) {
        throw new UploadValidationException('The uploaded file is empty.');
    }
    if ($size > MAX_DOCUMENT_UPLOAD_BYTES) {
        throw new UploadValidationException('The uploaded file must be 5 MB or smaller.', 413);
    }

    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($extension === '' || !in_array($extension, array_map('strtolower', $allowedExtensions), true)) {
        throw new UploadValidationException('Unsupported document type.');
    }
    if (!class_exists('finfo')) {
        throw new UploadValidationException('File validation is unavailable.', 500);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpPath);
    if (!is_string($mimeType) || $mimeType === '') {
        throw new UploadValidationException('Unable to determine the document type.');
    }

    validateDocumentContents($tmpPath, $extension, $mimeType);

    return [
        'tmp_path' => $tmpPath,
        'extension' => $extension,
        'mime_type' => canonicalDocumentMimeType($extension),
        'size' => (int) $size,
    ];
}

function validateDocumentContents(string $path, string $extension, string $detectedMime): void
{
    $allowedMimes = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ],
        'txt' => ['text/plain'],
    ];

    if (!isset($allowedMimes[$extension]) || !in_array($detectedMime, $allowedMimes[$extension], true)) {
        throw new UploadValidationException('The file content does not match its extension.');
    }

    $header = file_get_contents($path, false, null, 0, 8);
    if ($header === false) {
        throw new UploadValidationException('Unable to read the uploaded document.');
    }

    if ($extension === 'pdf') {
        if (strncmp($header, '%PDF-', 5) !== 0) {
            throw new UploadValidationException('The uploaded PDF is malformed.');
        }
        $fileSize = filesize($path);
        $tailLength = min(2048, $fileSize === false ? 0 : $fileSize);
        $tail = $tailLength > 0
            ? file_get_contents($path, false, null, max(0, (int) $fileSize - $tailLength), $tailLength)
            : false;
        if ($tail === false || strpos($tail, '%%EOF') === false) {
            throw new UploadValidationException('The uploaded PDF is malformed.');
        }
    } elseif ($extension === 'doc') {
        if ($header !== hex2bin('D0CF11E0A1B11AE1')) {
            throw new UploadValidationException('The uploaded Word document is malformed.');
        }
        $contents = file_get_contents($path);
        if ($contents === false || strpos($contents, "W\0o\0r\0d\0D\0o\0c\0u\0m\0e\0n\0t\0") === false) {
            throw new UploadValidationException('The uploaded Word document is malformed.');
        }
    } elseif ($extension === 'docx') {
        if (!class_exists('ZipArchive')) {
            throw new UploadValidationException('DOCX validation is unavailable.', 500);
        }
        $archive = new ZipArchive();
        if ($archive->open($path) !== true) {
            throw new UploadValidationException('The uploaded Word document is malformed.');
        }
        $validPackage = $archive->locateName('[Content_Types].xml') !== false
            && $archive->locateName('word/document.xml') !== false;
        $uncompressedBytes = 0;
        $entryCount = $archive->numFiles;
        if ($entryCount > 1000) {
            $validPackage = false;
        }
        for ($index = 0; $validPackage && $index < $entryCount; $index++) {
            $entry = $archive->statIndex($index);
            if ($entry === false || !isset($entry['size'])) {
                $validPackage = false;
                break;
            }
            $uncompressedBytes += (int) $entry['size'];
            if ($uncompressedBytes > 20 * 1024 * 1024) {
                $validPackage = false;
            }
        }
        $archive->close();
        if (!$validPackage) {
            throw new UploadValidationException('The uploaded Word document is malformed.');
        }
    } elseif ($extension === 'txt') {
        $contents = file_get_contents($path);
        if ($contents === false || strpos($contents, "\0") !== false) {
            throw new UploadValidationException('The uploaded text document is malformed.');
        }
        if (function_exists('mb_check_encoding') && !mb_check_encoding($contents, 'UTF-8')) {
            throw new UploadValidationException('The uploaded text document must use UTF-8 encoding.');
        }
    }
}

function canonicalDocumentMimeType(string $extension): string
{
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain',
    ];

    return $mimeTypes[$extension];
}

/** @param array{tmp_path: string, size: int} $document */
function readValidatedDocument(array $document): string
{
    $contents = file_get_contents($document['tmp_path']);
    if ($contents === false || strlen($contents) !== $document['size']) {
        throw new UploadValidationException('Unable to read the uploaded document.');
    }
    return $contents;
}

function validateDocumentTextLength(string $text, string $label, int $maximumBytes): void
{
    if (strlen($text) > $maximumBytes) {
        throw new UploadValidationException(
            sprintf('%s is too long. Please shorten it and try again.', $label),
            413
        );
    }
}

<?php
require_once '../../includes/session.php';
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . "/../db_connect.php";

/**
 * API Endpoint: GET /api/download.php?id=RESOURCE_ID
 * Secure file download with validation and directory traversal protection
 * 
 * Query Parameters:
 * - id (int): Resource ID (required)
 * 
 * Returns: File stream or JSON error
 */

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "error" => "Method Not Allowed"]);
    exit();
}

// Error handler
function sendError($message, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo json_encode(["success" => false, "error" => $message]);
    exit();
}

// Check database connection
if (!isset($conn) || !$conn) {
    sendError("Service temporarily unavailable", 503);
}

// Validate resource ID parameter
if (!isset($_GET['id']) || $_GET['id'] === '') {
    sendError("Resource ID is required", 400);
}

$resourceId = (int) $_GET['id'];

if ($resourceId <= 0) {
    sendError("Invalid resource ID", 400);
}

// Fetch resource metadata from database
$query = "
    SELECT id, file_path, title, user_id
    FROM resources
    WHERE id = {$resourceId}
    LIMIT 1
";

$result = @mysqli_query($conn, $query);

if (!$result) {
    sendError("Database error", 500);
}

if (mysqli_num_rows($result) === 0) {
    sendError("Resource not found", 404);
}

$resource = mysqli_fetch_assoc($result);
$filePath = $resource['file_path'] ?? '';
$title = $resource['title'] ?? 'download';

// Security validation: Prevent directory traversal
// File path should not contain directory navigation sequences
if (empty($filePath) || 
    strpos($filePath, '..') !== false || 
    strpos($filePath, '/') !== false || 
    strpos($filePath, '\\') !== false ||
    preg_match('/[<>"|?*]/', $filePath)) {
    sendError("Invalid file path", 403);
}

// Build safe full path
$uploadsDir = realpath(__DIR__ . "/../uploads/");
if (!$uploadsDir) {
    sendError("Server configuration error", 500);
}

$fullPath = realpath(__DIR__ . "/../uploads/" . $filePath);

// Verify file is within uploads directory (prevent directory traversal)
if ($fullPath === false || strpos($fullPath, $uploadsDir) !== 0) {
    sendError("File access denied", 403);
}

// Final security check: file must exist and be readable
if (!file_exists($fullPath)) {
    sendError("File not found on server", 404);
}

if (!is_file($fullPath)) {
    sendError("Invalid file type", 403);
}

if (!is_readable($fullPath)) {
    sendError("File is not readable", 403);
}

// Get file extension and MIME type
$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

$mimeTypes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'txt' => 'text/plain',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'avif' => 'image/avif'
];

$mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

// Get file size
$fileSize = @filesize($fullPath);
if ($fileSize === false) {
    sendError("Unable to determine file size", 500);
}

// Sanitize filename for download
$downloadName = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $title);
if (strlen($downloadName) === 0) {
    $downloadName = 'download';
}
$downloadName .= '.' . $ext;

// Clear any buffered output
if (ob_get_level()) {
    ob_end_clean();
}

// Set secure headers for file download
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Disable caching
session_cache_limiter('nocache');

// Stream file to client
$handle = @fopen($fullPath, 'rb');
if ($handle === false) {
    sendError("Unable to open file", 500);
}

// Stream in chunks to handle large files
$chunkSize = 8192;
while (!feof($handle)) {
    $chunk = @fread($handle, $chunkSize);
    if ($chunk === false) {
        break;
    }
    echo $chunk;
}
@fclose($handle);

exit();

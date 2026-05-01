<?php
require_once '../../includes/session.php';
error_reporting(0);
ini_set('display_errors', 0);

// Include database connection
require_once __DIR__ . "/../db_connect.php";

/**
 * API Endpoint: GET /api/resources.php
 * Secure book listing with search, filter, and pagination
 * 
 * Query Parameters:
 * - page (int): Page number, default 1
 * - limit (int): Items per page, default 20 (max 50)
 * - search (string): Search in title/description
 * - user_id (int): Filter by uploader
 */

// CORS Headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["success" => false, "error" => "Method Not Allowed"]);
    exit();
}

// Helper function to send JSON responses
function sendJson($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload);
    exit();
}

// Check database connection
if (!isset($conn) || !$conn) {
    sendJson(["success" => false, "error" => "Service temporarily unavailable"], 503);
}

// Get and validate query parameters
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, (int) $_GET['limit'])) : 20;
$offset = ($page - 1) * $limit;

// Validate search input (max 100 chars)
$search = isset($_GET['search']) ? trim(substr($_GET['search'], 0, 100)) : '';
$userId = isset($_GET['user_id']) ? max(0, (int) $_GET['user_id']) : null;

// Build WHERE clause with proper escaping
$whereConditions = [];

if ($search) {
    $searchTerm = mysqli_real_escape_string($conn, $search);
    $whereConditions[] = "(r.title LIKE '%{$searchTerm}%' OR r.description LIKE '%{$searchTerm}%')";
}

if ($userId && $userId > 0) {
    $whereConditions[] = "r.user_id = {$userId}";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get total count
$countQuery = "SELECT COUNT(*) AS total FROM resources r {$whereClause}";
$countResult = @mysqli_query($conn, $countQuery);

if (!$countResult) {
    sendJson(["success" => false, "error" => "Unable to fetch data"], 500);
}

$totalCount = (int) (mysqli_fetch_assoc($countResult)['total'] ?? 0);
$totalPages = max(1, ceil($totalCount / $limit));

// Ensure page doesn't exceed total pages
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Fetch resources
$query = "
    SELECT 
        r.id,
        r.user_id,
        r.title,
        r.file_path,
        r.description,
        r.created_at
    FROM resources r
    {$whereClause}
    ORDER BY r.created_at DESC
    LIMIT {$limit} OFFSET {$offset}
";

$result = @mysqli_query($conn, $query);

if (!$result) {
    sendJson(["success" => false, "error" => "Unable to fetch resources"], 500);
}

// Build resources array
$resources = [];
while ($row = mysqli_fetch_assoc($result)) {
    $descriptionPreview = $row['description'] ? substr($row['description'], 0, 100) : '';
    $resources[] = [
        "id" => (int) $row['id'],
        "title" => htmlspecialchars($row['title'] ?? '', ENT_QUOTES, 'UTF-8'),
        "description" => htmlspecialchars($descriptionPreview, ENT_QUOTES, 'UTF-8'),
        "file_path" => htmlspecialchars($row['file_path'] ?? '', ENT_QUOTES, 'UTF-8'),
        "user_id" => (int) $row['user_id'],
        "created_at" => $row['created_at'] ?? '',
        "download_url" => "download.php?id=" . (int) $row['id']
    ];
}

// Send successful response
sendJson([
    "success" => true,
    "data" => $resources,
    "pagination" => [
        "current_page" => $page,
        "total_pages" => $totalPages,
        "total_resources" => $totalCount,
        "per_page" => $limit,
        "has_next" => $page < $totalPages,
        "has_prev" => $page > 1
    ],
    "api_version" => "1.0"
]);
?>

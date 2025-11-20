<?php
declare(strict_types=1);

// Suppress output that might interfere with JSON responses
ob_start();

// Set error reporting (disable display errors in production, enable for debugging)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

$dbHost = 'localhost';
$dbName = 'sunrise_hospital';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed. Please check your database configuration.',
        'details' => $e->getMessage(),
    ]);
    exit;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed',
        ]);
        exit;
    }
}

function json_success(array $data = []): void
{
    // Clean any output that might have been generated
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(array_merge(['status' => 'success'], $data));
    exit;
}

function json_error(string $message, int $code = 400, array $data = []): void
{
    // Clean any output that might have been generated
    ob_clean();
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'status' => 'error',
        'message' => $message,
    ], $data));
    exit;
}
?>
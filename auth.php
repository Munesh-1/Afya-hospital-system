<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function require_auth(): void
{
    if (empty($_SESSION['user_id'])) {
        json_error('Authentication required', 401);
    }
}

function current_user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

// Add a function to check auth status for AJAX calls
function check_auth(): array
{
    if (empty($_SESSION['user_id'])) {
        return ['authenticated' => false];
    }
    
    return [
        'authenticated' => true,
        'user_id' => (int)$_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? ''
    ];
}

// Optional: Add a route to check authentication status
if (isset($_GET['check'])) {
    header('Content-Type: application/json');
    echo json_encode(check_auth());
    exit;
}
?>
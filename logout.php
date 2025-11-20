<?php
declare(strict_types=1);

session_start();
$_SESSION = [];
session_destroy();

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Logged out successfully.',
]);


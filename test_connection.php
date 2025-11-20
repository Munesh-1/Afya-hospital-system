<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$result = [
    'status' => 'success',
    'checks' => []
];

// Check session
$result['checks']['session'] = [
    'active' => session_status() === PHP_SESSION_ACTIVE,
    'user_id' => $_SESSION['user_id'] ?? null,
    'full_name' => $_SESSION['full_name'] ?? null
];

// Check database connection
try {
    $testQuery = $pdo->query("SELECT 1");
    $result['checks']['database'] = [
        'connected' => true,
        'database' => 'sunrise_hospital'
    ];
    
    // Check if tables exist
    $tables = ['users', 'patients', 'appointments', 'bills'];
    $existingTables = [];
    foreach ($tables as $table) {
        try {
            $pdo->query("SELECT 1 FROM $table LIMIT 1");
            $existingTables[] = $table;
        } catch (PDOException $e) {
            // Table doesn't exist
        }
    }
    $result['checks']['tables'] = [
        'expected' => $tables,
        'existing' => $existingTables,
        'all_exist' => count($existingTables) === count($tables)
    ];
} catch (PDOException $e) {
    $result['checks']['database'] = [
        'connected' => false,
        'error' => $e->getMessage()
    ];
    $result['status'] = 'error';
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>


<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_auth();

try {
    $stmt = $pdo->prepare('SELECT id, full_name, username, email, phone FROM users WHERE id = :user_id');
    $stmt->execute([':user_id' => current_user_id()]);
    $user = $stmt->fetch();
    
    if (!$user) {
        json_error('User not found', 404);
    }
    
    json_success([
        'user' => [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
        ]
    ]);
} catch (PDOException $e) {
    error_log("Get user info error: " . $e->getMessage());
    json_error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("Get user info error: " . $e->getMessage());
    json_error('Error loading user info: ' . $e->getMessage(), 500);
}
?>


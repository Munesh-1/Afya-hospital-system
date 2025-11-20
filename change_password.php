<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_post();
require_auth();

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$userId = current_user_id();

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    json_error('All password fields are required.');
}

if ($newPassword !== $confirmPassword) {
    json_error('New password and confirmation do not match.');
}

$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
    json_error('Current password is incorrect.', 401);
}

$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

$update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
$update->execute([':hash' => $newHash, ':id' => $userId]);

json_success(['message' => 'Password updated successfully.']);


<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_post();
require_auth();

$fullName = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$userId = current_user_id();

if ($fullName === '' || $username === '' || $email === '') {
    json_error('Full name, username, and email are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Invalid email address.');
}

$stmt = $pdo->prepare('
    SELECT id FROM users
    WHERE (username = :username OR email = :email) AND id <> :id
');
$stmt->execute([
    ':username' => $username,
    ':email' => $email,
    ':id' => $userId,
]);

if ($stmt->fetch()) {
    json_error('Username or email already taken by another user.');
}

$stmt = $pdo->prepare('
    UPDATE users
    SET full_name = :full_name,
        username = :username,
        email = :email,
        phone = :phone
    WHERE id = :id
');

$stmt->execute([
    ':full_name' => $fullName,
    ':username' => $username,
    ':email' => $email,
    ':phone' => $phone ?: null,
    ':id' => $userId,
]);

$_SESSION['full_name'] = $fullName;

json_success(['message' => 'Profile updated successfully.']);


<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_post();

$fullName = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($fullName === '' || $username === '' || $password === '' || $email === '') {
    json_error('Full name, username, email, and password are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Invalid email address.');
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username OR email = :email');
$stmt->execute([':username' => $username, ':email' => $email]);
if ($stmt->fetchColumn() > 0) {
    json_error('Username or email already exists.');
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('
    INSERT INTO users (full_name, username, password_hash, email, phone)
    VALUES (:full_name, :username, :password_hash, :email, :phone)
');

$stmt->execute([
    ':full_name' => $fullName,
    ':username' => $username,
    ':password_hash' => $hash,
    ':email' => $email,
    ':phone' => $phone ?: null,
]);

json_success(['message' => 'Account created successfully.']);


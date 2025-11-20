<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_post();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    json_error('Username and password are required.');
}

$stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM users WHERE username = :username');
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_error('Invalid username or password.', 401);
}

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['full_name'] = $user['full_name'];

json_success([
    'message' => 'Login successful.',
    'user' => [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'username' => $username,
    ],
]);


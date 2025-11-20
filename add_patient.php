<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_post();
require_auth();

$fullName = trim($_POST['full_name'] ?? '');
$age = (int)($_POST['age'] ?? 0);
$gender = $_POST['gender'] ?? '';
$contact = trim($_POST['contact'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$medicalHistory = trim($_POST['medical_history'] ?? '');

$allowedGenders = ['Male', 'Female', 'Other'];

if ($fullName === '' || $age <= 0 || !in_array($gender, $allowedGenders, true) || $contact === '') {
    json_error('Full name, age, gender, and contact are required.');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Invalid email address.');
}

$stmt = $pdo->prepare('
    INSERT INTO patients
        (full_name, age, gender, contact, email, address, medical_history, created_by)
    VALUES
        (:full_name, :age, :gender, :contact, :email, :address, :medical_history, :created_by)
');

$stmt->execute([
    ':full_name' => $fullName,
    ':age' => $age,
    ':gender' => $gender,
    ':contact' => $contact,
    ':email' => $email ?: null,
    ':address' => $address ?: null,
    ':medical_history' => $medicalHistory ?: null,
    ':created_by' => current_user_id(),
]);

json_success(['message' => 'Patient registered successfully.']);


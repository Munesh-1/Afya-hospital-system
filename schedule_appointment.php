<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_post();
require_auth();

$patientId = (int)($_POST['patient_id'] ?? 0);
$doctor = trim($_POST['doctor'] ?? '');
$department = trim($_POST['department'] ?? '');
$appointmentDate = $_POST['appointment_date'] ?? '';
$appointmentTime = $_POST['appointment_time'] ?? '';
$status = $_POST['status'] ?? 'Scheduled';
$notes = trim($_POST['notes'] ?? '');

$allowedStatus = ['Scheduled', 'Confirmed', 'Completed', 'Cancelled'];

if ($patientId <= 0 || $doctor === '' || $department === '' || $appointmentDate === '' || $appointmentTime === '') {
    json_error('All required fields must be provided.');
}

if (!in_array($status, $allowedStatus, true)) {
    $status = 'Scheduled';
}

$patientCheck = $pdo->prepare('SELECT id FROM patients WHERE id = :id');
$patientCheck->execute([':id' => $patientId]);

if (!$patientCheck->fetch()) {
    json_error('Patient not found.', 404);
}

$stmt = $pdo->prepare('
    INSERT INTO appointments
        (patient_id, doctor, department, appointment_date, appointment_time, status, notes, created_by)
    VALUES
        (:patient_id, :doctor, :department, :appointment_date, :appointment_time, :status, :notes, :created_by)
');

$stmt->execute([
    ':patient_id' => $patientId,
    ':doctor' => $doctor,
    ':department' => $department,
    ':appointment_date' => $appointmentDate,
    ':appointment_time' => $appointmentTime,
    ':status' => $status,
    ':notes' => $notes ?: null,
    ':created_by' => current_user_id(),
]);

json_success(['message' => 'Appointment scheduled successfully.']);


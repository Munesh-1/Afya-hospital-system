<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_post();
require_auth();

$patientId = (int)($_POST['patient_id'] ?? 0);
$serviceType = trim($_POST['service_type'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? '';
$status = $_POST['status'] ?? 'Pending';
$billingDate = $_POST['billing_date'] ?? '';
$description = trim($_POST['description'] ?? '');

$allowedPayment = ['Cash', 'MPesa', 'Card', 'Insurance', 'Other'];
$allowedStatus = ['Pending', 'Paid', 'Partial'];

if ($patientId <= 0 || $serviceType === '' || $amount <= 0 || $billingDate === '' || !in_array($paymentMethod, $allowedPayment, true)) {
    json_error('All required fields must be provided.');
}

if (!in_array($status, $allowedStatus, true)) {
    $status = 'Pending';
}

$patientCheck = $pdo->prepare('SELECT id FROM patients WHERE id = :id');
$patientCheck->execute([':id' => $patientId]);

if (!$patientCheck->fetch()) {
    json_error('Patient not found.', 404);
}

$stmt = $pdo->prepare('
    INSERT INTO bills
        (patient_id, service_type, amount, payment_method, status, billing_date, description, created_by)
    VALUES
        (:patient_id, :service_type, :amount, :payment_method, :status, :billing_date, :description, :created_by)
');

$stmt->execute([
    ':patient_id' => $patientId,
    ':service_type' => $serviceType,
    ':amount' => $amount,
    ':payment_method' => $paymentMethod,
    ':status' => $status,
    ':billing_date' => $billingDate,
    ':description' => $description ?: null,
    ':created_by' => current_user_id(),
]);

$result = ['message' => 'Bill generated successfully.'];

// If payment method is MPesa, initiate STK push to the patient's phone (if available)
if ($paymentMethod === 'MPesa') {
    // get patient contact
    $pstmt = $pdo->prepare('SELECT contact, full_name FROM patients WHERE id = :id');
    $pstmt->execute([':id' => $patientId]);
    $patient = $pstmt->fetch();

    if ($patient && !empty($patient['contact'])) {
        // attempt to call M-Pesa helper
        try {
            require_once __DIR__ . '/mpesa.php';
            $phone = $patient['contact'];
            $accountRef = 'Bill-' . time();
            $mpesaResponse = mpesa_stk_push($phone, $amount, $accountRef, $description ?: 'Bill Payment');
            $result['mpesa'] = $mpesaResponse;
        } catch (Throwable $e) {
            // log and include error in response
            error_log('MPesa initiation failed: ' . $e->getMessage());
            $result['mpesa'] = ['success' => false, 'message' => 'MPesa initiation error'];
        }
    } else {
        $result['mpesa'] = ['success' => false, 'message' => 'Patient phone number not available'];
    }
}

json_success($result);


<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_auth();

try {
    $stmt = $pdo->query("
        SELECT b.id,
               p.full_name AS patient_name,
               b.service_type,
               b.amount,
               b.status,
               b.billing_date
        FROM bills b
        JOIN patients p ON p.id = b.patient_id
        ORDER BY b.billing_date DESC, b.id DESC
        LIMIT 50
    ");

    $bills = $stmt->fetchAll();

    json_success(['bills' => $bills]);
} catch (PDOException $e) {
    error_log("Billing list error: " . $e->getMessage());
    json_error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("Billing list error: " . $e->getMessage());
    json_error('Error loading billing records: ' . $e->getMessage(), 500);
}
?>
<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_auth();

try {
    $stmt = $pdo->query("
        SELECT id,
               full_name,
               age,
               gender,
               contact,
               email
        FROM patients
        ORDER BY created_at DESC
        LIMIT 100
    ");

    $patients = $stmt->fetchAll();

    json_success(['patients' => $patients]);
} catch (PDOException $e) {
    error_log("Patients list error: " . $e->getMessage());
    json_error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("Patients list error: " . $e->getMessage());
    json_error('Error loading patients: ' . $e->getMessage(), 500);
}
?>
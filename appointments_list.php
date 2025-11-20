<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_auth();

try {
    $date = $_GET['date'] ?? date('Y-m-d');
    $params = [];
    $where = '';

    if ($date) {
        $where = 'WHERE appointment_date = :date';
        $params[':date'] = $date;
    }

    $stmt = $pdo->prepare("
        SELECT a.id,
               p.full_name AS patient_name,
               a.doctor,
               a.department,
               a.appointment_date,
               TIME_FORMAT(a.appointment_time, '%H:%i') AS appointment_time,
               a.status
        FROM appointments a
        JOIN patients p ON p.id = a.patient_id
        $where
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 50
    ");
    $stmt->execute($params);

    $appointments = $stmt->fetchAll();

    json_success(['appointments' => $appointments]);
} catch (PDOException $e) {
    error_log("Appointments list error: " . $e->getMessage());
    json_error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("Appointments list error: " . $e->getMessage());
    json_error('Error loading appointments: ' . $e->getMessage(), 500);
}
?>
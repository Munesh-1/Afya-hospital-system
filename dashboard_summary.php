<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_auth();

try {
    // Patients today
    $patientsTodayStmt = $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()");
    $patientsToday = (int)$patientsTodayStmt->fetchColumn();

    // Appointments today
    $appointmentsTodayStmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()");
    $appointmentsToday = (int)$appointmentsTodayStmt->fetchColumn();

    // Total payments today
    $paymentsStmt = $pdo->query("
        SELECT COALESCE(SUM(amount), 0)
        FROM bills
        WHERE status = 'Paid'
          AND billing_date = CURDATE()
    ");
    $totalPayments = (float)$paymentsStmt->fetchColumn();

    // Recent appointments
    $recentAppointmentsStmt = $pdo->query("
        SELECT a.id,
               p.full_name AS patient_name,
               a.doctor,
               TIME_FORMAT(a.appointment_time, '%H:%i') AS time,
               a.status
        FROM appointments a
        JOIN patients p ON p.id = a.patient_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 5
    ");
    $recentAppointments = $recentAppointmentsStmt->fetchAll();

    json_success([
        'patients_today' => $patientsToday,
        'appointments_today' => $appointmentsToday,
        'total_payments' => $totalPayments,
        'emergencies' => 0, // You can implement this later
        'recent_appointments' => $recentAppointments,
    ]);
} catch (PDOException $e) {
    error_log("Dashboard summary error: " . $e->getMessage());
    json_error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("Dashboard summary error: " . $e->getMessage());
    json_error('Error loading dashboard data: ' . $e->getMessage(), 500);
}
?>
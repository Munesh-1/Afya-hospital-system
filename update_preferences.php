<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_post();
require_auth();

$emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
$smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
$autoBackup = isset($_POST['auto_backup']) ? 1 : 0;
$userId = current_user_id();

$stmt = $pdo->prepare('
    INSERT INTO user_preferences (user_id, email_notifications, sms_notifications, auto_backup)
    VALUES (:user_id, :email_notifications, :sms_notifications, :auto_backup)
    ON DUPLICATE KEY UPDATE
        email_notifications = VALUES(email_notifications),
        sms_notifications = VALUES(sms_notifications),
        auto_backup = VALUES(auto_backup)
');

$stmt->execute([
    ':user_id' => $userId,
    ':email_notifications' => $emailNotifications,
    ':sms_notifications' => $smsNotifications,
    ':auto_backup' => $autoBackup,
]);

json_success(['message' => 'Preferences updated successfully.']);


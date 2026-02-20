<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notifier.php';

$pdo = db();
$stmt = $pdo->query(
    'SELECT ar.id, ar.appointment_at, u.email, k.full_name, k.phone_number
     FROM appointment_reminders ar
     INNER JOIN users u ON u.id = ar.user_id
     LEFT JOIN (
        SELECT s1.*
        FROM kyc_submissions s1
        INNER JOIN (
            SELECT user_id, MAX(id) AS max_id FROM kyc_submissions GROUP BY user_id
        ) s2 ON s1.user_id = s2.user_id AND s1.id = s2.max_id
     ) k ON k.user_id = u.id
     WHERE ar.appointment_at <= DATE_ADD(NOW(), INTERVAL 1 DAY)
       AND (ar.reminder_email_sent = 0 OR ar.reminder_sms_sent = 0)'
);

$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $id = (int)$row['id'];
    $emailBody = "Hi {$row['full_name']},\n\nReminder: your appointment is scheduled on {$row['appointment_at']}.\nPlease reply if you need to reschedule.";
    $smsBody = "Reminder from " . APP_NAME . ": appointment on {$row['appointment_at']}.";

    $emailSent = send_gmail_notification((string)$row['email'], APP_NAME . ' appointment reminder', $emailBody);
    $smsSent = send_sms_reminder((string)$row['phone_number'], $smsBody);

    $update = $pdo->prepare('UPDATE appointment_reminders SET reminder_email_sent = ?, reminder_sms_sent = ?, updated_at = NOW() WHERE id = ?');
    $update->execute([$emailSent ? 1 : 0, $smsSent ? 1 : 0, $id]);
}

echo 'Processed reminders: ' . count($rows) . PHP_EOL;

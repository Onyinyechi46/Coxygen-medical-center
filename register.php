<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notifier.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload.']);
    exit;
}

$email = strtolower(trim((string)($input['email'] ?? '')));
$password = (string)($input['password'] ?? '');
$fullName = trim((string)($input['full_name'] ?? ''));
$phone = trim((string)($input['phone_number'] ?? ''));
$appointmentDate = trim((string)($input['appointment_date'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $fullName === '' || $phone === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'email, password (min 8 chars), full_name, and phone_number are required.']);
    exit;
}

$pdo = db();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, email_verified_at) VALUES (?, ?, NOW())');
    $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);

    $userId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO kyc_submissions (user_id, full_name, phone_number, country, status) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $fullName, $phone, 'Unspecified', 'approved']);

    if ($appointmentDate !== '') {
        $stmt = $pdo->prepare(
            'INSERT INTO appointment_reminders (user_id, appointment_at, reminder_email_sent, reminder_sms_sent) VALUES (?, ?, 0, 0)'
        );
        $stmt->execute([$userId, date('Y-m-d H:i:s', strtotime($appointmentDate))]);
    }

    $pdo->commit();

    $_SESSION['user_id'] = $userId;

    $welcomeMessage = "Hi {$fullName},\n\nThank you for registering with " . APP_NAME . ".\n\nOur services include medical consultation, appointment scheduling, and healthcare financing support.\n\nReply to this email and our team will assist you directly.";
    send_gmail_notification($email, APP_NAME . ' registration confirmation', $welcomeMessage);

    send_gmail_notification(
        ADMIN_NOTIFY_EMAIL,
        'New patient registered',
        "A new patient was registered.\nName: {$fullName}\nEmail: {$email}\nPhone: {$phone}"
    );

    echo json_encode(['ok' => true, 'user_id' => $userId]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Email already registered.']);
        exit;
    }

    error_log('register error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}

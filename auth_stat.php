<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$user = current_user();
if (!$user) {
    echo json_encode([
        'ok' => true,
        'authenticated' => false,
        'email_verified' => false,
        'kyc_status' => null,
    ]);
    exit;
}

$submission = latest_kyc_submission((int)$user['id']);

echo json_encode([
    'ok' => true,
    'authenticated' => true,
    'user' => [
        'id' => (int)$user['id'],
        'email' => (string)$user['email'],
    ],
    'email_verified' => !empty($user['email_verified_at']),
    'kyc_status' => $submission['status'] ?? null,
]);

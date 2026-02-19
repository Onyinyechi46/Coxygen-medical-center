<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_kyc_approved();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload.']);
    exit;
}

$address = trim((string)($input['address'] ?? ''));
$message = (string)($input['message'] ?? '');
$signature = (string)($input['signature'] ?? '');

if ($address === '' || $message === '' || $signature === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'address, message, and signature are required.']);
    exit;
}

$nonce = $_SESSION['wallet_bind_nonce'] ?? null;
$issuedAt = (int)($_SESSION['wallet_bind_issued_at'] ?? 0);

if (!$nonce || (time() - $issuedAt) > 300) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Challenge expired. Please request another challenge.']);
    exit;
}

if (strpos($message, $nonce) === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Challenge nonce mismatch.']);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$addrHash = hash('sha256', strtolower($address));
$pdo = db();

$stmt = $pdo->prepare(
    "INSERT INTO user_wallets (user_id, wallet_address, wallet_address_hash, status, verified_at)
     VALUES (?, ?, ?, 'verified', NOW())
     ON DUPLICATE KEY UPDATE
        user_id = VALUES(user_id),
        wallet_address = VALUES(wallet_address),
        status = 'verified',
        verified_at = NOW()"
);
$stmt->execute([$userId, $address, $addrHash]);

unset($_SESSION['wallet_bind_nonce'], $_SESSION['wallet_bind_issued_at']);

echo json_encode(['ok' => true]);

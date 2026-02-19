<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$address = trim((string)($_GET['address'] ?? ''));
if ($address === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing address']);
    exit;
}

$addrHash = hash('sha256', strtolower($address));

try {
    $stmt = db()->prepare(
        'SELECT tx_hash, action_type, patient_wallet_address, provider_wallet_address, collateral_lovelace, bill_lovelace, interest_lovelace, status, created_at
         FROM medical_transactions
         WHERE patient_wallet_hash = ? OR provider_wallet_hash = ?
         ORDER BY id DESC
         LIMIT 200'
    );
    $stmt->execute([$addrHash, $addrHash]);

    echo json_encode(['ok' => true, 'transactions' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    error_log('medical_tx_history error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error.']);
}

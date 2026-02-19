<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

try {
    $limit = (int)($_GET['limit'] ?? 10);
    if ($limit <= 0) {
        $limit = 10;
    }
    if ($limit > 100) {
        $limit = 100;
    }

    $stmt = db()->prepare(
        'SELECT tx_hash, action_type, patient_wallet_address, provider_wallet_address, collateral_lovelace, bill_lovelace, interest_lovelace, status, created_at
         FROM medical_transactions
         ORDER BY id DESC
         LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'ok' => true,
        'transactions' => $stmt->fetchAll()
    ]);
} catch (Throwable $e) {
    error_log('medical_recent_transactions error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error.']);
}

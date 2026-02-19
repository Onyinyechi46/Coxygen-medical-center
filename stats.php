<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

try {
    $pdo = db();

    $totalCredits = (int)$pdo->query(
        "SELECT COUNT(*) AS total FROM medical_transactions WHERE action_type = 'open_credit'"
    )->fetchColumn();

    $totalBills = (string)$pdo->query(
        "SELECT COALESCE(SUM(CAST(bill_lovelace AS SIGNED)), 0) FROM medical_transactions WHERE action_type = 'open_credit'"
    )->fetchColumn();

    $totalRepaid = (string)$pdo->query(
        "SELECT COALESCE(SUM(CAST(bill_lovelace AS SIGNED) + CAST(interest_lovelace AS SIGNED)), 0) FROM medical_transactions WHERE action_type = 'repay_credit'"
    )->fetchColumn();

    $activeCredits = (int)$pdo->query(
        "SELECT GREATEST(
            (SELECT COUNT(*) FROM medical_transactions WHERE action_type = 'open_credit') -
            (SELECT COUNT(*) FROM medical_transactions WHERE action_type = 'repay_credit'),
            0
        )"
    )->fetchColumn();

    echo json_encode([
        'ok' => true,
        'total_open_credits' => $totalCredits,
        'active_credits' => $activeCredits,
        'total_bills_lovelace' => $totalBills,
        'total_repaid_lovelace' => $totalRepaid
    ]);
} catch (Throwable $e) {
    error_log('medical_stats error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error.']);
}

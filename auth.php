<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, email, email_verified_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function latest_kyc_submission(int $userId): ?array
{
    $stmt = db()->prepare('SELECT id, status, submitted_at FROM kyc_submissions WHERE user_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
        exit;
    }

    return $user;
}

function require_kyc_approved(): void
{
    $user = require_auth();
    $submission = latest_kyc_submission((int)$user['id']);

    if (($submission['status'] ?? null) !== 'approved') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'KYC approval required']);
        exit;
    }
}

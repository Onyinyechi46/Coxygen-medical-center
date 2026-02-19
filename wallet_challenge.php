<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

require_kyc_approved();

$nonce = bin2hex(random_bytes(16));
$_SESSION['wallet_bind_nonce'] = $nonce;
$_SESSION['wallet_bind_issued_at'] = time();

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'nonce' => $nonce,
    'message' => "Coxygen Medical Center wallet binding\nNonce: {$nonce}\nUser: " . ($_SESSION['user_id'] ?? '') . "\nIssuedAt: " . date('c')
]);

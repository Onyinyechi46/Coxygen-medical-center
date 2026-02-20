<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload.']);
    exit;
}

$email = strtolower(trim((string)($input['email'] ?? '')));
$password = (string)($input['password'] ?? '');

$stmt = db()->prepare('SELECT id, email, password_hash FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, (string)$user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Invalid credentials']);
    exit;
}

$_SESSION['user_id'] = (int)$user['id'];

echo json_encode(['ok' => true, 'user' => ['id' => (int)$user['id'], 'email' => (string)$user['email']]]);

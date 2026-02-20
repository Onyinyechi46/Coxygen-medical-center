<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');
require_auth();

$stmt = db()->query(
    'SELECT u.id, u.email, u.created_at, k.full_name, k.phone_number
     FROM users u
     LEFT JOIN (
        SELECT s1.*
        FROM kyc_submissions s1
        INNER JOIN (
            SELECT user_id, MAX(id) AS max_id FROM kyc_submissions GROUP BY user_id
        ) s2 ON s1.user_id = s2.user_id AND s1.id = s2.max_id
     ) k ON k.user_id = u.id
     ORDER BY u.id DESC'
);

echo json_encode(['ok' => true, 'registered_users' => $stmt->fetchAll()]);

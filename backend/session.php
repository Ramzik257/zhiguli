<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user = current_user();

echo json_encode([
    'authenticated' => $user !== null,
    'name' => $user['name'] ?? null,
], JSON_UNESCAPED_UNICODE);

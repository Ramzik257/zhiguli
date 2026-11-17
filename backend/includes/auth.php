<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_app_session(): void
{
    $config = require __DIR__ . '/config.php';

    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name($config['session_name']);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

function csrf_token(): string
{
    start_app_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    start_app_session();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function current_user(): ?array
{
    start_app_session();

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function login_user(int $userId): void
{
    start_app_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    start_app_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function text_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return (int) mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => normalize_email($email)]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function register_user(string $name, string $email, string $password, string $phone): string|true
{
    $name = trim($name);
    $email = normalize_email($email);
    $password = trim($password);
    $phone = trim($phone);
    $phoneDigits = preg_replace('/\D+/', '', $phone) ?? '';

    if (text_length($name) < 2) {
        return 'Ник должен быть не короче 2 символов.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Введите корректный e-mail.';
    }

    if (text_length($password) < 8) {
        return 'Пароль должен быть не короче 8 символов.';
    }

    if (strlen($phoneDigits) < 10) {
        return 'Введите корректный номер телефона.';
    }

    if (find_user_by_email($email)) {
        return 'Пользователь с таким e-mail уже зарегистрирован.';
    }

    $stmt = db()->prepare(
        'INSERT INTO users (name, email, phone, password_hash)
         VALUES (:name, :email, :phone, :password_hash)'
    );

    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    login_user((int) db()->lastInsertId());

    return true;
}

function attempt_login(string $email, string $password): string|true
{
    $email = normalize_email($email);
    $password = trim($password);

    if ($email === '' || $password === '') {
        return 'Введите e-mail и пароль.';
    }

    $user = find_user_by_email($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return 'Неверный e-mail или пароль.';
    }

    login_user((int) $user['id']);

    return true;
}

function require_login(): array
{
    $user = current_user();

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

function update_profile(int $userId, string $name, string $email, string $phone, string $currentPassword = '', string $newPassword = ''): string|true
{
    $name = trim($name);
    $email = normalize_email($email);
    $phone = trim($phone);
    $currentPassword = trim($currentPassword);
    $newPassword = trim($newPassword);
    $phoneDigits = preg_replace('/\D+/', '', $phone) ?? '';

    if (text_length($name) < 2) {
        return 'Ник должен быть не короче 2 символов.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Введите корректный e-mail.';
    }

    if (strlen($phoneDigits) < 10) {
        return 'Введите корректный номер телефона.';
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return 'Пользователь не найден.';
    }

    $emailOwner = find_user_by_email($email);
    if ($emailOwner && (int) $emailOwner['id'] !== $userId) {
        return 'Этот e-mail уже занят другим участником.';
    }

    $changePassword = $newPassword !== '';

    if ($changePassword) {
        if ($currentPassword === '' || !password_verify($currentPassword, $user['password_hash'])) {
            return 'Текущий пароль указан неверно.';
        }

        if (text_length($newPassword) < 8) {
            return 'Новый пароль должен быть не короче 8 символов.';
        }
    }

    if ($changePassword) {
        $update = db()->prepare(
            'UPDATE users
             SET name = :name, email = :email, phone = :phone, password_hash = :password_hash
             WHERE id = :id'
        );
        $update->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);
    } else {
        $update = db()->prepare(
            'UPDATE users
             SET name = :name, email = :email, phone = :phone
             WHERE id = :id'
        );
        $update->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'id' => $userId,
        ]);
    }

    return true;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

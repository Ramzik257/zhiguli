<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$user = require_login();
$error = '';
$success = '';

$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Сессия устарела. Обновите страницу и попробуйте снова.';
    } else {
        $name = (string) ($_POST['name'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $phone = (string) ($_POST['phone'] ?? '');
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');

        $result = update_profile((int) $user['id'], $name, $email, $phone, $currentPassword, $newPassword);

        if ($result === true) {
            $success = $newPassword !== ''
                ? 'Данные и пароль обновлены.'
                : 'Данные профиля сохранены.';
            $user = require_login();
            $name = $user['name'];
            $email = $user['email'];
            $phone = $user['phone'];
        } else {
            $error = $result;
        }
    }
}

$memberSince = '';
if (!empty($user['created_at'])) {
    $timestamp = strtotime((string) $user['created_at']);
    if ($timestamp !== false) {
        $memberSince = date('d.m.Y', $timestamp);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль | Клуб любителей Жигули</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Russo+One&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/prof.css">
</head>

<body>
    <header class="header">
        <div class="container header__inner">
            <nav class="header__nav" aria-label="Основная навигация">
                <a href="../html/models.html" class="header__link">Модели и характеристики</a>
                <a href="../html/contacts.html" class="header__link">Контакты</a>
                <a href="../html/about.html" class="header__link">О нас</a>
                <a href="../html/main.html" class="header__logo">ЖИГУЛЬ</a>
                <a href="../html/certificate.html" class="header__link">Сертификат</a>
                <a href="#" class="header__link">Проекты и тюнинг</a>
                <a href="profile.php" class="header__link header__link--active">Профиль</a>
            </nav>
            <button type="button" class="header__theme" id="themeBtn" aria-label="Сменить тему"></button>
        </div>
    </header>

    <main class="main">
        <section class="profile">
            <div class="container">
                <div class="profile__panel">
                    <div class="profile__top">
                        <div>
                            <p class="profile__eyebrow">Личный кабинет</p>
                            <h1 class="profile__title"><?= e($user['name']) ?></h1>
                            <?php if ($memberSince !== ''): ?>
                                <p class="profile__meta">В клубе с <?= e($memberSince) ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="../html/main.html" class="profile__back">На главную</a>
                    </div>

                    <form class="profile__form" method="post" action="profile.php" novalidate>
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                        <div class="profile__group">
                            <label class="profile__label" for="name">Ник</label>
                            <input class="profile__input" type="text" id="name" name="name" required minlength="2"
                                value="<?= e($name) ?>">
                        </div>

                        <div class="profile__group">
                            <label class="profile__label" for="email">E-mail</label>
                            <input class="profile__input" type="email" id="email" name="email" required
                                value="<?= e($email) ?>">
                        </div>

                        <div class="profile__group">
                            <label class="profile__label" for="phone">Телефон</label>
                            <input class="profile__input" type="tel" id="phone" name="phone" required
                                value="<?= e($phone) ?>">
                        </div>

                        <div class="profile__divider">
                            <p class="profile__divider-title">Смена пароля</p>
                            <p class="profile__divider-text">Оставь пустым, если пароль менять не нужно.</p>
                        </div>

                        <div class="profile__group">
                            <label class="profile__label" for="current_password">Текущий пароль</label>
                            <input class="profile__input" type="password" id="current_password" name="current_password"
                                autocomplete="current-password">
                        </div>

                        <div class="profile__group">
                            <label class="profile__label" for="new_password">Новый пароль</label>
                            <input class="profile__input" type="password" id="new_password" name="new_password"
                                minlength="8" autocomplete="new-password" placeholder="Минимум 8 символов">
                        </div>

                        <?php if ($error !== ''): ?>
                            <p class="profile__error"><?= e($error) ?></p>
                        <?php endif; ?>

                        <?php if ($success !== ''): ?>
                            <p class="profile__success"><?= e($success) ?></p>
                        <?php endif; ?>

                        <div class="profile__actions">
                            <button type="submit" class="profile__btn profile__btn--primary">Сохранить</button>
                            <a href="logout.php" class="profile__btn">Выйти</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/theme.js"></script>
</body>

</html>

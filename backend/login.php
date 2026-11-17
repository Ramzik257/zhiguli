<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

start_app_session();

if (current_user()) {
    header('Location: profile.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Сессия устарела. Обновите страницу и попробуйте снова.';
    } else {
        $email = (string) ($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $result = attempt_login($email, $password);

        if ($result === true) {
            header('Location: ../html/main.html');
            exit;
        }

        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Вход | Клуб любителей Жигули</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="../css/reg.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Russo+One&display=swap"
        rel="stylesheet" />
</head>

<body>
    <div class="hero-bg" aria-hidden="true"></div>

    <div class="container">
        <form class="form" method="post" action="login.php" novalidate>
            <h1 class="form__title">Вход</h1>

            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="form__group">
                <label class="form__label" for="email">E-mail</label>
                <input class="form__input" type="email" id="email" name="email" placeholder="example@mail.ru"
                    required value="<?= e($email) ?>" />
            </div>

            <div class="form__group">
                <label class="form__label" for="password">Пароль</label>
                <input class="form__input" type="password" id="password" name="password" placeholder="Ваш пароль"
                    required minlength="8" />
            </div>

            <?php if ($error !== ''): ?>
                <p class="form__error"><?= e($error) ?></p>
            <?php endif; ?>

            <button type="submit" class="form__submit">Войти</button>

            <div class="form__login">
                Нет аккаунта? <a class="form__login-link" href="register.php">Зарегистрироваться</a>
            </div>
        </form>
    </div>
</body>

</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$user = require_login();
$error = '';
$success = '';

$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];
$city = (string) ($user['city'] ?? '');
$favoriteModel = (string) ($user['favorite_model'] ?? '');
$models = allowed_favorite_models();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Сессия устарела. Обновите страницу и попробуйте снова.';
    } else {
        $name = (string) ($_POST['name'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $phone = (string) ($_POST['phone'] ?? '');
        $city = (string) ($_POST['city'] ?? '');
        $favoriteModel = (string) ($_POST['favorite_model'] ?? '');
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');

        $result = update_profile(
            (int) $user['id'],
            $name,
            $email,
            $phone,
            $currentPassword,
            $newPassword,
            $city,
            $favoriteModel
        );

        if ($result === true) {
            $success = $newPassword !== ''
                ? 'Данные и пароль обновлены.'
                : 'Данные профиля сохранены.';
            $user = require_login();
            $name = $user['name'];
            $email = $user['email'];
            $phone = $user['phone'];
            $city = (string) ($user['city'] ?? '');
            $favoriteModel = (string) ($user['favorite_model'] ?? '');
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

$initials = '';
$parts = preg_split('/\s+/u', trim((string) $user['name'])) ?: [];
foreach (array_slice($parts, 0, 2) as $part) {
    if ($part === '') {
        continue;
    }
    if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
    } else {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
if ($initials === '') {
    $initials = 'Ж';
}

$hasGarage = $city !== '' || $favoriteModel !== '';
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
                <a href="../html/projects.html" class="header__link">Проекты и тюнинг</a>
                <a href="profile.php" class="header__link header__link--active">Профиль</a>
            </nav>
            <button type="button" class="header__theme" id="themeBtn" aria-label="Сменить тему"></button>
        </div>
    </header>

    <main class="main">
        <section class="profile">
            <div class="container">
                <div class="profile__identity">
                    <div class="profile__avatar" aria-hidden="true"><?= e($initials) ?></div>
                    <div class="profile__identity-body">
                        <p class="profile__eyebrow">Личный кабинет клуба</p>
                        <h1 class="profile__title"><?= e($user['name']) ?></h1>
                        <p class="profile__meta">
                            <?php if ($city !== ''): ?>
                                <span><?= e($city) ?></span>
                            <?php endif; ?>
                            <?php if ($favoriteModel !== ''): ?>
                                <?php if ($city !== ''): ?><span class="profile__dot" aria-hidden="true">·</span><?php endif; ?>
                                <span><?= e($favoriteModel) ?></span>
                            <?php endif; ?>
                            <?php if ($memberSince !== ''): ?>
                                <?php if ($city !== '' || $favoriteModel !== ''): ?><span class="profile__dot" aria-hidden="true">·</span><?php endif; ?>
                                <span>в клубе с <?= e($memberSince) ?></span>
                            <?php endif; ?>
                            <?php if ($city === '' && $favoriteModel === '' && $memberSince === ''): ?>
                                <span>Заполни гараж — профиль оживет</span>
                            <?php endif; ?>
                        </p>
                        <ul class="profile__tags" aria-label="Метки профиля">
                            <li class="profile__tag">Участник</li>
                            <?php if ($favoriteModel !== ''): ?>
                                <li class="profile__tag profile__tag--accent"><?= e($favoriteModel) ?></li>
                            <?php else: ?>
                                <li class="profile__tag">Модель не выбрана</li>
                            <?php endif; ?>
                            <?php if ($city !== ''): ?>
                                <li class="profile__tag"><?= e($city) ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <a href="../html/main.html" class="profile__back">На главную</a>
                </div>

                <div class="profile__layout">
                    <aside class="profile__aside">
                        <div class="profile__card">
                            <p class="profile__card-eyebrow">Гараж</p>
                            <h2 class="profile__card-title">
                                <?= $hasGarage ? 'Твой угол' : 'Пока пусто' ?>
                            </h2>
                            <?php if ($hasGarage): ?>
                                <dl class="profile__facts">
                                    <?php if ($favoriteModel !== ''): ?>
                                        <div class="profile__fact">
                                            <dt>Любимая модель</dt>
                                            <dd><?= e($favoriteModel) ?></dd>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($city !== ''): ?>
                                        <div class="profile__fact">
                                            <dt>Город</dt>
                                            <dd><?= e($city) ?></dd>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($memberSince !== ''): ?>
                                        <div class="profile__fact">
                                            <dt>С нами</dt>
                                            <dd>с <?= e($memberSince) ?></dd>
                                        </div>
                                    <?php endif; ?>
                                </dl>
                            <?php else: ?>
                                <p class="profile__card-text">
                                    Укажи город и любимую модель справа — на карточке появится
                                    твой гараж, а не только контакты.
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="profile__card">
                            <p class="profile__card-eyebrow">Быстрые ссылки</p>
                            <ul class="profile__links">
                                <li>
                                    <a href="../html/projects.html" class="profile__link">
                                        <span class="profile__link-title">Проекты и тюнинг</span>
                                        <span class="profile__link-text">Стенс, дрифт, стритуха и классика</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="../html/models.html" class="profile__link">
                                        <span class="profile__link-title">Модели и характеристики</span>
                                        <span class="profile__link-text">От «копейки» до «четырнадцатой»</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="../html/certificate.html" class="profile__link">
                                        <span class="profile__link-title">Сертификат участника</span>
                                        <span class="profile__link-text">Оформить или скачать</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="../html/contacts.html" class="profile__link">
                                        <span class="profile__link-title">Контакты клуба</span>
                                        <span class="profile__link-text">Написать или заехать</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="profile__card profile__card--tip">
                            <p class="profile__card-eyebrow">Совет</p>
                            <p class="profile__card-text">
                                Загляни в витрину проектов — там живые машины участников.
                                Может, скоро и твоя встанет рядом.
                            </p>
                        </div>
                    </aside>

                    <div class="profile__panel">
                        <div class="profile__panel-head">
                            <h2 class="profile__panel-title">Настройки профиля</h2>
                            <p class="profile__panel-lead">Контакты, гараж и пароль — всё в одном месте.</p>
                        </div>

                        <form class="profile__form" method="post" action="profile.php" novalidate>
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                            <div class="profile__divider">
                                <p class="profile__divider-title">Контакты</p>
                                <p class="profile__divider-text">Ник виден в клубе, e-mail и телефон — для связи.</p>
                            </div>

                            <div class="profile__row">
                                <div class="profile__group">
                                    <label class="profile__label" for="name">Ник</label>
                                    <input class="profile__input" type="text" id="name" name="name" required minlength="2"
                                        value="<?= e($name) ?>">
                                </div>

                                <div class="profile__group">
                                    <label class="profile__label" for="phone">Телефон</label>
                                    <input class="profile__input" type="tel" id="phone" name="phone" required
                                        value="<?= e($phone) ?>">
                                </div>
                            </div>

                            <div class="profile__group">
                                <label class="profile__label" for="email">E-mail</label>
                                <input class="profile__input" type="email" id="email" name="email" required
                                    value="<?= e($email) ?>">
                            </div>

                            <div class="profile__divider">
                                <p class="profile__divider-title">Гараж</p>
                                <p class="profile__divider-text">Необязательно, но профиль сразу выглядит живее.</p>
                            </div>

                            <div class="profile__row">
                                <div class="profile__group">
                                    <label class="profile__label" for="city">Город</label>
                                    <input class="profile__input" type="text" id="city" name="city" maxlength="100"
                                        placeholder="Например, Тольятти" value="<?= e($city) ?>">
                                </div>

                                <div class="profile__group">
                                    <label class="profile__label" for="favorite_model">Любимая модель</label>
                                    <select class="profile__input profile__select" id="favorite_model" name="favorite_model">
                                        <option value="">Не выбрана</option>
                                        <?php foreach ($models as $model): ?>
                                            <option value="<?= e($model) ?>" <?= $favoriteModel === $model ? 'selected' : '' ?>>
                                                <?= e($model) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="profile__divider">
                                <p class="profile__divider-title">Смена пароля</p>
                                <p class="profile__divider-text">Оставь пустым, если пароль менять не нужно.</p>
                            </div>

                            <div class="profile__row">
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
            </div>
        </section>
    </main>

    <button type="button" class="to-top" id="toTop" aria-label="Наверх" hidden>
        <svg class="to-top__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12 5l7 7h-4v7h-6v-7H5l7-7z" />
        </svg>
    </button>

    <script src="../js/theme.js"></script>
    <script src="../js/to-top.js"></script>
</body>

</html>

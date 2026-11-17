<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

start_app_session();

if (current_user()) {
    header('Location: ../html/main.html');
    exit;
}

header('Location: register.php');
exit;

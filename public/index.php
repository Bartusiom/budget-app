<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';

Auth::startSession();

if (Auth::check()) {
    App::redirect('dashboard.php');
}
App::redirect('login.php');
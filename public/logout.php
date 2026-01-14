<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';

Auth::startSession();
Auth::logout();

App::redirect('login.php');
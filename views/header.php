<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
Auth::startSession();
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Budżet domowy</title>
    <style>
        body{font-family:system-ui,Arial;margin:0;background:#f6f7fb;color:#111}
        header{background:#111;color:#fff;padding:12px 16px}
        nav a{color:#fff;margin-right:12px;text-decoration:none}
        main{max-width:1000px;margin:16px auto;padding:0 16px}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;margin:12px 0}
        .row{display:flex;gap:12px;flex-wrap:wrap}
        .btn{display:inline-block;padding:8px 12px;border-radius:8px;border:1px solid #111;background:#111;color:#fff;text-decoration:none;cursor:pointer}
        .btn.secondary{background:#fff;color:#111}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
        .error{background:#fee2e2;border:1px solid #ef4444;padding:10px;border-radius:8px}
        .ok{background:#dcfce7;border:1px solid #22c55e;padding:10px;border-radius:8px}
        input,select{padding:8px;border-radius:8px;border:1px solid #d1d5db;width:100%;max-width:420px}
        label{display:block;margin:10px 0 6px}
        form.inline{display:inline}
    </style>
</head>
<body>
<header>
    <nav>
        <?php if (Auth::check()): ?>
            <a href="<?= App::url('dashboard.php') ?>">Dashboard</a>
            <a href="<?= App::url('categories.php') ?>">Kategorie</a>
            <a href="<?= App::url('transactions.php') ?>">Transakcje</a>
            <a href="<?= App::url('transactions_import.php') ?>">Import CSV</a>
            <a href="<?= App::url('transactions_trash.php') ?>">Kosz</a>
            <a href="<?= App::url('logout.php') ?>">Wyloguj</a>
        <?php else: ?>
            <a href="<?= App::url('login.php') ?>">Login</a>
            <a href="<?= App::url('register.php') ?>">Rejestracja</a>
        <?php endif; ?>
    </nav>
</header>
<main>
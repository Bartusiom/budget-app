<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/TransactionRepository.php';

Auth::startSession();
Auth::requireLogin();

$id = (int)($_POST['id'] ?? 0);
$month = (string)($_POST['month'] ?? date('Y-m'));

if ($id > 0) {
    (new TransactionRepository(Db::pdo()))->restore($id, Auth::id());
}

App::redirect('transactions_trash.php?month=' . urlencode($month));
<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

Auth::startSession();
Auth::requireLogin();

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    try {
        (new CategoryRepository(Db::pdo()))->delete($id, Auth::id());
    } catch (PDOException $e) {
        // FK RESTRICT może zablokować usunięcie, jeśli są transakcje
        // celowo bez szczegółów - wracamy na listę
    }
}

App::redirect('categories.php');
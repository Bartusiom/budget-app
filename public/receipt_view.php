<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/AttachmentRepository.php';

Auth::startSession();
Auth::requireLogin();

$id = (int)($_GET['id'] ?? 0);
$repo = new AttachmentRepository(Db::pdo());

$a = $repo->findByIdForUser($id, Auth::id());
if (!$a) {
    http_response_code(404);
    exit('Not found');
}

$path = __DIR__ . '/uploads/receipts/' . $a['stored_name'];
if (!is_file($path)) {
    http_response_code(404);
    exit('File missing');
}

header('Content-Type: ' . $a['mime_type']);
header('Content-Length: ' . (string)filesize($path));
readfile($path);
exit;
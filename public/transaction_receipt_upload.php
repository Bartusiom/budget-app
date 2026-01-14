<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/TransactionRepository.php';
require_once __DIR__ . '/../src/AttachmentRepository.php';
require_once __DIR__ . '/../src/Upload.php';

Auth::startSession();
Auth::requireLogin();

$txId = (int)($_GET['id'] ?? 0);
$pdo = Db::pdo();
$txRepo = new TransactionRepository($pdo);
$attRepo = new AttachmentRepository($pdo);

$tx = $txRepo->findByIdForUser($txId, Auth::id());
if (!$tx) {
    App::redirect('transactions.php');
}

$errors = [];
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_FILES['receipt'])) {
            throw new RuntimeException('Brak pliku.');
        }

        $absDir = realpath(__DIR__ . '/uploads') ?: (__DIR__ . '/uploads');
        $absDir = rtrim($absDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'receipts';

        $meta = Upload::saveReceipt($_FILES['receipt'], $absDir);
        $attRepo->create(Auth::id(), $txId, $meta);

        $ok = 'Załącznik dodany.';
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

$attachments = $attRepo->listForTransaction($txId, Auth::id());

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Załączniki do transakcji</h1>
        <p><a class="btn secondary" href="<?= App::url('transactions.php') ?>">← Wróć</a></p>

        <div class="card">
            <strong>Data:</strong> <?= htmlspecialchars((string)$tx['happened_on']) ?><br>
            <strong>Kwota:</strong> <?= htmlspecialchars((string)$tx['amount']) ?><br>
            <strong>Opis:</strong> <?= htmlspecialchars((string)($tx['description'] ?? '')) ?>
        </div>

        <?php if ($errors): ?>
            <div class="error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <?php if ($ok): ?>
            <div class="ok"><?= htmlspecialchars($ok) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Dodaj paragon (JPG/PNG/PDF, max 5MB)</label>
            <input type="file" name="receipt" accept="image/jpeg,image/png,application/pdf" required>
            <p><button class="btn" type="submit">Wyślij</button></p>
        </form>

        <h2>Lista załączników</h2>
        <?php if (!$attachments): ?>
            <p>Brak załączników.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Nazwa</th><th>Typ</th><th>Rozmiar</th><th>Akcje</th></tr></thead>
                <tbody>
                <?php foreach ($attachments as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['original_name']) ?></td>
                        <td><?= htmlspecialchars($a['mime_type']) ?></td>
                        <td><?= (int)$a['size_bytes'] ?> B</td>
                        <td>
                            <a class="btn secondary" href="<?= App::url('receipt_view.php?id=' . (int)$a['id']) ?>" target="_blank">Otwórz</a>
                            <form class="inline" method="post" action="<?= App::url('receipt_delete.php') ?>" onsubmit="return confirm('Usunąć załącznik?');">
                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                <input type="hidden" name="tx" value="<?= (int)$txId ?>">
                                <button class="btn" type="submit">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
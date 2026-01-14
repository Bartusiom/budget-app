<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Validation.php';
require_once __DIR__ . '/../src/TransactionRepository.php';

Auth::startSession();
Auth::requireLogin();

$month = (string)($_GET['month'] ?? date('Y-m'));
if (!Validation::ymMonth($month)) $month = date('Y-m');

$from = $month . '-01';
$to = date('Y-m-t', strtotime($from));

$repo = new TransactionRepository(Db::pdo());
$rows = $repo->listForUserByRange(Auth::id(), $from, $to);

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Transakcje</h1>

        <div class="row" style="align-items:flex-end">
            <form method="get" style="flex:1;max-width:420px" novalidate>
                <label>Miesiąc</label>
                <input type="month" name="month" value="<?= htmlspecialchars($month) ?>">
                <p><button class="btn" type="submit">Filtruj</button></p>
            </form>

            <div style="padding-bottom:12px;display:flex;gap:8px;flex-wrap:wrap">
                <a class="btn" href="<?= App::url('transaction_create.php') ?>">+ Dodaj</a>
                <a class="btn secondary" href="<?= App::url('transactions_import.php') ?>">Import CSV</a>
                <a class="btn secondary" href="<?= App::url('transactions_trash.php?month=' . urlencode($month)) ?>">Kosz</a>
            </div>
        </div>

        <?php if (!$rows): ?>
            <p>Brak transakcji w tym miesiącu.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Data</th><th>Typ</th><th>Kategoria</th><th>Kwota</th><th>Opis</th><th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['happened_on']) ?></td>
                        <td><?= $t['type'] === 'income' ? 'Przychód' : 'Wydatek' ?></td>
                        <td><?= htmlspecialchars($t['category_name']) ?></td>
                        <td><?= number_format((float)$t['amount'], 2, '.', ' ') ?> zł</td>
                        <td><?= htmlspecialchars((string)$t['description']) ?></td>
                        <td>
                            <a class="btn secondary" href="<?= App::url('transaction_edit.php?id=' . (int)$t['id']) ?>">Edytuj</a>
                            <a class="btn secondary" href="<?= App::url('transaction_receipt_upload.php?id=' . (int)$t['id']) ?>">Załączniki</a>

                            <form class="inline" method="post" action="<?= App::url('transaction_delete.php') ?>" onsubmit="return confirm('Przenieść do kosza?');">
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
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
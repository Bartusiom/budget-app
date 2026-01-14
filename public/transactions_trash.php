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
$rows = $repo->listDeletedForUserByRange(Auth::id(), $from, $to);

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Kosz — usunięte transakcje</h1>

        <div class="row" style="align-items:flex-end">
            <form method="get" style="flex:1;max-width:420px" novalidate>
                <label>Miesiąc</label>
                <input type="month" name="month" value="<?= htmlspecialchars($month) ?>">
                <p><button class="btn" type="submit">Filtruj</button></p>
            </form>

            <div style="padding-bottom:12px">
                <a class="btn secondary" href="<?= App::url('transactions.php?month=' . urlencode($month)) ?>">← Wróć do transakcji</a>
            </div>
        </div>

        <?php if (!$rows): ?>
            <p>Brak usuniętych transakcji w tym miesiącu.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Data</th><th>Typ</th><th>Kategoria</th><th>Kwota</th><th>Opis</th><th>Usunięto</th><th>Akcje</th>
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
                        <td><?= htmlspecialchars((string)$t['deleted_at']) ?></td>
                        <td>
                            <form class="inline" method="post" action="<?= App::url('transaction_restore.php') ?>">
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                                <button class="btn secondary" type="submit">Przywróć</button>
                            </form>

                            <form class="inline" method="post" action="<?= App::url('transaction_force_delete.php') ?>"
                                  onsubmit="return confirm('Usunąć NA STAŁE? Tej operacji nie da się cofnąć.');">
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                                <button class="btn" type="submit">Usuń na stałe</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
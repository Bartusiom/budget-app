<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
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
$sums = $repo->sumsForUserByRange(Auth::id(), $from, $to);
$top = $repo->topExpenseCategories(Auth::id(), $from, $to, 5);

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Dashboard</h1>

        <form method="get">
            <label>Miesiąc</label>
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>">
            <p><button class="btn" type="submit">Pokaż</button></p>
        </form>

        <div class="row">
            <div class="card" style="flex:1">
                <h3>Przychody</h3>
                <p><strong><?= number_format($sums['income'], 2, '.', ' ') ?> zł</strong></p>
            </div>
            <div class="card" style="flex:1">
                <h3>Wydatki</h3>
                <p><strong><?= number_format($sums['expense'], 2, '.', ' ') ?> zł</strong></p>
            </div>
            <div class="card" style="flex:1">
                <h3>Bilans</h3>
                <p><strong><?= number_format($sums['balance'], 2, '.', ' ') ?> zł</strong></p>
            </div>
        </div>

        <h2>Top 5 kategorii wydatków</h2>
        <?php if (!$top): ?>
            <p>Brak wydatków w tym miesiącu.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Kategoria</th><th>Suma</th></tr></thead>
                <tbody>
                <?php foreach ($top as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= number_format((float)$r['sum_amount'], 2, '.', ' ') ?> zł</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
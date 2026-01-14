<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Validation.php';
require_once __DIR__ . '/../src/CategoryRepository.php';
require_once __DIR__ . '/../src/TransactionRepository.php';

Auth::startSession();
Auth::requireLogin();

$pdo = Db::pdo();
$catRepo = new CategoryRepository($pdo);
$txRepo = new TransactionRepository($pdo);

$categories = $catRepo->allByUser(Auth::id());

$errors = [];
$type = 'expense';
$amount = '';
$happened_on = date('Y-m-d');
$category_id = $categories ? (int)$categories[0]['id'] : 0;
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = (string)($_POST['type'] ?? '');
    $amount = trim((string)($_POST['amount'] ?? ''));
    $happened_on = (string)($_POST['happened_on'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim((string)($_POST['description'] ?? ''));

    if (!Validation::type($type)) $errors[] = 'Niepoprawny typ.';
    if (!Validation::money($amount)) $errors[] = 'Niepoprawna kwota.';
    if (!Validation::ymdDate($happened_on)) $errors[] = 'Niepoprawna data.';
    if (!$catRepo->existsForUser($category_id, Auth::id())) $errors[] = 'Niepoprawna kategoria.';

    if (!$errors) {
        $txRepo->create(
            Auth::id(),
            $category_id,
            $type,
            (float)$amount,
            $happened_on,
            $description !== '' ? $description : null
        );
        App::redirect('transactions.php?month=' . substr($happened_on, 0, 7));
    }
}

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Dodaj transakcję</h1>

        <?php if (!$categories): ?>
            <div class="error">
                Najpierw dodaj przynajmniej jedną kategorię:
                <a href="<?= App::url('category_create.php') ?>">Dodaj kategorię</a>
            </div>
        <?php else: ?>

            <?php if ($errors): ?>
                <div class="error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <label>Typ</label>
                <select name="type" required>
                    <option value="income" <?= $type==='income'?'selected':'' ?>>Przychód</option>
                    <option value="expense" <?= $type==='expense'?'selected':'' ?>>Wydatek</option>
                </select>

                <label>Kwota</label>
                <input type="number" name="amount" required min="0.01" step="0.01" value="<?= htmlspecialchars($amount) ?>">

                <label>Data</label>
                <input type="date" name="happened_on" required value="<?= htmlspecialchars($happened_on) ?>">

                <label>Kategoria</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$category_id === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Opis (opcjonalnie)</label>
                <input type="text" name="description" maxlength="255" value="<?= htmlspecialchars($description) ?>">

                <p>
                    <button class="btn" type="submit">Zapisz</button>
                    <a class="btn secondary" href="<?= App::url('transactions.php') ?>">Anuluj</a>
                </p>
            </form>
        <?php endif; ?>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
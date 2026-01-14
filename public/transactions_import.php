<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Validation.php';
require_once __DIR__ . '/../src/CategoryRepository.php';
require_once __DIR__ . '/../src/TransactionRepository.php';
require_once __DIR__ . '/../src/CsvImport.php';

Auth::startSession();
Auth::requireLogin();

$errors = [];
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv']) || !isset($_FILES['csv']['error']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Dodaj plik CSV.';
    } else {
        // podstawowa walidacja pliku
        $size = (int)($_FILES['csv']['size'] ?? 0);
        if ($size <= 0 || $size > 2 * 1024 * 1024) { // 2MB
            $errors[] = 'Plik za duży (max 2MB).';
        }

        $name = (string)($_FILES['csv']['name'] ?? '');
        if (!preg_match('~\.csv$~i', $name)) {
            $errors[] = 'Plik musi mieć rozszerzenie .csv';
        }

        if (!$errors) {
            $tmp = (string)$_FILES['csv']['tmp_name'];

            try {
                $rows = CsvImport::readRows($tmp);

                if (!$rows) {
                    throw new RuntimeException('Plik CSV nie zawiera danych.');
                }

                $pdo = Db::pdo();
                $catRepo = new CategoryRepository($pdo);
                $txRepo = new TransactionRepository($pdo);

                // mapowanie nazw kategorii -> id
                $cats = $catRepo->allByUser(Auth::id());
                $catMap = [];
                foreach ($cats as $c) {
                    $catMap[mb_strtolower($c['name'])] = (int)$c['id'];
                }

                $pdo->beginTransaction();

                $inserted = 0;
                foreach ($rows as $r) {
                    // Walidacje pól CSV
                    if (!Validation::ymdDate($r['date'])) {
                        throw new RuntimeException("Linia {$r['line']}: zła data (wymagane YYYY-MM-DD).");
                    }
                    if (!Validation::type($r['type'])) {
                        throw new RuntimeException("Linia {$r['line']}: zły typ (income/expense).");
                    }
                    if (!Validation::money($r['amount'])) {
                        throw new RuntimeException("Linia {$r['line']}: zła kwota (np. 25.50).");
                    }
                    if (!Validation::categoryName($r['category'])) {
                        throw new RuntimeException("Linia {$r['line']}: zła nazwa kategorii.");
                    }

                    $key = mb_strtolower($r['category']);

                    // Auto-tworzenie kategorii jeśli nie istnieje
                    if (!isset($catMap[$key])) {
                        try {
                            $catRepo->create(Auth::id(), $r['category']);
                        } catch (PDOException $e) {
                            // jeśli konflikt UNIQUE (ktoś dodał w międzyczasie) - ignoruj
                        }

                        // odśwież mapę kategorii
                        $cats2 = $catRepo->allByUser(Auth::id());
                        $catMap = [];
                        foreach ($cats2 as $c) {
                            $catMap[mb_strtolower($c['name'])] = (int)$c['id'];
                        }

                        // ważne: przelicz key po odświeżeniu mapy
                        $key = mb_strtolower($r['category']);
                    }

                    if (!isset($catMap[$key])) {
                        throw new RuntimeException("Linia {$r['line']}: nie udało się utworzyć/znaleźć kategorii.");
                    }

                    $txRepo->create(
                        Auth::id(),
                        $catMap[$key],
                        $r['type'],
                        (float)$r['amount'],
                        $r['date'],
                        ($r['description'] !== '' ? $r['description'] : null)
                    );

                    $inserted++;
                }

                $pdo->commit();
                $ok = "Zaimportowano: {$inserted} rekordów.";
            } catch (Throwable $e) {
                $pdo = Db::pdo();
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = $e->getMessage();
            }
        }
    }
}

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Import transakcji z CSV</h1>

        <p><strong>Format (separator ;):</strong></p>
        <pre>date;type;amount;category;description
2026-01-13;expense;25.50;Jedzenie;Obiad</pre>

        <p>
            <a class="btn secondary" href="<?= App::url('transactions.php') ?>">← Wróć do transakcji</a>
        </p>

        <?php if ($errors): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($ok): ?>
            <div class="ok">
                <?= htmlspecialchars($ok) ?>
                <a href="<?= App::url('transactions.php') ?>">Przejdź do transakcji</a>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Plik CSV</label>
            <input type="file" name="csv" accept=".csv,text/csv" required>
            <p><button class="btn" type="submit">Importuj</button></p>
        </form>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
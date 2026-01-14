<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Validation.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

Auth::startSession();
Auth::requireLogin();

$repo = new CategoryRepository(Db::pdo());
$id = (int)($_GET['id'] ?? 0);

$cat = $repo->findByIdForUser($id, Auth::id());
if (!$cat) {
    App::redirect('categories.php');
}

$errors = [];
$name = (string)$cat['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));

    if (!Validation::categoryName($name)) {
        $errors[] = 'Nazwa: 2-50 znaków (litery/cyfry/spacje/._-).';
    }

    if (!$errors) {
        try {
            $repo->update($id, Auth::id(), $name);
            App::redirect('categories.php');
        } catch (PDOException $e) {
            $errors[] = 'Nie udało się zapisać (może konflikt nazwy).';
        }
    }
}

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Edytuj kategorię</h1>

        <?php if ($errors): ?>
            <div class="error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>Nazwa</label>
            <input type="text" name="name" required minlength="2" maxlength="50" value="<?= htmlspecialchars($name) ?>">

            <p>
                <button class="btn" type="submit">Zapisz</button>
                <a class="btn secondary" href="<?= App::url('categories.php') ?>">Anuluj</a>
            </p>
        </form>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
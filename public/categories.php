<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

Auth::startSession();
Auth::requireLogin();

$repo = new CategoryRepository(Db::pdo());
$cats = $repo->allByUser(Auth::id());

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Kategorie</h1>
        <p><a class="btn" href="<?= App::url('category_create.php') ?>">+ Dodaj kategorię</a></p>

        <?php if (!$cats): ?>
            <p>Brak kategorii. Dodaj pierwszą.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Nazwa</th><th>Akcje</th></tr></thead>
                <tbody>
                <?php foreach ($cats as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td>
                            <a class="btn secondary" href="<?= App::url('category_edit.php?id=' . (int)$c['id']) ?>">Edytuj</a>

                            <form class="inline" method="post" action="<?= App::url('category_delete.php') ?>" onsubmit="return confirm('Usunąć kategorię?');">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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
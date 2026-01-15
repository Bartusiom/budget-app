<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/App.php';
require_once __DIR__ . '/../src/Validation.php';

Auth::startSession();

if (Auth::check()) {
    App::redirect('dashboard.php');
}

$errors = [];
$ok = '';

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if (!Validation::email($email)) $errors[] = 'Niepoprawny email.';
    if (!Validation::password($password)) $errors[] = 'Hasło min. 8 znaków i bez spacji.';
    if ($password !== $password2) $errors[] = 'Hasła nie są takie same.';

    if (!$errors) {
        $pdo = Db::pdo();

        $st = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $st->execute(['email' => $email]);
        if ($st->fetchColumn()) {
            $errors[] = 'Taki email już istnieje.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)');
            $ins->execute(['email' => $email, 'hash' => $hash]);


            $ok = 'Konto utworzone. Możesz się zalogować.';
        }
    }
}

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Rejestracja</h1>

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
                <a href="<?= App::url('login.php') ?>">Przejdź do logowania</a>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>">

            <label>Hasło</label>
            <input type="password" name="password" minlength="8" required>

            <label>Powtórz hasło</label>
            <input type="password" name="password2" minlength="8" required>

            <p>
                <button class="btn" type="submit">Utwórz konto</button>
            </p>

            <p>Masz już konto? <a href="<?= App::url('login.php') ?>">Zaloguj się</a></p>
        </form>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
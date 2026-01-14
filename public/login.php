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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!Validation::email($email)) $errors[] = 'Niepoprawny email.';
    if ($password === '') $errors[] = 'Podaj hasło.';

    if (!$errors) {
        $pdo = Db::pdo();

        $st = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email');
        $st->execute(['email' => $email]);
        $user = $st->fetch();

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            $errors[] = 'Błędny email lub hasło.';
        } else {
            Auth::login((int)$user['id']);
            App::redirect('dashboard.php');
        }
    }
}

require __DIR__ . '/../views/header.php';
?>
    <div class="card">
        <h1>Logowanie</h1>

        <?php if ($errors): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>">

            <label>Hasło</label>
            <input type="password" name="password" required>

            <p>
                <button class="btn" type="submit">Zaloguj</button>
            </p>

            <p>Nie masz konta? <a href="<?= App::url('register.php') ?>">Rejestracja</a></p>
        </form>
    </div>
<?php require __DIR__ . '/../views/footer.php'; ?>
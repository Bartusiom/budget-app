<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Db.php';

$users = [
    ['email' => 'admin@admin.com', 'password' => 'Administrator123!'],
    ['email' => 'test@test.com',   'password' => 'KontoTest11!'],
    ['email' => 'user@user.com',   'password' => 'User12345!'],
];

$pdo = Db::pdo();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$st = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)');

$created = 0;
$skipped = 0;

foreach ($users as $u) {
    $email = $u['email'];
    $pass = $u['password'];

    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $check->execute(['email' => $email]);
    if ($check->fetch()) {
        $skipped++;
        continue;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $st->execute(['email' => $email, 'hash' => $hash]);
    $created++;
}

header('Content-Type: text/plain; charset=utf-8');

echo "DONE\n";
echo "Created: {$created}\n";
echo "Skipped (already existed): {$skipped}\n\n";
echo "Test accounts:\n";
foreach ($users as $u) {
    echo "- {$u['email']} / {$u['password']}\n";
}

echo "\nIMPORTANT: delete this file: public/make_test_users.php\n";
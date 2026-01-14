<?php
declare(strict_types=1);

final class CategoryRepository
{
    public function __construct(private PDO $pdo) {}

    public function allByUser(int $userId): array
    {
        $st = $this->pdo->prepare('SELECT id, name FROM categories WHERE user_id = :uid ORDER BY name');
        $st->execute(['uid' => $userId]);
        return $st->fetchAll();
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $st = $this->pdo->prepare('SELECT id, name FROM categories WHERE id = :id AND user_id = :uid');
        $st->execute(['id' => $id, 'uid' => $userId]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function existsForUser(int $id, int $userId): bool
    {
        $st = $this->pdo->prepare('SELECT 1 FROM categories WHERE id = :id AND user_id = :uid');
        $st->execute(['id' => $id, 'uid' => $userId]);
        return (bool)$st->fetchColumn();
    }

    public function create(int $userId, string $name): void
    {
        $st = $this->pdo->prepare('INSERT INTO categories (user_id, name) VALUES (:uid, :name)');
        $st->execute(['uid' => $userId, 'name' => $name]);
    }

    public function update(int $id, int $userId, string $name): void
    {
        $st = $this->pdo->prepare('UPDATE categories SET name = :name WHERE id = :id AND user_id = :uid');
        $st->execute(['id' => $id, 'uid' => $userId, 'name' => $name]);
    }

    public function delete(int $id, int $userId): void
    {
        $st = $this->pdo->prepare('DELETE FROM categories WHERE id = :id AND user_id = :uid');
        $st->execute(['id' => $id, 'uid' => $userId]);
    }
}
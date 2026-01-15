<?php
declare(strict_types=1);

final class TransactionRepository
{
    public function __construct(private PDO $pdo) {}

    public function listForUserByRange(int $userId, string $from, string $to): array
    {
        $st = $this->pdo->prepare(
            'SELECT t.id, t.type, t.amount, t.happened_on, t.description, c.name AS category_name
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :uid
               AND t.deleted_at IS NULL
               AND t.happened_on BETWEEN :from AND :to
             ORDER BY t.happened_on DESC, t.id DESC'
        );
        $st->execute(['uid' => $userId, 'from' => $from, 'to' => $to]);
        return $st->fetchAll();
    }

    public function listDeletedForUserByRange(int $userId, string $from, string $to): array
    {
        $st = $this->pdo->prepare(
            'SELECT t.id, t.type, t.amount, t.happened_on, t.description, t.deleted_at, c.name AS category_name
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :uid
               AND t.deleted_at IS NOT NULL
               AND t.happened_on BETWEEN :from AND :to
             ORDER BY t.deleted_at DESC, t.id DESC'
        );
        $st->execute(['uid' => $userId, 'from' => $from, 'to' => $to]);
        return $st->fetchAll();
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT id, category_id, type, amount, happened_on, description
             FROM transactions
             WHERE id = :id AND user_id = :uid AND deleted_at IS NULL'
        );
        $st->execute(['id' => $id, 'uid' => $userId]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function create(int $userId, int $categoryId, string $type, float $amount, string $date, ?string $desc): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO transactions (user_id, category_id, type, amount, happened_on, description)
             VALUES (:uid, :cid, :type, :amount, :date, :desc)'
        );
        $st->execute([
            'uid' => $userId,
            'cid' => $categoryId,
            'type' => $type,
            'amount' => $amount,
            'date' => $date,
            'desc' => $desc,
        ]);
    }

    public function update(int $id, int $userId, int $categoryId, string $type, float $amount, string $date, ?string $desc): void
    {
        $st = $this->pdo->prepare(
            'UPDATE transactions
             SET category_id = :cid, type = :type, amount = :amount, happened_on = :date, description = :desc
             WHERE id = :id AND user_id = :uid AND deleted_at IS NULL'
        );
        $st->execute([
            'id' => $id,
            'uid' => $userId,
            'cid' => $categoryId,
            'type' => $type,
            'amount' => $amount,
            'date' => $date,
            'desc' => $desc,
        ]);
    }


    public function softDelete(int $id, int $userId): void
    {
        $st = $this->pdo->prepare(
            'UPDATE transactions
             SET deleted_at = NOW()
             WHERE id = :id AND user_id = :uid AND deleted_at IS NULL'
        );
        $st->execute(['id' => $id, 'uid' => $userId]);
    }


    public function restore(int $id, int $userId): void
    {
        $st = $this->pdo->prepare(
            'UPDATE transactions
             SET deleted_at = NULL
             WHERE id = :id AND user_id = :uid AND deleted_at IS NOT NULL'
        );
        $st->execute(['id' => $id, 'uid' => $userId]);
    }


    public function forceDelete(int $id, int $userId): void
    {
        $st = $this->pdo->prepare('DELETE FROM transactions WHERE id = :id AND user_id = :uid');
        $st->execute(['id' => $id, 'uid' => $userId]);
    }

    public function sumsForUserByRange(int $userId, string $from, string $to): array
    {
        $st = $this->pdo->prepare(
            'SELECT type, COALESCE(SUM(amount),0) AS sum_amount
             FROM transactions
             WHERE user_id = :uid
               AND deleted_at IS NULL
               AND happened_on BETWEEN :from AND :to
             GROUP BY type'
        );
        $st->execute(['uid' => $userId, 'from' => $from, 'to' => $to]);
        $rows = $st->fetchAll();

        $income = 0.0; $expense = 0.0;
        foreach ($rows as $r) {
            if ($r['type'] === 'income') $income = (float)$r['sum_amount'];
            if ($r['type'] === 'expense') $expense = (float)$r['sum_amount'];
        }
        return ['income' => $income, 'expense' => $expense, 'balance' => $income - $expense];
    }

    public function topExpenseCategories(int $userId, string $from, string $to, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $sql =
            'SELECT c.name, COALESCE(SUM(t.amount),0) AS sum_amount
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :uid
               AND t.deleted_at IS NULL
               AND t.type = "expense"
               AND t.happened_on BETWEEN :from AND :to
             GROUP BY c.id, c.name
             ORDER BY sum_amount DESC
             LIMIT ' . (int)$limit;

        $st = $this->pdo->prepare($sql);
        $st->execute(['uid' => $userId, 'from' => $from, 'to' => $to]);
        return $st->fetchAll();
    }
}
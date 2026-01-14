<?php
declare(strict_types=1);

final class AttachmentRepository
{
    public function __construct(private PDO $pdo) {}

    public function listForTransaction(int $transactionId, int $userId): array
    {
        $st = $this->pdo->prepare(
            'SELECT id, original_name, stored_name, mime_type, size_bytes, created_at
             FROM transaction_attachments
             WHERE transaction_id = :tid AND user_id = :uid
             ORDER BY id DESC'
        );
        $st->execute(['tid' => $transactionId, 'uid' => $userId]);
        return $st->fetchAll();
    }

    public function create(int $userId, int $transactionId, array $meta): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO transaction_attachments (user_id, transaction_id, original_name, stored_name, mime_type, size_bytes)
             VALUES (:uid, :tid, :oname, :sname, :mime, :size)'
        );
        $st->execute([
            'uid' => $userId,
            'tid' => $transactionId,
            'oname' => $meta['original_name'],
            'sname' => $meta['stored_name'],
            'mime' => $meta['mime_type'],
            'size' => $meta['size_bytes'],
        ]);
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT id, transaction_id, original_name, stored_name, mime_type, size_bytes
             FROM transaction_attachments
             WHERE id = :id AND user_id = :uid'
        );
        $st->execute(['id' => $id, 'uid' => $userId]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function delete(int $id, int $userId): void
    {
        $st = $this->pdo->prepare('DELETE FROM transaction_attachments WHERE id = :id AND user_id = :uid');
        $st->execute(['id' => $id, 'uid' => $userId]);
    }
}
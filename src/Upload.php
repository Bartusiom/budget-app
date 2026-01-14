<?php
declare(strict_types=1);

final class Upload
{
    public static function ensureDir(string $absDir): void
    {
        if (!is_dir($absDir)) {
            mkdir($absDir, 0775, true);
        }
    }

    public static function saveReceipt(array $file, string $absDir): array
    {
        // $file = $_FILES['receipt']
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Niepoprawny upload.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Błąd uploadu.');
        }

        $max = 5 * 1024 * 1024; // 5MB
        if ((int)$file['size'] <= 0 || (int)$file['size'] > $max) {
            throw new RuntimeException('Plik za duży (max 5MB).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'application/pdf' => 'pdf',
        ];

        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Dozwolone: JPG, PNG, PDF.');
        }

        self::ensureDir($absDir);

        $stored = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $target = rtrim($absDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $stored;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('Nie udało się zapisać pliku.');
        }

        return [
            'original_name' => (string)$file['name'],
            'stored_name' => $stored,
            'mime_type' => $mime,
            'size_bytes' => (int)$file['size'],
        ];
    }
}
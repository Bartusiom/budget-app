<?php
declare(strict_types=1);

final class CsvImport
{
    /**
     * Format CSV (separator ;):
     * date;type;amount;category;description
     * 2026-01-13;expense;25.50;Jedzenie;Obiad
     */
    public static function readRows(string $tmpFile): array
    {
        $fh = fopen($tmpFile, 'rb');
        if (!$fh) {
            throw new RuntimeException('Nie można otworzyć pliku CSV.');
        }

        $rows = [];
        $lineNo = 0;

        while (($line = fgets($fh)) !== false) {
            $lineNo++;
            $line = trim($line);
            if ($line === '') continue;

            // pomiń nagłówek jeśli jest
            if ($lineNo === 1 && str_starts_with(mb_strtolower($line), 'date;type;amount;category')) {
                continue;
            }

            $parts = str_getcsv($line, ';');
            if (count($parts) < 4) {
                throw new RuntimeException("Błędny format w linii {$lineNo}.");
            }

            $date = trim((string)$parts[0]);
            $type = trim((string)$parts[1]);
            $amount = trim((string)$parts[2]);
            $category = trim((string)$parts[3]);
            $desc = isset($parts[4]) ? trim((string)$parts[4]) : '';

            $rows[] = [
                'date' => $date,
                'type' => $type,
                'amount' => $amount,
                'category' => $category,
                'description' => $desc,
                'line' => $lineNo,
            ];
        }

        fclose($fh);
        return $rows;
    }
}
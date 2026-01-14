<?php
declare(strict_types=1);

final class Validation
{
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function password(string $password): bool
    {
        return strlen($password) >= 8 && !preg_match('~\s~', $password);
    }

    public static function categoryName(string $name): bool
    {
        return (bool)preg_match('~^[\p{L}0-9 _\.\-]{2,50}$~u', $name);
    }

    public static function money(string $amount): bool
    {
        if (!preg_match('~^\d+(\.\d{1,2})?$~', $amount)) return false;
        return (float)$amount > 0;
    }

    public static function type(string $type): bool
    {
        return in_array($type, ['income', 'expense'], true);
    }

    public static function ymdDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public static function ymMonth(string $month): bool
    {
        return (bool)preg_match('~^\d{4}-\d{2}$~', $month);
    }
}
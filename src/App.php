<?php
declare(strict_types=1);

final class App
{
    public const BASE = '/budget-app/public';

    public static function url(string $path = ''): string
    {
        $path = '/' . ltrim($path, '/');
        return self::BASE . ($path === '/' ? '' : $path);
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . self::url($path));
        exit;
    }
}
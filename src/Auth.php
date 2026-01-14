<?php
declare(strict_types=1);

final class Auth
{
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
    }

    public static function id(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            require_once __DIR__ . '/App.php';
            App::redirect('login.php');
        }
    }

    public static function login(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
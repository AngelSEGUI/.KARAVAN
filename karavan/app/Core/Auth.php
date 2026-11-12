<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/** Gestion de la session du membre connecté. */
final class Auth
{
    private static ?array $user = null;

    public static function user(): ?array
    {
        if (self::$user === null && isset($_SESSION['user_id'])) {
            $user = User::find((int) $_SESSION['user_id']);
            // Compte supprimé ou suspendu entre-temps : on ferme la session
            if ($user === null || (int) $user['suspended'] === 1) {
                unset($_SESSION['user_id']);
                return null;
            }
            self::$user = $user;
        }
        return self::$user;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        self::$user = $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        self::$user = null;
    }
}

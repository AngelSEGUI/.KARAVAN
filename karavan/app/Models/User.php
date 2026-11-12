<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return static::first('SELECT * FROM users WHERE email = ?', [mb_strtolower(trim($email))]);
    }

    /** Liste d'administration avec le nombre d'offres et de réservations de chaque membre. */
    public static function adminList(string $q = '', string $filter = ''): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.city LIKE ?)';
            array_push($params, "%$q%", "%$q%", "%$q%");
        }
        if ($filter === 'admin') {
            $where[] = "u.role = 'admin'";
        } elseif ($filter === 'suspended') {
            $where[] = 'u.suspended = 1';
        }

        return static::select(
            'SELECT u.*,
                (SELECT COUNT(*) FROM rides r WHERE r.user_id = u.id)    AS rides_count,
                (SELECT COUNT(*) FROM housings h WHERE h.user_id = u.id) AS housings_count,
                (SELECT COUNT(*) FROM ride_bookings b WHERE b.user_id = u.id)
                  + (SELECT COUNT(*) FROM housing_bookings b WHERE b.user_id = u.id) AS bookings_count
             FROM users u' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY u.created_at DESC, u.id DESC',
            $params
        );
    }

    public static function latest(int $limit = 5): array
    {
        return static::select('SELECT * FROM users ORDER BY created_at DESC, id DESC LIMIT ' . $limit);
    }

    public static function adminCount(): int
    {
        return (int) (static::first("SELECT COUNT(*) AS n FROM users WHERE role = 'admin' AND suspended = 0")['n'] ?? 0);
    }

    public static function register(string $name, string $email, string $password, ?string $city): int
    {
        return static::create([
            'name'     => trim($name),
            'email'    => mb_strtolower(trim($email)),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'city'     => $city ? trim($city) : null,
        ]);
    }
}

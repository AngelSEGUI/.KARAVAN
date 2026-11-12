<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Flux d'activité récente pour le tableau de bord d'administration. */
final class Activity extends Model
{
    protected static string $table = 'users';

    public static function recent(int $limit = 8): array
    {
        // Libellés assemblés en PHP pour rester compatible SQLite / MySQL
        $rows = static::select(
            "SELECT * FROM (
                SELECT 'ride' AS kind, r.id AS target_id, r.created_at, u.name AS actor,
                       r.departure_city AS label, f.name AS label2
                FROM rides r JOIN users u ON u.id = r.user_id JOIN festivals f ON f.id = r.festival_id
                UNION ALL
                SELECT 'housing', h.id, h.created_at, u.name, h.title, NULL
                FROM housings h JOIN users u ON u.id = h.user_id
                UNION ALL
                SELECT 'ride_booking', b.ride_id, b.created_at, u.name, r.departure_city, f.name
                FROM ride_bookings b JOIN users u ON u.id = b.user_id JOIN rides r ON r.id = b.ride_id JOIN festivals f ON f.id = r.festival_id
                UNION ALL
                SELECT 'housing_booking', b.housing_id, b.created_at, u.name, h.title, NULL
                FROM housing_bookings b JOIN users u ON u.id = b.user_id JOIN housings h ON h.id = b.housing_id
                UNION ALL
                SELECT 'user', u.id, u.created_at, u.name, u.city, NULL FROM users u
            ) AS a ORDER BY created_at DESC LIMIT " . $limit
        );

        foreach ($rows as &$row) {
            $row['label'] = $row['label2'] ? $row['label'] . ' → ' . $row['label2'] : (string) $row['label'];
        }
        return $rows;
    }

    public static function bookingsCount(): int
    {
        return (int) (static::first(
            'SELECT (SELECT COUNT(*) FROM ride_bookings) + (SELECT COUNT(*) FROM housing_bookings) AS n'
        )['n'] ?? 0);
    }
}

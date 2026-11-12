<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Trajet en covoiturage proposé par un membre vers un festival. */
final class Ride extends Model
{
    protected static string $table = 'rides';

    private const BASE = '
        SELECT r.*,
               u.name AS driver_name, u.city AS driver_city,
               f.name AS festival_name, f.slug AS festival_slug, f.city AS festival_city, f.hue AS festival_hue,
               r.seats - COALESCE((SELECT SUM(b.seats) FROM ride_bookings b WHERE b.ride_id = r.id), 0) AS seats_left
        FROM rides r
        JOIN users u     ON u.id = r.user_id
        JOIN festivals f ON f.id = r.festival_id';

    public static function search(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['festival'])) {
            $where[] = 'r.festival_id = ?';
            $params[] = (int) $filters['festival'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'r.departure_city LIKE ?';
            $params[] = '%' . $filters['from'] . '%';
        }
        if (!empty($filters['seats'])) {
            $where[] = 'seats_left >= ?';
            $params[] = (int) $filters['seats'];
        }

        $sql = 'SELECT * FROM (' . self::BASE . ') AS r'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY r.departure_at';

        return static::select($sql, $params);
    }

    public static function forFestival(int $festivalId): array
    {
        return static::search(['festival' => $festivalId]);
    }

    public static function findDetailed(int $id): ?array
    {
        return static::first(self::BASE . ' WHERE r.id = ?', [$id]);
    }

    public static function byDriver(int $userId): array
    {
        return static::select(self::BASE . ' WHERE r.user_id = ? ORDER BY r.departure_at', [$userId]);
    }

    public static function passengers(int $rideId): array
    {
        return static::select(
            'SELECT b.*, u.name FROM ride_bookings b JOIN users u ON u.id = b.user_id WHERE b.ride_id = ? ORDER BY b.created_at',
            [$rideId]
        );
    }

    public static function departureCities(): array
    {
        return array_column(static::select('SELECT DISTINCT departure_city FROM rides ORDER BY departure_city'), 'departure_city');
    }
}

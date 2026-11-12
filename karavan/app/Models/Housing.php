<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Logement proposé par un membre à proximité d'un festival. */
final class Housing extends Model
{
    protected static string $table = 'housings';

    public const TYPES = [
        'tente'       => 'Emplacement tente',
        'van'         => 'Van / camping-car',
        'chambre'     => 'Chambre chez l\'habitant',
        'appartement' => 'Appartement',
        'maison'      => 'Maison entière',
    ];

    private const BASE = '
        SELECT h.*,
               u.name AS host_name, u.city AS host_city,
               f.name AS festival_name, f.slug AS festival_slug, f.city AS festival_city, f.hue AS festival_hue,
               f.start_date AS festival_start, f.end_date AS festival_end
        FROM housings h
        JOIN users u     ON u.id = h.user_id
        JOIN festivals f ON f.id = h.festival_id';

    public static function search(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['festival'])) {
            $where[] = 'h.festival_id = ?';
            $params[] = (int) $filters['festival'];
        }
        if (!empty($filters['type']) && isset(self::TYPES[$filters['type']])) {
            $where[] = 'h.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['guests'])) {
            $where[] = 'h.capacity >= ?';
            $params[] = (int) $filters['guests'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = 'h.price_night <= ?';
            $params[] = (float) $filters['max_price'];
        }

        $order = ($filters['sort'] ?? '') === 'price' ? 'h.price_night' : 'h.distance_km';

        return static::select(
            self::BASE . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $order,
            $params
        );
    }

    public static function forFestival(int $festivalId): array
    {
        return static::search(['festival' => $festivalId]);
    }

    public static function findDetailed(int $id): ?array
    {
        return static::first(self::BASE . ' WHERE h.id = ?', [$id]);
    }

    public static function byHost(int $userId): array
    {
        return static::select(self::BASE . ' WHERE h.user_id = ? ORDER BY f.start_date', [$userId]);
    }

    public static function requests(int $housingId): array
    {
        return static::select(
            'SELECT b.*, u.name FROM housing_bookings b JOIN users u ON u.id = b.user_id WHERE b.housing_id = ? ORDER BY b.created_at',
            [$housingId]
        );
    }

    /** Liste de modération avec le nombre de demandes reçues. */
    public static function adminList(array $filters = []): array
    {
        $rows = static::search($filters + ['sort' => 'price']);
        $counts = [];
        foreach (static::select('SELECT housing_id, COUNT(*) AS n FROM housing_bookings GROUP BY housing_id') as $r) {
            $counts[$r['housing_id']] = (int) $r['n'];
        }
        foreach ($rows as &$row) {
            $row['requests_count'] = $counts[$row['id']] ?? 0;
        }
        return $rows;
    }

    public static function typeLabel(string $type): string
    {
        return self::TYPES[$type] ?? ucfirst($type);
    }
}

<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Demande de séjour dans un logement de la communauté. */
final class HousingBooking extends Model
{
    protected static string $table = 'housing_bookings';

    public static function exists(int $housingId, int $userId): bool
    {
        return static::first('SELECT id FROM housing_bookings WHERE housing_id = ? AND user_id = ?', [$housingId, $userId]) !== null;
    }

    public static function byGuest(int $userId): array
    {
        return static::select(
            'SELECT b.*, h.title, h.city, h.price_night, u.name AS host_name,
                    f.name AS festival_name, f.slug AS festival_slug, f.hue AS festival_hue, f.start_date
             FROM housing_bookings b
             JOIN housings h  ON h.id = b.housing_id
             JOIN users u     ON u.id = h.user_id
             JOIN festivals f ON f.id = h.festival_id
             WHERE b.user_id = ? ORDER BY f.start_date',
            [$userId]
        );
    }
}

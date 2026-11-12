<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Réservation d'une ou plusieurs places dans un covoiturage. */
final class RideBooking extends Model
{
    protected static string $table = 'ride_bookings';

    public static function exists(int $rideId, int $userId): bool
    {
        return static::first('SELECT id FROM ride_bookings WHERE ride_id = ? AND user_id = ?', [$rideId, $userId]) !== null;
    }

    public static function byPassenger(int $userId): array
    {
        return static::select(
            'SELECT b.*, r.departure_city, r.departure_at, r.price, u.name AS driver_name,
                    f.name AS festival_name, f.slug AS festival_slug, f.hue AS festival_hue
             FROM ride_bookings b
             JOIN rides r     ON r.id = b.ride_id
             JOIN users u     ON u.id = r.user_id
             JOIN festivals f ON f.id = r.festival_id
             WHERE b.user_id = ? ORDER BY r.departure_at',
            [$userId]
        );
    }
}

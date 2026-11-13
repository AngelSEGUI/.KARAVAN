<?php

namespace Database\Seeders;

use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Remplit la base avec les données de démonstration de database/seeders/data/demo.php.
 *   php artisan migrate:fresh --seed
 * Tous les comptes de démo ont le mot de passe « karavan ».
 */
class DatabaseSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'karavan';

    public function run(): void
    {
        $data = require __DIR__ . '/data/demo.php';
        $password = Hash::make(self::DEMO_PASSWORD);

        $users = [];
        foreach ($data['users'] as $row) {
            $user = new User(collect($row)->except('role')->all());
            $user->password = $password;
            $user->role = $row['role'];
            $user->email_verified_at = now();
            $user->save();
            $users[$row['email']] = $user;
        }

        $festivals = [];
        foreach ($data['festivals'] as $row) {
            $festivals[$row['slug']] = Festival::create($row);
        }

        $rides = [];
        foreach ($data['rides'] as $row) {
            $ride = new Ride(collect($row)->except(['driver', 'festival'])->all());
            $ride->festival()->associate($festivals[$row['festival']]);
            $ride->driver()->associate($users[$row['driver']]);
            $ride->save();
            $rides[] = $ride;
        }

        $housings = [];
        foreach ($data['housings'] as $row) {
            $housing = new Housing(collect($row)->except(['host', 'festival'])->all());
            $housing->festival()->associate($festivals[$row['festival']]);
            $housing->host()->associate($users[$row['host']]);
            $housing->save();
            $housings[] = $housing;
        }

        foreach ($data['ride_bookings'] as $row) {
            $booking = $rides[$row['ride'] - 1]->bookings()->make([
                'user_id' => $users[$row['passenger']]->id,
                'seats' => $row['seats'],
                'message' => $row['message'],
            ]);
            $booking->status = $row['status'];
            $booking->responded_at = $row['status'] === 'pending' ? null : now();
            $booking->save();
        }

        foreach ($data['housing_bookings'] as $row) {
            $booking = $housings[$row['housing'] - 1]->bookings()->make([
                'user_id' => $users[$row['guest']]->id,
                'guests' => $row['guests'],
                'nights' => $row['nights'],
                'message' => $row['message'],
            ]);
            $booking->status = $row['status'];
            $booking->responded_at = $row['status'] === 'pending' ? null : now();
            $booking->save();
        }

        // Espace organisateur : compte pro et page de festival personnalisée
        $this->call(OrganizerDemoSeeder::class);

        $this->command?->info('Données de démo chargées. Mot de passe de tous les comptes : ' . self::DEMO_PASSWORD);
        $this->command?->info('Administrateur : admin@karavan.test — Organisateur : pro@karavan.test');
    }
}

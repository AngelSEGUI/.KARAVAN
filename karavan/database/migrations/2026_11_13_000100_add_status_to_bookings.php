<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les réservations deviennent des demandes que le conducteur ou l'hôte accepte ou refuse.
 * Les réservations déjà présentes étaient confirmées d'office : elles passent en « acceptée ».
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['ride_bookings', 'housing_bookings'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('status', 20)->default('pending')->index();   // App\Enums\BookingStatus
                $t->timestamp('responded_at')->nullable();               // date d'acceptation / refus / annulation
            });

            DB::table($table)->update(['status' => 'accepted', 'responded_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (['ride_bookings', 'housing_bookings'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['status']);
                $t->dropColumn(['status', 'responded_at']);
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // hôte
            $table->foreignId('festival_id')->constrained()->cascadeOnDelete();
            $table->string('title', 140);
            $table->string('type', 20)->index();                                  // App\Enums\HousingType
            $table->string('city', 80);
            $table->decimal('distance_km', 5, 1);
            $table->unsignedTinyInteger('capacity');
            $table->decimal('price_per_night', 8, 2);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();                                // ["Douche", "Parking"]
            $table->timestamps();
        });

        Schema::create('housing_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // voyageur
            $table->unsignedTinyInteger('guests')->default(1);
            $table->unsignedTinyInteger('nights')->default(1);
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['housing_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_bookings');
        Schema::dropIfExists('housings');
    }
};

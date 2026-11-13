<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // conducteur
            $table->foreignId('festival_id')->constrained()->cascadeOnDelete();
            $table->string('departure_city', 80)->index();
            $table->string('meeting_point', 160)->nullable();
            $table->dateTime('departs_at')->index();
            $table->dateTime('returns_at')->nullable();
            $table->unsignedTinyInteger('seats');
            $table->decimal('price', 8, 2);
            $table->string('vehicle', 80)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ride_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // passager
            $table->unsignedTinyInteger('seats')->default(1);
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['ride_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_bookings');
        Schema::dropIfExists('rides');
    }
};

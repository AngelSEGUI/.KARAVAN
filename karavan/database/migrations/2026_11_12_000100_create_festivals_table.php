<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('festivals', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('name', 120);
            $table->string('city', 80);
            $table->string('region', 80)->index();
            $table->string('venue', 160)->nullable();
            $table->date('starts_on')->index();
            $table->date('ends_on');
            $table->json('genres');                 // ["Rock", "Pop"]
            $table->json('lineup')->nullable();     // ["Tête d'affiche", ...]
            $table->string('tagline', 200)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('attendance')->nullable();
            $table->decimal('price_from', 8, 2)->nullable();
            $table->unsignedSmallInteger('hue')->default(20);   // teinte 0-359 de la couverture générée
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festivals');
    }
};

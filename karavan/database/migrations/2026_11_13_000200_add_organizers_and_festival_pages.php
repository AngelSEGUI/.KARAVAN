<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organisateurs (profil pro) et pages de festival personnalisées :
 * propriétaire, brouillon/publié, apparence, images, paragraphes, carrousel et programmation.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Demande de profil pro (organisateur)
        Schema::table('users', function (Blueprint $table) {
            $table->string('organization', 120)->nullable();          // nom de la structure organisatrice
            $table->string('organization_website', 200)->nullable();
            $table->text('pro_request_message')->nullable();
            $table->timestamp('pro_requested_at')->nullable();       // demande en attente de validation
        });

        Schema::table('festivals', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete(); // organisateur
            $table->boolean('is_published')->default(true)->index();  // false = brouillon, invisible du public
            $table->string('website', 200)->nullable();
            $table->string('ticket_url', 200)->nullable();
            $table->json('design')->nullable();                       // palette et polices (App\Support\FestivalDesign)
            $table->string('banner_path')->nullable();                // images dans public/uploads/
            $table->string('icon_path')->nullable();
            $table->string('poster_path')->nullable();
        });

        // Paragraphes libres de la page
        Schema::create('festival_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120)->nullable();
            $table->text('body');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // Photos du carrousel
        Schema::create('festival_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption', 160)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // Programmation : un créneau = un artiste sur une scène
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained()->cascadeOnDelete();
            $table->string('artist', 120);
            $table->string('stage', 80);
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->unsignedInteger('capacity')->nullable();          // facultatif, masqué si vide
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performances');
        Schema::dropIfExists('festival_photos');
        Schema::dropIfExists('festival_sections');

        Schema::table('festivals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropIndex(['is_published']);
            $table->dropColumn(['is_published', 'website', 'ticket_url', 'design', 'banner_path', 'icon_path', 'poster_path']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['organization', 'organization_website', 'pro_request_message', 'pro_requested_at']);
        });
    }
};

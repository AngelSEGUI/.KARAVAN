<?php

/*
|--------------------------------------------------------------------------
| Routes web de Karavan
|--------------------------------------------------------------------------
| Les URL sont en français, les noms de routes en anglais :
| dans les vues, on écrit route('rides.show', $ride) et jamais l'URL en dur.
| Liste complète : php artisan route:list
*/

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FestivalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HousingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\StyleGuideController;
use Illuminate\Support\Facades\Route;

/* ---------- Découverte (public) ---------- */
Route::get('/', HomeController::class)->name('home');
Route::get('/festivals', [FestivalController::class, 'index'])->name('festivals.index');
Route::get('/festivals/{festival}', [FestivalController::class, 'show'])->name('festivals.show');

/* ---------- Guide de styles (aperçu des thèmes et composants) ---------- */
Route::get('/guide-styles', StyleGuideController::class)->name('styleguide');

/* ---------- Invités ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->middleware('throttle:6,1');
});

/* ---------- Membres connectés ---------- */
Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');

    // Mon espace & profil
    Route::get('/mon-espace', AccountController::class)->name('account');
    Route::get('/mon-espace/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/mon-espace/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/mon-espace/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/mon-espace', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Covoiturages : publier, modifier, supprimer, réserver
    Route::get('/covoiturages/nouveau', [RideController::class, 'create'])->name('rides.create');
    Route::post('/covoiturages', [RideController::class, 'store'])->name('rides.store');
    Route::get('/covoiturages/{ride}/modifier', [RideController::class, 'edit'])->name('rides.edit');
    Route::put('/covoiturages/{ride}', [RideController::class, 'update'])->name('rides.update');
    Route::delete('/covoiturages/{ride}', [RideController::class, 'destroy'])->name('rides.destroy');
    Route::post('/covoiturages/{ride}/reserver', [RideController::class, 'book'])->name('rides.book');

    // Logements : publier, modifier, supprimer, demander
    Route::get('/logements/nouveau', [HousingController::class, 'create'])->name('housings.create');
    Route::post('/logements', [HousingController::class, 'store'])->name('housings.store');
    Route::get('/logements/{housing}/modifier', [HousingController::class, 'edit'])->name('housings.edit');
    Route::put('/logements/{housing}', [HousingController::class, 'update'])->name('housings.update');
    Route::delete('/logements/{housing}', [HousingController::class, 'destroy'])->name('housings.destroy');
    Route::post('/logements/{housing}/reserver', [HousingController::class, 'book'])->name('housings.book');
});

/* ---------- Covoiturages & logements (public) — après "nouveau" pour ne pas le capturer ---------- */
Route::get('/covoiturages', [RideController::class, 'index'])->name('rides.index');
Route::get('/covoiturages/{ride}', [RideController::class, 'show'])->name('rides.show')->whereNumber('ride');
Route::get('/logements', [HousingController::class, 'index'])->name('housings.index');
Route::get('/logements/{housing}', [HousingController::class, 'show'])->name('housings.show')->whereNumber('housing');

/* ---------- Administration ---------- */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/festivals', [Admin\FestivalController::class, 'index'])->name('festivals.index');
    Route::get('/festivals/nouveau', [Admin\FestivalController::class, 'create'])->name('festivals.create');
    Route::post('/festivals', [Admin\FestivalController::class, 'store'])->name('festivals.store');
    Route::get('/festivals/{festival:id}/modifier', [Admin\FestivalController::class, 'edit'])->name('festivals.edit');
    Route::put('/festivals/{festival:id}', [Admin\FestivalController::class, 'update'])->name('festivals.update');
    Route::patch('/festivals/{festival:id}/une', [Admin\FestivalController::class, 'toggleFeatured'])->name('festivals.featured');
    Route::delete('/festivals/{festival:id}', [Admin\FestivalController::class, 'destroy'])->name('festivals.destroy');

    Route::get('/covoiturages', [Admin\RideController::class, 'index'])->name('rides.index');
    Route::get('/logements', [Admin\HousingController::class, 'index'])->name('housings.index');

    Route::get('/membres', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/membres/{user}/role', [Admin\UserController::class, 'toggleRole'])->name('users.role');
    Route::patch('/membres/{user}/suspension', [Admin\UserController::class, 'toggleSuspension'])->name('users.suspension');
    Route::delete('/membres/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
});

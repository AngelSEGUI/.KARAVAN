<?php
/**
 * Table de routage.
 * Syntaxe : $router->verbe('/chemin/{param}', [Controleur::class, 'methode']);
 *
 * @var App\Core\Router $router
 */

use App\Controllers\AccountController;
use App\Controllers\Admin;
use App\Controllers\AuthController;
use App\Controllers\FestivalController;
use App\Controllers\HomeController;
use App\Controllers\HousingController;
use App\Controllers\RideController;

// Découverte
$router->get('/', [HomeController::class, 'index']);
$router->get('/festivals', [FestivalController::class, 'index']);
$router->get('/festivals/{slug}', [FestivalController::class, 'show']);

// Covoiturage
$router->get('/covoiturages', [RideController::class, 'index']);
$router->get('/covoiturages/nouveau', [RideController::class, 'create']);
$router->post('/covoiturages', [RideController::class, 'store']);
$router->get('/covoiturages/{id}', [RideController::class, 'show']);
$router->post('/covoiturages/{id}/reserver', [RideController::class, 'book']);

// Logements
$router->get('/logements', [HousingController::class, 'index']);
$router->get('/logements/nouveau', [HousingController::class, 'create']);
$router->post('/logements', [HousingController::class, 'store']);
$router->get('/logements/{id}', [HousingController::class, 'show']);
$router->post('/logements/{id}/reserver', [HousingController::class, 'book']);

// Compte
$router->get('/connexion', [AuthController::class, 'loginForm']);
$router->post('/connexion', [AuthController::class, 'login']);
$router->get('/inscription', [AuthController::class, 'registerForm']);
$router->post('/inscription', [AuthController::class, 'register']);
$router->post('/deconnexion', [AuthController::class, 'logout']);
$router->get('/mon-espace', [AccountController::class, 'index']);

// Administration (réservée au rôle admin — contrôle dans chaque contrôleur Admin\*)
$router->get('/admin', [Admin\DashboardController::class, 'index']);

$router->get('/admin/festivals', [Admin\FestivalController::class, 'index']);
$router->get('/admin/festivals/nouveau', [Admin\FestivalController::class, 'create']);
$router->post('/admin/festivals', [Admin\FestivalController::class, 'store']);
$router->get('/admin/festivals/{id}/modifier', [Admin\FestivalController::class, 'edit']);
$router->post('/admin/festivals/{id}', [Admin\FestivalController::class, 'update']);
$router->post('/admin/festivals/{id}/une', [Admin\FestivalController::class, 'toggleFeatured']);
$router->post('/admin/festivals/{id}/supprimer', [Admin\FestivalController::class, 'destroy']);

$router->get('/admin/covoiturages', [Admin\RideController::class, 'index']);
$router->post('/admin/covoiturages/{id}/supprimer', [Admin\RideController::class, 'destroy']);

$router->get('/admin/logements', [Admin\HousingController::class, 'index']);
$router->post('/admin/logements/{id}/supprimer', [Admin\HousingController::class, 'destroy']);

$router->get('/admin/membres', [Admin\UserController::class, 'index']);
$router->post('/admin/membres/{id}/role', [Admin\UserController::class, 'toggleRole']);
$router->post('/admin/membres/{id}/suspension', [Admin\UserController::class, 'toggleSuspend']);
$router->post('/admin/membres/{id}/supprimer', [Admin\UserController::class, 'destroy']);

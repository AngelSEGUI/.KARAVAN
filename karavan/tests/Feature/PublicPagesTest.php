<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Toutes les pages publiques s'affichent, avec les données de démo. */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // données de démo (database/seeders/data/demo.php)
    }

    public function test_public_pages_render(): void
    {
        $festival = Festival::where('slug', 'hellfest')->first();

        foreach ([
            '/', '/festivals', '/festivals/hellfest', '/covoiturages', '/logements', '/connexion', '/inscription', '/guide-styles',
            '/covoiturages/' . Ride::first()->id, '/logements/' . Housing::first()->id,
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/festivals/hellfest')->assertSee($festival->name)->assertSee('Iron Maiden');
    }

    public function test_festival_filters(): void
    {
        $this->get('/festivals?genre=Metal')->assertOk()->assertSee('Hellfest')->assertDontSee('Nuits Sonores');
        $this->get('/festivals?q=Carhaix')->assertSee('Les Vieilles Charrues')->assertDontSee('Garorock');
        $this->get('/festivals?month=5')->assertSee('Nuits Sonores')->assertDontSee('Hellfest');
        $this->get('/festivals?sort=offers')->assertOk();
    }

    public function test_ride_and_housing_filters(): void
    {
        $this->get('/covoiturages?from=Grenoble')->assertSee('Grenoble')->assertSee("1\u{00A0}trajet");
        $this->get('/logements?type=van')->assertSee('Van aménagé')->assertDontSee('Camping à la ferme');
        $this->get('/logements?max_price=20&sort=price')->assertOk()->assertDontSee('Maison de bourg');
    }

    public function test_unknown_pages_return_french_404(): void
    {
        $this->get('/festivals/inexistant')->assertNotFound()->assertSee('Page introuvable');
        $this->get('/covoiturages/9999')->assertNotFound();
    }

    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/mon-espace', '/mon-espace/profil', '/covoiturages/nouveau', '/logements/nouveau', '/admin'] as $url) {
            $this->get($url)->assertRedirect('/connexion');
        }
    }

    public function test_theme_can_be_previewed_outside_production(): void
    {
        $this->get('/?theme=nuit')->assertSee('css/themes/nuit.css', false);
        $this->get('/')->assertSee('css/themes/nuit.css', false); // mémorisé en session
        $this->get('/?theme=inexistant')->assertSee('css/themes/nuit.css', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register(): void
    {
        $this->post('/inscription', [
            'name' => 'Élodie Ünal',
            'email' => 'Elodie@Test.FR',
            'city' => 'Nîmes',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ])->assertRedirect('/mon-espace');

        $this->assertAuthenticated();
        $user = User::where('email', 'elodie@test.fr')->firstOrFail();
        $this->assertSame('ÉÜ', $user->initials);
        $this->get('/mon-espace')->assertOk()->assertSee('Élodie Ünal');
    }

    public function test_registration_is_validated(): void
    {
        User::factory()->create(['email' => 'pris@test.fr']);

        $this->from('/inscription')->post('/inscription', [
            'name' => 'A', 'email' => 'pris@test.fr', 'password' => 'court', 'password_confirmation' => 'autre',
        ])->assertRedirect('/inscription')->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'lea@test.fr']);

        $this->post('/connexion', ['email' => 'lea@test.fr', 'password' => 'mauvais'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/connexion', ['email' => 'LEA@test.fr', 'password' => 'password'])
            ->assertRedirect('/mon-espace');
        $this->assertAuthenticatedAs($user);

        $this->post('/deconnexion')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_after_login_the_member_returns_to_the_requested_page(): void
    {
        User::factory()->create(['email' => 'lea@test.fr']);

        $this->get('/covoiturages/nouveau')->assertRedirect('/connexion');
        $this->post('/connexion', ['email' => 'lea@test.fr', 'password' => 'password'])
            ->assertRedirect('/covoiturages/nouveau');
    }

    public function test_suspended_members_cannot_log_in(): void
    {
        User::factory()->suspended()->create(['email' => 'banni@test.fr']);

        $this->post('/connexion', ['email' => 'banni@test.fr', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => "Ce compte est suspendu. Contactez l'équipe Karavan."]);
        $this->assertGuest();
    }

    public function test_a_suspended_member_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/mon-espace')->assertOk();

        $user->forceFill(['suspended_at' => now()])->save();

        $this->get('/mon-espace')->assertRedirect('/connexion');
        $this->assertGuest();
    }

    public function test_logged_in_members_skip_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/connexion')->assertRedirect('/mon-espace');
    }
}

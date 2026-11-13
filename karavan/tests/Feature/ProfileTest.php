<?php

namespace Tests\Feature;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Modification du profil, du mot de passe et suppression du compte. */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_profile_page_shows_current_values(): void
    {
        $user = User::factory()->create(['name' => 'Léa Martin', 'bio' => 'Fan de rock']);

        $this->actingAs($user)->get('/mon-espace/profil')->assertOk()->assertSee('Léa Martin')->assertSee('Fan de rock');
    }

    public function test_a_member_updates_the_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/mon-espace/profil', [
            'name' => 'Léa Durand', 'email' => 'NOUVEL@test.fr', 'city' => 'Lyon', 'bio' => 'Électro et van aménagé.',
        ])->assertRedirect('/mon-espace/profil')->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Léa Durand', $user->name);
        $this->assertSame('nouvel@test.fr', $user->email);
        $this->assertSame('Électro et van aménagé.', $user->bio);
    }

    public function test_email_must_stay_unique_but_can_be_kept(): void
    {
        User::factory()->create(['email' => 'pris@test.fr']);
        $user = User::factory()->create(['email' => 'moi@test.fr']);

        $this->actingAs($user)->put('/mon-espace/profil', ['name' => 'Moi', 'email' => 'pris@test.fr'])
            ->assertSessionHasErrors('email');
        $this->put('/mon-espace/profil', ['name' => 'Moi', 'email' => 'moi@test.fr'])->assertSessionHasNoErrors();
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/mon-espace/mot-de-passe', [
            'current_password' => 'faux', 'password' => 'nouveau-mdp', 'password_confirmation' => 'nouveau-mdp',
        ])->assertSessionHasErrorsIn('password', 'current_password');

        $this->put('/mon-espace/mot-de-passe', [
            'current_password' => 'password', 'password' => 'nouveau-mdp', 'password_confirmation' => 'nouveau-mdp',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertTrue(Hash::check('nouveau-mdp', $user->fresh()->password));
    }

    public function test_a_member_deletes_the_account_with_everything(): void
    {
        $ride = Ride::factory()->create();
        $user = $ride->driver;

        $this->actingAs($user)->delete('/mon-espace', ['password' => 'faux'])->assertSessionHasErrorsIn('deletion', 'password');
        $this->assertModelExists($user);

        $this->delete('/mon-espace', ['password' => 'password'])->assertRedirect('/');
        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelMissing($ride);
    }

    public function test_the_last_admin_cannot_delete_the_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from('/mon-espace/profil')->delete('/mon-espace', ['password' => 'password'])
            ->assertSessionHas('error');
        $this->assertModelExists($admin);

        User::factory()->admin()->create();
        $this->delete('/mon-espace', ['password' => 'password'])->assertRedirect('/');
        $this->assertModelMissing($admin);
    }

    public function test_my_space_lists_my_listings_with_edit_actions(): void
    {
        $ride = Ride::factory()->create(['departure_city' => 'Quimper']);

        $this->actingAs($ride->driver)->get('/mon-espace')
            ->assertSee('Quimper')
            ->assertSee(route('rides.edit', $ride), false)
            ->assertSee('Aucune réservation');
    }
}

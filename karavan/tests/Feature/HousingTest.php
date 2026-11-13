<?php

namespace Tests\Feature;

use App\Enums\HousingType;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Logements : publication, modification, suppression, demandes. */
class HousingTest extends TestCase
{
    use RefreshDatabase;

    private function validData(Festival $festival, array $overrides = []): array
    {
        return $overrides + [
            'festival_id' => $festival->id,
            'type' => 'van',
            'title' => 'Van aménagé près du site',
            'city' => 'Carhaix',
            'distance_km' => 1.5,
            'capacity' => 2,
            'price_per_night' => 30,
            'description' => 'Lit double et réchaud.',
            'amenities' => 'Douche, Parking, , Douche',
        ];
    }

    public function test_a_member_publishes_a_housing(): void
    {
        $host = User::factory()->create();
        $festival = Festival::factory()->create();

        $this->actingAs($host)->post('/logements', $this->validData($festival))->assertRedirect();

        $housing = Housing::firstOrFail();
        $this->assertSame(HousingType::Van, $housing->type);
        $this->assertSame(['Douche', 'Parking'], $housing->amenities);
        $this->get(route('housings.show', $housing))->assertSee('Van aménagé près du site')->assertSee('Parking');
    }

    public function test_housing_validation(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logements', $this->validData(Festival::factory()->create(), ['type' => 'chateau', 'title' => 'Ha', 'capacity' => 0]))
            ->assertSessionHasErrors(['type', 'title', 'capacity']);
    }

    public function test_the_host_edits_and_deletes_the_housing(): void
    {
        $housing = Housing::factory()->create();

        $this->actingAs($housing->host)
            ->put(route('housings.update', $housing), $this->validData($housing->festival, ['title' => 'Nouveau titre', 'amenities' => '']))
            ->assertRedirect(route('housings.show', $housing));
        $this->assertSame('Nouveau titre', $housing->fresh()->title);
        $this->assertNull($housing->fresh()->amenities);

        $this->delete(route('housings.destroy', $housing))->assertRedirect(route('account'));
        $this->assertModelMissing($housing);
    }

    public function test_others_cannot_edit_a_housing(): void
    {
        $housing = Housing::factory()->create();

        $this->actingAs(User::factory()->create())->get(route('housings.edit', $housing))->assertForbidden();
        $this->delete(route('housings.destroy', $housing))->assertForbidden();
    }

    public function test_capacity_cannot_drop_below_a_requested_group(): void
    {
        $housing = Housing::factory()->create(['capacity' => 4]);
        $housing->bookings()->create(['user_id' => User::factory()->create()->id, 'guests' => 3, 'nights' => 2]);

        $this->actingAs($housing->host)
            ->put(route('housings.update', $housing), $this->validData($housing->festival, ['capacity' => 2, 'festival_id' => Festival::factory()->create()->id]))
            ->assertSessionHasErrors(['capacity', 'festival_id']);
    }

    public function test_booking_rules(): void
    {
        $housing = Housing::factory()->create(['capacity' => 2]);
        $guest = User::factory()->create();

        $this->actingAs($housing->host)->post(route('housings.book', $housing), ['guests' => 1, 'nights' => 2])
            ->assertSessionHas('error', 'Vous ne pouvez pas réserver votre propre logement.');

        $this->actingAs($guest)->post(route('housings.book', $housing), ['guests' => 3, 'nights' => 2])
            ->assertSessionHasErrors('guests');

        $this->post(route('housings.book', $housing), ['guests' => 2, 'nights' => 3])->assertSessionHas('success');
        $this->post(route('housings.book', $housing), ['guests' => 1, 'nights' => 1])
            ->assertSessionHas('error', 'Vous avez déjà envoyé une demande pour ce logement.');

        $this->assertDatabaseCount('housing_bookings', 1);
        $this->get('/mon-espace')->assertSee($housing->title)->assertSee("3\u{00A0}nuits");
    }
}

<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\Ride;
use App\Models\User;
use App\Enums\BookingStatus;
use App\Notifications\BookingRequested;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Covoiturage : publication, modification, suppression, réservation. */
class RideTest extends TestCase
{
    use RefreshDatabase;

    private function validData(Festival $festival, array $overrides = []): array
    {
        return $overrides + [
            'festival_id' => $festival->id,
            'departure_city' => 'Nantes',
            'meeting_point' => 'Gare sud',
            'departs_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'returns_at' => now()->addMonth()->addDays(3)->format('Y-m-d\TH:i'),
            'seats' => 3,
            'price' => 18.5,
            'vehicle' => 'Clio',
            'notes' => 'Bonne ambiance',
        ];
    }

    public function test_a_member_publishes_a_ride(): void
    {
        $driver = User::factory()->create();
        $festival = Festival::factory()->create();

        $response = $this->actingAs($driver)->post('/covoiturages', $this->validData($festival));

        $ride = Ride::firstOrFail();
        $response->assertRedirect(route('rides.show', $ride));
        $this->assertTrue($ride->driver->is($driver));
        $this->get(route('rides.show', $ride))->assertSee('Nantes')->assertSee('18,50');
    }

    public function test_ride_validation(): void
    {
        $festival = Festival::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post('/covoiturages', $this->validData($festival, [
                'departure_city' => '',
                'departs_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'returns_at' => now()->subWeek()->format('Y-m-d\TH:i'),
                'seats' => 0,
                'festival_id' => 999,
            ]))
            ->assertSessionHasErrors(['departure_city', 'departs_at', 'returns_at', 'seats', 'festival_id']);

        $this->assertDatabaseCount('rides', 0);
    }

    public function test_the_driver_edits_the_ride(): void
    {
        $ride = Ride::factory()->create(['price' => 20]);

        $this->actingAs($ride->driver)->get(route('rides.edit', $ride))->assertOk()->assertSee('Enregistrer les modifications');

        $this->put(route('rides.update', $ride), $this->validData($ride->festival, ['price' => 25, 'departure_city' => 'Angers']))
            ->assertRedirect(route('rides.show', $ride));

        $this->assertSame('Angers', $ride->fresh()->departure_city);
        $this->assertEquals(25, $ride->fresh()->price);
    }

    public function test_other_members_cannot_edit_or_delete_a_ride(): void
    {
        $ride = Ride::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('rides.edit', $ride))->assertForbidden();
        $this->put(route('rides.update', $ride), $this->validData($ride->festival))->assertForbidden();
        $this->delete(route('rides.destroy', $ride))->assertForbidden();
        $this->assertModelExists($ride);
    }

    public function test_booked_rides_keep_their_festival_and_enough_seats(): void
    {
        $ride = Ride::factory()->create(['seats' => 4]);
        $ride->bookings()->create(['user_id' => User::factory()->create()->id, 'seats' => 3]);
        $otherFestival = Festival::factory()->create();

        $this->actingAs($ride->driver)
            ->put(route('rides.update', $ride), $this->validData($ride->festival, ['festival_id' => $otherFestival->id, 'seats' => 2]))
            ->assertSessionHasErrors(['festival_id', 'seats']);

        $this->put(route('rides.update', $ride), $this->validData($ride->festival, ['seats' => 3]))
            ->assertSessionHasNoErrors();
        $this->assertSame(3, $ride->fresh()->seats);
    }

    public function test_the_driver_deletes_the_ride_and_bookings_go_with_it(): void
    {
        Notification::fake();
        $ride = Ride::factory()->create();
        $ride->bookings()->create(['user_id' => User::factory()->create()->id, 'seats' => 1]);

        $this->actingAs($ride->driver)->delete(route('rides.destroy', $ride), ['redirect' => '/mon-espace'])
            ->assertRedirect('/mon-espace');

        $this->assertModelMissing($ride);
        $this->assertDatabaseCount('ride_bookings', 0);
    }

    public function test_delete_redirect_cannot_leave_the_site(): void
    {
        $ride = Ride::factory()->create();

        $this->actingAs($ride->driver)->delete(route('rides.destroy', $ride), ['redirect' => '//evil.example'])
            ->assertRedirect(route('account'));
    }

    public function test_booking_rules(): void
    {
        Notification::fake();
        $ride = Ride::factory()->create(['seats' => 2]);
        $passenger = User::factory()->create();

        // Le conducteur ne réserve pas son propre trajet
        $this->actingAs($ride->driver)->post(route('rides.book', $ride), ['seats' => 1])
            ->assertSessionHas('error', 'Vous ne pouvez pas réserver votre propre trajet.');

        // Pas plus de places que disponibles
        $this->actingAs($passenger)->post(route('rides.book', $ride), ['seats' => 3])->assertSessionHasErrors('seats');

        // Demande valide : en attente, elle occupe déjà les places, et le conducteur reçoit un email
        $this->post(route('rides.book', $ride), ['seats' => 2, 'message' => 'Merci !'])
            ->assertRedirect(route('rides.show', $ride))->assertSessionHas('success');
        $this->assertSame(BookingStatus::Pending, $ride->bookings()->first()->status);
        $this->assertSame(0, $ride->fresh()->seatsLeft());
        Notification::assertSentTo($ride->driver, BookingRequested::class);

        // Pas deux demandes
        $this->post(route('rides.book', $ride), ['seats' => 1])
            ->assertSessionHas('error', 'Votre demande est en attente de réponse du conducteur.');

        // Complet pour les autres
        $this->actingAs(User::factory()->create())->post(route('rides.book', $ride), ['seats' => 1])
            ->assertSessionHas('error', 'Ce trajet est complet.');

        $this->get(route('rides.show', $ride))->assertSee('Complet');
    }

    public function test_unverified_members_cannot_publish_or_book(): void
    {
        $ride = Ride::factory()->create();
        $unverified = User::factory()->unverified()->create();

        $this->actingAs($unverified)->get('/covoiturages/nouveau')->assertRedirect('/email/verification');
        $this->post(route('rides.book', $ride), ['seats' => 1])->assertRedirect('/email/verification');
        $this->assertDatabaseCount('ride_bookings', 0);
    }

    public function test_an_admin_can_moderate_any_ride(): void
    {
        $ride = Ride::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('rides.edit', $ride))->assertOk();
        $this->delete(route('rides.destroy', $ride), ['redirect' => '/admin/covoiturages'])->assertRedirect('/admin/covoiturages');
        $this->assertModelMissing($ride);
    }
}

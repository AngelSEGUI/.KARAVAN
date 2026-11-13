<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\BookingAccepted;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingDeclined;
use App\Notifications\BookingRequested;
use App\Notifications\ListingCancelled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Acceptation, refus et annulation des demandes, avec les emails envoyés à chaque étape. */
class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function requestSeat(Ride $ride, User $passenger, int $seats = 1)
    {
        $this->actingAs($passenger)->post(route('rides.book', $ride), ['seats' => $seats]);

        return $ride->bookings()->where('user_id', $passenger->id)->firstOrFail();
    }

    private function url($booking, string $action): string
    {
        return $booking->actionUrl($action);
    }

    public function test_the_driver_accepts_a_request(): void
    {
        $ride = Ride::factory()->create(['seats' => 3]);
        $passenger = User::factory()->create();
        $booking = $this->requestSeat($ride, $passenger, 2);

        $this->actingAs($ride->driver)->get('/mon-espace')->assertSee('Demandes à traiter')->assertSee($passenger->name);

        $this->patch($this->url($booking, 'accepter'))->assertSessionHas('success');

        $this->assertSame(BookingStatus::Accepted, $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->responded_at);
        Notification::assertSentTo($passenger, BookingAccepted::class);

        // Le passager voit sa demande acceptée et apparaît « à bord »
        $this->actingAs($passenger)->get(route('rides.show', $ride))->assertSee('Acceptée')->assertSee('À bord');
    }

    public function test_declining_frees_the_seats_and_blocks_a_new_request(): void
    {
        $ride = Ride::factory()->create(['seats' => 2]);
        $passenger = User::factory()->create();
        $booking = $this->requestSeat($ride, $passenger, 2);
        $this->assertSame(0, $ride->fresh()->seatsLeft());

        $this->actingAs($ride->driver)->patch($this->url($booking, 'refuser'))->assertSessionHas('info');

        $this->assertSame(BookingStatus::Declined, $booking->fresh()->status);
        $this->assertSame(2, $ride->fresh()->seatsLeft());
        Notification::assertSentTo($passenger, BookingDeclined::class);

        $this->actingAs($passenger)->post(route('rides.book', $ride), ['seats' => 1])
            ->assertSessionHas('error', "{$ride->driver->first_name} a refusé votre demande pour ce trajet.");
    }

    public function test_a_request_gets_a_single_answer(): void
    {
        $ride = Ride::factory()->create();
        $booking = $this->requestSeat($ride, User::factory()->create());

        $this->actingAs($ride->driver)->patch($this->url($booking, 'accepter'));
        $this->patch($this->url($booking, 'refuser'))->assertSessionHas('error');

        $this->assertSame(BookingStatus::Accepted, $booking->fresh()->status);
    }

    public function test_only_the_owner_can_answer_and_only_the_requester_can_cancel(): void
    {
        $ride = Ride::factory()->create();
        $passenger = User::factory()->create();
        $booking = $this->requestSeat($ride, $passenger);
        $stranger = User::factory()->create();

        $this->actingAs($passenger)->patch($this->url($booking, 'accepter'))->assertSessionHas('error');
        $this->actingAs($stranger)->patch($this->url($booking, 'refuser'))->assertSessionHas('error');
        $this->actingAs($stranger)->patch($this->url($booking, 'annuler'))->assertSessionHas('error');
        $this->actingAs($ride->driver)->patch($this->url($booking, 'annuler'))->assertSessionHas('error');

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
        Notification::assertNotSentTo($passenger, BookingAccepted::class);
    }

    public function test_the_passenger_cancels_then_requests_again(): void
    {
        $ride = Ride::factory()->create(['seats' => 2]);
        $passenger = User::factory()->create();
        $booking = $this->requestSeat($ride, $passenger, 2);
        $this->actingAs($ride->driver)->patch($this->url($booking, 'accepter'));

        $this->actingAs($passenger)->patch($this->url($booking, 'annuler'))->assertSessionHas('info');

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertSame(2, $ride->fresh()->seatsLeft());
        Notification::assertSentTo($ride->driver, BookingCancelled::class);

        // Nouvelle demande possible : la même ligne repasse « en attente »
        $this->post(route('rides.book', $ride), ['seats' => 1])->assertSessionHas('success');
        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
        $this->assertSame(1, $booking->fresh()->seats);
        $this->assertDatabaseCount('ride_bookings', 1);
        Notification::assertSentToTimes($ride->driver, BookingRequested::class, 2);
    }

    public function test_seats_cannot_drop_below_active_requests_but_ignore_declined_ones(): void
    {
        $ride = Ride::factory()->create(['seats' => 4]);
        $this->requestSeat($ride, User::factory()->create(), 2);
        $declined = $this->requestSeat($ride, User::factory()->create(), 2);
        $this->actingAs($ride->driver)->patch($this->url($declined, 'refuser'));

        $data = [
            'festival_id' => $ride->festival_id, 'departure_city' => 'Nantes',
            'departs_at' => now()->addMonth()->format('Y-m-d\TH:i'), 'price' => 10,
        ];
        $this->put(route('rides.update', $ride), $data + ['seats' => 1])->assertSessionHasErrors('seats');
        $this->put(route('rides.update', $ride), $data + ['seats' => 2])->assertSessionHasNoErrors();
    }

    public function test_housing_requests_follow_the_same_workflow(): void
    {
        $housing = Housing::factory()->create(['capacity' => 4]);
        $guest = User::factory()->create();

        $this->actingAs($guest)->post(route('housings.book', $housing), ['guests' => 2, 'nights' => 3]);
        $booking = $housing->bookings()->firstOrFail();
        Notification::assertSentTo($housing->host, BookingRequested::class);

        $this->actingAs($housing->host)->get(route('housings.show', $housing))->assertSee('Demandes reçues')->assertSee('Accepter');
        $this->patch($this->url($booking, 'accepter'))->assertSessionHas('success');
        Notification::assertSentTo($guest, BookingAccepted::class);

        $this->actingAs($guest)->patch($this->url($booking, 'annuler'));
        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        Notification::assertSentTo($housing->host, BookingCancelled::class);
    }

    public function test_deleting_a_listing_warns_active_requesters_only(): void
    {
        $ride = Ride::factory()->create(['seats' => 3]);
        $active = User::factory()->create();
        $declined = User::factory()->create();
        $this->requestSeat($ride, $active);
        $toDecline = $this->requestSeat($ride, $declined);
        $this->actingAs($ride->driver)->patch($this->url($toDecline, 'refuser'));

        $this->actingAs($ride->driver)->delete(route('rides.destroy', $ride));

        Notification::assertSentTo($active, ListingCancelled::class);
        Notification::assertNotSentTo($declined, ListingCancelled::class);
    }

    public function test_deleting_a_festival_or_an_account_warns_requesters(): void
    {
        $housing = Housing::factory()->create();
        $guest = User::factory()->create();
        $this->actingAs($guest)->post(route('housings.book', $housing), ['guests' => 1, 'nights' => 1]);

        $ride = Ride::factory()->create();
        $passenger = $this->requestSeat($ride, User::factory()->create())->passenger;

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->delete(route('admin.festivals.destroy', $housing->festival_id));
        Notification::assertSentTo($guest, ListingCancelled::class);

        // Le passager supprime son compte : le conducteur est prévenu de l'annulation
        $this->actingAs($passenger)->delete('/mon-espace', ['password' => 'password']);
        Notification::assertSentTo($ride->driver, BookingCancelled::class);
    }

    public function test_header_badge_counts_pending_requests(): void
    {
        $ride = Ride::factory()->create();
        $this->requestSeat($ride, User::factory()->create());

        $this->actingAs($ride->driver)->get('/')->assertSee('data-count="1"', false);
    }

    public function test_notification_emails_render_in_french(): void
    {
        $ride = Ride::factory()->for(Festival::factory()->state(['name' => 'Hellfest']))->create(['departure_city' => 'Paris']);
        $booking = $this->requestSeat($ride, User::factory()->create(['name' => 'Malik Benali']), 2);

        $mail = (new BookingRequested($booking))->toMail($ride->driver)->render();
        $this->assertStringContainsString('Malik Benali souhaite réserver votre trajet', (string) $mail);
        $this->assertStringContainsString('Paris → Hellfest', (string) $mail);
        $this->assertStringContainsString('Accepter ou refuser', (string) $mail);
        $this->assertStringContainsString("L'équipe Karavan", html_entity_decode((string) $mail, ENT_QUOTES));

        foreach ([BookingAccepted::class, BookingDeclined::class, BookingCancelled::class, ListingCancelled::class] as $class) {
            $this->assertNotEmpty((string) (new $class($booking))->toMail($booking->passenger)->render());
        }
    }
}

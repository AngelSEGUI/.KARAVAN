<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_members_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden()->assertSee('Accès réservé');
    }

    public function test_admin_pages_render(): void
    {
        Ride::factory()->create();
        Housing::factory()->create();

        $this->actingAs($this->admin);
        foreach (['/admin', '/admin/festivals', '/admin/festivals/nouveau', '/admin/covoiturages', '/admin/logements', '/admin/membres', '/admin/membres?filtre=admin&q=a'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('admin.festivals.edit', Festival::first()->id))->assertOk();
    }

    public function test_admin_creates_and_updates_a_festival(): void
    {
        $this->actingAs($this->admin)->post('/admin/festivals', [
            'name' => 'Été Électrique à Nîmes', 'city' => 'Nîmes', 'region' => 'Occitanie',
            'starts_on' => '2027-07-01', 'ends_on' => '2027-07-03', 'genres' => 'Électro, Pop', 'lineup' => 'A, B',
            'hue' => 200, 'is_featured' => '1',
        ])->assertRedirect();

        $festival = Festival::where('slug', 'ete-electrique-a-nimes')->firstOrFail();
        $this->assertSame(['Électro', 'Pop'], $festival->genres);
        $this->assertTrue($festival->is_featured);
        $this->get('/festivals/ete-electrique-a-nimes')->assertOk();

        $this->put(route('admin.festivals.update', $festival->id), [
            'name' => 'Été Électrique', 'slug' => 'ete-electrique', 'city' => 'Nîmes', 'region' => 'Occitanie',
            'starts_on' => '2027-07-01', 'ends_on' => '2027-07-03', 'genres' => 'Électro', 'hue' => 10,
        ])->assertSessionHasNoErrors();
        $this->assertSame('ete-electrique', $festival->fresh()->slug);
        $this->assertFalse($festival->fresh()->is_featured);
    }

    public function test_festival_validation(): void
    {
        Festival::factory()->create(['slug' => 'pris']);

        $this->actingAs($this->admin)->post('/admin/festivals', [
            'name' => 'X', 'slug' => 'pris', 'city' => '', 'region' => 'R',
            'starts_on' => '2027-07-05', 'ends_on' => '2027-07-01', 'genres' => '', 'hue' => 500,
        ])->assertSessionHasErrors(['slug', 'city', 'ends_on', 'genres', 'hue']);
    }

    public function test_toggle_featured_and_delete_festival_cascades(): void
    {
        $ride = Ride::factory()->create();
        $festival = $ride->festival;

        $this->actingAs($this->admin)->patch(route('admin.festivals.featured', $festival->id))->assertSessionHas('success');
        $this->assertTrue($festival->fresh()->is_featured);

        $this->delete(route('admin.festivals.destroy', $festival->id))->assertRedirect('/admin/festivals');
        $this->assertModelMissing($festival);
        $this->assertModelMissing($ride);
    }

    public function test_suspend_and_promote_members(): void
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.users.suspension', $member))->assertSessionHas('success');
        $this->assertTrue($member->fresh()->isSuspended());
        $this->patch(route('admin.users.suspension', $member));
        $this->assertFalse($member->fresh()->isSuspended());

        $this->patch(route('admin.users.role', $member));
        $this->assertSame(UserRole::Admin, $member->fresh()->role);
        $this->patch(route('admin.users.role', $member));
        $this->assertSame(UserRole::Member, $member->fresh()->role);
    }

    public function test_admin_cannot_act_on_own_account(): void
    {
        $this->actingAs($this->admin);
        $this->patch(route('admin.users.role', $this->admin))->assertSessionHas('error');
        $this->patch(route('admin.users.suspension', $this->admin))->assertSessionHas('error');
        $this->delete(route('admin.users.destroy', $this->admin))->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->isAdmin());
    }

    public function test_deleting_a_member_removes_their_listings(): void
    {
        $housing = Housing::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $housing->host))->assertRedirect('/admin/membres');
        $this->assertModelMissing($housing);
    }
}

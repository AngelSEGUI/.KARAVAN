<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Festival;
use App\Models\User;
use App\Notifications\ProRequestApproved;
use App\Notifications\ProRequestDeclined;
use App\Notifications\ProRequestReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Profil organisateur (pro), création et personnalisation des pages de festival, programmation. */
class OrganizerTest extends TestCase
{
    use RefreshDatabase;

    private function festivalOf(User $pro, array $attributes = []): Festival
    {
        return Festival::factory()->create([
            'user_id' => $pro->id, 'is_published' => false,
            'starts_on' => '2027-07-22', 'ends_on' => '2027-07-24', ...$attributes,
        ]);
    }

    /* ---------- Statut organisateur ---------- */

    public function test_member_requests_pro_status_and_admin_approves(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('pro-request.store'), [
            'organization' => 'Association Test', 'organization_website' => 'https://asso.example',
            'pro_request_message' => 'Nous organisons un festival.',
        ])->assertRedirect(route('profile.edit'));
        $this->assertTrue($member->fresh()->hasPendingProRequest());
        Notification::assertSentTo($admin, ProRequestReceived::class);

        // Une seconde demande est ignorée
        $this->post(route('pro-request.store'), ['organization' => 'Autre'])->assertSessionHas('info');

        $this->actingAs($admin)->get('/admin/membres?filtre=demandes-pro')->assertSee('Association Test');
        $this->patch(route('admin.users.pro-request', [$member, 'valider']))->assertSessionHas('success');

        $member->refresh();
        $this->assertSame(UserRole::Pro, $member->role);
        $this->assertFalse($member->hasPendingProRequest());
        Notification::assertSentTo($member, ProRequestApproved::class);
    }

    public function test_admin_declines_pro_request(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['organization' => 'X', 'pro_requested_at' => now()]);

        $this->actingAs($admin)->patch(route('admin.users.pro-request', [$member, 'refuser']))->assertSessionHas('info');
        $this->assertSame(UserRole::Member, $member->fresh()->role);
        $this->assertFalse($member->fresh()->hasPendingProRequest());
        Notification::assertSentTo($member, ProRequestDeclined::class);
    }

    public function test_pro_request_requires_an_organization(): void
    {
        $this->actingAs(User::factory()->create())->post(route('pro-request.store'), ['organization' => ''])
            ->assertSessionHasErrors('organization');
    }

    /* ---------- Accès à l'espace organisateur ---------- */

    public function test_only_pros_and_admins_can_create_festivals(): void
    {
        $this->get(route('organizer.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('organizer.index'))->assertForbidden();
        $this->post(route('organizer.festivals.store'), ['name' => 'X'])->assertForbidden();

        $this->actingAs(User::factory()->pro()->create())->get(route('organizer.index'))->assertOk()->assertSee('Mes festivals');
        $this->actingAs(User::factory()->unverified()->pro()->create())->get(route('organizer.index'))->assertRedirect(route('verification.notice'));
    }

    public function test_pro_creates_a_draft_festival(): void
    {
        $pro = User::factory()->pro()->create();

        $this->actingAs($pro)->post(route('organizer.festivals.store'), [
            'name' => 'Marées Test', 'city' => 'Saint-Malo', 'region' => 'Bretagne',
            'starts_on' => '2027-07-22', 'ends_on' => '2027-07-24', 'genres' => 'Électro, Pop',
            'website' => 'https://maree.example', 'ticket_url' => 'pas-une-url',
        ])->assertSessionHasErrors('ticket_url');

        $this->post(route('organizer.festivals.store'), [
            'name' => 'Marées Test', 'city' => 'Saint-Malo', 'region' => 'Bretagne',
            'starts_on' => '2027-07-22', 'ends_on' => '2027-07-24', 'genres' => 'Électro, Pop',
        ]);
        $festival = Festival::where('slug', 'marees-test')->firstOrFail();
        $this->assertSame($pro->id, $festival->user_id);
        $this->assertFalse($festival->is_published);

        // Brouillon : invisible du public et des listes, visible par son organisateur
        $this->get('/festivals/marees-test')->assertOk()->assertSee('Brouillon');
        auth()->logout();
        $this->get('/festivals/marees-test')->assertNotFound();
        $this->get('/festivals')->assertDontSee('Marées Test');

        $this->actingAs($pro)->patch(route('organizer.festivals.publish', $festival->id))->assertSessionHas('success');
        auth()->logout();
        $this->get('/festivals/marees-test')->assertOk()->assertDontSee('Brouillon');
    }

    public function test_pro_cannot_edit_another_organizers_festival_but_admin_can(): void
    {
        $owner = User::factory()->pro()->create();
        $festival = $this->festivalOf($owner);

        $this->actingAs(User::factory()->pro()->create());
        $this->get(route('organizer.festivals.edit', $festival->id))->assertForbidden();
        $this->put(route('organizer.festivals.design', $festival->id), ['palette' => 'neon'])->assertForbidden();
        $this->delete(route('organizer.festivals.destroy', $festival->id))->assertForbidden();

        $admin = User::factory()->admin()->create();
        foreach (array_keys(\App\Http\Controllers\Organizer\FestivalController::TABS) as $tab) {
            $this->actingAs($admin)->get(route('organizer.festivals.edit', [$festival->id, $tab]))->assertOk();
        }
        $this->actingAs($owner)->get(route('organizer.index'))->assertSee($festival->name);
    }

    public function test_cannot_book_offers_for_a_draft_festival(): void
    {
        $festival = $this->festivalOf(User::factory()->pro()->create());

        $this->actingAs(User::factory()->create())->post(route('rides.store'), [
            'festival_id' => $festival->id, 'departure_city' => 'Rennes', 'departs_at' => '2027-07-22 10:00',
            'seats' => 2, 'price' => 10,
        ])->assertSessionHasErrors('festival_id');
    }

    /* ---------- Apparence ---------- */

    public function test_design_palette_fonts_and_sections(): void
    {
        $pro = User::factory()->pro()->create();
        $festival = $this->festivalOf($pro);

        $this->actingAs($pro)->put(route('organizer.festivals.design', $festival->id), [
            'palette' => 'custom', 'primary' => '#ff6b4a', 'secondary' => '#3ad6c5', 'mode' => 'dark',
            'heading_font' => 'Unbounded', 'body_font' => 'Work Sans', 'hue' => 20,
            'sections' => [
                ['title' => 'Venir', 'body' => 'En train.'],
                ['title' => '', 'body' => ''],          // bloc vide ignoré
                ['title' => '', 'body' => 'Sans titre'],
            ],
        ])->assertSessionHasNoErrors();

        $festival->refresh();
        $this->assertSame('custom', $festival->design['palette']);
        $this->assertSame('Unbounded', $festival->design['heading_font']);
        $this->assertSame(['Venir', null], $festival->sections->pluck('title')->all());

        $page = $this->get('/festivals/' . $festival->slug)->assertOk();
        $page->assertSee('--color-primary: #ff6b4a', false)->assertSee('family=Unbounded', false)->assertSee('En train.');

        $this->put(route('organizer.festivals.design', $festival->id), [
            'palette' => 'inconnue', 'heading_font' => 'Comic Sans', 'body_font' => 'Inter', 'hue' => 400,
            'sections' => [['title' => 'Titre seul', 'body' => '']],
        ])->assertSessionHasErrors(['palette', 'heading_font', 'hue', 'sections.0.body']);

        $this->put(route('organizer.festivals.design', $festival->id), [
            'palette' => 'custom', 'primary' => 'rouge', 'heading_font' => 'Syne', 'body_font' => 'Inter', 'hue' => 1,
        ])->assertSessionHasErrors(['primary', 'secondary', 'mode']);
    }

    public function test_readable_text_color_on_light_and_dark_colors(): void
    {
        $this->assertSame('#ffffff', \App\Support\FestivalDesign::readableOn('#0e7c9b'));
        $this->assertSame('#17132b', \App\Support\FestivalDesign::readableOn('#f5c400'));
    }

    /* ---------- Images ---------- */

    public function test_media_upload_replace_and_remove(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Extension GD requise pour générer des images de test.');
        }
        Storage::fake('uploads');
        $pro = User::factory()->pro()->create();
        $festival = $this->festivalOf($pro, ['is_published' => true]);

        $this->actingAs($pro)->post(route('organizer.festivals.media', $festival->id), [
            'banner' => UploadedFile::fake()->image('banniere.jpg', 1920, 800),
            'icon' => UploadedFile::fake()->image('icone.png', 256, 256),
            'poster' => UploadedFile::fake()->image('affiche.jpg', 600, 900),
            'photos' => [UploadedFile::fake()->image('a.jpg', 800, 600), UploadedFile::fake()->image('b.jpg', 800, 600)],
        ])->assertSessionHasNoErrors();

        $festival->refresh();
        Storage::disk('uploads')->assertExists([$festival->banner_path, $festival->icon_path, $festival->poster_path]);
        $this->assertCount(2, $festival->photos);
        $oldBanner = $festival->banner_path;

        // Remplacement : l'ancien fichier est supprimé ; légendes enregistrées
        $photo = $festival->photos->first();
        $this->post(route('organizer.festivals.media', $festival->id), [
            'banner' => UploadedFile::fake()->image('nouvelle.jpg', 1920, 800),
            'captions' => [$photo->id => 'Grande scène'],
        ]);
        Storage::disk('uploads')->assertMissing($oldBanner);
        $this->assertSame('Grande scène', $photo->fresh()->caption);

        $page = $this->get('/festivals/' . $festival->slug);
        $page->assertSee($festival->fresh()->bannerUrl())->assertSee('Grande scène')->assertSee("L'affiche officielle", false);

        // Icône trop petite et fichier non image refusés
        $this->post(route('organizer.festivals.media', $festival->id), [
            'icon' => UploadedFile::fake()->image('mini.png', 20, 20),
            'poster' => UploadedFile::fake()->create('affiche.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['icon', 'poster']);

        // Retrait
        $path = $photo->path;
        $this->delete(route('organizer.festivals.photos.destroy', [$festival->id, $photo->id]));
        Storage::disk('uploads')->assertMissing($path);
        $poster = $festival->fresh()->poster_path;
        $this->delete(route('organizer.festivals.media.destroy', [$festival->id, 'poster']));
        $this->assertNull($festival->fresh()->poster_path);
        Storage::disk('uploads')->assertMissing($poster);

        // Supprimer le festival supprime son dossier d'images
        $this->delete(route('organizer.festivals.destroy', $festival->id))->assertRedirect(route('organizer.index'));
        Storage::disk('uploads')->assertMissing($festival->mediaDirectory());
    }

    /* ---------- Programmation ---------- */

    public function test_programme_sync_handles_after_midnight_and_capacity(): void
    {
        $pro = User::factory()->pro()->create();
        $festival = $this->festivalOf($pro, ['is_published' => true]);

        $this->actingAs($pro)->put(route('organizer.festivals.programme', $festival->id), ['performances' => [
            ['day' => '2027-07-22', 'start' => '21:00', 'end' => '22:30', 'artist' => 'Nova Brume', 'stage' => 'Grande Scène', 'capacity' => '12000'],
            ['day' => '2027-07-22', 'start' => '23:30', 'end' => '01:00', 'artist' => 'Atlas Vega', 'stage' => 'Chapiteau', 'capacity' => ''],
            ['day' => '2027-07-22', 'start' => '01:30', 'end' => '03:00', 'artist' => 'Nuit Blanche', 'stage' => 'Chapiteau'],
            ['day' => '', 'start' => '', 'end' => '', 'artist' => '', 'stage' => '', 'capacity' => ''],   // ligne vide ignorée
        ]])->assertSessionHasNoErrors();

        $p = $festival->performances()->get()->keyBy('artist');
        $this->assertCount(3, $p);
        $this->assertSame('2027-07-23 01:00', $p['Atlas Vega']->ends_at->format('Y-m-d H:i'));
        $this->assertSame('2027-07-23 01:30', $p['Nuit Blanche']->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2027-07-22', $p['Nuit Blanche']->festivalDay());
        $this->assertNull($p['Atlas Vega']->capacity);

        // Calendrier public : une seule journée, capacité affichée seulement si renseignée
        $page = $this->get('/festivals/' . $festival->slug)->assertOk();
        $page->assertSee('Programmation')->assertSee('data-artist="Nova Brume"', false)
            ->assertSee('data-capacity="12 000"', false)->assertSee('Jeudi 22 juillet');
        $this->assertSame(1, substr_count($page->getContent(), 'data-capacity='));
        $this->assertSame(1, substr_count($page->getContent(), 'role="tabpanel" id="tt-day-'));

        // Mise à jour : la ligne gardée est modifiée, les autres supprimées
        $this->put(route('organizer.festivals.programme', $festival->id), ['performances' => [
            ['id' => $p['Nova Brume']->id, 'day' => '2027-07-23', 'start' => '20:00', 'end' => '21:00', 'artist' => 'Nova Brume', 'stage' => 'Grande Scène'],
        ]]);
        $this->assertSame(1, $festival->performances()->count());
        $this->assertSame($p['Nova Brume']->id, $festival->performances()->first()->id);
        $this->assertNull($festival->performances()->first()->capacity);
    }

    public function test_programme_validation_reports_line_numbers(): void
    {
        $pro = User::factory()->pro()->create();
        $festival = $this->festivalOf($pro);

        $this->actingAs($pro)->put(route('organizer.festivals.programme', $festival->id), ['performances' => [
            ['day' => '2027-07-22', 'start' => '20:00', 'end' => '21:00', 'artist' => 'Ok', 'stage' => 'A'],
            ['day' => '2027-08-01', 'start' => '25:00', 'end' => '21:00', 'artist' => 'Hors dates', 'stage' => 'A', 'capacity' => '-3'],
        ]])->assertSessionHasErrors(['performances.1.day', 'performances.1.start', 'performances.1.capacity']);

        $errors = session('errors')->all();
        $this->assertTrue(collect($errors)->contains(fn ($m) => str_starts_with($m, 'Ligne 2 :')), implode(' | ', $errors));
        $this->assertSame(0, $festival->performances()->count());
    }

    public function test_performance_from_another_festival_cannot_be_hijacked(): void
    {
        $pro = User::factory()->pro()->create();
        $mine = $this->festivalOf($pro);
        $other = $this->festivalOf(User::factory()->pro()->create());
        $foreign = $other->performances()->create(['artist' => 'X', 'stage' => 'A', 'starts_at' => '2027-07-22 20:00', 'ends_at' => '2027-07-22 21:00']);

        $this->actingAs($pro)->put(route('organizer.festivals.programme', $mine->id), ['performances' => [
            ['id' => $foreign->id, 'day' => '2027-07-22', 'start' => '20:00', 'end' => '21:00', 'artist' => 'Volé', 'stage' => 'A'],
        ]]);
        $this->assertSame('X', $foreign->fresh()->artist);
        $this->assertSame(1, $mine->performances()->count());
    }
}

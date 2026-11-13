<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
    }

    public function test_the_notice_page_is_shown_to_unverified_members(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/email/verification')->assertOk()->assertSee($user->email)->assertSee('Renvoyer');
        $this->get('/mon-espace')->assertOk()->assertSee('Confirmez votre adresse');
    }

    public function test_verified_members_skip_the_notice(): void
    {
        $this->actingAs(User::factory()->create())->get('/email/verification')->assertRedirect('/mon-espace');
    }

    public function test_the_link_verifies_the_address(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get($this->verificationUrl($user))->assertRedirect('/mon-espace')->assertSessionHas('success');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get('/covoiturages/nouveau')->assertOk();
    }

    public function test_a_tampered_or_foreign_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();

        $this->actingAs($user)->get($this->verificationUrl($user) . 'x')->assertForbidden();
        $this->actingAs($user)->get($this->verificationUrl($other))->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_the_email_can_be_sent_again(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post('/email/verification')->assertSessionHas('success');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_the_verification_email_is_in_french(): void
    {
        $user = User::factory()->unverified()->create(['name' => 'Léa Martin']);
        $mail = (string) (new VerifyEmail)->toMail($user)->render();

        $this->assertStringContainsString('Bienvenue Léa', $mail);
        $this->assertStringContainsString('Confirmer mon adresse', $mail);
    }

    public function test_dev_shortcut_link_is_shown_only_locally_with_log_mailer(): void
    {
        $user = User::factory()->unverified()->create();
        config(['mail.default' => 'log']);

        $this->app['env'] = 'local';
        $this->actingAs($user)->get('/email/verification')->assertSee('Mode développement');

        $this->app['env'] = 'production';
        $this->get('/email/verification')->assertDontSee('Mode développement');
    }
}

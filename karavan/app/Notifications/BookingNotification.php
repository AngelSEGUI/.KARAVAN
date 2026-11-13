<?php

namespace App\Notifications;

use App\Models\HousingBooking;
use App\Models\RideBooking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/**
 * Base des emails liés à une réservation (trajet ou logement).
 *
 * Envoi immédiat (synchrone). Pour envoyer en arrière-plan en production :
 * ajouter « implements ShouldQueue » + « use Queueable » et lancer « php artisan queue:work ».
 */
abstract class BookingNotification extends Notification
{
    public function __construct(public RideBooking|HousingBooking $booking)
    {
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    abstract public function toMail(object $notifiable): MailMessage;

    /** Message de départ commun : salutation et signature en français. */
    protected function message(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Bonjour ' . $notifiable->first_name . ',')
            ->salutation(new HtmlString("À bientôt sur la route,<br>L'équipe Karavan"));
    }

    protected function isRide(): bool
    {
        return $this->booking instanceof RideBooking;
    }

    /** « trajet » ou « logement » */
    protected function kind(): string
    {
        return $this->isRide() ? 'trajet' : 'logement';
    }

    /** Lien vers les autres offres pour le même festival. */
    protected function alternativesUrl(): string
    {
        return $this->isRide()
            ? route('rides.index', ['festival' => $this->booking->ride->festival_id])
            : route('housings.index', ['festival' => $this->booking->housing->festival_id]);
    }
}

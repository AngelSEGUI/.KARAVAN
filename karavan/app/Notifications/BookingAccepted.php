<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Au passager / voyageur : sa demande est acceptée. */
class BookingAccepted extends BookingNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $owner = $booking->owner();

        return $this->message($notifiable)
            ->subject("C'est validé : « {$booking->listingTitle()} »")
            ->line("Bonne nouvelle : {$owner->name} a accepté votre demande pour « {$booking->listingTitle()} ».")
            ->line('Détail : ' . $booking->summary() . ' · total ' . euros($booking->total()) . '.')
            ->action('Voir l\'annonce', $booking->listingUrl())
            ->line("Un empêchement ? Vous pouvez annuler depuis votre espace : {$owner->first_name} sera prévenu(e) automatiquement.");
    }
}

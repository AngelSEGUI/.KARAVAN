<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Au passager / voyageur : l'annonce sur laquelle il avait une demande est supprimée. */
class ListingCancelled extends BookingNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;

        return $this->message($notifiable)
            ->subject("Annulation : « {$booking->listingTitle()} »")
            ->line("L'annonce « {$booking->listingTitle()} » a été supprimée. Votre demande ({$booking->summary()}) est donc annulée.")
            ->line("Nous sommes désolés pour ce contretemps. D'autres membres proposent peut-être une solution pour ce festival.")
            ->action('Voir les autres offres', $this->alternativesUrl());
    }
}

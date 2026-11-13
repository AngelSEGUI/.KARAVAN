<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Au passager / voyageur : sa demande est refusée. */
class BookingDeclined extends BookingNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $other = $this->isRide() ? "d'autres trajets" : "d'autres logements";

        return $this->message($notifiable)
            ->subject("Votre demande pour « {$booking->listingTitle()} » n'a pas été retenue")
            ->line("{$booking->owner()->name} n'a pas pu accepter votre demande pour « {$booking->listingTitle()} ».")
            ->line("Pas d'inquiétude : {$other} sont proposés par la communauté pour ce festival.")
            ->action('Voir les autres offres', $this->alternativesUrl());
    }
}

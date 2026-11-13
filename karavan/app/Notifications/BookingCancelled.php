<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Au conducteur / à l'hôte : le demandeur annule sa demande. */
class BookingCancelled extends BookingNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $freed = $this->isRide() ? ' Les places sont de nouveau disponibles.' : '';

        return $this->message($notifiable)
            ->subject("Demande annulée pour « {$booking->listingTitle()} »")
            ->line("{$booking->requester()->name} a annulé sa demande pour « {$booking->listingTitle()} » ({$booking->summary()}).{$freed}")
            ->action('Voir mon annonce', $booking->listingUrl());
    }
}

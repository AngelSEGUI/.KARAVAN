<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Au conducteur / à l'hôte : un membre demande une place ou un séjour. */
class BookingRequested extends BookingNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $mail = $this->message($notifiable)
            ->subject("Nouvelle demande pour « {$booking->listingTitle()} »")
            ->line("{$booking->requester()->name} souhaite réserver votre {$this->kind()} « {$booking->listingTitle()} ».")
            ->line('Détail : ' . $booking->summary() . '.');

        if ($booking->message) {
            $mail->line("Son message : « {$booking->message} »");
        }

        return $mail
            ->action('Accepter ou refuser', route('account') . '#demandes')
            ->line('Pensez à répondre rapidement : la demande reste en attente tant que vous ne l\'avez pas traitée.');
    }
}

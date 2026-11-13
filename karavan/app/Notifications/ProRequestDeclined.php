<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Au demandeur : sa demande de profil organisateur n'est pas retenue. */
class ProRequestDeclined extends ProNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->message($notifiable)
            ->subject('Votre demande de profil organisateur')
            ->line("Nous n'avons pas pu valider votre demande de profil organisateur pour « {$this->applicant->organization} ».")
            ->line("Les profils organisateurs sont réservés aux structures qui organisent un festival. Si vous pensez qu'il s'agit d'une erreur, vous pouvez refaire une demande en précisant votre événement.")
            ->action('Refaire une demande', route('profile.edit') . '#organisateur');
    }
}

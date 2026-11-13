<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Au demandeur : son profil organisateur est validé. */
class ProRequestApproved extends ProNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->message($notifiable)
            ->subject('Votre profil organisateur est activé')
            ->line("Bonne nouvelle : votre profil organisateur pour « {$this->applicant->organization} » est validé.")
            ->line('Vous pouvez maintenant créer la page de votre festival : couleurs, polices, photos, affiche et programmation complète.')
            ->action('Créer mon festival', route('organizer.festivals.create'))
            ->line('Votre page reste un brouillon invisible du public jusqu\'à ce que vous la publiiez.');
    }
}

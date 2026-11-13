<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Aux administrateurs : un membre demande un profil organisateur. */
class ProRequestReceived extends ProNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->applicant;
        $mail = $this->message($notifiable)
            ->subject("Demande de profil organisateur : {$a->organization}")
            ->line("{$a->name} ({$a->email}) demande un profil organisateur pour « {$a->organization} ».");

        if ($a->organization_website) {
            $mail->line("Site web : {$a->organization_website}");
        }
        if ($a->pro_request_message) {
            $mail->line("Message : « {$a->pro_request_message} »");
        }

        return $mail->action('Examiner la demande', route('admin.users.index', ['filtre' => 'demandes-pro']));
    }
}

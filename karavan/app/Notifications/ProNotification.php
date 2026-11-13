<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/** Base des emails liés aux demandes de profil organisateur. */
abstract class ProNotification extends Notification
{
    public function __construct(public User $applicant)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    abstract public function toMail(object $notifiable): MailMessage;

    protected function message(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Bonjour ' . $notifiable->first_name . ',')
            ->salutation(new HtmlString("À bientôt sur la route,<br>L'équipe Karavan"));
    }
}

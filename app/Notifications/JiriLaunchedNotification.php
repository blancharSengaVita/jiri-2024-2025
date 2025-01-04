<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Jiri;
use Illuminate\Support\Facades\Auth;

class JiriLaunchedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Jiri $jiri;
    public Contact $evaluator;
    public string $name;
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(Jiri $jiri, Contact $evaluator, string $name, string $token)
    {
        $this->jiri = $jiri;
        $this->evaluator = $evaluator;
        $this->name = $name;
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Le Jiri est lancé !')
            ->greeting('Bonjour ' . $this->evaluator->name . ',')
            ->line('Nous avons le plaisir de vous annoncer que le jiri ' . $this->jiri->name . ' est maintenant lancé.')
            ->line('Vous pouvez maintenant accéder à la plateforme et démarrer les évaluations.')
            ->action('Accéder au jiri', url('/evaluator?token='. $this->token))
            ->line(__('Cordialement,'))
            ->salutation(__('jiri.mail.salutation', ['name' => $this->name]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

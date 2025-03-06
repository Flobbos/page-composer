<?php

namespace Flobbos\PageComposer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugResponseNotification extends Notification
{
    use Queueable;

    public $bugId;
    public $name;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $bugId, string $name)
    {
        $this->bugId = $bugId;
        $this->name = $name;
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
            ->line($this->name . 'has responded to your bug report.')
            ->action('View response here', url('/page-composer?bugId=' . $this->bugId))
            ->line('Thank you for helping out.');
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

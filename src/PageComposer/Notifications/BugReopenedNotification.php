<?php

namespace Flobbos\PageComposer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugReopenedNotification extends Notification
{
    use Queueable;

    public $title;
    public $bugId;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, int $bugId)
    {
        $this->title = $title;
        $this->bugId = $bugId;
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
            ->line('The isse ' . $this->title . ' has been reopened.')
            ->action('View the issue here', url('/page-composer?bugId=' . $this->bugId))
            ->line('Please take a look!');
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

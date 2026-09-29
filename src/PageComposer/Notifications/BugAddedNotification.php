<?php

namespace Flobbos\PageComposer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugAddedNotification extends Notification
{
    use Queueable;

    public $bugId;
    public $submittedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $bugId, string $submittedBy)
    {
        $this->bugId = $bugId;
        $this->submittedBy = $submittedBy;
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
            ->line('A new bug has been added at ' . env('APP_NAME') . '.')
            ->action('View the new request here ', route('page-composer::bugs', ['bugId' => $this->bugId]))
            ->line('Request was submitted by: ' . $this->submittedBy);
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

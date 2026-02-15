<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public $action;
    public $module;
    public $description;
    public $actorName;

    /**
     * Create a new notification instance.
     */
    public function __construct($action, $module, $description, $actorName)
    {
        $this->action = $action;
        $this->module = $module;
        $this->description = $description;
        $this->actorName = $actorName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'module' => $this->module,
            'description' => $this->description,
            'actor_name' => $this->actorName,
            'time' => now()->toDateTimeString(),
        ];
    }
}

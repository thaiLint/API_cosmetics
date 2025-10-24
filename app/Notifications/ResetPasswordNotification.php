<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = url('/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset()));

        return (new MailMessage)
            ->subject(' Reset your Cosmetics App password')
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . '!')
            ->line('We received a request to reset your password for your Cosmetics App account.')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, please ignore this email.')
            ->salutation('Thank you for using Cosmetics App ');
    }

    /**
     * Get the array representation of the notification (optional).
     */
    public function toArray($notifiable)
    {
        return [];
    }
}

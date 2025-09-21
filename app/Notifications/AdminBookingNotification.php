<?php

namespace App\Notifications;

use App\Mail\AdminBookingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $booking;
    /**
     * Create a new notification instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
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
    public function toMail(object $notifiable)
    {
        $email =  $this->getEmail($notifiable);
        \Log::info('Email', [$email]);
        return (new AdminBookingMail($this->booking))->to($email);
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

    protected function getEmail(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            if ($email = $notifiable->routeNotificationFor('mail')) {
                return $email;
            }
        }


        return $notifiable->email ?? null;
    }
}

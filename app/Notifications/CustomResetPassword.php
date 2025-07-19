<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $link = url("/reset-password-redirect?token={$this->token}&email={$this->email}");

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->line('Click the button below to reset your password.')
            ->action('Reset Password', $link)
            ->line('If you didn’t request a password reset, please ignore this email.');
    }
}

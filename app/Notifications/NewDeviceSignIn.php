<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceSignIn extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $deviceName,
        private readonly ?string $ipAddress,
        private readonly \DateTimeInterface $signedInAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('auth.new_device.subject'))
            ->greeting(trans('auth.new_device.greeting'))
            ->line(trans('auth.new_device.body', [
                'device' => $this->deviceName,
                'time' => $this->signedInAt->format('d M Y, H:i').' UTC',
                'ip' => $this->ipAddress ?? trans('auth.new_device.unknown_ip'),
            ]))
            ->line(trans('auth.new_device.not_you'));
    }
}

<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDelivered extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order
    ) {
    }

    public function via($notifiable): array
    {
        $channels = [];

        if ($notifiable->notify_email) {
            $channels[] = 'mail';
        }

        if ($notifiable->notify_telegram && $notifiable->telegramLinks()->exists()) {
            $channels[] = \App\Notifications\Channels\TelegramChannel::class;
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Заказ #' . $this->order->id . ' выдан')
            ->greeting('UC успешно доставлены!')
            ->line('Заказ #' . $this->order->id . ' на PUBG ID ' . $this->order->uid . ' выполнен.');
    }

    public function toTelegram($notifiable): string
    {
        return "🎮 Заказ #{$this->order->id} выдан на PUBG ID {$this->order->uid}.";
    }
}
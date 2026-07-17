<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaymentConfirmed extends Notification
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
            ->subject('Оплата заказа #' . $this->order->id . ' подтверждена')
            ->greeting('Оплата получена!')
            ->line('Заказ #' . $this->order->id . ' на сумму ' . (int) $this->order->price . ' ₽ оплачен.')
            ->line('Мы уже готовим выдачу.');
    }

    public function toTelegram($notifiable): string
    {
        return "✅ Оплата заказа #{$this->order->id} на сумму " . (int) $this->order->price . " ₽ подтверждена.\nГотовим выдачу.";
    }
}
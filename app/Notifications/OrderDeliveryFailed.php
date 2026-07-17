<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveryFailed extends Notification
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
            ->subject('Проблема с выдачей заказа #' . $this->order->id)
            ->greeting('Возникла задержка')
            ->line('Заказ #' . $this->order->id . ' оплачен, но выдача пока не завершена.')
            ->line('Наша поддержка уже уведомлена и разберётся в ближайшее время.');
    }

    public function toTelegram($notifiable): string
    {
        return "⚠️ Заказ #{$this->order->id} оплачен, но выдача пока не завершена. Поддержка уже уведомлена.";
    }
}
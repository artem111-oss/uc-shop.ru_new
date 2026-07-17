<?php

namespace App\Notifications\Channels;

use App\Helpers\Telegram;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TelegramChannel
{
    public function __construct(
        private readonly Telegram $telegram
    ) {
    }

    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toTelegram')) {
            return;
        }

        $message = $notification->toTelegram($notifiable);

        if (!$message) {
            return;
        }

        $links = $notifiable->telegramLinks ?? $notifiable->telegramLinks()->get();

        foreach ($links as $link) {
            try {
                $this->telegram->sendMessage((string) $link->telegram_id, $message);
            } catch (\Throwable $e) {
                Log::warning('TelegramChannel: send failed', [
                    'bot' => $link->bot,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
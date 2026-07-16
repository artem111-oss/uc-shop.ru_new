<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Telegram
{
    protected string $bot;
    protected string $apiUrl;

    public function __construct()
    {
        $this->bot    = (string) config('services.telegram.bot_token');
        $this->apiUrl = rtrim((string) config('services.telegram.api_url', 'https://api.telegram.org'), '/');
    }

    public function sendMessage(string $chat_id, string $message, array $buttons = []): void
    {
        try {
            $payload = [
                'chat_id'    => $chat_id,
                'text'       => $message,
                'parse_mode' => 'html',
            ];

            if (! empty($buttons)) {
                $payload['reply_markup'] = $buttons;
            }

            Http::timeout(10)
                ->post($this->apiUrl . '/bot' . $this->bot . '/sendMessage', $payload);

        } catch (Throwable $e) {
            Log::error('Ошибка при отправке в Telegram: ' . $e->getMessage());
        }
    }
}
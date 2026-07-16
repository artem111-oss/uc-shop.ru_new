<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CheckTelegramProxy extends Command
{
    protected $signature = 'telegram:check-proxy';
    protected $description = 'Checks Telegram API availability through configured Worker URL';

    public function handle(): int
    {
        $checkedAt = now()->toIso8601String();
        $isHealthy = false;

        try {
            $token  = (string) config('services.telegram.bot_token');
            $apiUrl = rtrim((string) config('services.telegram.api_url', 'https://api.telegram.org'), '/');

            if ($token === '') {
                throw new \RuntimeException('Telegram token is not configured.');
            }

            $response = Http::timeout(15)
                ->get($apiUrl . '/bot' . $token . '/getMe');

            $isHealthy = $response->successful() && $response->json('ok') === true;

        } catch (Throwable $e) {
            $isHealthy = false;
        }

        Storage::put('monitoring/telegram-proxy.json', json_encode([
            'checked_at' => $checkedAt,
            'healthy'    => $isHealthy,
        ], JSON_THROW_ON_ERROR));

        if (! $isHealthy) {
            Log::warning('Telegram Worker health check failed.', ['checked_at' => $checkedAt]);
            $this->error('Telegram Worker is unavailable.');
            return self::FAILURE;
        }

        $this->info('Telegram Worker is available.');
        return self::SUCCESS;
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\TelegramLinkService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly TelegramLinkService $service
    ) {
    }

    public function uctyt(Request $request): Response
    {
        $expectedSecret = config('services.telegram.uctyt_webhook_secret');
        $providedSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');

        if (!$expectedSecret || !hash_equals($expectedSecret, (string) $providedSecret)) {
            Log::warning('Telegram webhook: invalid secret token attempt.');

            return response()->noContent(403);
        }

        $message = $request->input('message');
        $text = (string) ($message['text'] ?? '');
        $telegramUser = $message['from'] ?? null;

        if (!$telegramUser || !str_starts_with($text, '/start link_')) {
            return response()->noContent(200);
        }

        $token = substr($text, strlen('/start link_'));
        $payload = $this->service->resolveLinkToken($token);

        if (!$payload || $payload['bot'] !== 'uctyt') {
            return response()->noContent(200);
        }

        $this->service->link(
            userId: $payload['user_id'],
            bot: 'uctyt',
            telegramId: (int) $telegramUser['id'],
            username: $telegramUser['username'] ?? null,
            firstName: $telegramUser['first_name'] ?? null
        );

        return response()->noContent(200);
    }
}
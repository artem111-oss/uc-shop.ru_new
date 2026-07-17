<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\TelegramLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramLinkController extends Controller
{
    private const ALLOWED_BOTS = ['uctyt'];

    private const BOT_USERNAMES = [
        'uctyt' => 'uctyt_bot',
    ];

    public function __construct(
        private readonly TelegramLinkService $service
    ) {
    }

    public function createLinkToken(Request $request): JsonResponse
    {
        $bot = (string) $request->input('bot', 'uctyt');

        if (!in_array($bot, self::ALLOWED_BOTS, true)) {
            return response()->json(['message' => 'Бот недоступен.'], 422);
        }

        $token = $this->service->makeLinkToken($request->user(), $bot);
        $botUsername = self::BOT_USERNAMES[$bot];

        return response()->json([
            'deep_link' => "https://t.me/{$botUsername}?start=link_{$token}",
            'expires_in_minutes' => 10,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $links = $request->user()->telegramLinks()->get(['bot', 'telegram_username', 'linked_at']);

        return response()->json(['data' => $links]);
    }

    public function unlink(Request $request, string $bot): JsonResponse
    {
        $request->user()->telegramLinks()->where('bot', $bot)->delete();

        return response()->json(['message' => 'Отключено.']);
    }
}
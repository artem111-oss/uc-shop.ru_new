<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramLink;
use App\Services\Auth\TelegramWidgetAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWidgetLinkController extends Controller
{
    public function __construct(
        private readonly TelegramWidgetAuthService $service
    ) {
    }

    public function link(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'auth_date' => ['required', 'integer'],
            'hash' => ['required', 'string'],
        ]);

        $botToken = (string) config('services.telegram.bot_token');

        if ($botToken === '' || !$this->service->verify($validated, $botToken)) {
            return response()->json([
                'message' => 'Подпись Telegram недействительна.',
            ], 422);
        }

        $existingLink = TelegramLink::query()
            ->where('bot', 'uctyt')
            ->where('telegram_id', $validated['id'])
            ->first();

        if ($existingLink && $existingLink->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Этот Telegram уже привязан к другому аккаунту.',
            ], 409);
        }

        $link = TelegramLink::query()->updateOrCreate(
            [
                'bot' => 'uctyt',
                'telegram_id' => $validated['id'],
            ],
            [
                'user_id' => $request->user()->id,
                'telegram_username' => $validated['username'] ?? null,
                'telegram_first_name' => $validated['first_name'] ?? null,
                'linked_at' => now(),
            ]
        );

        return response()->json([
                'data' => [
                'bot' => $link->bot,
                'telegram_username' => $link->telegram_username,
                'linked_at' => $link->linked_at?->toIso8601String(),
            ],
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
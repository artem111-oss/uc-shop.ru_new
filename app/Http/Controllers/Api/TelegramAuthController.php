<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramLink;
use App\Models\User;
use App\Services\Auth\TelegramWidgetAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    public function __construct(
        private readonly TelegramWidgetAuthService $telegramWidgetAuthService
    ) {
    }

    public function login(Request $request): JsonResponse
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

        if ($botToken === '' || !$this->telegramWidgetAuthService->verify($validated, $botToken)) {
            return response()->json([
                'message' => 'Не удалось подтвердить вход через Telegram. Попробуйте ещё раз.',
            ], 422);
        }

        $telegramLink = TelegramLink::query()
            ->where('bot', 'uctyt')
            ->where('telegram_id', $validated['id'])
            ->with('user')
            ->first();

        if ($telegramLink) {
            $telegramLink->update([
                'telegram_username' => $validated['username'] ?? null,
                'telegram_first_name' => $validated['first_name'] ?? null,
                'linked_at' => now(),
            ]);

            $user = $telegramLink->user;
        } else {
            $name = trim(implode(' ', array_filter([
                $validated['first_name'] ?? null,
                $validated['last_name'] ?? null,
            ])));

            $user = User::query()->create([
                'name' => $name !== '' ? $name : 'Telegram user',
                'email' => 'telegram-' . $validated['id'] . '@users.uc-shop.ru',
                'password' => Str::random(64),
                'notify_email' => false,
                'notify_telegram' => true,
            ]);

            TelegramLink::query()->create([
                'user_id' => $user->id,
                'bot' => 'uctyt',
                'telegram_id' => $validated['id'],
                'telegram_username' => $validated['username'] ?? null,
                'telegram_first_name' => $validated['first_name'] ?? null,
                'linked_at' => now(),
            ]);
        }

        $token = $user->createToken('customer:telegram-web')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }
}
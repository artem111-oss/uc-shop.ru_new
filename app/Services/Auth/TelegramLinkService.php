<?php

namespace App\Services\Auth;

use App\Models\TelegramLink;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class TelegramLinkService
{
    private const TTL_MINUTES = 10;

    public function makeLinkToken(User $user, string $bot): string
    {
        $payload = [
            'user_id' => $user->id,
            'bot' => $bot,
            'nonce' => Str::random(16),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->timestamp,
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    public function resolveLinkToken(string $token): ?array
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($payload) || !isset($payload['expires_at'], $payload['user_id'], $payload['bot'])) {
            return null;
        }

        if ($payload['expires_at'] < now()->timestamp) {
            return null;
        }

        return $payload;
    }

    public function link(int $userId, string $bot, int $telegramId, ?string $username, ?string $firstName): TelegramLink
    {
        return TelegramLink::query()->updateOrCreate(
            ['bot' => $bot, 'telegram_id' => $telegramId],
            [
                'user_id' => $userId,
                'telegram_username' => $username,
                'telegram_first_name' => $firstName,
                'linked_at' => now(),
            ]
        );
    }
}
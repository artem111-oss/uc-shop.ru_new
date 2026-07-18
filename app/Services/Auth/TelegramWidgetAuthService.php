<?php

namespace App\Services\Auth;

class TelegramWidgetAuthService
{
    private const MAX_AUTH_AGE_SECONDS = 86400;

    public function verify(array $payload, string $botToken): bool
    {
        if (!isset($payload['hash'], $payload['id'], $payload['auth_date'])) {
            return false;
        }

        if ((now()->timestamp - (int) $payload['auth_date']) > self::MAX_AUTH_AGE_SECONDS) {
            return false;
        }

        $receivedHash = $payload['hash'];
        $checkPayload = $payload;
        unset($checkPayload['hash']);

        ksort($checkPayload);

        $dataCheckString = collect($checkPayload)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $computedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($computedHash, $receivedHash);
    }
}
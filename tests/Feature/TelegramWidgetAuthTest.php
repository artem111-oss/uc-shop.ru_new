<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramWidgetAuthTest extends TestCase
{
    use RefreshDatabase;

    private function buildValidPayload(string $botToken, ?int $authDate = null): array
    {
        $payload = [
            'id' => 123456789,
            'first_name' => 'Test',
            'username' => 'testuser',
            'auth_date' => $authDate ?? now()->timestamp,
        ];

        ksort($payload);

        $dataCheckString = collect($payload)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $payload['hash'] = hash_hmac('sha256', $dataCheckString, $secretKey);

        return $payload;
    }

    public function test_rejects_forged_hash(): void
    {
        $user = User::factory()->create();
        $botToken = config('services.telegram.bot_token') ?: 'test-bot-token';

        $payload = $this->buildValidPayload($botToken);
        $payload['hash'] = str_repeat('0', 64);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/account/telegram/widget-link', $payload)
            ->assertStatus(422);
    }

    public function test_accepts_valid_hash_and_creates_link(): void
    {
        $user = User::factory()->create();
        $botToken = config('services.telegram.bot_token') ?: 'test-bot-token';

        $payload = $this->buildValidPayload($botToken);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/account/telegram/widget-link', $payload)
            ->assertStatus(200)
            ->assertJsonPath('data.telegram_username', 'testuser');

        $this->assertDatabaseHas('telegram_links', [
            'user_id' => $user->id,
            'bot' => 'uctyt',
        ]);
    }

    public function test_rejects_expired_auth_date(): void
    {
        $user = User::factory()->create();
        $botToken = config('services.telegram.bot_token') ?: 'test-bot-token';

        $payload = $this->buildValidPayload($botToken, now()->subDays(2)->timestamp);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/account/telegram/widget-link', $payload)
            ->assertStatus(422);
    }
}
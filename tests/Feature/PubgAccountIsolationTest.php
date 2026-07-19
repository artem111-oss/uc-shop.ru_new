<?php

namespace Tests\Feature;

use App\Models\PubgAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PubgAccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_delete_other_users_pubg_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $account = PubgAccount::factory()->create([
            'user_id' => $userB->id,
            'pubg_id' => '512345678',
        ]);

        $this->actingAs($userA, 'sanctum')
            ->deleteJson("/api/account/pubg-accounts/{$account->id}")
            ->assertStatus(404);

        $this->assertNotNull(PubgAccount::find($account->id));
    }

    public function test_user_does_not_see_other_users_pubg_accounts(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        PubgAccount::factory()->create(['user_id' => $userB->id, 'pubg_id' => '512345678']);
        PubgAccount::factory()->create(['user_id' => $userA->id, 'pubg_id' => '512345679']);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/account/pubg-accounts')
            ->assertStatus(200);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['pubg_id' => '512345679']);
    }
}
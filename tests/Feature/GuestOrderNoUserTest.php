<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestOrderNoUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_order_has_null_user_id(): void
    {
        $product = Product::factory()->create(['price' => 100, 'product_kind' => 'uc']);

        $response = $this->postJson('/order/add', [
            'uid' => '512345678',
            'product_id' => $product->id,
            'email' => 'guest@example.com',
        ]);



        $this->assertDatabaseHas('orders', [
            'id' => $response->json('id'),
            'user_id' => null,
        ]);
    }

    public function test_authenticated_order_is_linked_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100, 'product_kind' => 'uc']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/order/add', [
            'uid' => '512345678',
            'product_id' => $product->id,
            'email' => $user->email,
        ]);



        $this->assertDatabaseHas('orders', [
            'id' => $response->json('id'),
            'user_id' => $user->id,
        ]);
    }
}
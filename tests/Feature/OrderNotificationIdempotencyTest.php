<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderPaymentConfirmed;
use App\Services\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderNotificationIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_order_receives_no_notification(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => null]);

        app(OrderNotificationService::class)->paymentConfirmed($order);

        Notification::assertNothingSent();
    }

    public function test_linked_user_receives_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['notify_email' => true]);
        $order = Order::factory()->create(['user_id' => $user->id]);

        app(OrderNotificationService::class)->paymentConfirmed($order);

        Notification::assertSentTo($user, OrderPaymentConfirmed::class);
    }

    public function test_repeated_delivered_call_does_not_throw(): void
    {
        Notification::fake();

        $user = User::factory()->create(['notify_email' => true]);
        $order = Order::factory()->create(['user_id' => $user->id, 'status_id' => 3]);

        $service = app(OrderNotificationService::class);
        $service->delivered($order);
        $service->delivered($order);

        Notification::assertSentToTimes($user, OrderDelivered::class, 2);
    }
}
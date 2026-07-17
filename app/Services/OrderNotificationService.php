<?php

namespace App\Services;

use App\Models\Order;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderDeliveryFailed;
use App\Notifications\OrderPaymentConfirmed;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    public function paymentConfirmed(Order $order): void
    {
        $this->safeSend($order, fn ($user) => $user->notify(new OrderPaymentConfirmed($order)));
    }

    public function delivered(Order $order): void
    {
        $this->safeSend($order, fn ($user) => $user->notify(new OrderDelivered($order)));
    }

    public function deliveryFailed(Order $order): void
    {
        $this->safeSend($order, fn ($user) => $user->notify(new OrderDeliveryFailed($order)));
    }

    private function safeSend(Order $order, callable $send): void
    {
        if (!$order->user_id) {
            return;
        }

        try {
            $user = $order->user ?? \App\Models\User::find($order->user_id);

            if ($user) {
                $send($user);
            }
        } catch (\Throwable $e) {
            Log::warning('OrderNotificationService: notify failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
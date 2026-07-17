<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pendingExpiryMinutes = 30;
    
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($pendingExpiryMinutes) {
                $query->where('status_id', '!=', 1)
                    ->orWhere(function ($q) use ($pendingExpiryMinutes) {
                        $q->where('status_id', 1)
                            ->where('created_at', '>=', now()->subMinutes($pendingExpiryMinutes));
                    });
            })
            ->with('product:id,name')
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'data' => $orders->getCollection()
                ->map(fn (Order $order): array => $this->orderData($order))
                ->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, int $orderId): JsonResponse
    {
        $pendingExpiryMinutes = 30;
    
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($pendingExpiryMinutes) {
                $query->where('status_id', '!=', 1)
                    ->orWhere(function ($q) use ($pendingExpiryMinutes) {
                        $q->where('status_id', 1)
                            ->where('created_at', '>=', now()->subMinutes($pendingExpiryMinutes));
                    });
            })
            ->with('product:id,name')
            ->find($orderId);
        
        if (!$order) {
            return response()->json([
                'message' => 'Заказ не найден.',
            ], 404);
        }

        return response()->json([
            'data' => $this->orderData($order),
        ]);
    }

    private function orderData(Order $order): array
    {
        return [
            'id' => $order->id,
            'created_at' => $order->created_at?->toIso8601String(),
            'completed_at' => $order->completed_at?->toIso8601String(),
            'price' => (int) $order->price,
            'currency' => 'RUB',
            'status' => $this->statusData($order),
            'product' => [
                'id' => $order->product_id,
                'name' => $order->product?->name,
            ],
            'pubg_id' => $order->game_id ?? $order->uid,
            'uc_amount' => $order->uc_amount,
        ];
    }

    private function statusData(Order $order): array
    {
        if ((int) $order->status_id === 3) {
            return [
                'code' => 'completed',
                'label' => 'Выполнен',
            ];
        }

        if ($order->payment_status === 'pending') {
            return [
                'code' => 'pending_payment',
                'label' => 'Ожидает оплаты',
            ];
        }

        if ($order->payment_status === 'failed') {
            return [
                'code' => 'failed',
                'label' => 'Ошибка оплаты или выдачи',
            ];
        }

        return [
            'code' => 'processing',
            'label' => 'В обработке',
        ];
    }
}
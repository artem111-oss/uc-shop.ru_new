<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * OrderController - Управление заказами
 * 
 * Этот контроллер используется для:
 * - Создания заказов (legacy)
 * - Просмотра статуса заказа
 * - Обработки платежных вебхуков
 * 
 * НОВЫЕ API ENDPOINTS находятся в PaymentController
 */
class OrderController extends Controller
{
    /**
     * Создание заказа (legacy - используется PaymentController::createOrder)
     * 
     * @param OrderRequest $request
     * @return Order
     */
    public function create(OrderRequest $request): Order
    {
        try {
            $product = Product::find((int)$request->input('product_id'));
            
            if (!$product) {
                Log::error('Product not found', ['product_id' => $request->input('product_id')]);
                throw new \Exception('Product not found');
            }

            $order = Order::create([
                'client_id' => 1,
                'status_id' => 1,
                'status' => 'pending',
                'price' => (float) $product->price,
                'product_id' => $product->id,
                'type_id' => 1,
                'user_id' => 1,
                'game_id' => $request->input('uid'),
                'uid' => $request->input('uid')
            ]);

            Log::info('Order created (legacy)', [
                'order_id' => $order->id,
                'game_id' => $order->game_id,
                'product_id' => $product->id
            ]);

            return $order;

        } catch (\Exception $e) {
            Log::error('Failed to create order', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Просмотр заказа пользователем
     * 
     * @param Request $request
     * @param string $id
     * @param string $uid
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function orderUser(Request $request, string $id, string $uid)
    {
        try {
            $order = Order::where('id', $id)
                ->select('id', 'product_id', 'price', 'uid', 'status', 'created_at', 'paid_at')
                ->firstOrFail();

            if ($order->uid !== $uid) {
                Log::warning('Unauthorized order access attempt', [
                    'order_id' => $id,
                    'provided_uid' => $uid
                ]);
                return redirect()->route('home');
            }

            $product = Product::find((int)$order->product_id);
            
            // Определение статуса для отображения
            $isPaid = $request->input('paid') === '1' || $order->status === 'completed';
            $isFailed = $request->input('failed') === '1' || $order->status === 'failed';

            return view('order', [
                'order' => $order,
                'product' => $product,
                'isPaid' => $isPaid,
                'isFailed' => $isFailed
            ]);

        } catch (\Exception $e) {
            Log::error('Order view error', ['error' => $e->getMessage()]);
            return redirect()->route('home');
        }
    }

    /**
     * Инициализация платежа через Platima (legacy)
     * ВАЖНО: Используйте PaymentController::initPayment вместо этого!
     * 
     * @param Request $request
     * @return JsonResponse
     * @deprecated Use PaymentController::initPayment
     */
    public function createPayment(Request $request): JsonResponse
    {
        try {
            $orderId = $request->input('order_id');
            
            if (!$orderId) {
                return response()->json(['error' => 'order_id отсутствует'], 400);
            }

            $order = Order::find($orderId);
            
            if (!$order) {
                return response()->json(['error' => 'Заказ не найден'], 404);
            }

            $product = Product::find($order->product_id);
            
            if (!$product) {
                return response()->json(['error' => 'Товар не найден'], 404);
            }

            // Делегирование в новый контроллер
            $paymentController = new PaymentController();
            return $paymentController->initPayment(
                new Request([
                    'order_id' => $orderId,
                    'amount' => (float) $product->price,
                    'game_id' => $order->game_id
                ])
            );

        } catch (\Exception $e) {
            Log::error('Payment creation error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Внутренняя ошибка'], 500);
        }
    }

    /**
     * Перенаправление на страницу успеха после оплаты
     * 
     * @param int $order_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paymentSuccess($order_id)
    {
        try {
            $order = Order::findOrFail($order_id);

            Log::info('Payment success redirect', [
                'order_id' => $order->id,
                'status' => $order->status
            ]);

            return redirect()->to("/order/{$order->id}/{$order->uid}?paid=1");

        } catch (\Exception $e) {
            Log::error('Payment success redirect error', ['error' => $e->getMessage()]);
            return redirect('/')->with('error', 'Ошибка при обработке оплаты');
        }
    }

    /**
     * Обработка вебхука от Platima (legacy)
     * ВАЖНО: Используйте PaymentController::handleWebhook вместо этого!
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @deprecated Use PaymentController::handleWebhook
     */
    public function handlePaymentCallback(Request $request)
    {
        try {
            Log::info('Platima callback (legacy)', [
                'headers' => $request->headers->all(),
                'body_preview' => substr($request->getContent(), 0, 500)
            ]);

            $paymentController = new PaymentController();
            return $paymentController->handleWebhook($request);

        } catch (\Exception $e) {
            Log::error('Webhook handling error', ['error' => $e->getMessage()]);
            return response('Error', 500);
        }
    }

    /**
     * Выполнение заказа - отправка UC в игру
     * 
     * @param Order $order
     * @return void
     * @deprecated Use PaymentController
     */
    private function executeOrder($order): void
    {
        try {
            $product = Product::find($order->product_id);

            if (!$product || empty($product->name)) {
                Log::error('executeOrder: product not found or empty', [
                    'order_id' => $order->id,
                    'product_id' => $order->product_id
                ]);
                return;
            }

            // Маппинг товаров
            $products = [
                "60 UC" => 1,
                "180 UC" => 2,
                "325 UC" => 3,
                "385 UC" => 4,
                "660 UC" => 5,
                "720 UC" => 6,
                "985 UC" => 7,
                "1320 UC" => 8,
                "1800 UC" => 9,
                "1920 UC" => 10,
                "3850 UC" => 11,
                "8100 UC" => 12,
                "16200 UC" => 13,
                "24300 UC" => 14,
                "32400 UC" => 15,
                "40500 UC" => 109
            ];

            $productName = trim($product->name);

            if (!isset($products[$productName])) {
                Log::error('executeOrder: unknown product name', [
                    'order_id' => $order->id,
                    'product_name' => $productName
                ]);
                return;
            }

            $itemId = $products[$productName];
            $pubgId = $order->game_id ?? $order->uid;

            if (empty($pubgId)) {
                Log::error('executeOrder: pubg_id not found', ['order_id' => $order->id]);
                return;
            }

            $payload = [
                'item_id' => $itemId,
                'quantity' => 1,
                'pubg_id' => $pubgId
            ];

            $url = rtrim(config('services.pubg_api.base_url'), '/') . '/api/v1/orders/';
            $apiKey = config('services.pubg_api.api_key');

            Log::info('executeOrder: sending to PUBG API', [
                'order_id' => $order->id,
                'item_id' => $itemId,
                'pubg_id' => $pubgId
            ]);

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->timeout(30)
            ->post($url, $payload);

            if ($response->successful()) {
                Log::info('executeOrder: success', [
                    'order_id' => $order->id,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::error('executeOrder: API error', [
                    'order_id' => $order->id,
                    'status_code' => $response->status(),
                    'response' => substr($response->body(), 0, 500)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('executeOrder: error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Отправить сообщение в Telegram (helper)
     * 
     * @param string $message
     * @return void
     */
    public function sendToTelegram(string $message): void
    {
        try {
            $telegramHelper = app(\App\Helpers\Telegram::class);
            $telegramHelper->sendMessage($message);
            Log::info('Telegram message sent', ['message_preview' => substr($message, 0, 100)]);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', ['error' => $e->getMessage()]);
        }
    }
}

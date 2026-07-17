<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\PlatimayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class PaymentController extends Controller
{
    private PlatimayService $platimaService;

    public function __construct(PlatimayService $platimaService)
    {
        $this->platimaService = $platimaService;
    }

    public function createOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'game_id' => ['required', 'string', 'min:4', 'max:20', 'regex:/^\d+$/'],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.uc' => ['required', 'integer', 'min:1'],
            'cart.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'cart.*.price' => ['required', 'integer', 'min:1'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $cart = $request->cart;
            
            // Формируем строку uc_amount из всех товаров корзины
            $ucAmountParts = [];
            $totalAmount = 0;
            $mainProductId = $cart[0]['product_id']; // Берем первый товар как основной
            
            foreach ($cart as $item) {
                $qty = $item['quantity'];
                $ucAmount = $item['uc'];
                $price = $item['price'];
                
                if ($qty > 1) {
                    $ucAmountParts[] = "{$ucAmount} UC x{$qty}";
                } else {
                    $ucAmountParts[] = "{$ucAmount} UC";
                }
                
                $totalAmount += $price * $qty;
            }
            
            $ucAmountStr = implode(', ', $ucAmountParts);

            $order = Order::create([
                'uid' => Str::uuid()->toString(),
                'game_id' => $request->game_id,
                'email' => $request->email,
                'product_id' => $mainProductId,
                'uc_amount' => $ucAmountStr,
                'price' => $totalAmount,
                'amount' => $totalAmount,
                'payment_status' => 'pending',
                'status_id' => 1,
                'type_id' => 1,
                'user_id' => auth()->id(),
                'client_id' => 1,
            ]);

            return response()->json([
                'success' => true,
                'id' => $order->id,
                'uid' => $order->uid,
                'message' => 'Order created successfully',
            ], 201);

        } catch (Exception $e) {
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'game_id' => $request->game_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
            ], 500);
        }
    }

    public function initPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'game_id' => ['required', 'string', 'min:4', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::with(['product'])->findOrFail($request->order_id);

            if ($order->payment_status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order already paid',
                ], 400);
            }

            $paymentData = $this->platimaService->createPayment(
                $order,
                $request->amount,
                $request->game_id
            );

            return response()->json([
                'success' => true,
                'uuid' => $paymentData['uuid'],
                'link' => $paymentData['link'],
                'message' => 'Payment initialized successfully',
            ], 200);

        } catch (Exception $e) {
            Log::error('Payment initialization failed', [
                'error' => $e->getMessage(),
                'order_id' => $request->order_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            $receivedSign = $payload['sign'] ?? $request->header('X-Platima-Signature');

            Log::info('Webhook received', [
                'ip' => $request->ip(),
                'payload' => $payload,
                'has_signature' => !empty($receivedSign)
            ]);

            if (!$receivedSign) {
                Log::warning('Webhook received without signature', [
                    'ip' => $request->ip(),
                    'payload_keys' => array_keys($payload)
                ]);
                return response('Missing signature', 401)->header('Content-Type', 'text/plain');
            }

            if (!$this->platimaService->verifyWebhookSignature($payload, $receivedSign)) {
                Log::warning('Webhook signature verification failed', [
                    'ip' => $request->ip(),
                    'payload' => $payload,
                    'received_sign' => substr($receivedSign, 0, 20) . '...'
                ]);
                return response('Invalid signature', 403)->header('Content-Type', 'text/plain');
            }

            $this->platimaService->processWebhook($payload);
            
            Log::info('Webhook processed successfully', [
                'order_id' => $payload['order_id'] ?? 'N/A',
                'uuid' => $payload['id'] ?? 'N/A'
            ]);

            // Platima требует ответ HTTP 200 с телом "ok" или "OK"
            return response('OK', 200)->header('Content-Type', 'text/plain');

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            return response('Internal error', 500)->header('Content-Type', 'text/plain');
        }
    }

    public function paymentSuccess(Request $request)
    {
        try {
            $orderId = $request->query('order_id');
            
            if (!$orderId) {
                return redirect()->route('home')->with('error', 'Order ID missing');
            }
            
            $order = Order::with(['product', 'payment'])->findOrFail($orderId);

            Log::info('Payment success page view', [
                'order_id' => $order->id,
                'status' => $order->payment_status
            ]);

            // Если webhook еще не обновил статус, проверяем вручную через API
            if ($order->payment_status === 'pending' && $order->payment) {
                try {
                    $payment = $order->payment;
                    $metadata = json_decode($payment->metadata, true);
                    $platimaOrderId = $metadata['platima_order_id'] ?? null;
                    
                    if ($platimaOrderId) {
                        Log::info('Checking payment status via API', [
                            'order_id' => $order->id,
                            'platima_uuid' => $payment->platima_uuid,
                            'platima_order_id' => $platimaOrderId
                        ]);

                        $status = $this->platimaService->checkPaymentStatus(
                            $payment->platima_uuid,
                            $platimaOrderId,
                            (int) config('platima.project_id')
                        );

                        // Обрабатываем ответ API
                        if (isset($status['status'])) {
                            $apiStatus = strtolower($status['status']);
                            
                            Log::info('Payment status from API', [
                                'order_id' => $order->id,
                                'api_status' => $apiStatus
                            ]);

                            if ($apiStatus === 'success' || $apiStatus === 'completed') {
                                // Обрабатываем как успешный платеж
                                $this->platimaService->processWebhook($status);
                                $order->refresh();
                            } elseif ($apiStatus === 'cancelled' || $apiStatus === 'failed' || $apiStatus === 'rejected') {
                                // Платеж отклонен
                                return redirect()->route('payment.failed', ['order_id' => $order->id]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Failed to check payment status', [
                        'error' => $e->getMessage(),
                        'order_id' => $order->id
                    ]);
                    // Продолжаем показывать страницу, даже если проверка не удалась
                }
            }

            return view('payment.success', [
                'order' => $order,
            ]);

        } catch (Exception $e) {
            Log::error('Payment success page error', ['error' => $e->getMessage()]);
            return redirect()->route('home')->with('error', 'Ошибка при загрузке страницы');
        }
    }

    public function paymentFailed(Request $request)
    {
        try {
            $orderId = $request->query('order_id');
            
            $order = null;
            if ($orderId) {
                $order = Order::with('product')->find($orderId);
            }

            Log::warning('Payment failed page view', [
                'order_id' => $orderId,
            ]);

            return view('payment.failed', [
                'order' => $order,
            ]);

        } catch (Exception $e) {
            Log::error('Payment failed page error', ['error' => $e->getMessage()]);
            return redirect()->route('home');
        }
    }

}

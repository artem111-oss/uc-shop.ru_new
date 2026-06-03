<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PlatimayService
{
    private ?string $apiUrl;
    private ?string $apiKey;
    private ?string $secretKey;
    private ?string $merchantId;
    private int $timeout;
    private int $retryAttempts;
    private int $retryDelay;


    public function __construct()
    {
        $this->apiUrl = config('platima.api_url', 'https://platimapayments.com/api/v1');
        $this->apiKey = config('platima.api_key');
        $this->secretKey = config('platima.secret_key', '');
        $this->merchantId = config('platima.project_id');
        $this->timeout = config('platima.timeout', 30);
        $this->retryAttempts = config('platima.retry_attempts', 3);
        $this->retryDelay = config('platima.retry_delay', 1000);
    }

    public function createPayment(Order $order, float $amount, string $gameId): array
    {
        $amountFormatted = number_format($amount, 2, '.', '');
        $currency = 'RUB';
        $orderId = 'ORDER_' . $order->id . '_' . time();
        
        // Signature: SHA512(api_key_project + order_id + project_id + amount + currency)
        $signString = $this->apiKey . $orderId . $this->merchantId . $amountFormatted . $currency;
        $signature = hash('sha512', $signString);
        
        $payload = [
            'project_id' => (int) $this->merchantId,
            'order_id' => $orderId,
            'amount' => (float) $amount,
            'currency' => $currency,
            'method' => 'sbp', // СБП (Система быстрых платежей)
            'success_url' => url('/payment/success?order_id=' . $order->id),
            'fail_url' => url('/payment/failed?order_id=' . $order->id),
            'return_url' => url('/'), // Кнопка "в магазин"
            'callback_url' => url('/api/payment/webhook'),
        ];

        Log::info('Creating Platima payment', [
    'order_id' => $order->id,
    'platima_order_id' => $orderId,
    'amount' => $amountFormatted,
    'signature_preview' => substr($signature, 0, 8) . '...',
]);

        $response = $this->makeRequestWithRetry('POST', '/acquiring', $payload, $signature);

        if (!isset($response['id']) || !isset($response['link'])) {
            Log::error('Invalid Platima response', ['response' => $response]);
            throw new Exception('Invalid response from Platima API');
        }

        Payment::create([
            'order_id' => $order->id,
            'platima_uuid' => $response['id'],
            'payment_link' => $response['link'],
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'metadata' => json_encode(array_merge($payload, ['platima_order_id' => $orderId])),
        ]);

        return [
            'uuid' => $response['id'],
            'link' => $response['link'],
        ];
    }

    public function checkPaymentStatus(string $uuid, string $orderId, int $projectId): array
    {
        $signString = $this->apiKey . $uuid . $orderId . $projectId;
        $signature = hash('sha512', $signString);
        
        $payload = [
            'project_id' => $projectId,
            'id' => $uuid,
            'order_id' => $orderId,
        ];
        
        return $this->makeRequestWithRetry('POST', '/getpayAcquiring', $payload, $signature);
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        // Callback signature: SHA256(api_key_project + id + order_id + project_id + amount + currency)
        $id = $payload['id'] ?? '';
        $orderId = $payload['order_id'] ?? '';
        $projectId = $payload['project_id'] ?? '';
        $amount = isset($payload['amount']) ? number_format($payload['amount'], 2, '.', '') : '';
        $currency = $payload['currency'] ?? '';
        
        $signString = $this->apiKey . $id . $orderId . $projectId . $amount . $currency;
        $computedSignature = hash('sha256', $signString);
        
        Log::info('Verifying webhook signature', [
            'sign_string_length' => strlen($signString), // Не логируем сам string (содержит API key)
            'computed' => substr($computedSignature, 0, 20) . '...',
            'received' => substr($signature, 0, 20) . '...'
        ]);
        
        return hash_equals($computedSignature, $signature);
    }

    public function processWebhook(array $webhookData): void
    {
        $uuid = $webhookData['id'] ?? null;
        $platimaOrderId = $webhookData['order_id'] ?? null;
        $amount = $webhookData['amount'] ?? null;
        $timestamp = $webhookData['timestamp'] ?? $webhookData['created_at'] ?? time();

        if (!$uuid || !$platimaOrderId) {
            throw new Exception('Webhook missing required fields');
        }

        // ===== REPLAY ATTACK PROTECTION =====
        // Отклоняем webhook старше 5 минут
        $maxAge = 300; // 5 minutes
        $currentTime = time();
        
        if (is_numeric($timestamp) && ($currentTime - $timestamp) > $maxAge) {
            Log::warning('Webhook rejected: too old (replay attack?)', [
                'uuid' => $uuid,
                'webhook_timestamp' => $timestamp,
                'current_time' => $currentTime,
                'age_seconds' => $currentTime - $timestamp
            ]);
            throw new Exception('Webhook expired (possible replay attack)');
        }

        Log::info('Processing webhook', [
            'uuid' => $uuid,
            'platima_order_id' => $platimaOrderId,
            'amount' => $amount,
            'timestamp' => $timestamp
        ]);

        // Находим payment по UUID или по metadata с platima_order_id
        $payment = Payment::where('platima_uuid', $uuid)->first();
        
        if (!$payment) {
            $payment = Payment::whereRaw("JSON_EXTRACT(metadata, '$.platima_order_id') = ?", [$platimaOrderId])->first();
        }

        if (!$payment) {
            Log::error('Payment not found for webhook', [
                'uuid' => $uuid,
                'platima_order_id' => $platimaOrderId
            ]);
            throw new Exception('Payment not found');
        }
        
        $order = $payment->order;

        // ===== IDEMPOTENCY CHECK (DUPLICATE WEBHOOK PROTECTION) =====
        if ($payment->status === 'completed') {
            Log::info('Webhook already processed (idempotent)', [
                'uuid' => $uuid,
                'order_id' => $order->id,
                'current_status' => $payment->status
            ]);
            // Не бросаем исключение, возвращаем успех (idempotent)
            return;
        }

        // Callback приходит только при успешной оплате согласно документации
        $payment->update([
            'platima_uuid' => $uuid,
            'status' => 'completed',
        ]);

        if (method_exists($payment, 'markAsPaid')) {
            $payment->markAsPaid();
        }
        
        if (method_exists($order, 'markAsCompleted')) {
            $order->markAsCompleted();
        } else {
            $order->update([
                'payment_status' => 'completed',
                'completed_at' => now()
            ]);
        }

        Log::info("Payment completed for Order #{$order->id}, UUID: {$uuid}");

        // Автоматически отправляем заказ на выполнение в Ragner Shop
        if (config('ragner_shop.auto_fulfill', true)) {
            try {
                $ragnerShop = app(\App\Services\RagnerShopService::class);
                $ragnerOrder = $ragnerShop->fulfillOrder($order);
                
                // Сохраняем ID заказа в Ragner Shop
                $order->update([
                    'external_order_id' => $ragnerOrder['order_id'] ?? ($ragnerOrder['id'] ?? null),
                    'external_status' => ($ragnerOrder['success'] ?? false) ? 'processing' : 'failed'
                ]);

                Log::info("Order #{$order->id} sent to Ragner Shop", [
                    'ragner_order_id' => $ragnerOrder['order_id'] ?? ($ragnerOrder['id'] ?? 'unknown'),
                    'success' => $ragnerOrder['success'] ?? false
                ]);

            } catch (Exception $e) {
                Log::error("Failed to fulfill order #{$order->id} via Ragner Shop", [
                    'error' => $e->getMessage()
                ]);
                // Не бросаем исключение, чтобы не блокировать webhook
            }
        }
    }

    private function makeRequestWithRetry(string $method, string $endpoint, array $data = [], ?string $signature = null): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ];
                
                if ($signature) {
                    $headers['Authorization'] = 'Bearer ' . $signature;
                }
                
                $http = Http::timeout($this->timeout)
                    ->withHeaders($headers)
                    ->withOptions(['verify' => false]); // Disable SSL verification for Windows
                
                $response = $http->send($method, $this->apiUrl . $endpoint, [
                    'json' => $data,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() >= 500) {
                    throw new Exception("Platima API error {$response->status()}: {$response->body()}");
                }

                throw new Exception("Platima API error {$response->status()}: {$response->body()}");

            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $this->retryAttempts) {
                    $delay = $this->retryDelay * pow(2, $attempt - 1);
                    usleep($delay * 1000);
                    Log::warning("Retrying Platima API request, attempt {$attempt}/{$this->retryAttempts}");
                }
            }
        }

        Log::error('Platima API request failed after retries', [
            'method' => $method,
            'endpoint' => $endpoint,
            'error' => $lastException?->getMessage(),
        ]);

        throw new Exception('Platima API request failed: ' . $lastException?->getMessage());
    }

    private function mapPlatimaStatus(string $platimaStatus): string
    {
        return match(strtoupper($platimaStatus)) {
            'SUCCESS', 'COMPLETED', 'PAID' => 'completed',
            'PENDING', 'WAITING', 'PROCESSING', 'NEW' => 'processing',
            'FAILED', 'ERROR' => 'failed',
            'CANCELLED', 'CANCELED', 'REJECTED' => 'cancelled',
            default => 'pending',
        };
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RagnerShopService
{
    private string $apiUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('ragner_shop.api_url', 'http://92.51.47.47');
        $this->apiKey = config('ragner_shop.api_key');
        $this->timeout = config('ragner_shop.timeout', 30);

        if (!$this->apiKey) {
            throw new Exception('Ragner Shop API key not configured');
        }
    }

    /**
     * Получить список доступных PUBG UC продуктов
     */
    public function getProducts(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->apiUrl . '/api/v1/products/pubg_uc/');

            if (!$response->successful()) {
                throw new Exception("Ragner Shop API error: {$response->status()} - {$response->body()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Failed to get Ragner Shop products', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Создать заказ на пополнение UC
     * 
     * @param string $pubgId Player ID
     * @param int $itemId ID товара из Ragner Shop
     * @param int $quantity Количество
     * @return array Response from API
     */
    public function createOrder(string $pubgId, int $itemId, int $quantity = 1): array
    {
        try {
            $payload = [
                'item_id' => $itemId,
                'quantity' => $quantity,
                'pubg_id' => $pubgId,
            ];

            Log::info('Creating Ragner Shop order', [
                'pubg_id' => $pubgId,
                'item_id' => $itemId,
                'quantity' => $quantity
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '/api/v1/orders/', $payload);

            if (!$response->successful()) {
                Log::error('Ragner Shop order creation failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'payload' => $payload
                ]);
                throw new Exception("Ragner Shop API error: {$response->status()} - {$response->body()}");
            }

            $result = $response->json();

            Log::info('Ragner Shop order created successfully', [
                'ragner_order_id' => $result['order_id'] ?? ($result['id'] ?? 'unknown'),
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? ''
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Failed to create Ragner Shop order', [
                'error' => $e->getMessage(),
                'pubg_id' => $pubgId,
                'item_id' => $itemId
            ]);
            throw $e;
        }
    }

    /**
     * Получить информацию о заказе
     * 
     * @param int $orderId ID заказа в Ragner Shop
     * @return array
     */
    public function getOrder(int $orderId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->apiUrl . "/api/v1/orders/{$orderId}/");

            if (!$response->successful()) {
                throw new Exception("Ragner Shop API error: {$response->status()} - {$response->body()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Failed to get Ragner Shop order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ]);
            throw $e;
        }
    }

    /**
     * Маппинг UC количества на item_id в Ragner Shop
     * Эти ID нужно получить из API или настроить вручную
     */
    public function getItemIdByUcAmount(int $ucAmount): ?int
    {
        // Маппинг UC -> item_id (нужно обновить после получения реальных ID из API)
        $mapping = [
            60 => 1,
            180 => 2,
            325 => 3,
            385 => 4,
            660 => 5,
            720 => 6,
            985 => 7,
            1320 => 8,
            1800 => 9,
            1920 => 10,
            3850 => 11,
            8100 => 12,
            16200 => 13,
            24300 => 14,
            32400 => 15,
            40500 => 109,
        ];

        return $mapping[$ucAmount] ?? null;
    }

    /**
     * Отправить заказ после успешной оплаты
     * 
     * @param Order $order Laravel Order модель
     * @return array
     */
    public function fulfillOrder(Order $order): array
    {
        $pubgId = $order->game_id;
        
        if (empty($pubgId)) {
            throw new Exception('PUBG ID not found in order');
        }

        // Парсим все товары из заказа
        $ucData = $this->parseUcAmountAndQuantity($order);
        
        if (empty($ucData['items'])) {
            throw new Exception('Cannot determine UC items from order');
        }

        $items = $ucData['items'];
        
        Log::info('Fulfilling order with items', [
            'order_id' => $order->id,
            'items' => $items
        ]);

        $results = [];
        $totalOrders = 0;
        
        // Проходим по каждому типу товара
        foreach ($items as $item) {
            $ucAmount = $item['uc_amount'];
            $quantity = $item['quantity'];
            
            $itemId = $this->getItemIdByUcAmount($ucAmount);
            
            if (!$itemId) {
                Log::error("No item_id mapping found", ['uc_amount' => $ucAmount]);
                continue;
            }

            // Ragner Shop не поддерживает quantity > 1, создаем отдельные заказы
            for ($i = 0; $i < $quantity; $i++) {
                try {
                    $totalOrders++;
                    $result = $this->createOrder($pubgId, $itemId, 1);
                    $results[] = $result;
                    
                    Log::info("Created Ragner Shop order for {$ucAmount} UC", [
                        'order_number' => $totalOrders,
                        'ragner_order_id' => $result['order_id'] ?? 'unknown',
                        'uc_amount' => $ucAmount
                    ]);
                    
                    // Небольшая задержка между заказами
                    usleep(500000); // 0.5 секунды
                    
                } catch (Exception $e) {
                    Log::error("Failed to create Ragner Shop order for {$ucAmount} UC", [
                        'order_number' => $totalOrders,
                        'error' => $e->getMessage()
                    ]);
                    // Продолжаем создавать остальные заказы
                }
            }
        }
        
        // Возвращаем результат последнего успешного заказа
        if (!empty($results)) {
            Log::info("Successfully created {$totalOrders} Ragner Shop orders for Order #{$order->id}");
            return end($results);
        }
        
        throw new Exception("Failed to create any Ragner Shop orders");
    }

    /**
     * Извлечь список UC товаров из заказа
     * Возвращает массив ['items' => [{uc_amount, quantity}, ...]]
     */
    public function parseUcAmountAndQuantity(Order $order): array
    {
        $items = [];

        // Если есть поле uc_amount
        if (!empty($order->uc_amount)) {
            // Парсим строки типа:
            // "60 UC" -> [{uc: 60, qty: 1}]
            // "60 UC x2" -> [{uc: 60, qty: 2}]
            // "60 UC, 180 UC" -> [{uc: 60, qty: 1}, {uc: 180, qty: 1}]
            // "60 UC x2, 325 UC" -> [{uc: 60, qty: 2}, {uc: 325, qty: 1}]
            
            Log::debug('Parsing uc_amount', [
                'order_id' => $order->id,
                'uc_amount' => $order->uc_amount
            ]);
            
            // Разбиваем по запятой
            $parts = explode(',', $order->uc_amount);
            
            foreach ($parts as $part) {
                $part = trim($part);
                // Regex: "60 UC x2" или "325 UC" (без x означает qty=1)
                if (preg_match('/(\d+)\s*UC(?:\s*x(\d+))?/i', $part, $matches)) {
                    $ucAmount = (int) $matches[1];
                    $quantity = isset($matches[2]) && !empty($matches[2]) ? (int) $matches[2] : 1;
                    
                    Log::debug('Parsed UC item', [
                        'part' => $part,
                        'uc_amount' => $ucAmount,
                        'quantity' => $quantity
                    ]);
                    
                    $items[] = [
                        'uc_amount' => $ucAmount,
                        'quantity' => $quantity
                    ];
                }
            }
            
            Log::info('Parsed UC items from uc_amount', [
                'order_id' => $order->id,
                'items_count' => count($items),
                'items' => $items
            ]);
        }

        // Если не нашли, пытаемся получить из product
        if (empty($items) && $order->product && !empty($order->product->name)) {
            if (preg_match('/(\d+)\s*UC/i', $order->product->name, $matches)) {
                $items[] = [
                    'uc_amount' => (int) $matches[1],
                    'quantity' => 1
                ];
                
                Log::info('Parsed UC from product name', [
                    'order_id' => $order->id,
                    'product_name' => $order->product->name,
                    'uc_amount' => (int) $matches[1]
                ]);
            }
        }

        if (empty($items)) {
            Log::warning('Failed to parse any UC items', [
                'order_id' => $order->id,
                'uc_amount' => $order->uc_amount,
                'product_name' => $order->product->name ?? null
            ]);
        }

        return ['items' => $items];
    }
}

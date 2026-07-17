<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function resolveOrderUserId(Request $request): int
    {
        return (int) ($request->user()?->id ?? 1);
    }
    public function create(OrderRequest $request)
    {
        $uid  = (string) $request->input('uid');
        $cart = $request->input('cart');

        // === МУЛЬТИ-КОРЗИНА ===
        if (is_array($cart) && count($cart) > 0) {
            $totalAmount    = 0;
            $ucAmountParts  = [];
            $mainProductId  = null;

            foreach ($cart as $item) {
                $product = Product::find((int) $item['product_id']);
                if (!$product) {
                    Log::error('Order create: product not found in cart', ['product_id' => $item['product_id']]);
                    return response()->json(['success' => false, 'message' => 'Product not found: ' . $item['product_id']], 404);
                }

                $qty   = max(1, (int) ($item['quantity'] ?? 1));
                $price = isset($item['price']) ? (float) $item['price'] : (float) $product->price;

                $totalAmount += $price * $qty;

                if ($mainProductId === null) {
                    $mainProductId = $product->id;
                }

                if ($product->product_kind === 'uc') {
                    preg_match('/(\d+)/', $product->name, $m);
                    $ucVal = $m[1] ?? trim($product->name);
                    $ucAmountParts[] = $qty > 1 ? "{$ucVal} UC x{$qty}" : "{$ucVal} UC";
                } else {
                    $ucAmountParts[] = $qty > 1 ? "{$product->name} x{$qty}" : $product->name;
                }
            }

            $order = new Order();
            $order->client_id   = 1;
            $order->status_id   = 1;
            $order->price       = $totalAmount;
            $order->qty         = 1;
            $order->product_id  = $mainProductId;
            $order->type_id     = 1;
            $order->user_id     = $this->resolveOrderUserId($request);
            $order->uid         = $uid;
            $order->game_id     = $uid;
            $order->email       = $request->input('email');
            $order->uc_amount   = implode(', ', $ucAmountParts);
            $order->save();

            Log::info('Order created', [
                'order_id'   => $order->id,
                'uid'        => $order->uid,
                'product_id' => $order->product_id,
            ]);

            return response()->json([
                'success' => true,
                'id'      => $order->id,
                'uid'     => $order->uid,
            ]);
        }

        // === ОДИНОЧНЫЙ ТОВАР (обратная совместимость) ===
        $product = Product::find((int) $request->input('product_id'));
        if (!$product) {
            Log::error('Order create: product not found', ['product_id' => $request->input('product_id')]);
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $qty = max(1, (int) $request->input('qty', 1));

        $order = new Order();
        $order->client_id   = 1;
        $order->status_id   = 1;
        $order->price       = $product->price * $qty;
        $order->qty         = $qty;
        $order->product_id  = $product->id;
        $order->type_id     = 1;
        $order->user_id     = $this->resolveOrderUserId($request);
        $order->uid         = $uid;
        $order->game_id     = $uid;
        $order->email       = $request->input('email');

        if ($product->product_kind === 'uc') {
            preg_match('/(\d+)/', $product->name, $m);
            $ucVal = $m[1] ?? trim($product->name);
            $order->uc_amount = $qty > 1 ? "{$ucVal} UC x{$qty}" : "{$ucVal} UC";
        } else {
            $order->uc_amount = $qty > 1 ? "{$product->name} x{$qty}" : $product->name;
        }

        $order->save();

        Log::info('Order created', [
            'order_id'   => $order->id,
            'uid'        => $order->uid,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $order->id,
            'uid'     => $order->uid,
        ]);
    }

    public function createPayment(Request $request)
    {
        $orderId  = $request->input('order_id');
        $orderIds = $request->input('order_ids', []);

        if (!$orderId) {
            return response()->json(['error' => 'order_id отсутствует'], 400);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['error' => 'Заказ не найден'], 404);
        }

        // Если передан массив заказов — суммируем цены всех
        $orderIds = $request->input('order_ids', []);
        if (!empty($orderIds) && count($orderIds) > 1) {
            $totalPrice = Order::whereIn('id', $orderIds)->sum('price');
        } else {
            $totalPrice = $order->price;
        }

        $product = Product::find($order->product_id);
        if (!$product) {
            return response()->json(['error' => 'Товар не найден'], 404);
        }

        $amount = number_format((float)$totalPrice, 2, '.', '');
        $currency = 'RUB';

        // 1) Попытка через Pally
        $pally      = config('services.pally');
        $pallyError = null;
        $pallyLink  = null;

        try {
            $pallyPayload = [
                'amount'                 => (string)$amount,
                'orderid'                => (string)$order->id,
                'description'            => 'Order ' . $order->id,
                'type'                   => 'normal',
                'shop_id'                => $pally['shop_id'],
                'currency'               => $currency,
                'currencyin'             => $currency,
                'custom'                 => (string)$order->uid,
                'payer_pays_commission'  => 0,
                'ttl'                    => 900,
                'success_url'            => $pally['success_url'] . '?order_id=' . $order->id,
                'fail_url'               => $pally['fail_url'] . '?order_id=' . $order->id,
                'payment_method'         => 'SBP',
            ];

            Log::info('Pally createPayment: запрос', ['order_id' => $order->id]);

            $pallyResp = Http::withToken($pally['token'])
                ->asForm()
                ->timeout(15)
                ->connectTimeout(5)
                ->post('https://pal24.pro/api/v1/bill/create', $pallyPayload);

            Log::info('Pally createPayment: ответ', [
                'status' => $pallyResp->status(),
                'body'   => mb_substr($pallyResp->body(), 0, 500),
            ]);

            if ($pallyResp->successful()) {
                $body    = $pallyResp->json();
                $success = $body['success'] ?? false;

                if ($success === true || $success === 'true') {
                    $pallyLink = $body['link_page_url'] ?? $body['link_url'] ?? null;
                }

                if (!$pallyLink) {
                    $pallyError = 'Pally: некорректный ответ';
                    Log::warning('Pally createPayment: неожиданный формат', ['body' => $body]);
                }
            } else {
                $pallyError = 'Pally HTTP ' . $pallyResp->status();
            }
        } catch (\Exception $e) {
            Log::error('Pally createPayment: exception', ['error' => $e->getMessage()]);
            $pallyError = 'Pally exception';
        }

        if ($pallyLink) {
            return response()->json([
                'link'       => $pallyLink,
                'payment_id' => null,
                'provider'   => 'pally',
            ]);
        }

        // 2) Fallback на Platima
        Log::warning('Pally недоступен, fallback на Platima', [
            'order_id'    => $order->id,
            'pally_error' => $pallyError,
        ]);

        $apiKeyProject = config('services.platima.api_key_project');
        $projectId     = (int)config('services.platima.project_id');
        $baseUrl       = rtrim(config('services.platima.base_url'), '/');

        $stringToSign = $apiKeyProject . (string)$order->id . (string)$projectId . $amount . $currency;
        $signature    = hash('sha512', $stringToSign);

        $payload = [
            'project_id'   => $projectId,
            'order_id'     => (string)$order->id,
            'amount'       => (float)$amount,
            'currency'     => $currency,
            'method'       => 'sbp',
            'success_url'  => route('payment.success'),
            'failed_url'   => url("/order/{$order->id}/{$order->uid}?failed=1"),
            'callback_url' => route('webhook.payment.callback'),
        ];

        Log::info('Platima createPayment: запрос', [
            'order_id'           => $order->id,
            'amount'             => $amount,
            'signature_preview'  => substr($signature, 0, 8) . '...',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $signature,
                'Content-Type'  => 'application/json',
            ])
            ->timeout(15)
            ->connectTimeout(5)
            ->post("{$baseUrl}/acquiring", $payload);

            Log::info('Platima createPayment: ответ', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);

            if ($response->successful()) {
                $data      = $response->json();
                $paymentId = $data['id'] ?? null;
                $link      = $data['link'] ?? null;

                if ($paymentId) {
                    Cache::put("platima:payment:{$paymentId}", [
                        'payment_id' => $paymentId,
                        'order_id'   => (string)$order->id,
                        'order_uid'  => $order->uid ?? null,
                        'amount'     => (float)$amount,
                        'currency'   => $currency,
                        'project_id' => $projectId,
                        'link'       => $link,
                        'created_at' => now()->toDateTimeString(),
                    ], now()->addHours(24));

                    return response()->json([
                        'link'       => $link,
                        'payment_id' => $paymentId,
                        'provider'   => 'platima',
                    ]);
                }

                Log::error('Platima createPayment: нет payment_id в ответе', ['response' => $data]);
                return response()->json(['error' => 'Ответ платёжного провайдера не содержит payment_id'], 500);
            }

            $errorMsg = $response->json()['error'] ?? 'HTTP ' . $response->status();
            Log::error('Platima createPayment: ошибка API', ['error' => $errorMsg]);
            return response()->json(['error' => 'Ошибка API: ' . $errorMsg], $response->status());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Platima connection error: ' . $e->getMessage());
            return response()->json(['error' => 'Платёжный шлюз недоступен'], 503);
        } catch (\Exception $e) {
            Log::error('Platima unexpected error: ' . $e->getMessage());
            return response()->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                return view('payment.success', [
                    'order'   => $order,
                    'product' => Product::find($order->product_id),
                ]);
            }
        }

        return view('payment.success', ['order' => null, 'product' => null]);
    }

    public function paymentFail(Request $request)
    {
        $orderId = $request->query('order_id');

        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
        }

        return view('payment.failed', [
            'order'   => $order,
            'product' => $order ? Product::find($order->product_id) : null,
        ]);
    }

    public function handlePallyCallback(Request $request)
    {
        Log::info('Pally callback: входящий запрос', ['data' => $request->all()]);

        $status  = $request->input('Status');
        $outSum  = $request->input('OutSum');
        $custom  = $request->input('custom');

        $order = Order::where('uid', $custom)
            ->where('price', (float)$outSum)
            ->orderByDesc('id')
            ->first();

        if (!$order) {
            Log::warning('Pally callback: заказ не найден', [
                'custom' => $custom,
                'outSum' => $outSum,
            ]);
            return response('ORDER NOT FOUND', 404);
        }

        if ($status === 'SUCCESS') {
            Log::info('Pally callback: SUCCESS', ['order_id' => $order->id]);

            $updated = DB::table('orders')
                ->where('id', $order->id)
                ->where('status_id', '!=', 3)
                ->update(['status_id' => 2]);

            if ($updated === 0) {
                Log::warning('Pally callback: заказ уже обрабатывается или выполнен', [
                    'order_id' => $order->id,
                ]);
                return response('OK', 200);
            }

            $this->executeOrder($order);
        } else {
            Log::info('Pally callback: статус не SUCCESS', [
                'order_id' => $order->id,
                'status'   => $status,
            ]);
        }

        return response('OK', 200);
    }

    public function handlePaymentCallback(Request $request)
    {
        Log::info('Platima callback: входящий запрос', [
            'body' => substr($request->getContent(), 0, 500),
        ]);

        $payload      = $request->all();
        $receivedSign = $payload['sign'] ?? $request->header('X-Platima-Signature');

        if (!$receivedSign) {
            Log::warning('Platima callback: нет подписи', ['ip' => $request->ip()]);
            return response('Missing signature', 401);
        }

        $apiKeyProject = config('services.platima.api_key_project');
        $projectId     = (int)config('services.platima.project_id');
        $orderId       = $payload['order_id'] ?? null;
        $amount        = $payload['amount'] ?? null;
        $currency      = $payload['currency'] ?? 'RUB';
        $status        = $payload['status'] ?? null;

        $stringToSign = $apiKeyProject . $orderId . $projectId . $amount . $currency . $status;
        $expectedSign = hash('sha512', $stringToSign);

        if (!hash_equals($expectedSign, $receivedSign)) {
            Log::warning('Platima callback: неверная подпись', ['ip' => $request->ip()]);
            return response('Invalid signature', 403);
        }

        if (strtolower($status) !== 'success') {
            Log::info('Platima callback: статус не success', [
                'order_id' => $orderId,
                'status'   => $status,
            ]);
            return response('OK', 200);
        }

        $cacheKey = "platima:payment:" . ($payload['id'] ?? '');
        $cached   = Cache::get($cacheKey);
        $resolvedOrderId = $cached['order_id'] ?? $orderId;

        $order = Order::find($resolvedOrderId);

        if (!$order) {
            Log::error('Platima callback: заказ не найден', ['order_id' => $resolvedOrderId]);
            return response('Order not found', 404);
        }

        $updated = DB::table('orders')
            ->where('id', $order->id)
            ->where('status_id', '!=', 3)
            ->update(['status_id' => 2]);

        if ($updated === 0) {
            Log::warning('Platima callback: заказ уже обрабатывается или выполнен', [
                'order_id' => $order->id,
            ]);
            return response('OK', 200);
        }

        $this->executeOrder($order);

        return response('OK', 200);
    }

    private function executeOrder($order): void
    {
        $order->refresh();

        if ($order->status_id === 3) {
            Log::warning('executeOrder: заказ уже выполнен, пропускаем', ['order_id' => $order->id]);
            return;
        }

        $product = Product::find($order->product_id);

        if ($product && $product->delivery_mode === 'manual') {
            Log::info('executeOrder: ручная выдача, пропускаем авто', [
                'order_id' => $order->id,
                'product_id' => $order->product_id,
                'product' => $product->name,
            ]);
            DB::table('orders')->where('id', $order->id)->update(['status_id' => 3]);
            $this->sendToTelegram("✋ РУЧНАЯ ВЫДАЧА\n\nЗаказ: #{$order->id}\nТовар: {$product->name}\nИгровой ID: {$order->uid}\nСумма: {$order->price}₽\n\nНужно передать скин вручную!");
            return;
        }

        if ($product && $product->api_title === 'BUNDLE_UPGRADE_X3') {
            Log::info('executeOrder: бандл Подъём х3, отправляем 3 отдельных заказа', ['order_id' => $order->id]);
            $itemsToSend = [
                ['name' => 'First Purchase Pack', 'product_id' => 112],
                ['name' => 'Upgradable Firearm Materials Pack', 'product_id' => 113],
                ['name' => 'Mythic Emblem Pack', 'product_id' => 114],
            ];
        } elseif (!$product || empty($product->name)) {
            Log::error('executeOrder: товар не найден', [
                'order_id' => $order->id,
                'product_id' => $order->product_id,
            ]);
            return;
        } else {
            // Актуальный маппинг product name -> Ragner product_id
            $products = [
                '60 UC'    => 50,
                '120 UC'   => 55,
                '180 UC'   => 56,
                '325 UC'   => 53,
                '385 UC'   => 57,
                '660 UC'   => 58,
                '720 UC'   => 59,
                '985 UC'   => 60,
                '1320 UC'  => 61,
                '1800 UC'  => 62,
                '1920 UC'  => 63,
                '2460 UC'  => 64,
                '3850 UC'  => 65,
                '5650 UC'  => 66,
                '8100 UC'  => 67,
                '9900 UC'  => 68,
                '12010 UC' => 69,
                '16200 UC' => 70,
                '24300 UC' => 71,
                '32400 UC' => 72,
                '40500 UC' => 73,
                '81000 UC' => 74,

                'Prime 1 Month'                     => 104,
                'Prime 3 Month'                     => 105,
                'Prime 6 Month'                     => 106,
                'Prime 12 Month'                    => 107,
                'Prime Plus 1 Month'                => 108,
                'Prime Plus 3 Month'                => 109,
                'Prime Plus 6 Month'                => 110,
                'Prime Plus 12 Month'               => 111,
                'First Purchase Pack'               => 112,
                'Upgradable Firearm Materials Pack' => 113,
                'Mythic Emblem Pack'                => 114,
                'Weekly Mythic Emblem Value Pack'   => 115,
                'Weekly Deal Pack 2'                => 116,
                'Weekly Deal Pack 1'                => 117,

                'EVO (Radiant Gold)' => 155,
                'EVO (Glacier)'      => 156,
            ];

            // === МУЛЬТИ-ТОВАР ИЗ uc_amount ===
            $itemsToSend = [];

            if (!empty($order->uc_amount)) {
                foreach (explode(',', $order->uc_amount) as $part) {
                    $part = trim($part);
                    if (preg_match('/(\d+)\s*UC(?:\s*x(\d+))?/i', $part, $pm)) {
                        $ucVal = (int) $pm[1];
                        $qty = isset($pm[2]) && $pm[2] > 0 ? (int) $pm[2] : 1;
                        $lookupName = $ucVal . ' UC';
                        if (isset($products[$lookupName])) {
                            for ($i = 0; $i < $qty; $i++) {
                                $itemsToSend[] = ['name' => $lookupName, 'product_id' => $products[$lookupName]];
                            }
                        } else {
                            Log::error('executeOrder: товар не найден в маппинге', ['uc_amount_part' => $part]);
                        }
                    }
                }
            }

            // Fallback — одиночный товар из product_id
            if (empty($itemsToSend)) {
                $productName = trim($product->api_title ?: $product->name);
                if (!isset($products[$productName])) {
                    Log::error('executeOrder: товар не найден в маппинге', [
                        'order_id' => $order->id,
                        'product_name' => $productName,
                    ]);
                    return;
                }
                $qty = $order->qty ?? 1;
                for ($i = 0; $i < $qty; $i++) {
                    $itemsToSend[] = ['name' => $productName, 'product_id' => $products[$productName]];
                }
            }
        }

        $pubgId = $order->uid;
        if (empty($pubgId)) {
            Log::error('executeOrder: пустой PUBG UID', ['order_id' => $order->id]);
            return;
        }

        $apiKey = config('services.ragner.key');
        $validateUrl = 'https://ragnergiftcard.com/api/v1/validate-player';
        $validatedProductIds = [];
        $allSuccess = true;
        $lastExternalId = null;

        foreach ($itemsToSend as $idx => $item) {
            if (!in_array($item['product_id'], $validatedProductIds, true)) {
                Log::info('executeOrder: validate-player', [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'pubg_id' => $pubgId,
                    'item_index' => $idx,
                    'product_name' => $item['name'],
                ]);

                $validateResp = Http::withHeaders([
                    'X-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->timeout(15)->post($validateUrl, [
                    'product_id' => $item['product_id'],
                    'player_id' => $pubgId,
                ]);

                if (!$validateResp->successful()) {
                    $allSuccess = false;
                    Log::error('executeOrder: validate-player failed', [
                        'order_id' => $order->id,
                        'item_index' => $idx,
                        'product_name' => $item['name'],
                        'product_id' => $item['product_id'],
                        'status' => $validateResp->status(),
                        'response' => substr($validateResp->body(), 0, 300),
                    ]);
                    continue;
                }

                $validatedProductIds[] = $item['product_id'];
                Log::info('executeOrder: validate-player OK', [
                    'order_id' => $order->id,
                    'item_index' => $idx,
                    'product_name' => $item['name'],
                    'product_id' => $item['product_id'],
                ]);
            }

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://ragnergiftcard.com/api/v1/order', [
                'product_id' => $item['product_id'],
                'qty' => 1,
                'player_id' => $pubgId,
            ]);

            if ($response->successful()) {
                $lastExternalId = $response->json()['data']['order_id'] ?? null;
                Log::info('executeOrder: заказ выполнен', [
                    'order_id' => $order->id,
                    'item_index' => $idx,
                    'product_name' => $item['name'],
                    'external_order_id' => $lastExternalId,
                ]);
            } else {
                $allSuccess = false;
                Log::error('executeOrder: Ragner вернул ошибку', [
                    'order_id' => $order->id,
                    'item_index' => $idx,
                    'product_name' => $item['name'],
                    'status' => $response->status(),
                    'response' => substr($response->body(), 0, 300),
                ]);
            }

            if ($idx < count($itemsToSend) - 1) {
                usleep(300000);
            }
        }

        if ($allSuccess) {
            DB::table('orders')->where('id', $order->id)->update([
                'status_id' => 3,
                'updated_at' => now(),
            ]);
            Log::info('executeOrder: заказ полностью выполнен', [
                'order_id' => $order->id,
                'external_order_id' => $lastExternalId,
            ]);
        } else {
            Log::warning('executeOrder: заказ выполнен частично или с ошибками', ['order_id' => $order->id]);
        }

        $productSummary = implode(', ', array_unique(array_column($itemsToSend, 'name')));

        if ($allSuccess) {
            $this->sendToTelegram(
                "✅ **Заказ выполнен**\n\n" .
                "🆔 Заказ: **#" . $order->id . "**\n" .
                "🎮 PUBG ID: **" . $order->uid . "**\n" .
                "📦 Товар: **" . $productSummary . "**\n" .
                "💰 Сумма: **" . number_format($order->price, 0, ',', ' ') . " ₽**\n" .
                "⏱ Время: " . now()->format('d.m.Y H:i:s')
            );
        } else {
            $this->sendToTelegram(
                "❌ **Заказ НЕ выполнен**\n\n" .
                "🆔 Заказ: **#" . $order->id . "**\n" .
                "🎮 PUBG ID: **" . $order->uid . "**\n" .
                "📦 Товар: **" . $productSummary . "**\n" .
                "💰 Сумма: **" . number_format($order->price, 0, ',', ' ') . " ₽**\n" .
                "⏱ Время: " . now()->format('d.m.Y H:i:s') . "\n\n" .
                "⚠️ **Причина:** ошибка выдачи через Ragner. Проверь логи и баланс."
            );
        }
    }

    public function sendToTelegram(string $message): void
    {
        try {
            $chatId = config('services.telegram.chat_id');
            app(\App\Helpers\Telegram::class)->sendMessage($chatId, $message);
        } catch (\Exception $e) {
            Log::error('Telegram send error', ['error' => $e->getMessage()]);
        }
    }
    public function orderUser(Request $request, $id, $uid)
{
    $order = Order::where('id', $id)->where('uid', $uid)->firstOrFail();
    $product = Product::find($order->product_id);

    return view('order', [
        'order'   => $order,
        'product' => $product,
    ]);
}
} // закрывает класс
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating Order #29517...\n\n";

$order = App\Models\Order::find(29517);

if (!$order) {
    echo "Order not found!\n";
    exit;
}

echo "Current uc_amount: {$order->uc_amount}\n";
echo "Current amount: {$order->amount}\n";
echo "Payment amount: " . ($order->payment ? $order->payment->amount : 'N/A') . "\n\n";

// Обновляем uc_amount чтобы отразить 2 товара
$order->uc_amount = "60 UC x2";
$order->save();

echo "✅ Updated uc_amount to: {$order->uc_amount}\n\n";

// Теперь отправим второй заказ в Ragner Shop
echo "Creating additional Ragner Shop order for second 60 UC...\n";

try {
    $ragnerService = app(App\Services\RagnerShopService::class);
    
    // Создаем второй заказ на 60 UC
    $result = $ragnerService->createOrder($order->game_id, 1, 1); // item_id=1 это 60UC
    
    echo "✅ Second order created!\n";
    echo "Order ID: " . ($result['order_id'] ?? 'unknown') . "\n";
    echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}
